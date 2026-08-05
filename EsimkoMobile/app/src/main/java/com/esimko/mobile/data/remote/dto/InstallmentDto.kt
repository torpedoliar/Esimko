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
    @Json(name = "angsuran_bunga") val angsuranBunga: Long? = 0,
    @Json(name = "angsuran_pokok") val angsuranPokok: Long? = 0,
    @Json(name = "status_angsuran") val statusAngsuran: String? = null,
    @Json(name = "color") val color: String? = null
)

@JsonClass(generateAdapter = true)
data class SalaryResponse(
    @Json(name = "gaji_pokok") val gajiPokok: Long? = 0
)
