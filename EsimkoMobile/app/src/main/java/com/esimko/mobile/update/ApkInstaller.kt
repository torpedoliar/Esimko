package com.esimko.mobile.update

import android.app.DownloadManager
import android.content.Context
import android.content.ClipData
import kotlinx.coroutines.currentCoroutineContext
import kotlinx.coroutines.ensureActive
import android.content.Intent
import android.net.Uri
import android.os.Environment
import android.provider.Settings
import androidx.core.content.FileProvider
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.delay
import kotlinx.coroutines.withContext
import java.io.File
import java.security.MessageDigest

/** Unduh persisten via DownloadManager; hasil selalu diperiksa sebelum membuka installer. */
class ApkInstaller(private val context: Context) {
    private val prefs = context.getSharedPreferences("apk_download", Context.MODE_PRIVATE)
    private val dm = context.getSystemService(Context.DOWNLOAD_SERVICE) as DownloadManager

    fun bisaMintaInstall(): Boolean = context.packageManager.canRequestPackageInstalls()

    fun bukaIzinInstall() {
        context.startActivity(
            Intent(Settings.ACTION_MANAGE_UNKNOWN_APP_SOURCES, Uri.parse("package:${context.packageName}"))
                .addFlags(Intent.FLAG_ACTIVITY_NEW_TASK)
        )
    }

    // Query status, bukan mengandalkan broadcast yang dapat terlewat/diblokir ROM OEM.
    // ID disimpan agar rotasi/proses mati tidak memicu unduhan duplikat.
    suspend fun unduh(url: String, versi: String, releaseKey: String): String? = withContext(Dispatchers.IO) {
        require(validUpdateUrl(url)) { "URL pembaruan tidak aman." }
        var id = prefs.getLong("id", -1L).takeIf { prefs.getString("release", null) == releaseKey } ?: -1L
        if (id == -1L || status(id) == null || status(id)?.first == DownloadManager.STATUS_FAILED) {
            hapusUnduhan()
            val root = context.getExternalFilesDir(Environment.DIRECTORY_DOWNLOADS)
                ?: error("Penyimpanan pembaruan tidak tersedia.")
            val dir = File(root, "update").apply { mkdirs() }
            // Never interpolate remote version text into a filesystem path.
            val file = File(dir, "esimko-mobile.apk")
            if (file.exists()) file.delete()
            id = dm.enqueue(
                DownloadManager.Request(Uri.parse(url))
                    .setTitle("Pembaruan eSIMKO v$versi")
                    .setDescription("Setelah selesai, kembali ke eSIMKO untuk memasang pembaruan")
                    .setMimeType("application/vnd.android.package-archive")
                    .setDestinationUri(Uri.fromFile(file))
                    .setNotificationVisibility(DownloadManager.Request.VISIBILITY_VISIBLE_NOTIFY_COMPLETED)
            )
            prefs.edit().putLong("id", id).putString("release", releaseKey).apply()
        }
        var path: String? = null
        while (true) {
            currentCoroutineContext().ensureActive()
            val result = status(id) ?: break
            if (result.first == DownloadManager.STATUS_SUCCESSFUL) {
                path = result.second
                break
            }
            if (result.first == DownloadManager.STATUS_FAILED) break
            delay(750)
        }
        path
    }

    fun adaUnduhan(releaseKey: String): Boolean =
        prefs.getString("release", null) == releaseKey && prefs.getLong("id", -1L) != -1L

    fun hapusUnduhan() {
        val id = prefs.getLong("id", -1L)
        if (id != -1L) dm.remove(id)
        prefs.edit().clear().apply()
    }

    private fun status(id: Long): Pair<Int, String?>? =
        dm.query(DownloadManager.Query().setFilterById(id))?.use { cursor ->
            if (!cursor.moveToFirst()) null else {
                val state = cursor.getInt(cursor.getColumnIndexOrThrow(DownloadManager.COLUMN_STATUS))
                val uri = cursor.getString(cursor.getColumnIndexOrThrow(DownloadManager.COLUMN_LOCAL_URI))
                state to uri?.let { Uri.parse(it).path }
            }
        }

    suspend fun sha256(path: String): String = File(path).inputStream().use { input ->
        val md = MessageDigest.getInstance("SHA-256")
        val buffer = ByteArray(1 shl 16)
        while (true) {
            currentCoroutineContext().ensureActive()
            val n = input.read(buffer)
            if (n < 0) break
            md.update(buffer, 0, n)
        }
        md.digest().joinToString("") { "%02x".format(it) }
    }

    fun paketValid(path: String, versi: String?, code: Int): Boolean {
        val archive = context.packageManager.getPackageArchiveInfo(path, 0) ?: return false
        return validUpdatePackage(
            archive.packageName, archive.versionName, archive.longVersionCode,
            context.packageName, versi, code.toLong(), com.esimko.mobile.BuildConfig.VERSION_CODE.toLong()
        )
    }

    /** Activity yang benar-benar RESUMED membuka dialog konfirmasi Android (bukan silent install). */
    fun pasang(path: String, foregroundContext: Context): Boolean = try {
        val uri = FileProvider.getUriForFile(context, "${context.packageName}.provider", File(path))
        foregroundContext.startActivity(
            Intent(Intent.ACTION_VIEW)
                .setDataAndType(uri, "application/vnd.android.package-archive")
                .addFlags(Intent.FLAG_GRANT_READ_URI_PERMISSION)
                .apply { clipData = ClipData.newRawUri("Pembaruan eSIMKO", uri) }
        )
        true
    } catch (_: Exception) {
        false
    }
}
