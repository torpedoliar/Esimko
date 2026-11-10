package com.esimko.mobile.ui.common

import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.ColumnScope
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.Shape
import androidx.compose.ui.graphics.luminance
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.unit.Dp
import androidx.compose.ui.unit.dp

/**
 * Latar kartu. `background` dan `surface` tema sama warnanya, jadi kartu memakai putih
 * (terang) / hijau sangat gelap sedikit lebih terang dari latar (gelap) supaya terangkat.
 * Dibaca dari luminans latar — bukan `isSystemInDarkTheme()` — karena mode tampilan
 * bisa dipilih anggota di Pengaturan.
 */
@Composable
fun cardContainerColor(): Color =
    if (MaterialTheme.colorScheme.background.luminance() < 0.5f) Color(0xFF18211B) else Color.White

/** Kartu datar dengan garis tipis — kesan rapi tanpa bayangan berat. */
@Composable
fun PremiumCard(
    modifier: Modifier = Modifier,
    shape: Shape = RoundedCornerShape(20.dp),
    content: @Composable ColumnScope.() -> Unit
) {
    Column(
        modifier = modifier
            .fillMaxWidth()
            .clip(shape)
            .background(cardContainerColor(), shape)
            .border(1.dp, MaterialTheme.colorScheme.outlineVariant.copy(alpha = 0.45f), shape),
        content = content
    )
}

/** Ikon di dalam kotak membulat berwarna aksen pudar. */
@Composable
fun IconBadge(
    icon: ImageVector,
    tint: Color,
    modifier: Modifier = Modifier,
    size: Dp = 44.dp,
    iconSize: Dp = 22.dp
) {
    Box(
        modifier = modifier
            .size(size)
            .background(tint.copy(alpha = 0.13f), RoundedCornerShape(size * 0.32f)),
        contentAlignment = Alignment.Center
    ) {
        Icon(icon, contentDescription = null, tint = tint, modifier = Modifier.size(iconSize))
    }
}
