package com.esimko.mobile.data.repository

import com.esimko.mobile.data.local.TokenStore
import com.esimko.mobile.data.remote.api.ReceiptApi
import com.esimko.mobile.util.readBounded
import kotlinx.coroutines.CancellationException
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.NonCancellable
import kotlinx.coroutines.currentCoroutineContext
import kotlinx.coroutines.ensureActive
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.withContext
import okhttp3.ResponseBody
import java.io.File
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class ReceiptRepository @Inject constructor(
    private val api: ReceiptApi,
    private val tokenStore: TokenStore,
    private val files: ReceiptFileStore
) {
    private val revision = MutableStateFlow(tokenStore.sessionRevision)
    val sessionRevision = revision.asStateFlow()

    init {
        tokenStore.addSessionListener { revision.value = tokenStore.sessionRevision }
    }

    suspend fun isCurrent(receipt: SavedReceipt): Boolean = withContext(Dispatchers.IO) {
        synchronized(tokenStore) { tokenStore.token != null && tokenStore.sessionRevision == receipt.sessionRevision }
    }

    suspend fun download(jenis: String, id: Long): SavedReceipt {
        var published: SavedReceipt? = null
        try {
            return withContext(Dispatchers.IO) {
        require(jenis in setOf("transaksi", "penjualan", "angsuran") && id > 0)
        val owner = synchronized(tokenStore) {
            check(!tokenStore.token.isNullOrBlank()) { "Sesi berakhir. Silakan masuk kembali." }
            tokenStore.sessionRevision
        }
        var temporary: File? = null
        var saved: SavedReceipt? = null
        try {
            val response = api.download(jenis, id)
            if (!response.isSuccessful) {
                response.errorBody()?.close()
                error(when (response.code()) {
                    401 -> "Sesi berakhir. Silakan masuk kembali."
                    403, 404 -> "Bukti belum tersedia atau tidak dapat diakses. Muat ulang rincian."
                    else -> "Gagal mengunduh bukti. Coba lagi."
                })
            }
            val body = response.body() ?: error("Bukti kosong. Coba lagi.")
            body.use {
                temporary = files.temporary()
                copyValidatedPdf(it, temporary!!)
            }
            currentCoroutineContext().ensureActive()
            checkOwner(owner)
            val downloadContext = currentCoroutineContext()
            saved = files.publish(temporary!!, "eSIMKO-$jenis-$id-${java.util.UUID.randomUUID()}.pdf") {
                downloadContext.ensureActive()
                checkOwner(owner)
            }.copy(sessionRevision = owner)
            published = saved
            currentCoroutineContext().ensureActive()
            checkOwner(owner)
            saved!!
        } catch (e: CancellationException) {
            saved?.let { runCatching { files.discard(it) } }
            throw e
        } catch (e: Exception) {
            saved?.let { runCatching { files.discard(it) } }
            throw e
        } finally {
            temporary?.delete()
        }
            }
        } catch (e: CancellationException) {
            // withContext can cancel while delivering a completed IO result.
            withContext(NonCancellable + Dispatchers.IO) {
                published?.let { runCatching { files.discard(it) } }
            }
            throw e
        }
    }

    /** Lampiran bukti unggahan anggota: ambil byte dengan klien ber-token, batas ukuran aman. */
    suspend fun loadLampiran(id: Long): ByteArray = withContext(Dispatchers.IO) {
        require(id > 0)
        synchronized(tokenStore) { check(!tokenStore.token.isNullOrBlank()) { "Sesi berakhir. Silakan masuk kembali." } }
        val response = api.lampiran(id)
        if (!response.isSuccessful) {
            response.errorBody()?.close()
            error(when (response.code()) {
                401 -> "Sesi berakhir. Silakan masuk kembali."
                403, 404 -> "Lampiran tidak tersedia atau bukan milik akun ini."
                else -> "Gagal memuat lampiran. Coba lagi."
            })
        }
        val body = response.body() ?: error("Lampiran kosong.")
        body.use { it.source().inputStream().readBounded(MAX_LAMPIRAN_BYTES) }
    }

    private fun checkOwner(owner: Long) {
        synchronized(tokenStore) {
            check(tokenStore.token != null && tokenStore.sessionRevision == owner) {
                "Sesi berubah. Bukti dibatalkan; silakan unduh kembali."
            }
        }
    }
}

internal const val MAX_RECEIPT_BYTES = 10L * 1024 * 1024
internal const val MAX_LAMPIRAN_BYTES = 8 * 1024 * 1024

/** Validate without buffering the whole response or trusting Content-Length. */
internal suspend fun copyValidatedPdf(body: ResponseBody, target: File) {
    check(body.contentType()?.let { it.type == "application" && it.subtype == "pdf" } == true) {
        "Bukti bukan PDF yang valid. Coba lagi."
    }
    check(body.contentLength() <= MAX_RECEIPT_BYTES) { "Ukuran bukti melebihi 10 MB." }
    body.source().inputStream().use { input ->
        target.outputStream().use { output ->
            val magic = ByteArray(5)
            var position = 0
            while (position < magic.size) {
                currentCoroutineContext().ensureActive()
                val count = input.read(magic, position, magic.size - position)
                check(count > 0) { "Bukti PDF tidak lengkap." }
                position += count
            }
            check(magic.contentEquals("%PDF-".toByteArray(Charsets.US_ASCII))) { "Bukti bukan PDF yang valid." }
            output.write(magic)
            var total = magic.size.toLong()
            val buffer = ByteArray(8192)
            while (true) {
                currentCoroutineContext().ensureActive()
                val count = input.read(buffer)
                if (count < 0) break
                total += count
                check(total <= MAX_RECEIPT_BYTES) { "Ukuran bukti melebihi 10 MB." }
                output.write(buffer, 0, count)
            }
        }
    }
}
