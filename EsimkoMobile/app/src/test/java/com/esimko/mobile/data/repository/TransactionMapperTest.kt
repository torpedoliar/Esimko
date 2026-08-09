package com.esimko.mobile.data.repository

import com.esimko.mobile.data.remote.dto.TransactionResponse
import com.google.common.truth.Truth.assertThat
import org.junit.Test

class TransactionMapperTest {

    private fun dto(
        nominalTampil: String? = null,
        color: String? = null,
        totalAngsuran: Double? = null,
        sisaPinjaman: Double? = null,
        sisaTenor: Int? = null
    ) = TransactionResponse(
        id = 1L,
        jenisTransaksi = "Simpanan Wajib",
        nominal = 250000.0,
        tanggal = "2026-08-01",
        status = "Belum Verifikasi",
        color = color,
        metodePembayaran = "Tunai",
        keterangan = null,
        nominalTampil = nominalTampil,
        totalAngsuran = totalAngsuran,
        sisaPinjaman = sisaPinjaman,
        sisaTenor = sisaTenor
    )

    @Test
    fun `warna status dibawa apa adanya`() {
        assertThat(dto(color = "#e67e22").toDomain("simpanan").color).isEqualTo("#e67e22")
    }

    @Test
    fun `warna null tetap null, bukan string kosong`() {
        assertThat(dto().toDomain("simpanan").color).isNull()
    }

    @Test
    fun `nominal_tampil berawalan minus dibaca sebagai debit`() {
        assertThat(dto(nominalTampil = "-Rp 250.000").toDomain("simpanan").isDebit).isTrue()
    }

    @Test
    fun `nominal_tampil berawalan plus bukan debit`() {
        assertThat(dto(nominalTampil = "+Rp 250.000").toDomain("simpanan").isDebit).isFalse()
    }

    @Test
    fun `nominal_tampil hilang dianggap bukan debit`() {
        // Backend hanya mengisi nominal_tampil untuk pinjaman kalau baris angsuran ada
        assertThat(dto().toDomain("pinjaman").isDebit).isFalse()
    }

    @Test
    fun `kolom pinjaman pecahan dibulatkan ke rupiah utuh`() {
        val t = dto(totalAngsuran = 208333.5, sisaPinjaman = 1874999.5, sisaTenor = 9)
            .toDomain("pinjaman")
        assertThat(t.totalAngsuran).isEqualTo(208334L)
        assertThat(t.sisaPinjaman).isEqualTo(1875000L)
        assertThat(t.sisaTenor).isEqualTo(9)
    }

    @Test
    fun `kolom pinjaman kosong tetap null, bukan nol`() {
        // Nol berarti "lunas"; null berarti "tidak diketahui". UI harus bisa membedakan.
        val t = dto().toDomain("pinjaman")
        assertThat(t.totalAngsuran).isNull()
        assertThat(t.sisaPinjaman).isNull()
        assertThat(t.sisaTenor).isNull()
    }

    @Test
    fun `modul diteruskan ke model`() {
        assertThat(dto().toDomain("pinjaman").modul).isEqualTo("pinjaman")
    }

    @Test
    fun `status kosong jadi string kosong, bukan crash`() {
        val t = TransactionResponse(id = 2L, status = null).toDomain("simpanan")
        assertThat(t.status).isEmpty()
        assertThat(t.jenis).isEmpty()
        assertThat(t.nominal).isEqualTo(0L)
    }
}
