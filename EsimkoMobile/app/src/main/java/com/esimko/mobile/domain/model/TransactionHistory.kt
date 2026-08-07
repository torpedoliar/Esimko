package com.esimko.mobile.domain.model

data class TransactionHistory(
    val createdAt: String,
    val caption: String,
    val noAnggota: String,
    val namaLengkap: String
) {
    /** Nama pelaku untuk ditampilkan; kosong = tindakan sistem, bukan anggota. */
    val pelaku: String get() = namaLengkap.ifBlank { "Sistem" }
}
