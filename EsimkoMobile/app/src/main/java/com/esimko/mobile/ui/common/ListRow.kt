package com.esimko.mobile.ui.common

import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.heightIn
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp

@Composable
fun ListRow(
    title: String,
    modifier: Modifier = Modifier,
    subtitle: String? = null,
    leading: @Composable (() -> Unit)? = null,
    trailing: @Composable (() -> Unit)? = null,
    onClick: (() -> Unit)? = null
) {
    Row(
        modifier = modifier
            .fillMaxWidth()
            .let { if (onClick != null) it.clickable(onClick = onClick) else it }
            .heightIn(min = 56.dp)
            .padding(horizontal = 16.dp, vertical = 8.dp),
        verticalAlignment = Alignment.CenterVertically,
        horizontalArrangement = Arrangement.spacedBy(12.dp)
    ) {
        leading?.invoke()
        Column(Modifier.weight(1f)) {
            Text(
                text = title,
                style = MaterialTheme.typography.bodyLarge,
                maxLines = 2,
                overflow = TextOverflow.Ellipsis
            )
            if (subtitle != null) {
                Text(
                    text = subtitle,
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                    maxLines = 1,
                    overflow = TextOverflow.Ellipsis
                )
            }
        }
        trailing?.invoke()
    }
}

@LightDarkPreview
@Composable
private fun ListRowPreview() {
    EsimkoPreview {
        Column {
            SectionHeader(title = "Pengajuan Berjalan", actionLabel = "Lihat semua", onAction = {})
            Spacer(Modifier.height(8.dp))
            ListRow(
                title = "Setoran Simpanan Wajib",
                subtitle = "12 Agu 2026",
                trailing = { StatusChip("Belum Verifikasi", "#e67e22") }
            )
            RowDivider()
            ListRow(
                title = "Penarikan Simpanan Sukarela",
                subtitle = "10 Agu 2026",
                trailing = { Money(-250_000L) },
                onClick = {}
            )
            RowDivider()
            ListRow(
                title = "Judul transaksi yang sangat panjang sehingga harus dipotong setelah dua baris penuh",
                subtitle = "9 Agu 2026",
                trailing = { Money(1_500_000L) }
            )
        }
    }
}
