package com.esimko.mobile.ui.activity

import androidx.compose.foundation.layout.*
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Close
import androidx.compose.material.icons.filled.Search
import androidx.compose.material.icons.filled.ArrowDropDown
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import com.esimko.mobile.ui.common.EsimkoPreview
import com.esimko.mobile.ui.common.LightDarkPreview

@Composable
internal fun ActivityFilters(
    state: ActivityState,
    onSegment: (ActivitySegment) -> Unit,
    onStatus: (Int?) -> Unit,
    onSearch: (String) -> Unit,
    onSort: () -> Unit
) {
    Column(
        Modifier.fillMaxWidth().padding(horizontal = 16.dp, vertical = 8.dp),
        verticalArrangement = Arrangement.spacedBy(8.dp)
    ) {
        FilterMenu("Jenis aktivitas", state.segment.label, Modifier.fillMaxWidth()) { close ->
            ActivitySegment.values().forEach { segment ->
                DropdownMenuItem(
                    text = { Text(segment.label) },
                    onClick = { close(); onSegment(segment) }
                )
            }
        }
        OutlinedTextField(
            value = state.search,
            onValueChange = onSearch,
            placeholder = { Text("Cari ${state.segment.label.lowercase()}…") },
            leadingIcon = { Icon(Icons.Default.Search, contentDescription = null) },
            trailingIcon = {
                if (state.search.isNotEmpty()) {
                    IconButton(onClick = { onSearch("") }) {
                        Icon(Icons.Default.Close, contentDescription = "Hapus pencarian")
                    }
                }
            },
            singleLine = true,
            modifier = Modifier.fillMaxWidth()
        )
        Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(8.dp)) {
            if (state.showStatusFilter) {
                val status = state.statuses.firstOrNull { it.id == state.selectedStatusId }?.nama ?: "Semua"
                FilterMenu("Status", status, Modifier.weight(1f)) { close ->
                    DropdownMenuItem(text = { Text("Semua") }, onClick = { close(); onStatus(null) })
                    state.statuses.forEach { item ->
                        DropdownMenuItem(text = { Text(item.nama) }, onClick = { close(); onStatus(item.id) })
                    }
                }
            }
            FilterMenu("Urutan", if (state.sortTerbaru) "Terbaru" else "Terlama", Modifier.weight(1f)) { close ->
                DropdownMenuItem(text = { Text("Terbaru") }, onClick = { close(); if (!state.sortTerbaru) onSort() })
                DropdownMenuItem(text = { Text("Terlama") }, onClick = { close(); if (state.sortTerbaru) onSort() })
            }
        }
    }
}

@Composable
private fun FilterMenu(
    label: String,
    value: String,
    modifier: Modifier,
    items: @Composable (() -> Unit) -> Unit
) {
    var expanded by remember { mutableStateOf(false) }
    Box(modifier) {
        OutlinedButton(
            onClick = { expanded = true },
            modifier = Modifier.fillMaxWidth().heightIn(min = 56.dp),
            shape = MaterialTheme.shapes.small,
            contentPadding = PaddingValues(horizontal = 12.dp, vertical = 8.dp)
        ) {
            Column(Modifier.weight(1f)) {
                Text(label, style = MaterialTheme.typography.labelSmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
                Text(value, style = MaterialTheme.typography.bodyMedium, color = MaterialTheme.colorScheme.onSurface)
            }
            Icon(Icons.Default.ArrowDropDown, contentDescription = null)
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
