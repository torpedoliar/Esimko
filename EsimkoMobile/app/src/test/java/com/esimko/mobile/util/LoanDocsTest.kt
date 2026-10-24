package com.esimko.mobile.util

import com.google.common.truth.Truth.assertThat
import org.junit.Test

class LoanDocsTest {

    @Test
    fun `ktp2 wajib hanya di atas threshold`() {
        assertThat(LoanDocs.perluKtp2(10_000_000L, 10_000_000L)).isFalse() // tepat 10jt tidak
        assertThat(LoanDocs.perluKtp2(10_000_001L, 10_000_000L)).isTrue()
        assertThat(LoanDocs.perluKtp2(5_000_000L, 10_000_000L)).isFalse()
    }

    @Test
    fun `kurang - semua dokumen wajib terdaftar saat kosong`() {
        val kurang = LoanDocs.kurang(emptySet(), 5_000_000L, 10_000_000L)
        assertThat(kurang).containsExactly("kk", "ktp1", "kartu_anggota", "id_card").inOrder()
    }

    @Test
    fun `kurang - ktp2 ikut wajib saat nominal besar`() {
        val kurang = LoanDocs.kurang(setOf("kk"), 25_000_000L, 10_000_000L)
        assertThat(kurang).containsExactly("ktp1", "kartu_anggota", "id_card", "ktp2").inOrder()
    }

    @Test
    fun `kurang - kosong saat lengkap`() {
        val terlampir = setOf("kk", "ktp1", "kartu_anggota", "id_card", "ktp2")
        assertThat(LoanDocs.kurang(terlampir, 25_000_000L, 10_000_000L)).isEmpty()
        // nominal kecil: ktp2 tak wajib, tak masalah tak dilampirkan
        assertThat(LoanDocs.kurang(terlampir - "ktp2", 5_000_000L, 10_000_000L)).isEmpty()
    }

    @Test
    fun `mime diterima hanya jpg png pdf`() {
        assertThat(LoanDocs.mimeDiterima("image/jpeg")).isTrue()
        assertThat(LoanDocs.mimeDiterima("image/png")).isTrue()
        assertThat(LoanDocs.mimeDiterima("application/pdf")).isTrue()
        assertThat(LoanDocs.mimeDiterima("image/webp")).isFalse()
        assertThat(LoanDocs.mimeDiterima(null)).isFalse()
    }

    @Test
    fun `info slot ktp2 menyebut dokumen dan alasannya`() {
        val info = LoanDocs.infoSlot(LoanDocs.KTP2, 10_000_000L)
        assertThat(info).contains("KTP suami/istri")
        assertThat(info).contains("10.000.000")
    }

    @Test
    fun `info slot lain menyebut nama dokumen`() {
        assertThat(LoanDocs.infoSlot("kk", 10_000_000L)).contains("Kartu Keluarga")
        assertThat(LoanDocs.infoSlot("ktp1", 10_000_000L)).contains("KTP")
        assertThat(LoanDocs.infoSlot(LoanDocs.SLIP, 10_000_000L)).contains("slip gaji")
    }
}
