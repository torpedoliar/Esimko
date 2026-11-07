package com.esimko.mobile.data.repository

import android.content.ContentValues
import android.content.Context
import android.net.Uri
import android.os.Build
import android.os.Environment
import android.provider.MediaStore
import androidx.core.content.FileProvider
import dagger.hilt.android.qualifiers.ApplicationContext
import java.io.File
import javax.inject.Inject

/** All calls are made on Dispatchers.IO. No legacy storage permission is required. */
class ReceiptFileStore @Inject constructor(@ApplicationContext private val context: Context) {
    fun temporary(): File {
        val directory = File(context.cacheDir, "receipts").apply { mkdirs() }
        return File.createTempFile("receipt-", ".pdf", directory)
    }

    fun publish(file: File, name: String, beforePublish: () -> Unit): SavedReceipt {
        if (Build.VERSION.SDK_INT < Build.VERSION_CODES.Q) {
            beforePublish()
            val target = File(file.parentFile, name)
            check(file.renameTo(target)) { "Gagal menyiapkan bukti. Coba lagi." }
            try {
                return SavedReceipt(FileProvider.getUriForFile(context, "${context.packageName}.provider", target).toString(), false)
            } catch (e: Exception) {
                target.delete()
                throw e
            }
        }
        val resolver = context.contentResolver
        val values = ContentValues().apply {
            put(MediaStore.Downloads.DISPLAY_NAME, name)
            put(MediaStore.Downloads.MIME_TYPE, "application/pdf")
            put(MediaStore.Downloads.RELATIVE_PATH, "${Environment.DIRECTORY_DOWNLOADS}/eSIMKO")
            put(MediaStore.Downloads.IS_PENDING, 1)
        }
        val uri = resolver.insert(MediaStore.Downloads.EXTERNAL_CONTENT_URI, values)
            ?: error("Gagal menyimpan bukti. Periksa ruang penyimpanan.")
        try {
            resolver.openOutputStream(uri)?.use { output -> file.inputStream().use { it.copyTo(output) } }
                ?: error("Gagal menulis bukti. Periksa ruang penyimpanan.")
            beforePublish() // Recheck after the potentially long storage copy, while still pending.
            check(resolver.update(uri, ContentValues().apply { put(MediaStore.Downloads.IS_PENDING, 0) }, null, null) > 0)
            return SavedReceipt(uri.toString(), true)
        } catch (e: Exception) {
            runCatching { resolver.delete(uri, null, null) }
            throw e
        }
    }

    fun discard(receipt: SavedReceipt) {
        if (receipt.inDownloads) context.contentResolver.delete(Uri.parse(receipt.uri), null, null)
        else {
            val name = Uri.parse(receipt.uri).lastPathSegment ?: return
            File(context.cacheDir, "receipts/$name").delete()
        }
    }
}

data class SavedReceipt(val uri: String, val inDownloads: Boolean, val sessionRevision: Long = -1)
