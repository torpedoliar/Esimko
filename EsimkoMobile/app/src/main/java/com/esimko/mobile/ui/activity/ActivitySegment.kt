package com.esimko.mobile.ui.activity

/**
 * Lima segmen tab Aktivitas. `filterKey` adalah nilai yang dikirim `ActionGrid` lewat
 * `aktivitas?filter=...` (Task 12) — bukan nama modul backend.
 *
 * `modul` hanya terisi untuk segmen yang memakai `transaksi/{modul}`; tiga segmen belanja
 * punya endpoint sendiri, jadi `null`.
 */
enum class ActivitySegment(
    val label: String,
    val filterKey: String,
    val modul: String? = null
) {
    // API filter modules; transaction rows still dispatch details with domain modul=transaksi.
    SIMPANAN("Simpanan", "simpanan", modul = "simpanan"),
    PINJAMAN("Pinjaman", "pinjaman", modul = "pinjaman"),
    BELANJA("Belanja", "belanja"),
    ANGSURAN_BELANJA("Angsuran Belanja", "angsuran_belanja"),
    RETUR("Retur", "retur");

    companion object {
        fun fromKey(key: String?): ActivitySegment =
            values().firstOrNull { it.filterKey == key } ?: SIMPANAN
    }
}
