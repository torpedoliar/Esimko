package com.esimko.mobile.data.remote.dto

import com.squareup.moshi.Json
import com.squareup.moshi.JsonClass

@JsonClass(generateAdapter = true)
data class TransactionTypeResponse(
    @Json(name = "id") val id: Int,
    @Json(name = "nama") val nama: String? = null,
    @Json(name = "jenis_transaksi") val jenisTransaksi: String? = null,
    @Json(name = "kode") val kode: String? = null
)

@JsonClass(generateAdapter = true)
data class TransactionStatusResponse(
    @Json(name = "id") val id: Int,
    @Json(name = "nama") val nama: String? = null,
    @Json(name = "status_transaksi") val statusTransaksi: String? = null,
    @Json(name = "kode") val kode: String? = null,
    @Json(name = "color") val color: String? = null
)
