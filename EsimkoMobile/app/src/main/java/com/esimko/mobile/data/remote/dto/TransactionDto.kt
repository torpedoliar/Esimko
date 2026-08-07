package com.esimko.mobile.data.remote.dto

import com.squareup.moshi.Json
import com.squareup.moshi.JsonClass

@JsonClass(generateAdapter = true)
data class TransactionResponse(
    @Json(name = "id") val id: Long,
    @Json(name = "jenis_transaksi") val jenisTransaksi: String? = null,
    @Json(name = "nominal") val nominal: Long? = null,
    @Json(name = "tanggal") val tanggal: String? = null,
    @Json(name = "status") val status: String? = null,
    @Json(name = "color") val color: String? = null,
    @Json(name = "metode_pembayaran") val metodePembayaran: String? = null,
    @Json(name = "keterangan") val keterangan: String? = null,
    // Sudah diformat backend ('+Rp 250.000' / '-Rp 250.000'). Dipakai hanya untuk membaca
    // tandanya; angkanya tetap diformat MoneyFormatter supaya satu gaya di seluruh app.
    // Absen untuk baris pinjaman yang belum punya baris angsuran.
    @Json(name = "nominal_tampil") val nominalTampil: String? = null,
    // Double, bukan Long: angsuran_pokok + angsuran_bunga bisa pecahan dan Moshi menolak
    // membaca 208333.5 sebagai Long. Dibulatkan di mapper.
    @Json(name = "total_angsuran") val totalAngsuran: Double? = null,
    @Json(name = "sisa_pinjaman") val sisaPinjaman: Double? = null,
    @Json(name = "sisa_tenor") val sisaTenor: Int? = null
)

@JsonClass(generateAdapter = true)
data class TransactionDetailResponse(
    @Json(name = "id") val id: Long,
    @Json(name = "jenis_transaksi") val jenisTransaksi: String? = null,
    @Json(name = "nominal") val nominal: Long? = null,
    @Json(name = "tanggal") val tanggal: String? = null,
    @Json(name = "status") val status: String? = null,
    @Json(name = "color") val color: String? = null,
    @Json(name = "metode_pembayaran") val metodePembayaran: String? = null,
    @Json(name = "keterangan") val keterangan: String? = null,
    @Json(name = "bukti_transaksi") val buktiTransaksi: String? = null,
    @Json(name = "nama_lengkap") val namaLengkap: String? = null,
    @Json(name = "no_anggota") val noAnggota: String? = null,
    @Json(name = "avatar") val avatar: String? = null,
    @Json(name = "status_keterangan") val statusKeterangan: String? = null,
    @Json(name = "nama_petugas") val namaPetugas: String? = null
)

@JsonClass(generateAdapter = true)
data class TransactionRequest(
    @Json(name = "action") val action: String = "add",
    @Json(name = "nominal") val nominal: Long? = null,
    @Json(name = "keterangan") val keterangan: String? = null,
    @Json(name = "tenor") val tenor: Int? = null,
    @Json(name = "jenis_pinjaman") val jenisPinjaman: Int? = null,
    @Json(name = "gaji_pokok") val gajiPokok: Long? = null
)

@JsonClass(generateAdapter = true)
data class CancelRequest(
    @Json(name = "id") val id: Long,
    @Json(name = "alasan") val alasan: String? = null
)
