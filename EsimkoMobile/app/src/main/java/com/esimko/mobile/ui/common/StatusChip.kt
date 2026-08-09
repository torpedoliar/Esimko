package com.esimko.mobile.ui.common

import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.Block
import androidx.compose.material.icons.outlined.Cancel
import androidx.compose.material.icons.outlined.CheckCircle
import androidx.compose.material.icons.outlined.Info
import androidx.compose.material.icons.outlined.Schedule
import androidx.compose.material.icons.outlined.Verified
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.unit.dp
import com.esimko.mobile.util.StatusIcon
import com.esimko.mobile.util.StatusMeta

@Composable
fun StatusChip(
    status: String,
    color: String?,
    modifier: Modifier = Modifier
) {
    val accent = StatusMeta.parseColor(color) ?: MaterialTheme.colorScheme.outline
    Row(
        modifier = modifier
            .border(1.dp, accent.copy(alpha = 0.5f), RoundedCornerShape(50))
            .background(accent.copy(alpha = 0.14f), RoundedCornerShape(50))
            .padding(horizontal = 8.dp, vertical = 4.dp),
        verticalAlignment = Alignment.CenterVertically,
        horizontalArrangement = Arrangement.spacedBy(4.dp)
    ) {
        Icon(
            imageVector = StatusMeta.iconFor(status).vector(),
            contentDescription = null,
            modifier = Modifier.size(14.dp),
            tint = accent
        )
        Text(
            text = status,
            style = MaterialTheme.typography.labelSmall,
            color = accent,
            maxLines = 1
        )
    }
}

private fun StatusIcon.vector(): ImageVector = when (this) {
    StatusIcon.PENDING -> Icons.Outlined.Schedule
    StatusIcon.APPROVED -> Icons.Outlined.Verified
    StatusIcon.REJECTED -> Icons.Outlined.Cancel
    StatusIcon.DONE -> Icons.Outlined.CheckCircle
    StatusIcon.CANCELLED -> Icons.Outlined.Block
    StatusIcon.UNKNOWN -> Icons.Outlined.Info
}

@LightDarkPreview
@Composable
private fun StatusChipPreview() {
    EsimkoPreview {
        Column(
            modifier = Modifier.padding(16.dp),
            verticalArrangement = Arrangement.spacedBy(8.dp)
        ) {
            StatusChip("Belum Verifikasi", "#e67e22")
            StatusChip("Disetujui", "#2ea3cc")
            StatusChip("Selesai", "#27ae60")
            StatusChip("Ditolak", "#e74c3c")
            StatusChip("Dibatalkan", "#95a5a6")
            StatusChip("Pinjaman Lunas", "#8e44ad")
            StatusChip("Dibayar", "#27ae60")
            StatusChip("Simulasi", "#7f8c8d")
            StatusChip("Status Aneh", null)   // fallback tema
        }
    }
}
