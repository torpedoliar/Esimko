package com.esimko.mobile.util

import com.google.common.truth.Truth.assertThat
import org.junit.Test

class LoanMathTest {

    @Test
    fun `angsuran = pokok dibagi tenor plus bunga flat atas nominal`() {
        // 5.000.000 / 12 = 416.666,67 → 416.667; bunga 1% × 5.000.000 = 50.000
        assertThat(LoanMath.angsuranPerBulan(5_000_000, 12)).isEqualTo(466_667)
    }

    @Test
    fun `pembulatan setengah ke atas`() {
        // 2.500.000 / 12 = 208.333,33 → 208.333; bunga 25.000
        assertThat(LoanMath.angsuranPerBulan(2_500_000, 12)).isEqualTo(233_333)
        // 1.250.000 / 12 = 104.166,67 → 104.167
        assertThat(LoanMath.angsuranPerBulan(1_250_000, 12)).isEqualTo(116_667)
    }

    @Test
    fun `tenor nol atau negatif mengembalikan nol, bukan melempar`() {
        assertThat(LoanMath.angsuranPerBulan(5_000_000, 0)).isEqualTo(0)
        assertThat(LoanMath.angsuranPerBulan(5_000_000, -3)).isEqualTo(0)
    }

    @Test
    fun `nominal nol mengembalikan nol`() {
        assertThat(LoanMath.angsuranPerBulan(0, 12)).isEqualTo(0)
    }

    @Test
    fun `total bayar = angsuran kali tenor`() {
        assertThat(LoanMath.totalBayar(5_000_000, 12)).isEqualTo(466_667L * 12)
    }

    @Test
    fun `rate bisa diganti`() {
        // bunga 0% → murni pokok dibagi tenor
        assertThat(LoanMath.angsuranPerBulan(1_200_000, 12, rate = 0.0)).isEqualTo(100_000)
    }
}
