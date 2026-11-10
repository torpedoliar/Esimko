package com.esimko.mobile.ui.activity

import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.selection.selectable
import androidx.compose.foundation.selection.selectableGroup
import androidx.compose.ui.semantics.Role
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyRow
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.BasicTextField
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Close
import androidx.compose.material.icons.outlined.FilterList
import androidx.compose.material.icons.outlined.KeyboardArrowDown
import androidx.compose.material.icons.outlined.Search
import androidx.compose.material.icons.outlined.SwapVert
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.SolidColor
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.unit.dp
import com.esimko.mobile.ui.common.EsimkoPreview
import com.esimko.mobile.ui.common.LightDarkPreview
import com.esimko.mobile.ui.common.cardContainerColor

@OptIn(ExperimentalLayoutApi::class)
@Composable
internal fun ActivityFilters(
    state: ActivityState,
    onSegment: (ActivitySegment) -> Unit,
    onStatus: (Int?) -> Unit,
    onSearch: (String) -> Unit,
    onSort: () -> Unit
) {
    Column(
        Modifier.fillMaxWidth().padding(vertical = 4.dp),
        verticalArrangement = Arrangement.spacedBy(12.dp)
    ) {
        LazyRow(
            modifier = Modifier.selectableGroup(),
            contentPadding = PaddingValues(horizontal = 16.dp),
            horizontalArrangement = Arrangement.spacedBy(8.dp)
        ) {
            items(ActivitySegment.values().toList(), key = { it.filterKey }) { segment ->
                SegmentChip(
                    label = segment.label,
                    icon = segment.icon(),
                    selected = segment == state.segment,
                    onClick = { if (segment != state.segment) onSegment(segment) }
                )
            }
        }

        SearchPill(
            value = state.search,
            placeholder = "Cari ${state.segment.label.lowercase()}…",
            onValueChange = onSearch,
            modifier = Modifier.padding(horizontal = 16.dp)
        )

        FlowRow(
            Modifier.fillMaxWidth().padding(horizontal = 16.dp),
            horizontalArrangement = Arrangement.spacedBy(8.dp),
            verticalArrangement = Arrangement.spacedBy(8.dp)
        ) {
            if (state.showStatusFilter) {
                val status = state.statuses.firstOrNull { it.id == state.selectedStatusId }?.nama
                FilterPill(
                    icon = Icons.Outlined.FilterList,
                    text = status ?: "Semua status",
                    active = status != null
                ) { close ->
                    DropdownMenuItem(text = { Text("Semua status") }, onClick = { close(); onStatus(null) })
                    state.statuses.forEach { item ->
                        DropdownMenuItem(text = { Text(item.nama) }, onClick = { close(); onStatus(item.id) })
                    }
                }
            }
            FilterPill(
                icon = Icons.Outlined.SwapVert,
                text = if (state.sortTerbaru) "Terbaru" else "Terlama",
                active = false
            ) { close ->
                DropdownMenuItem(text = { Text("Terbaru dulu") }, onClick = { close(); if (!state.sortTerbaru) onSort() })
                DropdownMenuItem(text = { Text("Terlama dulu") }, onClick = { close(); if (state.sortTerbaru) onSort() })
            }
        }
    }
}

@Composable
private fun SegmentChip(label: String, icon: ImageVector, selected: Boolean, onClick: () -> Unit) {
    val shape = RoundedCornerShape(50)
    val container = if (selected) MaterialTheme.colorScheme.primary else cardContainerColor()
    val content = if (selected) MaterialTheme.colorScheme.onPrimary else MaterialTheme.colorScheme.onSurface
    Row(
        modifier = Modifier
            .heightIn(min = 48.dp)
            .clip(shape)
            .background(container, shape)
            .border(
                1.dp,
                if (selected) MaterialTheme.colorScheme.primary else MaterialTheme.colorScheme.outlineVariant,
                shape
            )
            .selectable(selected = selected, role = Role.Tab, onClick = onClick)
            .padding(horizontal = 14.dp, vertical = 8.dp),
        verticalAlignment = Alignment.CenterVertically,
        horizontalArrangement = Arrangement.spacedBy(6.dp)
    ) {
        Icon(icon, contentDescription = null, tint = content, modifier = Modifier.size(18.dp))
        Text(
            label,
            style = MaterialTheme.typography.labelLarge,
            fontWeight = if (selected) FontWeight.SemiBold else FontWeight.Medium,
            color = content
        )
    }
}

