package com.esimko.mobile.util

import com.google.common.truth.Truth.assertThat
import org.junit.Test

class AmountFormatterTest {

    @Test
    fun `formatRupiah with zero returns Rp 0`() {
        val result = AmountFormatter.formatRupiah(0L)
        assertThat(result).isEqualTo("Rp 0")
    }

    @Test
    fun `formatRupiah with positive amount formats correctly`() {
        val result = AmountFormatter.formatRupiah(1000000L)
        assertThat(result).contains("Rp")
        assertThat(result).contains("1.000.000")
    }

    @Test
    fun `formatRupiah with small amount formats correctly`() {
        val result = AmountFormatter.formatRupiah(50000L)
        assertThat(result).contains("Rp")
        assertThat(result).contains("50.000")
    }

    @Test
    fun `formatRupiah with double formats correctly`() {
        val result = AmountFormatter.formatRupiah(1500000.50)
        assertThat(result).contains("Rp")
    }
}
