package com.esimko.mobile.data.remote.dto

import com.squareup.moshi.Json
import com.squareup.moshi.JsonClass

@JsonClass(generateAdapter = true)
data class InstallmentResponse(
    @Json(name = "id") val id: Long? = null,
    @Json(name = "jenis_transaksi") val jenisTransaksi: String? = null,
    @Json(name = "bulan") val bulan: String? = null,
    @Json(name = "nama_bulan") val namaBulan: String? = null,
    @Json(name = "angsuran_ke") val angsuranKe: Int? = 0,
    // Double, bukan Long: angsuran.angsuran_pokok/angsuran_bunga kolom DB double.
    // Moshi menolak pecahan sebagai Long. Dibulatkan di mapper.
    @Json(name = "angsuran_bunga") val angsuranBunga: Double? = 0.0,
    @Json(name = "angsuran_pokok") val angsuranPokok: Double? = 0.0,
    @Json(name = "status_angsuran") val statusAngsuran: String? = null,
    @Json(name = "color") val color: String? = null
)

@JsonClass(generateAdapter = true)
data class SalaryResponse(
    // Double, bukan Long: gaji_pokok.gaji_pokok kolom DB double. Dibulatkan di mapper.
    @Json(name = "gaji_pokok") val gajiPokok: Double? = 0.0
)
