package com.esimko.mobile.data.remote.dto

import com.squareup.moshi.JsonClass

@JsonClass(generateAdapter = true)
data class ApiResponse<T>(
    val success: Boolean,
    val message: String?,
    val data: T?,
    val meta: MetaResponse?
)

@JsonClass(generateAdapter = true)
data class MetaResponse(
    val page: Int?,
    val last_page: Int?,
    val per_page: Int?,
    val total: Int?
)
