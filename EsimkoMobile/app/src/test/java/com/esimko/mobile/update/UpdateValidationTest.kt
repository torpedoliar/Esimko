package com.esimko.mobile.update

import org.junit.Assert.*
import org.junit.Test

class UpdateValidationTest {
    private fun valid(name: String? = "1.0.25", code: Long = 26, pkg: String = "com.esimko.mobile") =
        validUpdatePackage(pkg, name, code, "com.esimko.mobile", "1.0.25", 26, 24)

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
