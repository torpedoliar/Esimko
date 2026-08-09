package com.esimko.mobile.ui.theme

import com.google.common.truth.Truth.assertThat
import org.junit.Test

/**
 * Saldo tidak boleh goyang saat angka berubah. Satu-satunya cara menjamin itu
 * adalah tnum, dan satu-satunya cara menjaganya adalah test ini.
 */
class TypeTest {

    @Test
    fun `gaya nominal memakai tabular figures`() {
        assertThat(MoneyHero.fontFeatureSettings).isEqualTo("tnum")
        assertThat(MoneyRow.fontFeatureSettings).isEqualTo("tnum")
        assertThat(MoneySmall.fontFeatureSettings).isEqualTo("tnum")
    }

    @Test
    fun `ukuran gaya nominal sesuai spec`() {
        assertThat(MoneyHero.fontSize.value).isEqualTo(32f)
        assertThat(MoneyRow.fontSize.value).isEqualTo(16f)
        assertThat(MoneySmall.fontSize.value).isEqualTo(14f)
    }

    @Test
    fun `tidak ada gaya teks di bawah 12sp`() {
        val sizes = listOf(
            Typography.bodySmall, Typography.labelSmall, Typography.labelMedium,
            Typography.bodyMedium, Typography.titleSmall
        ).map { it.fontSize.value }
        assertThat(sizes.min()).isAtLeast(12f)
    }
}
