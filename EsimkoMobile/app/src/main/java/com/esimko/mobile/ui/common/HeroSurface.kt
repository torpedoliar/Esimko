package com.esimko.mobile.ui.common

import androidx.compose.foundation.background
import androidx.compose.foundation.isSystemInDarkTheme
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.ColumnScope
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.windowInsetsPadding
import androidx.compose.foundation.layout.WindowInsets
import androidx.compose.foundation.layout.statusBars
import androidx.compose.material3.LocalContentColor
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.CompositionLocalProvider
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.unit.dp
import com.esimko.mobile.ui.theme.DarkHeroGreen
import com.esimko.mobile.ui.theme.GoldOnHero
import com.esimko.mobile.ui.theme.HeroGreen
import com.esimko.mobile.ui.theme.MoneyHero
import com.esimko.mobile.ui.theme.OnHero

/** Hijau pekat hero, satu-satunya di app (spec §3 aturan 1). */
@Composable
fun heroBackground(): Color = if (isSystemInDarkTheme()) DarkHeroGreen else HeroGreen

/**
 * Latar hijau pekat yang mengalir ke belakang status bar. Mengambil insets
 * atasnya sendiri — pemanggil TIDAK boleh memberi padding status bar lagi,
 * atau hijaunya berhenti sebelum status bar dan terlihat seperti bug.
 */
@Composable
fun HeroSurface(
    modifier: Modifier = Modifier,
    content: @Composable ColumnScope.() -> Unit
) {
    Column(
        modifier = modifier
            .fillMaxWidth()
            .background(heroBackground())
            .windowInsetsPadding(WindowInsets.statusBars)
            .padding(horizontal = 16.dp, vertical = 16.dp)
    ) {
        CompositionLocalProvider(LocalContentColor provides OnHero) { content() }
    }
}

@LightDarkPreview
@Composable
private fun HeroSurfacePreview() {
    EsimkoPreview {
        Column {
            HeroSurface {
                Text(
                    "Saldo Simpanan",
                    style = MaterialTheme.typography.labelMedium
                )
                Money(amount = 4_250_000L, style = MoneyHero, color = GoldOnHero)
            }
        }
    }
}
