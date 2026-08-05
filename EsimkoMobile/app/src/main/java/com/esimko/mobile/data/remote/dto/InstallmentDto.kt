package com.esimko.mobile.data.remote.dto

import com.squareup.moshi.Json
import com.squareup.moshi.JsonClass

@JsonClass(generateAdapter = true)
data class InstallmentResponse(
    @Json(name = "id") val id: Long,
    @Json(name = "ke") val ke: Int? = 0,
    @Json(name = "nominal") val nominal: Long? = 0,
    @Json(name = "bunga") val bunga: Long? = 0,
    @Json(name = "pokok") val pokok: Long? = 0,
    @Json(name = "angsuran_bunga") val angsuranBunga: Long? = 0,
    @Json(name = "angsuran_pokok") val angsuranPokok: Long? = 0,
    @Json(name = "tanggal_jatuh_tempo") val tanggalJatuhTempo: String? = null,
    @Json(name = "tanggal_bayar") val tanggalBayar: String? = null,
    @Json(name = "status") val status: String? = null,
    @Json(name = "status_angsuran") val statusAngsuran: String? = null,
    @Json(name = "color") val color: String? = null
)

@JsonClass(generateAdapter = true)
data class SalaryResponse(
    @Json(name = "gaji_pokok") val gajiPokok: Long? = 0
)
