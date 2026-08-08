package com.esimko.mobile.data.remote.dto

import com.squareup.moshi.Json
import com.squareup.moshi.JsonClass

@JsonClass(generateAdapter = true)
data class ProfileResponse(
    @Json(name = "no_anggota") val noAnggota: String? = null,
    @Json(name = "nama_lengkap") val namaLengkap: String? = null,
    @Json(name = "no_ktp") val noKtp: String? = null,
    @Json(name = "alamat") val alamat: String? = null,
    @Json(name = "no_handphone") val noHandphone: String? = null,
    @Json(name = "email") val email: String? = null,
    @Json(name = "avatar") val avatar: String? = null,
    // Double, bukan Long: semua field uang dibaca dari kolom DB double/decimal (simpanan,
    // transaksi, angsuran, gaji). Moshi menolak pecahan sebagai Long. Dibulatkan di mapper.
    @Json(name = "total_saldo_simpanan") val totalSaldoSimpanan: Double? = 0.0,
    @Json(name = "sisa_pinjaman") val sisaPinjaman: Double? = 0.0,
    @Json(name = "total_angsuran_pinjaman") val totalAngsuranPinjaman: Double? = 0.0,
    @Json(name = "saldo_simpanan_pokok") val saldoSimpananPokok: Double? = 0.0,
    @Json(name = "saldo_simpanan_wajib") val saldoSimpananWajib: Double? = 0.0,
    @Json(name = "saldo_simpanan_sukarela") val saldoSimpananSukarela: Double? = 0.0,
    @Json(name = "saldo_simpanan_hari_raya") val saldoSimpananHariRaya: Double? = 0.0,
    @Json(name = "bunga_pinjaman") val bungaPinjaman: Double? = 0.0,
    @Json(name = "angsuran_jangka_panjang") val angsuranJangkaPanjang: Double? = 0.0,
    @Json(name = "angsuran_jangka_pendek") val angsuranJangkaPendek: Double? = 0.0,
    @Json(name = "angsuran_barang") val angsuranBarang: Double? = 0.0,
    @Json(name = "total_angsuran_belanja") val totalAngsuranBelanja: Double? = 0.0,
    @Json(name = "angsuran_belanja_toko") val angsuranBelanjaToko: Double? = 0.0,
    @Json(name = "angsuran_belanja_konsinyasi") val angsuranBelanjaKonsinyasi: Double? = 0.0,
    @Json(name = "angsuran_belanja_online") val angsuranBelanjaOnline: Double? = 0.0,
    @Json(name = "setoran_berkala") val setoranBerkala: Double? = 0.0,
    @Json(name = "setoran_simpanan_anggota") val setoranSimpananAnggota: Double? = 0.0,
    @Json(name = "status_anggota") val statusAnggota: String? = null,
    @Json(name = "color") val color: String? = null,
    @Json(name = "nama_panggilan") val namaPanggilan: String? = null,
    @Json(name = "jenis_kelamin") val jenisKelamin: String? = null,
    @Json(name = "tempat_lahir") val tempatLahir: String? = null,
    @Json(name = "tanggal_lahir") val tanggalLahir: String? = null,
    @Json(name = "tanggal_bergabung") val tanggalBergabung: String? = null,
    @Json(name = "divisi") val divisi: String? = null,
    @Json(name = "bagian") val bagian: String? = null
)
