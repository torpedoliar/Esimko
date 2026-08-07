package com.esimko.mobile.data.remote.dto

import com.squareup.moshi.Json
import com.squareup.moshi.JsonClass

@JsonClass(generateAdapter = true)
data class AttachmentResponse(
    @Json(name = "id") val id: Long = 0,
    @Json(name = "judul") val judul: String? = null,
    @Json(name = "attachment") val attachment: String? = null
)

@JsonClass(generateAdapter = true)
data class NewsResponse(
    @Json(name = "id") val id: Long,
    @Json(name = "judul") val judul: String? = null,
    @Json(name = "content") val ringkasan: String? = null,
    @Json(name = "gambar") val gambar: String? = null,
    @Json(name = "created_at") val tanggal: String? = null,
    @Json(name = "jumlah_attachment") val jumlahAttachment: Int? = null
)

@JsonClass(generateAdapter = true)
data class NewsDetailResponse(
    @Json(name = "id") val id: Long,
    @Json(name = "judul") val judul: String? = null,
    @Json(name = "content") val konten: String? = null,
    @Json(name = "gambar") val gambar: String? = null,
    @Json(name = "created_at") val tanggal: String? = null,
    @Json(name = "attachment") val attachment: List<AttachmentResponse>? = null
)
