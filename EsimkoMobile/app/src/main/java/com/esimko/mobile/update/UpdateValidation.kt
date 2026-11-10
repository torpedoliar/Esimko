package com.esimko.mobile.update

internal fun validUpdatePackage(
    packageName: String?, versionName: String?, versionCode: Long,
    expectedPackage: String, expectedVersion: String?, expectedCode: Long, installedCode: Long
): Boolean = packageName == expectedPackage && !expectedVersion.isNullOrBlank() &&
    versionName == expectedVersion && expectedCode > installedCode && versionCode == expectedCode

internal fun validUpdateUrl(url: String?): Boolean = try {
    val uri = java.net.URI(url.orEmpty())
    uri.scheme.equals("https", ignoreCase = true) && !uri.host.isNullOrBlank() &&
        uri.rawUserInfo == null && uri.rawFragment == null
} catch (_: Exception) { false }

internal fun updateDownloadKey(url: String?, code: Int, hash: String?): String =
    "$code:${hash?.lowercase(java.util.Locale.ROOT)}:$url"

internal fun validUpdateHash(actual: String?, expected: String?): Boolean =
    expected != null && expected.matches(Regex("[a-fA-F0-9]{64}")) &&
        actual != null && actual.equals(expected, ignoreCase = true)
