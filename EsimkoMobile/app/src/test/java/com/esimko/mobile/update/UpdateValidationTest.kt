package com.esimko.mobile.update

import org.junit.Assert.*
import org.junit.Test

class UpdateValidationTest {
    private fun valid(name: String? = "1.0.25", code: Long = 26, pkg: String = "com.esimko.mobile") =
        validUpdatePackage(pkg, name, code, "com.esimko.mobile", "1.0.25", 26, 24)

    @Test fun acceptsOnlyHttpsDownloadUrlsWithoutCredentials() {
        assertTrue(validUpdateUrl("https://updates.example.test/mobile.apk"))
        assertFalse(validUpdateUrl("http://updates.example.test/mobile.apk"))
        assertFalse(validUpdateUrl("https://user:password@updates.example.test/mobile.apk"))
        assertFalse(validUpdateUrl("https:///mobile.apk"))
        assertFalse(validUpdateUrl("intent://installer"))
        assertFalse(validUpdateUrl(null))
    }
    @Test fun downloadIdentityChangesForSameNameRebuilds() {
        val old = updateDownloadKey("https://example.test/app.apk", 34, "a".repeat(64))
        assertNotEquals(old, updateDownloadKey("https://example.test/app.apk", 35, "a".repeat(64)))
        assertNotEquals(old, updateDownloadKey("https://example.test/app.apk", 34, "b".repeat(64)))
        assertNotEquals(old, updateDownloadKey("https://example.test/new.apk", 34, "a".repeat(64)))
        assertEquals(old, updateDownloadKey("https://example.test/app.apk", 34, "A".repeat(64)))
    }

    @Test fun acceptsMatchingNewRelease() { assertTrue(valid()) }
    @Test fun rejectsOldApkRenamedAsNewRelease() { assertFalse(valid("1.0.23", 24)) }
    @Test fun rejectsWrongPackage() { assertFalse(valid(pkg = "other.app")) }
    @Test fun rejectsMissingVersion() { assertFalse(valid(name = null)) }
    @Test fun rejectsWrongCodeDespiteMatchingName() { assertFalse(valid(code = 25)) }
    @Test fun rejectsReinstallOrDowngrade() {
        assertFalse(validUpdatePackage("app", "v", 24, "app", "v", 24, 24))
    }
    @Test fun hashMustBePresentValidAndMatch() {
        val hash = "a".repeat(64)
        assertTrue(validUpdateHash(hash.uppercase(), hash))
        assertFalse(validUpdateHash(hash, null))
        assertFalse(validUpdateHash(null, hash))
        assertFalse(validUpdateHash(hash, ""))
        assertFalse(validUpdateHash(hash, "b".repeat(64)))
    }
}
