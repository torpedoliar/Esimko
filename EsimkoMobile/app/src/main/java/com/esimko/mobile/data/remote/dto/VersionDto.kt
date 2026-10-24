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
    @Json(name = "updateAvailable") val updateAvailable: Boolean? = false,
    @Json(name = "forceUpdate") val forceUpdate: Boolean? = false,
    @Json(name = "message") val message: String? = null,
    @Json(name = "url") val url: String? = null,
    // Info paket utk self-update (backend: modul Rilis Aplikasi → version.json).
    @Json(name = "latestVersion") val latestVersion: String? = null,
    @Json(name = "latestVersionCode") val latestVersionCode: Int? = 0,
    @Json(name = "apkUrl") val apkUrl: String? = null,
    @Json(name = "apkSha256") val apkSha256: String? = null,
    @Json(name = "apkUkuran") val apkUkuran: Long? = 0L
)
