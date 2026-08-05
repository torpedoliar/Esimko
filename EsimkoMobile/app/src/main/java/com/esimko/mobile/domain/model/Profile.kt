package com.esimko.mobile.domain.model

data class Profile(
    val noAnggota: String,
    val nama: String,
    val ktp: String,
    val alamat: String,
    val telepon: String,
    val email: String?,
    val avatar: String?,
    val saldoSimpanan: Long,
    val saldoPinjaman: Long,
    val angsuranBulan: Long,
    val saldoSimpananPokok: Long = 0,
    val saldoSimpananWajib: Long = 0,
    val saldoSimpananSukarela: Long = 0,
    val saldoSimpananHariRaya: Long = 0,
    val bungaPinjaman: Long = 0,
    val angsuranJangkaPanjang: Long = 0,
    val angsuranJangkaPendek: Long = 0,
    val angsuranBarang: Long = 0,
    val totalAngsuranBelanja: Long = 0,
    val setoranBerkala: Long = 0,
    val setoranSimpananAnggota: Long = 0,
    val statusAnggota: String = "",
    val divisi: String = "",
    val bagian: String = ""
)
