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
    val url: String?,
    // Paket utk self-update in-app (modul Rilis Aplikasi di server).
    val latestVersion: String? = null,
    val latestVersionCode: Int = 0,
    val apkUrl: String? = null,
    val apkSha256: String? = null,
    val apkUkuran: Long = 0L
)
