package com.esimko.mobile.util

import com.esimko.mobile.data.remote.dto.SyaratPinjamanResponse
import com.esimko.mobile.ui.installment.LoanApplicationState
import com.google.common.truth.Truth.assertThat
import org.junit.Test

class LoanEstimateStateTest {
    @Test
    fun `estimasi mengikuti bunga dari syarat API`() {
        val syarat = SyaratPinjamanResponse(bungaPinjamanRate = 0.008).toDomain()
        val state = LoanApplicationState(nominal = "1000000", tenor = "10", syarat = syarat)
        assertThat(state.estimasiAngsuran).isEqualTo(108_000)
        assertThat(state.copy(syarat = syarat.copy(bungaPinjamanRate = 0.01)).estimasiAngsuran).isEqualTo(110_000)
    }

    @Test
    fun `bunga belum termuat tidak memakai tebakan satu persen`() {
        val state = LoanApplicationState(nominal = "1000000", tenor = "10")
        assertThat(state.bungaRate).isNull()
        assertThat(state.estimasiAngsuran).isEqualTo(0)
    }

    @Test
    fun `bunga nol valid tetapi nilai negatif ditolak`() {
        val syarat = SyaratPinjamanResponse(bungaPinjamanRate = 0.0).toDomain()
        val state = LoanApplicationState(nominal = "1000000", tenor = "10", syarat = syarat)
        assertThat(state.estimasiAngsuran).isEqualTo(100_000)
        assertThat(state.copy(syarat = syarat.copy(bungaPinjamanRate = -0.008)).bungaRate).isNull()
    }
}
