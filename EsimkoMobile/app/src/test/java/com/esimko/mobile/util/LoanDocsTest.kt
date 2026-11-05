package com.esimko.mobile.util

import com.esimko.mobile.ui.installment.LoanApplicationState
import com.esimko.mobile.domain.model.DokumenFile
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
    fun `kurang - semua dokumen wajib terdaftar saat kosong (3 slip + 4 dokumen)`() {
        val kurang = LoanDocs.kurang(emptySet(), 5_000_000L, 10_000_000L)
        assertThat(kurang).containsExactly(
            "slip_bulan_ini", "slip_bulan_lalu", "slip_dua_bulan_lalu",
            "kk", "ktp1", "kartu_anggota", "id_card", "surat_hutang"
        ).inOrder()
    }

    @Test
    fun `kurang - ktp2 ikut wajib saat nominal besar`() {
        val kurang = LoanDocs.kurang(setOf("kk"), 25_000_000L, 10_000_000L)
        assertThat(kurang).containsExactly(
            "slip_bulan_ini", "slip_bulan_lalu", "slip_dua_bulan_lalu",
            "ktp1", "kartu_anggota", "id_card", "surat_hutang", "ktp2"
        ).inOrder()
    }

    @Test
    fun `kurang - kosong saat lengkap`() {
        val terlampir = setOf(
            "slip_bulan_ini", "slip_bulan_lalu", "slip_dua_bulan_lalu",
            "kk", "ktp1", "kartu_anggota", "id_card", "surat_hutang", "ktp2"
        )
        assertThat(LoanDocs.kurang(terlampir, 25_000_000L, 10_000_000L)).isEmpty()
        // nominal kecil: ktp2 tak wajib, tak masalah tak dilampirkan
        assertThat(LoanDocs.kurang(terlampir - "ktp2", 5_000_000L, 10_000_000L)).isEmpty()
    }

    @Test
    fun `batas tenor mengikuti jenis pinjaman`() {
        for ((jenis, maksimum) in listOf(9 to 60, 10 to 15, 11 to 20)) {
            val state = LoanApplicationState(selectedJenis = jenis, tenor = maksimum.toString())
            assertThat(state.maxTenor).isEqualTo(maksimum)
            assertThat(state.tenorValid).isTrue()
            assertThat(state.copy(tenor = (maksimum + 1).toString()).tenorValid).isFalse()
            assertThat(state.copy(tenor = "0").tenorValid).isFalse()
        }
        assertThat(LoanApplicationState(selectedJenis = 99, tenor = "1").tenorValid).isFalse()
    }

    @Test
    fun `surat hutang wajib semua jenis dan ktp penjamin terpisah untuk jenis 9`() {
        val file = DokumenFile(byteArrayOf(1), "image/jpeg", "doc.jpg")
        for (jenis in listOf(9, 10, 11)) {
            val state = LoanApplicationState(selectedJenis = jenis, nominal = "10000001", tenor = "1",
                gajiPokok = "1", persetujuanSetuju = true, persetujuanPinjamanSetuju = true)
            assertThat(state.slotDokumen).contains(LoanDocs.SURAT_HUTANG)
            assertThat(state.slotDokumen).contains(LoanDocs.KTP2)
            if (jenis == 9) assertThat(state.slotDokumen).contains(LoanDocs.KTP_PENJAMIN)
            else assertThat(state.slotDokumen).doesNotContain(LoanDocs.KTP_PENJAMIN)
            val lengkap = state.copy(dokumen = state.slotDokumen.associateWith { file })
            assertThat(lengkap.bisaKirim).isTrue()
            assertThat(lengkap.copy(dokumen = lengkap.dokumen - LoanDocs.SURAT_HUTANG).bisaKirim).isFalse()
            if (jenis == 9) {
                assertThat(lengkap.copy(dokumen = lengkap.dokumen - LoanDocs.KTP_PENJAMIN).bisaKirim).isFalse()
                val kecil = state.copy(nominal = "1")
                assertThat(kecil.slotDokumen).contains(LoanDocs.KTP_PENJAMIN)
                assertThat(kecil.slotDokumen).doesNotContain(LoanDocs.KTP2)
            }
        }
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
        assertThat(LoanDocs.infoSlot("slip_bulan_ini", 10_000_000L)).contains("bulan INI")
        assertThat(LoanDocs.infoSlot("slip_dua_bulan_lalu", 10_000_000L)).contains("2 bulan lalu")
    }
}
