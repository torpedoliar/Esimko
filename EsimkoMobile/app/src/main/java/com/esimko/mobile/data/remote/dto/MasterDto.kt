package com.esimko.mobile.data.remote.dto

import com.squareup.moshi.Json
import com.squareup.moshi.JsonClass

@JsonClass(generateAdapter = true)
data class TransactionTypeResponse(
    @Json(name = "id") val id: Int,
    @Json(name = "jenis_transaksi") val nama: String? = null,
    @Json(name = "group") val group: String? = null,
    @Json(name = "operasi") val operasi: String? = null,
    @Json(name = "keterangan") val keterangan: String? = null
)

@JsonClass(generateAdapter = true)
data class TransactionStatusResponse(
    @Json(name = "id") val id: Int,
    @Json(name = "status") val nama: String? = null,
    @Json(name = "caption") val caption: String? = null,
    @Json(name = "icon") val icon: String? = null,
    @Json(name = "color") val color: String? = null
)