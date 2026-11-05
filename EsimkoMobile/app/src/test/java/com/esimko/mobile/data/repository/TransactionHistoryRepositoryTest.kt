package com.esimko.mobile.data.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.data.remote.api.TransactionHistoryApi
import com.esimko.mobile.data.remote.dto.ApiResponse
import com.esimko.mobile.data.remote.dto.TransactionHistoryResponse
import com.google.common.truth.Truth.assertThat
import com.squareup.moshi.Moshi
import io.mockk.coEvery
import io.mockk.mockk
import kotlinx.coroutines.test.runTest
import org.junit.Test

class TransactionHistoryRepositoryTest {
    private val adapter = Moshi.Builder().build().adapter(TransactionHistoryResponse::class.java)

    @Test
    fun `history reason is optional in API JSON`() {
        assertThat(adapter.fromJson("""{"caption":"Ditolak"}""")!!.keterangan).isNull()
        assertThat(adapter.fromJson("""{"keterangan":null}""")!!.keterangan).isNull()
        assertThat(adapter.fromJson("""{"keterangan":"Dokumen tidak lengkap"}""")!!.keterangan)
            .isEqualTo("Dokumen tidak lengkap")
    }

    @Test
    fun `repository forwards reason and omits blank values`() = runTest {
        val api = mockk<TransactionHistoryApi>()
        coEvery { api.getTransactionHistory(1L, "pinjaman") } returns ApiResponse(
            success = true, message = null, meta = null,
            data = listOf(
                TransactionHistoryResponse(keterangan = "Dokumen tidak lengkap"),
                TransactionHistoryResponse(keterangan = "  "),
                TransactionHistoryResponse()
            )
        )

        val result = TransactionHistoryRepositoryImpl(api).getTransactionHistory(1L, "pinjaman")
        assertThat(result).isInstanceOf(Result.Success::class.java)
        val history = (result as Result.Success).data
        assertThat(history.map { it.keterangan }).containsExactly("Dokumen tidak lengkap", null, null).inOrder()
    }
}
