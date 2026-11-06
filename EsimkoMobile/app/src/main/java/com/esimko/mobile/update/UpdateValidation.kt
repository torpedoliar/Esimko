package com.esimko.mobile.update

internal fun validUpdatePackage(
    packageName: String?, versionName: String?, versionCode: Long,
    expectedPackage: String, expectedVersion: String?, expectedCode: Long, installedCode: Long
): Boolean = packageName == expectedPackage && !expectedVersion.isNullOrBlank() &&
    versionName == expectedVersion && expectedCode > installedCode && versionCode == expectedCode

internal fun validUpdateHash(actual: String?, expected: String?): Boolean =
    expected != null && expected.matches(Regex("[a-fA-F0-9]{64}")) &&
        actual != null && actual.equals(expected, ignoreCase = true)
