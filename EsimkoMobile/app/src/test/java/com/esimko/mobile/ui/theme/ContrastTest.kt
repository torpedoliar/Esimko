package com.esimko.mobile.ui.theme

import androidx.compose.ui.graphics.Color
import com.google.common.truth.Truth.assertThat
import org.junit.Test
import kotlin.math.max
import kotlin.math.min
import kotlin.math.pow

/**
 * Kontras WCAG dihitung dari token yang benar-benar dipakai, bukan dari angka di dokumen.
 * Kalau seseorang mengubah satu hex dan diam-diam merusak keterbacaan, test ini yang jatuh.
 */
class ContrastTest {

    private fun channel(c: Double) = if (c <= 0.03928) c / 12.92 else ((c + 0.055) / 1.055).pow(2.4)

    private fun luminance(color: Color): Double =
        0.2126 * channel(color.red.toDouble()) +
            0.7152 * channel(color.green.toDouble()) +
            0.0722 * channel(color.blue.toDouble())

    private fun ratio(a: Color, b: Color): Double {
        val la = luminance(a)
        val lb = luminance(b)
        return (max(la, lb) + 0.05) / (min(la, lb) + 0.05)
    }

    @Test
    fun `putih di hero light lulus AA`() {
        assertThat(ratio(OnHero, HeroGreen)).isAtLeast(4.5)
    }

    @Test
    fun `emas di hero light lulus AA`() {
        assertThat(ratio(GoldOnHero, HeroGreen)).isAtLeast(4.5)
    }

    @Test
    fun `emas di hero dark lulus AA`() {
        assertThat(ratio(GoldOnHero, DarkHeroGreen)).isAtLeast(4.5)
    }

    @Test
    fun `primary di surface light lulus AA`() {
        assertThat(ratio(Primary, Surface)).isAtLeast(4.5)
    }

    @Test
    fun `emas versi light di surface lulus AA`() {
        assertThat(ratio(GoldOnLight, Surface)).isAtLeast(4.5)
    }

    @Test
    fun `teks di surface light lulus AA`() {
        assertThat(ratio(OnSurface, Surface)).isAtLeast(4.5)
    }

    @Test
    fun `primary dark di surface dark lulus AA`() {
        assertThat(ratio(DarkPrimary, DarkSurface)).isAtLeast(4.5)
    }

    @Test
    fun `emas dark di surface dark lulus AA`() {
        assertThat(ratio(GoldOnDark, DarkSurface)).isAtLeast(4.5)
    }

    @Test
    fun `teks dark di surface dark lulus AA`() {
        assertThat(ratio(DarkOnSurface, DarkSurface)).isAtLeast(4.5)
    }

    /** Aturan yang paling mudah dilanggar: emas segel di atas putih. Dijaga sebagai larangan. */
    @Test
    fun `emas segel di atas putih memang gagal - itu sebabnya ada GoldOnLight`() {
        assertThat(ratio(GoldOnHero, Color.White)).isLessThan(4.5)
    }
}
