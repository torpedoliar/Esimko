package com.esimko.mobile.util

import com.google.common.truth.Truth.assertThat
import org.junit.Test

/**
 * Format harus persis sama dengan backend: number_format($n, 0, ',', '.')
 * Tanpa ini, angka di app beda dengan angka di web untuk transaksi yang sama.
 */
class MoneyFormatterTest {

    @Test
    fun `nol tampil Rp 0 bukan kosong`() {
        assertThat(MoneyFormatter.format(0L)).isEqualTo("Rp 0")
    }

    @Test
    fun `ribuan pakai titik`() {
        assertThat(MoneyFormatter.format(1_000L)).isEqualTo("Rp 1.000")
        assertThat(MoneyFormatter.format(50_000L)).isEqualTo("Rp 50.000")
        assertThat(MoneyFormatter.format(1_250_000L)).isEqualTo("Rp 1.250.000")
    }

    @Test
    fun `di bawah seribu tanpa pemisah`() {
        assertThat(MoneyFormatter.format(1L)).isEqualTo("Rp 1")
        assertThat(MoneyFormatter.format(999L)).isEqualTo("Rp 999")
    }

    @Test
    fun `batas tepat kelipatan seribu`() {
        assertThat(MoneyFormatter.format(1_000_000L)).isEqualTo("Rp 1.000.000")
        assertThat(MoneyFormatter.format(1_000_000_000L)).isEqualTo("Rp 1.000.000.000")
    }

    @Test
    fun `negatif pakai minus di depan Rp`() {
        assertThat(MoneyFormatter.format(-50_000L)).isEqualTo("-Rp 50.000")
        assertThat(MoneyFormatter.format(-1L)).isEqualTo("-Rp 1")
    }

    @Test
    fun `plain tanpa prefix Rp untuk field input`() {
        assertThat(MoneyFormatter.plain(1_250_000L)).isEqualTo("1.250.000")
        assertThat(MoneyFormatter.plain(0L)).isEqualTo("0")
    }

    @Test
    fun `digitsToLong buang non digit`() {
        assertThat(MoneyFormatter.digitsToLong("1.250.000")).isEqualTo(1_250_000L)
        assertThat(MoneyFormatter.digitsToLong("Rp 50.000")).isEqualTo(50_000L)
        assertThat(MoneyFormatter.digitsToLong("")).isEqualTo(0L)
        assertThat(MoneyFormatter.digitsToLong("abc")).isEqualTo(0L)
    }

    @Test
    fun `digitsToLong tidak overflow saat user menahan tombol angka`() {
        val tooLong = "9".repeat(30)
        assertThat(MoneyFormatter.digitsToLong(tooLong)).isEqualTo(MoneyFormatter.MAX_INPUT)
    }

    @Test
    fun `Long MIN VALUE tidak bikin crash`() {
        assertThat(MoneyFormatter.format(Long.MIN_VALUE)).startsWith("-Rp ")
    }
}
