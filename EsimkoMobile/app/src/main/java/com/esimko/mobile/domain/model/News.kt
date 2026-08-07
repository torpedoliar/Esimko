package com.esimko.mobile.domain.model

data class News(
    val id: Long,
    val judul: String,
    val ringkasan: String?,
    val gambar: String?,
    val tanggal: String,
    val jumlahAttachment: Int = 0
)

data class NewsAttachment(
    val id: Long,
    val judul: String,
    val url: String
)

data class NewsDetail(
    val id: Long,
    val judul: String,
    val konten: String,
    val gambar: String?,
    val tanggal: String,
    val attachments: List<NewsAttachment> = emptyList()
)
