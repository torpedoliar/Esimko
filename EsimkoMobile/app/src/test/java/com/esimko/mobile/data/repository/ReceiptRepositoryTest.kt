package com.esimko.mobile.data.repository

import com.esimko.mobile.data.local.TokenStore
import com.esimko.mobile.data.remote.api.ReceiptApi
import io.mockk.coEvery
import io.mockk.every
import io.mockk.mockk
import io.mockk.verify
import kotlinx.coroutines.CancellationException
import kotlinx.coroutines.test.runTest
import okhttp3.MediaType.Companion.toMediaType
import okhttp3.ResponseBody.Companion.toResponseBody
import org.junit.Assert.*
import org.junit.Test
import retrofit2.Response
import java.io.File

class ReceiptRepositoryTest {
    private val api = mockk<ReceiptApi>()
    private val tokens = mockk<TokenStore>(relaxed = true)
    private val files = mockk<ReceiptFileStore>(relaxed = true)

    private fun repository(): ReceiptRepository {
        every { tokens.sessionRevision } returns 7L
        every { tokens.token } returns "private-test-token"
        return ReceiptRepository(api, tokens, files)
    }

    @Test fun `lampiran rejects account switch and closes response`() = runTest {
        val repo = repository()
        val body = okio.Buffer().writeUtf8("image")
        val response = mockk<okhttp3.ResponseBody>(relaxed = true)
        every { response.contentLength() } returns -1L
        every { response.source() } returns body
        coEvery { api.lampiran(12) } answers {
            every { tokens.sessionRevision } returns 8L
            Response.success(response)
        }
        try { repo.loadLampiran(12); fail("Must reject previous session") } catch (_: IllegalStateException) { }
        verify { response.close() }
    }

    @Test fun `lampiran rejects unknown length overflow and empty body`() = runTest {
        val response = mockk<okhttp3.ResponseBody>()
        every { response.contentLength() } returns -1L
        every { response.source() } returns okio.Buffer().write(ByteArray(MAX_LAMPIRAN_BYTES + 1))
        try { readLampiran(response); fail("Must bound unknown length") } catch (_: IllegalStateException) { }
        try { readLampiran(ByteArray(0).toResponseBody()); fail("Must reject empty") } catch (_: IllegalStateException) { }
    }

    @Test fun `lampiran preserves cancellation`() = runTest {
        val repo = repository()
        coEvery { api.lampiran(12) } throws CancellationException("cancelled")
        try { repo.loadLampiran(12); fail("Must cancel") } catch (_: CancellationException) { }
    }

    @Test fun `valid PDF streams then publishes with session identity`() = runTest {
        val repo = repository()
        val temp = File.createTempFile("receipt-test", ".pdf")
        every { files.temporary() } returns temp
        every { files.publish(temp, any(), any()) } returns SavedReceipt("content://receipt/1", true)
        coEvery { api.download("transaksi", 12) } returns Response.success("%PDF-1.4 test".toResponseBody("application/pdf".toMediaType()))
        val saved = repo.download("transaksi", 12)
        assertEquals(7L, saved.sessionRevision)
        assertFalse(temp.exists())
        verify(exactly = 1) { files.publish(temp, any(), any()) }
    }

    @Test fun `JSON response never publishes and removes staging file`() = runTest {
        val repo = repository()
        val temp = File.createTempFile("receipt-test", ".pdf")
        every { files.temporary() } returns temp
        coEvery { api.download("penjualan", 12) } returns Response.success("{}".toResponseBody("application/json".toMediaType()))
        try { repo.download("penjualan", 12); fail("Must reject JSON") } catch (_: IllegalStateException) { }
        assertFalse(temp.exists())
        verify(exactly = 0) { files.publish(any(), any(), any()) }
    }

    @Test fun `session switch during network discards without publishing`() = runTest {
        val repo = repository()
        val temp = File.createTempFile("receipt-test", ".pdf")
        every { files.temporary() } returns temp
        coEvery { api.download("angsuran", 12) } answers {
            every { tokens.sessionRevision } returns 8L
            Response.success("%PDF-1.4 test".toResponseBody("application/pdf".toMediaType()))
        }
        try { repo.download("angsuran", 12); fail("Must reject previous session") } catch (_: IllegalStateException) { }
        assertFalse(temp.exists())
        verify(exactly = 0) { files.publish(any(), any(), any()) }
    }

    @Test fun `session switch while publishing deletes output`() = runTest {
        val repo = repository()
        val temp = File.createTempFile("receipt-test", ".pdf")
        every { files.temporary() } returns temp
        every { files.publish(temp, any(), any()) } answers {
            every { tokens.sessionRevision } returns 8L
            SavedReceipt("content://receipt/1", true)
        }
        coEvery { api.download("transaksi", 12) } returns Response.success("%PDF-1.4 test".toResponseBody("application/pdf".toMediaType()))
        try { repo.download("transaksi", 12); fail("Must reject previous session") } catch (_: IllegalStateException) { }
        verify { files.discard(SavedReceipt("content://receipt/1", true, 7L)) }
        assertFalse(temp.exists())
    }

    @Test fun `cancellation is rethrown`() = runTest {
        val repo = repository()
        coEvery { api.download("transaksi", 12) } throws CancellationException("cancelled")
        try { repo.download("transaksi", 12); fail("Must cancel") } catch (_: CancellationException) { }
        verify(exactly = 0) { files.publish(any(), any(), any()) }
    }

    @Test fun `invalid magic and oversized unknown length body are rejected`() = runTest {
        val temp = File.createTempFile("receipt-test", ".pdf")
        try {
            try {
                copyValidatedPdf("not a pdf".toResponseBody("application/pdf".toMediaType()), temp)
                fail("Must reject invalid magic")
            } catch (_: IllegalStateException) { }
            val body = mockk<okhttp3.ResponseBody>()
            every { body.contentType() } returns "application/pdf".toMediaType()
            every { body.contentLength() } returns -1L
            val bytes = ByteArray(MAX_RECEIPT_BYTES.toInt() + 1) { 32 }
            "%PDF-".toByteArray().copyInto(bytes)
            every { body.source() } returns okio.Buffer().write(bytes)
            try { copyValidatedPdf(body, temp); fail("Must reject oversized body") } catch (_: IllegalStateException) { }
        } finally { temp.delete() }
    }
}
