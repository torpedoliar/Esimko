package com.esimko.mobile.ui.common

import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.AccountBalance
import androidx.compose.material.icons.outlined.AssignmentReturn
import androidx.compose.material.icons.outlined.Campaign
import androidx.compose.material.icons.outlined.EventRepeat
import androidx.compose.material.icons.outlined.MoveDown
import androidx.compose.material.icons.outlined.ReceiptLong
import androidx.compose.material.icons.outlined.RequestQuote
import androidx.compose.material.icons.outlined.Savings
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp

data class ActionItem(
    val label: String,
    val icon: ImageVector,
    val onClick: () -> Unit,
    val enabled: Boolean = true
)

@Composable
fun ActionGrid(
    items: List<ActionItem>,
    modifier: Modifier = Modifier,
    columns: Int = 4
) {
    Column(
        modifier = modifier.fillMaxWidth().padding(horizontal = 8.dp),
        verticalArrangement = Arrangement.spacedBy(8.dp)
    ) {
        items.chunked(columns).forEach { row ->
            Row(modifier = Modifier.fillMaxWidth()) {
                row.forEach { item ->
                    ActionCell(item, Modifier.weight(1f))
                }
                repeat(columns - row.size) { Spacer(Modifier.weight(1f)) }
            }
        }
    }
}

@Composable
private fun ActionCell(item: ActionItem, modifier: Modifier = Modifier) {
    Column(
        modifier = modifier
            .clip(MaterialTheme.shapes.medium)
            .clickable(enabled = item.enabled, onClick = item.onClick)
            .padding(vertical = 8.dp, horizontal = 4.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.spacedBy(4.dp)
    ) {
        Box(
            modifier = Modifier
                .size(44.dp)
                .clip(CircleShape)
                .background(MaterialTheme.colorScheme.primary.copy(alpha = 0.10f)),
            contentAlignment = Alignment.Center
        ) {
            Icon(
                imageVector = item.icon,
                contentDescription = null,
                tint = if (item.enabled) MaterialTheme.colorScheme.primary
                else MaterialTheme.colorScheme.onSurface.copy(alpha = 0.38f),
                modifier = Modifier.size(24.dp)
            )
        }
        Text(
            text = item.label,
            style = MaterialTheme.typography.labelMedium,
            textAlign = TextAlign.Center,
            maxLines = 2,
            minLines = 2
        )
    }
}

@LightDarkPreview
@Composable
private fun ActionGridPreview() {
    EsimkoPreview {
        ActionGrid(
            items = listOf(
                ActionItem("Setor", Icons.Outlined.Savings, {}),
                ActionItem("Tarik", Icons.Outlined.MoveDown, {}),
                ActionItem("Pinjaman", Icons.Outlined.RequestQuote, {}),
                ActionItem("Angsuran", Icons.Outlined.EventRepeat, {}),
                ActionItem("Angsuran Belanja", Icons.Outlined.ReceiptLong, {}),
                ActionItem("Retur Barang", Icons.Outlined.AssignmentReturn, {}),
                ActionItem("Simpanan", Icons.Outlined.AccountBalance, {}),
                ActionItem("Berita", Icons.Outlined.Campaign, {})
            ),
            modifier = Modifier.padding(vertical = 16.dp)
        )
    }
}
