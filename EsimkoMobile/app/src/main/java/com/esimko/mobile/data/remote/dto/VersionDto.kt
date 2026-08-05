package com.esimko.mobile.data.remote.dto

import com.squareup.moshi.Json
import com.squareup.moshi.JsonClass

@JsonClass(generateAdapter = true)
data class VersionResponse(
    @Json(name = "version") val version: String? = null,
    @Json(name = "build") val build: Int? = 0,
    @Json(name = "min_build") val minBuild: Int? = 0,
    @Json(name = "url") val url: String? = null
)

@JsonClass(generateAdapter = true)
data class VersionCheckResponse(
    @Json(name = "update_available") val updateAvailable: Boolean? = false,
    @Json(name = "force_update") val forceUpdate: Boolean? = false,
    @Json(name = "message") val message: String? = null,
    @Json(name = "url") val url: String? = null
)
