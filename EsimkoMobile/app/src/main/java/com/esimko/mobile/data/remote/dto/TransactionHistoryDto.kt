package com.esimko.mobile.data.remote.dto

import com.squareup.moshi.JsonClass

@JsonClass(generateAdapter = true)
data class TransactionHistoryResponse(
    val created_at: String? = null,
    val caption: String? = null,
    val no_anggota: String? = null,
    val nama_lengkap: String? = null
)
