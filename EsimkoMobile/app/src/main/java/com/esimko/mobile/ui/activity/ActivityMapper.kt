package com.esimko.mobile.ui.activity

import com.esimko.mobile.domain.model.PurchaseHistory
import com.esimko.mobile.domain.model.Return
import com.esimko.mobile.domain.model.ShoppingInstallment
import com.esimko.mobile.domain.model.Transaction

/**
 * Memetakan tiga bentuk data belanja ke `Transaction` supaya `ActivityGrouping` (Task 17) dan satu
 * komposabel baris melayani kelima segmen. Murni — diuji di JVM.
 *
 * `Transaction.modul` di sini adalah **kunci dispatch detail**, bukan nama tabel: `ActivityDetailScreen`
 * (Step 13) memakainya untuk memilih endpoint. Nilai yang mungkin: `transaksi` (dari
 * `TransactionResponse.toDomain`, Task 16), `toko` | `konsinyasi` | `online` (belanja & angsuran
 * belanja), `retur` (tanpa detail).
 */
object ActivityMapper {

    /** Tiga nilai `penjualan.jenis_belanja` yang dikenal backend. */
    private val JENIS_DETAIL = setOf("toko", "konsinyasi", "online")

    /** Jenis yang tak dikenal jatuh ke `toko` — itu juga default `detail_belanja($jenis = 'toko')`. */
    internal fun detailKey(jenisBelanja: String?): String =
        jenisBelanja?.trim()?.lowercase()?.takeIf { it in JENIS_DETAIL } ?: "toko"

    /**
     * `belanja()` mengirim `no_transaksi`, `jumlah` (SUM item), `color`, `angsuran`.
     * `modul` diisi `jenisBelanja` yang asli — `belanja/riwayat/{jenis}/detail` memakainya sebagai
     * segmen path, dan `detail_belanja` bercabang atasnya dua kali (join item dan lookup
     * `keterangan_status_transaksi`: `'belanja'` untuk toko, `'kredit belanja'` untuk sisanya).
     */
    fun purchaseToActivity(item: PurchaseHistory, jenisBelanja: String): Transaction = Transaction(
        id = item.id,
        jenis = when (jenisBelanja) {
            "toko" -> "Belanja Toko"
            "konsinyasi" -> "Belanja Konsinyasi"
            "online" -> "Belanja Online"
            else -> "Belanja"
        },
        modul = detailKey(jenisBelanja),
        nominal = item.total,
        tanggal = item.tanggal,
        status = item.status,
        statusLabel = item.status,
        keterangan = item.noTransaksi,
        color = item.color,
        isDebit = true,
        subtitleOverride = buildString {
            if (item.jumlah > 0) append("${item.jumlah} barang")
            if (item.angsuran != null && item.angsuran > 0) {
                if (isNotEmpty()) append(" · ")
                append("${item.angsuran}× angsuran")
            }
        }.takeIf { it.isNotEmpty() }
    )

    /**
     * `angsuran_belanja()` memakai `penjualan.id` sebagai `id` (bukan id baris angsuran), jadi
     * detailnya diarahkan ke `belanja/riwayat/{jenis}/detail` lewat `jenisBelanja` yang ikut
     * di select-nya. `bulan` berformat `MM-YYYY` disintesis jadi `YYYY-MM-01` supaya urutan dan
     * header bulan benar; `monthOverride` menyimpan teks periode asli karena tanggal 1 hasil
     * sintesis itu tidak pernah benar-benar ada.
     */
    fun installmentToActivity(item: ShoppingInstallment): Transaction = Transaction(
        id = item.id,
        jenis = "Angsuran Belanja",
        modul = detailKey(item.jenisBelanja),
        nominal = item.nominal,
        tanggal = monthToSortableDate(item.bulan),
        status = item.status,
        statusLabel = item.status,
        keterangan = item.noTransaksi,
        color = item.color,
        isDebit = true,
        subtitleOverride = "Angsuran ke-${item.ke}",
        monthOverride = item.namaBulan?.takeIf { it.isNotBlank() && it.any { c -> c.isLetter() } }
            ?: item.bulan?.takeIf { it.isNotBlank() }
            ?: "Tanpa periode"
    )

    /**
     * `retur_barang()` tidak menyertakan kolom status sama sekali — baris Retur tampil tanpa
     * `StatusChip`. `status` diisi string kosong, bukan tebakan "Selesai".
     */
    fun returnToActivity(item: Return): Transaction = Transaction(
        id = item.id,
        jenis = "Retur Barang",
        modul = "retur",
        nominal = 0L,
        tanggal = item.tanggal,
        status = "",
        statusLabel = "",
        keterangan = item.keterangan ?: item.noRetur,
        nominalTampil = "${item.jumlah} ${item.satuan}".trim(),
        subtitleOverride = item.namaProduk.takeIf { it.isNotBlank() }
    )

    /**
     * `MM-YYYY` → `YYYY-MM-01` supaya urutan leksikografis `ActivityGrouping` benar.
     * Format tak dikenal → `""`, yang oleh `ActivityGrouping` diurutkan terakhir.
     */
    internal fun monthToSortableDate(bulan: String?): String {
        val parts = bulan?.trim()?.split('-') ?: return ""
        if (parts.size != 2) return ""
        val month = parts[0].toIntOrNull() ?: return ""
        val year = parts[1].toIntOrNull() ?: return ""
        if (month !in 1..12) return ""
        return "%04d-%02d-01".format(year, month)
    }
}
