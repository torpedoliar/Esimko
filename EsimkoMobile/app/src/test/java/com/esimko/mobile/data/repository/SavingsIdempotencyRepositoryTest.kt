package com.esimko.mobile.data.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.data.local.TokenStore
import com.esimko.mobile.data.remote.api.TransactionApi
import com.esimko.mobile.data.remote.dto.ApiResponse
import com.esimko.mobile.data.remote.dto.TransactionRequest
import com.esimko.mobile.data.remote.dto.TransactionResponse
import com.esimko.mobile.data.remote.dto.SavingsReconciliationResponse
import com.esimko.mobile.domain.repository.SavingsReconciliation
import com.google.common.truth.Truth.assertThat
import io.mockk.*
import kotlinx.coroutines.CancellationException
import kotlinx.coroutines.test.runTest
import org.junit.Test
import java.io.IOException
import java.util.UUID

class SavingsIdempotencyRepositoryTest {
    private val api = mockk<TransactionApi>()
    private val tokens = mockk<TokenStore>()
    private val session = TokenStore.Session("test-token", "A001", 1)
    private val key = UUID.randomUUID().toString()
    private val repository = TransactionRepositoryImpl(api, tokens)

    @Test fun `timeout retry sends exact same key body and bound session and accepts existing id`() = runTest {
        every { tokens.currentSession() } returns session
        val requests = mutableListOf<TransactionRequest>()
        coEvery { api.processTransaction("setoran", capture(requests), key, session) } throws IOException("timeout") andThen
            ApiResponse(success = true, message = null, data = TransactionResponse(id = 77, nominal = 1000.0, status = "Diverifikasi"), meta = null)
        assertThat(repository.processTransaction("setoran", 1000, "frozen timestamp note", key, session)).isInstanceOf(Result.Error::class.java)
        val retried = repository.processTransaction("setoran", 1000, "frozen timestamp note", key, session)
        assertThat(retried).isInstanceOf(Result.Success::class.java)
        assertThat((retried as Result.Success).data.id).isEqualTo(77)
        assertThat(retried.data.status).isEqualTo("Diverifikasi")
        assertThat(requests).hasSize(2)
        assertThat(requests[0]).isEqualTo(requests[1])
        assertThat(requests[0].action).isEqualTo("add")
        coVerify(exactly = 2) { api.processTransaction("setoran", any(), key, session) }
    }

    @Test fun `replacement session cannot send original draft or proof`() = runTest {
        every { tokens.currentSession() } returns TokenStore.Session("other", "A002", 2)
        assertThat(repository.processTransaction("setoran", 1000, "note", key, session)).isInstanceOf(Result.Error::class.java)
        assertThat(repository.uploadTransactionProof(77, byteArrayOf(1), "image/jpeg", session)).isInstanceOf(Result.Error::class.java)
        coVerify(exactly = 0) { api.processTransaction(any(), any(), any(), any()) }
        coVerify(exactly = 0) { api.uploadTransactionProof(any(), any(), any()) }
    }

    @Test fun `proof upload carries original session tag`() = runTest {
        every { tokens.currentSession() } returns session
        coEvery { api.uploadTransactionProof(any(), any(), session) } returns ApiResponse(success = true, message = null, data = null, meta = null)
        assertThat(repository.uploadTransactionProof(77, byteArrayOf(1), "image/jpeg", session)).isInstanceOf(Result.Success::class.java)
        coVerify(exactly = 1) { api.uploadTransactionProof(any(), any(), session) }
    }

    @Test fun `invalid kind key or amount never reaches API`() = runTest {
        every { tokens.currentSession() } returns session
        assertThat(repository.processTransaction("pinjaman", 1000, null, key, session)).isInstanceOf(Result.Error::class.java)
        assertThat(repository.processTransaction("penarikan", 0, null, key, session)).isInstanceOf(Result.Error::class.java)
        assertThat(repository.processTransaction("penarikan", 1000, null, "bad", session)).isInstanceOf(Result.Error::class.java)
        coVerify(exactly = 0) { api.processTransaction(any(), any(), any(), any()) }
    }

