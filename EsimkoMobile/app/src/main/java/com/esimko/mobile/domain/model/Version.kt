package com.esimko.mobile.domain.model

data class Version(
    val version: String,
    val build: Int,
    val minBuild: Int,
    val url: String?
)

data class VersionCheck(
    val updateAvailable: Boolean,
    val forceUpdate: Boolean,
    val message: String?,
    val url: String?
)
