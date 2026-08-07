package com.esimko.mobile.domain.model

data class Transaction(
    val id: Long,
    val jenis: String,
    val modul: String,
    val nominal: Long,
    val tanggal: String,
    val status: String,
    val statusLabel: String,
    val keterangan: String?,
    // Hex dari status_transaksi.color, mis. "#e67e22". Null = pakai warna tema.
    val color: String? = null,
    val nominalTampil: String? = null,
    // Uang keluar (penarikan, jenis 6/7/8). Diturunkan dari tanda nominal_tampil,
    // bukan dari daftar id jenis di klien — aturan tandanya milik backend.
    val isDebit: Boolean = false,
    val totalAngsuran: Long? = null,
    val sisaPinjaman: Long? = null,
    val sisaTenor: Int? = null,
    // Dipakai segmen non-`transaksi` (belanja, angsuran belanja, retur) yang subjudul dan
    // label bulannya tidak bisa diturunkan dari `tanggal`/`keterangan`.
    val subtitleOverride: String? = null,
    val monthOverride: String? = null
)

data class TransactionDetail(
    val id: Long,
    val jenis: String,
    val nominal: Long,
    val tanggal: String,
    val status: String,
    val statusLabel: String,
    val keterangan: String?,
    val buktiTransaksi: String?,
    val items: List<TransactionItem>?,
    val color: String? = null,
    /** Kalimat penjelas status dari `keterangan_status_transaksi`; kosong = tidak ada. */
    val statusKeterangan: String? = null,
    val namaPetugas: String? = null,
    val metodePembayaran: String? = null,
    val noAnggota: String = "",
    val namaLengkap: String = ""
)

data class TransactionItem(
    val id: Long,
    val nama: String,
    val nominal: Long,
    val qty: Int
)
