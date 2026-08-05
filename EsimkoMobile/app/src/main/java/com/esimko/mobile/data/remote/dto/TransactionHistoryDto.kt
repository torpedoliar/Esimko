package com.esimko.mobile.data.remote.dto

import com.squareup.moshi.JsonClass

@JsonClass(generateAdapter = true)
data class TransactionHistoryResponse(
    val created_at: String,
    val caption: String,
    val no_anggota: String,
    val nama_lengkap: String
)
