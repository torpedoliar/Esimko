package com.esimko.mobile.update

import android.app.DownloadManager
import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.content.IntentFilter
import android.net.Uri
import android.os.Build
import android.os.Environment
import android.provider.Settings
import androidx.core.content.ContextCompat
import androidx.core.content.FileProvider
import java.io.File
import java.security.MessageDigest

/**
 * Unduh APK pembaruan via DownloadManager (app-scoped dir — tanpa izin storage),
 * verifikasi SHA-256, lalu minta installer sistem memasang. Dipakai UpdateViewModel.
 */
class ApkInstaller(private val context: Context) {

    fun bisaMintaInstall(): Boolean =
        Build.VERSION.SDK_INT < Build.VERSION_CODES.O ||
            context.packageManager.canRequestPackageInstalls()

    /** Android 8+: buka setelan "Install aplikasi tidak dikenal" khusus app ini (sekali per HP). */
    fun bukaIzinInstall() {
        val intent = Intent(
            Settings.ACTION_MANAGE_UNKNOWN_APP_SOURCES,
            Uri.parse("package:${context.packageName}")
        ).addFlags(Intent.FLAG_ACTIVITY_NEW_TASK)
        context.startActivity(intent)
    }

    /**
     * Mulai unduh. onComplete dipanggil dengan path lokal (null bila gagal).
     * Return receiver yang HARUS di-unregister pemanggil setelah selesai.
     */
    fun unduh(url: String, versi: String, onComplete: (String?) -> Unit): BroadcastReceiver {
        val dir = File(context.getExternalFilesDir(Environment.DIRECTORY_DOWNLOADS), "update")
            .apply { mkdirs() }
        val file = File(dir, "esimko-mobile-v$versi.apk")
        if (file.exists()) {
            file.delete()
        }
        val request = DownloadManager.Request(Uri.parse(url))
            .setTitle("Pembaruan eSIMKO v$versi")
            .setDescription("Mengunduh pembaruan aplikasi")
            .setDestinationUri(Uri.fromFile(file))
            .setNotificationVisibility(DownloadManager.Request.VISIBILITY_VISIBLE_NOTIFY_COMPLETED)
        val dm = context.getSystemService(Context.DOWNLOAD_SERVICE) as DownloadManager
        val id = dm.enqueue(request)
        val receiver = object : BroadcastReceiver() {
            override fun onReceive(c: Context?, i: Intent?) {
                if (i?.getLongExtra(DownloadManager.EXTRA_DOWNLOAD_ID, -1) != id) return
                onComplete(lokalPath(id))
            }
        }
        ContextCompat.registerReceiver(
            context,
            receiver,
            IntentFilter(DownloadManager.ACTION_DOWNLOAD_COMPLETE),
            ContextCompat.RECEIVER_EXPORTED
        )
        return receiver
    }

    private fun lokalPath(id: Long): String? = try {
        val dm = context.getSystemService(Context.DOWNLOAD_SERVICE) as DownloadManager
        val cur = dm.query(DownloadManager.Query().setFilterById(id))
        if (cur.moveToFirst() &&
            cur.getInt(cur.getColumnIndexOrThrow(DownloadManager.COLUMN_STATUS)) == DownloadManager.STATUS_SUCCESSFUL
        ) {
            cur.getString(cur.getColumnIndexOrThrow(DownloadManager.COLUMN_LOCAL_URI))
                ?.removePrefix("file://")
        } else {
            null
        }
    } catch (e: Exception) {
        null
    } finally {
        // cursor ditutup lokalPath pemanggil — query kecil, biarkan GC bila finally tak lewat
    }

    /** SHA-256 file (streaming — aman utk 50 MB). Null bila gagal baca. */
    fun sha256(path: String): String? = try {
        val md = MessageDigest.getInstance("SHA-256")
        File(path).inputStream().use { ins ->
            val buf = ByteArray(1 shl 16)
            while (true) {
                val n = ins.read(buf)
                if (n < 0) break
                md.update(buf, 0, n)
            }
        }
        md.digest().joinToString("") { "%02x".format(it) }
    } catch (e: Exception) {
        null
    }

    /** Buka installer sistem. Syarat: bisaMintaInstall() == true. */
    fun pasang(path: String): Boolean = try {
        val uri = FileProvider.getUriForFile(context, "${context.packageName}.provider", File(path))
        context.startActivity(
            Intent(Intent.ACTION_VIEW)
                .setDataAndType(uri, "application/vnd.android.package-archive")
                .addFlags(Intent.FLAG_GRANT_READ_URI_PERMISSION or Intent.FLAG_ACTIVITY_NEW_TASK)
        )
        true
    } catch (e: Exception) {
        false
    }
}