@Composable
private fun SearchPill(
    value: String,
    placeholder: String,
    onValueChange: (String) -> Unit,
    modifier: Modifier = Modifier
) {
    val shape = RoundedCornerShape(16.dp)
    Row(
        modifier = modifier
            .fillMaxWidth()
            .heightIn(min = 50.dp)
            .background(cardContainerColor(), shape)
            .border(1.dp, MaterialTheme.colorScheme.outlineVariant.copy(alpha = 0.7f), shape)
            .padding(start = 14.dp, end = 4.dp),
        verticalAlignment = Alignment.CenterVertically
    ) {
        Icon(
            Icons.Outlined.Search,
            contentDescription = null,
            tint = MaterialTheme.colorScheme.onSurfaceVariant,
            modifier = Modifier.size(20.dp)
        )
        Spacer(Modifier.width(10.dp))
        Box(Modifier.weight(1f), contentAlignment = Alignment.CenterStart) {
            if (value.isEmpty()) {
                Text(
                    placeholder,
                    style = MaterialTheme.typography.bodyMedium,
                    color = MaterialTheme.colorScheme.onSurfaceVariant.copy(alpha = 0.8f)
                )
            }
            BasicTextField(
                value = value,
                onValueChange = onValueChange,
                singleLine = true,
                textStyle = MaterialTheme.typography.bodyMedium.copy(color = MaterialTheme.colorScheme.onSurface),
                cursorBrush = SolidColor(MaterialTheme.colorScheme.primary),
                keyboardOptions = KeyboardOptions(imeAction = ImeAction.Search),
                modifier = Modifier.fillMaxWidth()
            )
        }
        if (value.isNotEmpty()) {
            IconButton(onClick = { onValueChange("") }) {
                Icon(Icons.Default.Close, contentDescription = "Hapus pencarian", modifier = Modifier.size(18.dp))
            }
        } else {
            Spacer(Modifier.size(10.dp))
        }
    }
}

@Composable
private fun FilterPill(
    icon: ImageVector,
    text: String,
    active: Boolean,
    items: @Composable (() -> Unit) -> Unit
) {
    var expanded by remember { mutableStateOf(false) }
    val shape = RoundedCornerShape(50)
    val tint = if (active) MaterialTheme.colorScheme.primary else MaterialTheme.colorScheme.onSurfaceVariant
    Box {
        Row(
            modifier = Modifier
                .heightIn(min = 48.dp)
                .clip(shape)
                .background(
                    if (active) MaterialTheme.colorScheme.primary.copy(alpha = 0.10f) else cardContainerColor(),
                    shape
                )
                .border(
                    1.dp,
                    if (active) MaterialTheme.colorScheme.primary.copy(alpha = 0.5f)
                    else MaterialTheme.colorScheme.outlineVariant.copy(alpha = 0.7f),
                    shape
                )
                .clickable { expanded = true }
                .padding(horizontal = 12.dp, vertical = 6.dp),
            verticalAlignment = Alignment.CenterVertically,
            horizontalArrangement = Arrangement.spacedBy(6.dp)
        ) {
            Icon(icon, contentDescription = null, tint = tint, modifier = Modifier.size(16.dp))
            Text(
                text,
                style = MaterialTheme.typography.labelMedium,
                color = if (active) MaterialTheme.colorScheme.primary else MaterialTheme.colorScheme.onSurface,
                maxLines = 1
            )
            Icon(Icons.Outlined.KeyboardArrowDown, contentDescription = null, tint = tint, modifier = Modifier.size(16.dp))
        }
        DropdownMenu(expanded = expanded, onDismissRequest = { expanded = false }) {
            items { expanded = false }
        }
    }
}

@LightDarkPreview
@Composable
private fun ActivityFiltersPreview() = EsimkoPreview {
    ActivityFilters(ActivityState(), {}, {}, {}, {})
}
