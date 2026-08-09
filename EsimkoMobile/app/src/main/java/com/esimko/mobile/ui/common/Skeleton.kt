package com.esimko.mobile.ui.common

import androidx.compose.animation.core.LinearEasing
import androidx.compose.animation.core.RepeatMode
import androidx.compose.animation.core.animateFloat
import androidx.compose.animation.core.infiniteRepeatable
import androidx.compose.animation.core.rememberInfiniteTransition
import androidx.compose.animation.core.tween
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.aspectRatio
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.material3.MaterialTheme
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Shape
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.unit.dp
import android.provider.Settings
import androidx.compose.runtime.remember

// ponytail: Compose belum mengekspos prefers-reduced-motion secara langsung di
// BOM ini. Baca Settings.Global.ANIMATOR_DURATION_SCALE — nilai 0 berarti
// pengguna mematikan animasi di Developer options / Accessibility.
@Composable
internal fun animationsEnabled(): Boolean {
    val context = LocalContext.current
    return remember(context) {
        Settings.Global.getFloat(context.contentResolver, Settings.Global.ANIMATOR_DURATION_SCALE, 1f) != 0f
    }
}

@Composable
fun SkeletonBox(
    modifier: Modifier = Modifier,
    shape: Shape = MaterialTheme.shapes.small
) {
    val animate = animationsEnabled()
    val alpha = if (animate) {
        val transition = rememberInfiniteTransition(label = "skeleton")
        val a by transition.animateFloat(
            initialValue = 0.06f,
            targetValue = 0.14f,
            animationSpec = infiniteRepeatable(
                animation = tween(900, easing = LinearEasing),
                repeatMode = RepeatMode.Reverse
            ),
            label = "skeletonAlpha"
        )
        a
    } else {
        0.10f
    }
    Box(
        modifier = modifier
            .clip(shape)
            .background(MaterialTheme.colorScheme.onSurface.copy(alpha = alpha))
    )
}

@Composable
fun SkeletonListRows(count: Int = 6, modifier: Modifier = Modifier) {
    Column(modifier = modifier.fillMaxWidth()) {
        repeat(count) { i ->
            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .height(56.dp)
                    .padding(horizontal = 16.dp, vertical = 8.dp),
                verticalAlignment = androidx.compose.ui.Alignment.CenterVertically,
                horizontalArrangement = Arrangement.spacedBy(12.dp)
            ) {
                SkeletonBox(modifier = Modifier.size(40.dp), shape = CircleShape)
                Column(modifier = Modifier.weight(1f)) {
                    SkeletonBox(modifier = Modifier.fillMaxWidth(0.55f).height(14.dp))
                    Spacer(Modifier.height(4.dp))
                    SkeletonBox(modifier = Modifier.fillMaxWidth(0.3f).height(12.dp))
                }
                SkeletonBox(modifier = Modifier.size(width = 80.dp, height = 14.dp))
            }
            if (i < count - 1) RowDivider()
        }
    }
}

@Composable
fun SkeletonHero(modifier: Modifier = Modifier) {
    Column(modifier = modifier.padding(16.dp)) {
        SkeletonBox(modifier = Modifier.fillMaxWidth(0.4f).height(12.dp))
        Spacer(Modifier.height(8.dp))
        SkeletonBox(modifier = Modifier.fillMaxWidth(0.65f).height(32.dp))
    }
}

@Composable
fun SkeletonProductGrid(count: Int = 6, modifier: Modifier = Modifier) {
    Column(modifier = modifier.fillMaxWidth().padding(horizontal = 8.dp)) {
        (0 until count).chunked(2).forEach { row ->
            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                row.forEach {
                    Column(Modifier.weight(1f).padding(4.dp)) {
                        SkeletonBox(
                            modifier = Modifier
                                .fillMaxWidth()
                                .aspectRatio(1f)
                                .clip(MaterialTheme.shapes.medium)
                        )
                        Spacer(Modifier.height(8.dp))
                        SkeletonBox(modifier = Modifier.fillMaxWidth(0.8f).height(14.dp))
                        Spacer(Modifier.height(4.dp))
                        SkeletonBox(modifier = Modifier.fillMaxWidth(0.5f).height(14.dp))
                    }
                }
                if (row.size < 2) Spacer(Modifier.weight(1f))
            }
            Spacer(Modifier.height(8.dp))
        }
    }
}

@LightDarkPreview
@Composable
private fun SkeletonListRowsPreview() {
    EsimkoPreview {
        SkeletonListRows(count = 6)
    }
}
