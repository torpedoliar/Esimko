package com.esimko.mobile.util

import androidx.compose.ui.graphics.Color

enum class StatusIcon { PENDING, APPROVED, REJECTED, DONE, CANCELLED, UNKNOWN }

/**
 * Terjemahan status transaksi/angsuran dari backend ke warna + ikon.
 *
 * Backend mengirim `color` per status (status_transaksi.color / status_angsuran.color),
 * jadi warna tidak dihardcode di client. Ikon dihardcode karena API tidak
 * mengirimnya, dan spec §6 mewajibkan status = warna DAN ikon (tidak boleh
 * bergantung warna sendirian).
 *
 * ponytail: pencocokan nama status, bukan id. Alasannya `transaksi/{modul}`
 * mengirim `status` sebagai string nama, bukan fid. Kalau nanti API mengirim
 * id status, ganti ke pencocokan id — lebih tahan perubahan nama.
 */
object StatusMeta {

    private val FINAL = setOf(
        "ditolak",         // status_transaksi 2, status_angsuran 4
        "selesai",         // status_transaksi 4, status_belanja 2
        "dibatalkan",      // status_transaksi 5, status_belanja 3
        "pinjaman lunas",  // status_transaksi 6
        "dibayar",         // status_angsuran 6
        "kredit lunas"     // status_belanja 4
    )

    private val ICONS = mapOf(
        "belum verifikasi" to StatusIcon.PENDING,
        "belum bayar" to StatusIcon.PENDING,     // status_belanja 1
        "simulasi" to StatusIcon.PENDING,
        "hold" to StatusIcon.PENDING,            // status_belanja 5 — masih berjalan
        "disetujui" to StatusIcon.APPROVED,
        "payroll" to StatusIcon.APPROVED,
        "ditolak" to StatusIcon.REJECTED,
        "selesai" to StatusIcon.DONE,
        "pinjaman lunas" to StatusIcon.DONE,
        "kredit lunas" to StatusIcon.DONE,
        "dibayar" to StatusIcon.DONE,
        "dibatalkan" to StatusIcon.CANCELLED
    )

    fun iconFor(status: String): StatusIcon = ICONS[key(status)] ?: StatusIcon.UNKNOWN

    /** Status yang tidak akan berubah lagi. Tak dikenal = dianggap berjalan. */
    fun isFinal(status: String): Boolean = key(status) in FINAL

    /**
     * Parse `#rrggbb` / `rrggbb` / `#aarrggbb`. Balik null kalau tidak bisa,
     * supaya pemanggil bisa jatuh ke warna tema, bukan menampilkan hitam pekat.
     */
    fun parseColor(hex: String?): Color? {
        val raw = hex?.trim()?.removePrefix("#") ?: return null
        if (raw.length != 6 && raw.length != 8) return null
        val value = raw.toLongOrNull(16) ?: return null
        return if (raw.length == 6) Color(value or 0xFF000000L) else Color(value)
    }

    private fun key(status: String) = status.trim().lowercase()
}