    @Test fun `reconciliation sends abandon action with exact original payload UUID and session`() = runTest {
        every { tokens.currentSession() } returns session
        val request = slot<TransactionRequest>()
        coEvery { api.reconcileSavings("setoran", capture(request), key, session) } returns
            ApiResponse(true, null, SavingsReconciliationResponse("abandoned", null, "setoran"), null)
        val result = repository.reconcileSavings("setoran", 1000, " frozen note — ", key, session)
        assertThat(request.captured).isEqualTo(TransactionRequest("abandon", 1000, " frozen note — "))
        assertThat((result as Result.Success).data).isEqualTo(SavingsReconciliation.Abandoned(key, "setoran"))
        coVerify(exactly = 0) { api.processTransaction(any(), any(), any(), any()) }
    }

    @Test fun `reconciliation existing preserves original positive transaction id`() = runTest {
        every { tokens.currentSession() } returns session
        coEvery { api.reconcileSavings(any(), any(), any(), any()) } returns
            ApiResponse(true, null, SavingsReconciliationResponse("existing", 77, "setoran"), null)
        assertThat((repository.reconcileSavings("setoran", 1000, "note", key, session) as Result.Success).data)
            .isEqualTo(SavingsReconciliation.Existing(77))
    }

    @Test fun `unknown malformed absent and unsuccessful reconciliation all fail closed`() = runTest {
        every { tokens.currentSession() } returns session
        val data = listOf(
            null, SavingsReconciliationResponse(), SavingsReconciliationResponse("absent", null, "setoran"),
            SavingsReconciliationResponse("abandoned", null, "penarikan"),
            SavingsReconciliationResponse("abandoned", 77, "setoran"),
            SavingsReconciliationResponse("existing", null, "setoran"),
            SavingsReconciliationResponse("existing", 0, "setoran")
        )
        for (dto in data) {
            coEvery { api.reconcileSavings(any(), any(), any(), any()) } returns ApiResponse(true, null, dto, null)
            assertThat(repository.reconcileSavings("setoran", 1000, "note", key, session)).isInstanceOf(Result.Error::class.java)
        }
        coEvery { api.reconcileSavings(any(), any(), any(), any()) } returns
            ApiResponse(false, "stored failure", SavingsReconciliationResponse("abandoned", null, "setoran"), null)
        assertThat(repository.reconcileSavings("setoran", 1000, "note", key, session)).isInstanceOf(Result.Error::class.java)
    }

    @Test fun `reconcile HTTP failures network and session replacement cannot become abandoned proof`() = runTest {
        every { tokens.currentSession() } returns session
        for (code in listOf(404, 422, 409, 500)) {
            coEvery { api.reconcileSavings(any(), any(), any(), any()) } throws retrofit2.HttpException(
                retrofit2.Response.error<Any>(code, okhttp3.ResponseBody.create(null, "{}"))
            )
            assertThat(repository.reconcileSavings("setoran", 1000, "note", key, session)).isInstanceOf(Result.Error::class.java)
        }
        coEvery { api.reconcileSavings(any(), any(), any(), any()) } throws IOException("timeout")
        assertThat(repository.reconcileSavings("setoran", 1000, "note", key, session)).isInstanceOf(Result.Error::class.java)
        coEvery { api.reconcileSavings(any(), any(), any(), any()) } coAnswers {
            every { tokens.currentSession() } returns TokenStore.Session("other", "A002", 2)
            ApiResponse(true, null, SavingsReconciliationResponse("abandoned", null, "setoran"), null)
        }
        assertThat(repository.reconcileSavings("setoran", 1000, "note", key, session)).isInstanceOf(Result.Error::class.java)
        clearMocks(api)
        assertThat(repository.reconcileSavings("setoran", 1000, "note", key, session)).isInstanceOf(Result.Error::class.java)
        coVerify(exactly = 0) { api.reconcileSavings(any(), any(), any(), any()) }
    }

    @Test fun `cancellation propagates without being treated as a fresh submission`() = runTest {
        every { tokens.currentSession() } returns session
        coEvery { api.processTransaction(any(), any(), any(), any()) } throws CancellationException("cancelled")
        var cancelled = false
        try {
            repository.processTransaction("setoran", 1000, "note", key, session)
        } catch (_: CancellationException) {
            cancelled = true
        }
        assertThat(cancelled).isTrue()
        coVerify(exactly = 1) { api.processTransaction(any(), any(), key, session) }
    }
}
