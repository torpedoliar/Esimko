package com.esimko.mobile.ui.activity

import com.esimko.mobile.domain.model.PurchaseHistory
import com.esimko.mobile.domain.model.Return
import com.esimko.mobile.domain.model.ShoppingInstallment
import com.esimko.mobile.util.ActivityGrouping
import com.google.common.truth.Truth.assertThat
import org.junit.Test

class ActivityMapperTest {

    @Test
    fun `belanja toko jadi transaksi debit dengan jumlah barang`() {
        val t = ActivityMapper.purchaseToActivity(
            PurchaseHistory(
                id = 12, total = 145_000L, tanggal = "2026-08-03", status = "Belum Bayar",
                angsuran = null, color = "#e67e22", noTransaksi = "PJ-001", jumlah = 3
            ),
            jenisBelanja = "toko"
        )
        assertThat(t.jenis).isEqualTo("Belanja Toko")
        assertThat(t.modul).isEqualTo("toko")
        assertThat(t.nominal).isEqualTo(145_000L)
        assertThat(t.color).isEqualTo("#e67e22")
        assertThat(t.isDebit).isTrue()
        assertThat(t.subtitleOverride).isEqualTo("3 barang")
        assertThat(t.keterangan).isEqualTo("PJ-001")
    }

    @Test
    fun `belanja berangsur menyebut jumlah angsuran`() {
        val t = ActivityMapper.purchaseToActivity(
            PurchaseHistory(
                id = 13, total = 600_000L, tanggal = "2026-08-03", status = "Hold",
                angsuran = 6, color = null, noTransaksi = null, jumlah = 2
            ),
            jenisBelanja = "online"
        )
        assertThat(t.jenis).isEqualTo("Belanja Online")
        assertThat(t.modul).isEqualTo("online")
        assertThat(t.subtitleOverride).isEqualTo("2 barang · 6× angsuran")
    }

    @Test
    fun `belanja tanpa barang dan tanpa angsuran tidak punya subjudul`() {
        val t = ActivityMapper.purchaseToActivity(
            PurchaseHistory(
                id = 14, total = 0L, tanggal = "", status = "", angsuran = 0,
                color = null, noTransaksi = null, jumlah = 0
            ),
            jenisBelanja = "konsinyasi"
        )
        assertThat(t.subtitleOverride).isNull()
        assertThat(t.modul).isEqualTo("konsinyasi")
    }

    @Test
    fun `jenis belanja tak dikenal jatuh ke toko`() {
        // detail_belanja($jenis = 'toko') juga memakai toko sebagai default
        assertThat(ActivityMapper.detailKey(null)).isEqualTo("toko")
        assertThat(ActivityMapper.detailKey("")).isEqualTo("toko")
        assertThat(ActivityMapper.detailKey("grosir")).isEqualTo("toko")
        assertThat(ActivityMapper.detailKey("Konsinyasi")).isEqualTo("konsinyasi")
        assertThat(ActivityMapper.detailKey(" online ")).isEqualTo("online")
    }

    @Test
    fun `angsuran belanja memakai id penjualan dan periode dari nama bulan`() {
        val t = ActivityMapper.installmentToActivity(
            ShoppingInstallment(
                id = 77, ke = 3, nominal = 100_000L, bulan = "08-2026",
                namaBulan = "Agustus 2026", status = "Payroll", color = "#2980b9",
                noTransaksi = "PJ-009", jenisBelanja = "konsinyasi"
            )
        )
        assertThat(t.id).isEqualTo(77)
        assertThat(t.modul).isEqualTo("konsinyasi")
        assertThat(t.tanggal).isEqualTo("2026-08-01")
        assertThat(t.monthOverride).isEqualTo("Agustus 2026")
        assertThat(t.subtitleOverride).isEqualTo("Angsuran ke-3")
    }

    @Test
    fun `nama bulan spasi-saja jatuh ke bulan mentah`() {
        // GlobalHelper::getBulan tidak punya cabang default → bulan di luar 1..12 menghasilkan " 2023"
        val t = ActivityMapper.installmentToActivity(
            ShoppingInstallment(
                id = 78, ke = 1, nominal = 50_000L, bulan = "13-2023",
                namaBulan = " 2023", status = "Dibayar"
            )
        )
        assertThat(t.monthOverride).isEqualTo("13-2023")
        assertThat(t.tanggal).isEqualTo("")
    }

    @Test
    fun `bulan null menghasilkan periode dan tanggal kosong yang aman`() {
        val t = ActivityMapper.installmentToActivity(
            ShoppingInstallment(id = 79, ke = 2, nominal = 50_000L, bulan = null,
                namaBulan = null, status = "Payroll")
        )
        assertThat(t.monthOverride).isEqualTo("Tanpa periode")
        assertThat(t.tanggal).isEmpty()
        // tanggal kosong tidak boleh melempar saat dikelompokkan
        assertThat(ActivityGrouping.build(listOf(t))).isNotEmpty()
    }

    @Test
    fun `retur tanpa status dan nominal berupa jumlah satuan`() {
        val t = ActivityMapper.returnToActivity(
            Return(
                id = 5, noRetur = "RT-002", namaProduk = "Beras 5kg", jumlah = 2,
                keterangan = "Kemasan rusak", tanggal = "2026-07-30",
                foto = null, satuan = "sak", kode = "BRS5"
            )
        )
        assertThat(t.status).isEmpty()
        assertThat(t.nominal).isEqualTo(0L)
        assertThat(t.nominalTampil).isEqualTo("2 sak")
        assertThat(t.subtitleOverride).isEqualTo("Beras 5kg")
        assertThat(t.keterangan).isEqualTo("Kemasan rusak")
    }

    @Test
    fun `retur tanpa satuan tidak meninggalkan spasi menggantung`() {
        val t = ActivityMapper.returnToActivity(
            Return(id = 6, noRetur = "RT-003", namaProduk = "Gula", jumlah = 1,
                keterangan = null, tanggal = "2026-07-30")
        )
        assertThat(t.nominalTampil).isEqualTo("1")
        assertThat(t.keterangan).isEqualTo("RT-003")
    }

    @Test
    fun `bulan tak terbaca jadi tanggal kosong`() {
        assertThat(ActivityMapper.monthToSortableDate("08-2026")).isEqualTo("2026-08-01")
        assertThat(ActivityMapper.monthToSortableDate("1-2026")).isEqualTo("2026-01-01")
        assertThat(ActivityMapper.monthToSortableDate("13-2026")).isEmpty()
        assertThat(ActivityMapper.monthToSortableDate("2026")).isEmpty()
        assertThat(ActivityMapper.monthToSortableDate("")).isEmpty()
        assertThat(ActivityMapper.monthToSortableDate(null)).isEmpty()
    }

    @Test
    fun `angsuran belanja urut turun setelah disintesis tanggalnya`() {
        val rows = ActivityGrouping.build(
            listOf(
                ActivityMapper.installmentToActivity(
                    ShoppingInstallment(1, 1, 100_000L, "06-2026", "Juni 2026", "Dibayar")
                ),
                ActivityMapper.installmentToActivity(
                    ShoppingInstallment(2, 2, 100_000L, "08-2026", "Agustus 2026", "Dibayar")
                )
            )
        )
        val ids = rows.filterIsInstance<com.esimko.mobile.util.ActivityRow.ItemRow>()
            .map { it.transaction.id }
        assertThat(ids).containsExactly(2L, 1L).inOrder()
    }
}
