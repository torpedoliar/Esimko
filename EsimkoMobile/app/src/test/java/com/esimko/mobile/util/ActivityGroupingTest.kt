package com.esimko.mobile.util

import com.esimko.mobile.domain.model.Transaction
import com.google.common.truth.Truth.assertThat
import org.junit.Test

class ActivityGroupingTest {

    private fun trx(id: Long, tanggal: String, status: String) = Transaction(
        id = id,
        jenis = "Simpanan Wajib",
        modul = "simpanan",
        nominal = 100_000L,
        tanggal = tanggal,
        status = status,
        statusLabel = status,
        keterangan = null
    )

    private fun labels(rows: List<ActivityRow>) = rows.map {
        when (it) {
            is ActivityRow.SectionHeaderRow -> "S:${it.title}"
            is ActivityRow.MonthHeaderRow -> "M:${it.title}"
            is ActivityRow.ItemRow -> "I:${it.transaction.id}"
        }
    }

    @Test
    fun `berjalan di atas, selesai di bawah`() {
        val rows = ActivityGrouping.build(
            listOf(
                trx(1, "2026-08-01", "Selesai"),
                trx(2, "2026-08-02", "Belum Verifikasi")
            )
        )
        assertThat(labels(rows)).containsExactly(
            "S:Berjalan", "I:2", "S:Selesai", "M:Agustus 2026", "I:1"
        ).inOrder()
    }

    @Test
    fun `grup berjalan tidak memakai header bulan`() {
        // Berjalan biasanya pendek dan yang penting statusnya, bukan bulannya
        val rows = ActivityGrouping.build(
            listOf(trx(1, "2026-07-30", "Disetujui"), trx(2, "2026-08-02", "Belum Verifikasi"))
        )
        assertThat(labels(rows)).containsExactly("S:Berjalan", "I:2", "I:1").inOrder()
    }

    @Test
    fun `selesai dipecah per bulan, terbaru dulu`() {
        val rows = ActivityGrouping.build(
            listOf(
                trx(1, "2026-06-15", "Selesai"),
                trx(2, "2026-08-03", "Ditolak"),
                trx(3, "2026-08-01", "Pinjaman Lunas")
            )
        )
        assertThat(labels(rows)).containsExactly(
            "S:Selesai", "M:Agustus 2026", "I:2", "I:3", "M:Juni 2026", "I:1"
        ).inOrder()
    }

    @Test
    fun `tanggal sama diurut id turun supaya stabil`() {
        val rows = ActivityGrouping.build(
            listOf(trx(7, "2026-08-01", "Selesai"), trx(9, "2026-08-01", "Selesai"))
        )
        assertThat(labels(rows)).containsExactly("S:Selesai", "M:Agustus 2026", "I:9", "I:7")
            .inOrder()
    }

    @Test
    fun `hanya berjalan berarti tanpa bagian selesai`() {
        val rows = ActivityGrouping.build(listOf(trx(1, "2026-08-01", "Belum Verifikasi")))
        assertThat(labels(rows)).containsExactly("S:Berjalan", "I:1").inOrder()
    }

    @Test
    fun `daftar kosong menghasilkan nol baris`() {
        assertThat(ActivityGrouping.build(emptyList())).isEmpty()
    }

    @Test
    fun `status tak dikenal dianggap berjalan`() {
        val rows = ActivityGrouping.build(listOf(trx(1, "2026-08-01", "Status Baru 2027")))
        assertThat(labels(rows)).containsExactly("S:Berjalan", "I:1").inOrder()
    }

    @Test
    fun `label bulan indonesia`() {
        assertThat(ActivityGrouping.monthLabel("2026-01-09")).isEqualTo("Januari 2026")
        assertThat(ActivityGrouping.monthLabel("2026-12-31")).isEqualTo("Desember 2026")
    }

    @Test
    fun `label bulan menerima tanggal bersuffix waktu`() {
        assertThat(ActivityGrouping.monthLabel("2026-03-04 09:12:00")).isEqualTo("Maret 2026")
    }

    @Test
    fun `tanggal tidak terbaca tidak melempar`() {
        assertThat(ActivityGrouping.monthLabel("")).isEqualTo("Tanpa tanggal")
        assertThat(ActivityGrouping.monthLabel("bukan tanggal")).isEqualTo("Tanpa tanggal")
        assertThat(ActivityGrouping.monthLabel("2026-13-01")).isEqualTo("Tanpa tanggal")
        assertThat(ActivityGrouping.dayLabel("")).isEqualTo("-")
    }

    @Test
    fun `label hari singkat`() {
        assertThat(ActivityGrouping.dayLabel("2026-08-01")).isEqualTo("1 Agu 2026")
        assertThat(ActivityGrouping.dayLabel("2026-11-25")).isEqualTo("25 Nov 2026")
    }

    @Test
    fun `tanggal tak terbaca dikumpulkan di bagian selesai paling bawah`() {
        val rows = ActivityGrouping.build(
            listOf(trx(1, "", "Selesai"), trx(2, "2026-08-01", "Selesai"))
        )
        assertThat(labels(rows)).containsExactly(
            "S:Selesai", "M:Agustus 2026", "I:2", "M:Tanpa tanggal", "I:1"
        ).inOrder()
    }

    @Test
    fun `kunci baris unik supaya LazyColumn tidak salah daur ulang`() {
        val rows = ActivityGrouping.build(
            listOf(
                trx(1, "2026-08-01", "Selesai"),
                trx(2, "2026-07-01", "Selesai"),
                trx(3, "2026-08-02", "Belum Verifikasi")
            )
        )
        val keys = rows.map { it.key }
        assertThat(keys).containsNoDuplicates()
    }
}
