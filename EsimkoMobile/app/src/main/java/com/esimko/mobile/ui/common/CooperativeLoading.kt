package com.esimko.mobile.ui.common

import androidx.compose.foundation.Canvas
import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.isSystemInDarkTheme
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.StrokeCap
import androidx.compose.ui.graphics.drawscope.Stroke
import androidx.compose.ui.graphics.luminance
import androidx.compose.ui.res.painterResource
import androidx.compose.ui.semantics.LiveRegionMode
import androidx.compose.ui.semantics.ProgressBarRangeInfo
import androidx.compose.ui.semantics.liveRegion
import androidx.compose.ui.semantics.progressBarRangeInfo
import androidx.compose.ui.semantics.semantics
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import com.esimko.mobile.R

/** Logo tetap terbaca; hanya cincin luarnya yang bergerak selama memuat data. */
@Composable
fun CooperativeLoading(
    message: String,
    modifier: Modifier = Modifier
) {
    StatusBarAppearance(darkIcons = MaterialTheme.colorScheme.background.luminance() > 0.5f)
    val animate = animationsEnabled()
    val ringColor = MaterialTheme.colorScheme.primary
    val trackColor = MaterialTheme.colorScheme.outlineVariant

    Column(
        modifier = modifier
            .fillMaxSize()
            .background(MaterialTheme.colorScheme.background)
            .padding(24.dp)
            .semantics { liveRegion = LiveRegionMode.Polite },
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.spacedBy(20.dp, Alignment.CenterVertically)
    ) {
        Box(Modifier.size(116.dp), contentAlignment = Alignment.Center) {
            if (animate) {
                CircularProgressIndicator(
                    modifier = Modifier.fillMaxSize(),
                    color = ringColor,
                    trackColor = trackColor,
                    strokeWidth = 3.dp
                )
            } else {
                Canvas(
                    Modifier.fillMaxSize().semantics {
                        progressBarRangeInfo = ProgressBarRangeInfo.Indeterminate
                    }
                ) {
                    val stroke = Stroke(width = 3.dp.toPx(), cap = StrokeCap.Round)
                    val inset = stroke.width / 2
                    val diameter = size.minDimension - stroke.width
                    drawCircle(trackColor, radius = diameter / 2, style = stroke)
                    drawArc(
                        color = ringColor,
                        startAngle = -90f,
                        sweepAngle = 100f,
                        useCenter = false,
                        topLeft = androidx.compose.ui.geometry.Offset(inset, inset),
                        size = androidx.compose.ui.geometry.Size(diameter, diameter),
                        style = stroke
                    )
                }
            }
            // Alas putih menjaga warna lambang koperasi terbaca di tema gelap.
            Surface(shape = CircleShape, color = Color.White, modifier = Modifier.size(92.dp)) {
                Image(
                    painter = painterResource(R.drawable.logo_esimko_seal),
                    contentDescription = "Logo Koperasi SIMKO",
                    modifier = Modifier.padding(8.dp)
                )
            }
        }
        Text(
            text = message,
            style = MaterialTheme.typography.bodyMedium,
            color = MaterialTheme.colorScheme.onSurfaceVariant,
            textAlign = TextAlign.Center
        )
    }
}

@LightDarkPreview
@Composable
private fun CooperativeLoadingPreview() = EsimkoPreview(darkTheme = isSystemInDarkTheme()) {
    CooperativeLoading(message = "Memuat akun Anda…")
}
