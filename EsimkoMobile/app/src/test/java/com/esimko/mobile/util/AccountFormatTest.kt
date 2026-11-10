package com.esimko.mobile.util

import com.google.common.truth.Truth.assertThat
import org.junit.Test

class AccountFormatTest {

    @Test
    fun `inisial dari dua kata pertama`() {
        assertThat(AccountFormat.initials("Yohanes Oktavianus Putra")).isEqualTo("YO")
        assertThat(AccountFormat.initials("  budi  ")).isEqualTo("B")
    }

    @Test
    fun `inisial kosong jadi tanda tanya`() {
        assertThat(AccountFormat.initials("   ")).isEqualTo("?")
    }

    @Test
    fun `NIK disamarkan kecuali empat digit awal dan akhir`() {
        assertThat(AccountFormat.maskKtp("3201234567890001")).isEqualTo("3201 •••• •••• 0001")
        assertThat(AccountFormat.maskKtp("3201 2345 6789 0001")).isEqualTo("3201 •••• •••• 0001")
    }

    @Test
    fun `NIK pendek tetap disamarkan semua dan kosong jadi strip`() {
        assertThat(AccountFormat.maskKtp("12345")).isEqualTo("•••••")
        assertThat(AccountFormat.maskKtp("")).isEqualTo("-")
    }

    @Test
    fun `porsi saldo dijumlah satu dan negatif dianggap nol`() {
        val s = AccountFormat.shares(listOf(500, -200, 300, 200))
        assertThat(s[1]).isEqualTo(0f)
        assertThat(s.sum()).isWithin(0.0001f).of(1f)
        assertThat(s[0]).isWithin(0.0001f).of(0.5f)
    }

    @Test
    fun `saldo besar tidak overflow saat menghitung porsi`() {
        val parts = AccountFormat.shares(listOf(Long.MAX_VALUE, Long.MAX_VALUE))
        assertThat(parts).containsExactly(0.5f, 0.5f)
    }

    @Test
    fun `total nol tidak membagi nol`() {
        assertThat(AccountFormat.shares(listOf(0, 0))).containsExactly(0f, 0f)
    }
}
