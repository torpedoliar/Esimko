package com.esimko.mobile.data.remote.dto

import com.squareup.moshi.Json
import com.squareup.moshi.JsonClass

@JsonClass(generateAdapter = true)
data class NewsResponse(
    @Json(name = "id") val id: Long,
    @Json(name = "judul") val judul: String? = null,
    @Json(name = "ringkasan") val ringkasan: String? = null,
    @Json(name = "gambar") val gambar: String? = null,
    @Json(name = "tanggal") val tanggal: String? = null
)

@JsonClass(generateAdapter = true)
data class NewsDetailResponse(
    @Json(name = "id") val id: Long,
    @Json(name = "judul") val judul: String? = null,
    @Json(name = "konten") val konten: String? = null,
    @Json(name = "gambar") val gambar: String? = null,
    @Json(name = "tanggal") val tanggal: String? = null
)
