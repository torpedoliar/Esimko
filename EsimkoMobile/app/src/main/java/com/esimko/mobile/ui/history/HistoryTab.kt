package com.esimko.mobile.ui.history

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import com.esimko.mobile.domain.model.TransactionHistory
import com.esimko.mobile.ui.common.ErrorView
import com.esimko.mobile.ui.common.LoadingOverlay
import com.esimko.mobile.ui.common.UiState

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun HistoryTab(
    transactionId: Long = 0L,
    viewModel: HistoryViewModel = hiltViewModel()
) {
    val uiState by viewModel.uiState.collectAsState()
    val selectedModule by viewModel.selectedModule.collectAsState()

    val modules = listOf("transaksi", "penjualan")

    LaunchedEffect(transactionId) {
        if (transactionId > 0L) {
            viewModel.loadHistory(transactionId)
        }
    }

    Column(modifier = Modifier.fillMaxSize()) {
        // Module filter dropdown
        var expanded by remember { mutableStateOf(false) }

        Box(modifier = Modifier.padding(16.dp)) {
            OutlinedButton(onClick = { expanded = true }) {
                Text("Jenis: ${selectedModule.replaceFirstChar { it.uppercase() }}")
            }

            DropdownMenu(
                expanded = expanded,
                onDismissRequest = { expanded = false }
            ) {
                modules.forEach { module ->
                    DropdownMenuItem(
                        text = { Text(module.replaceFirstChar { it.uppercase() }) },
                        onClick = {
                            viewModel.selectModule(module)
                            expanded = false
                            if (transactionId > 0L) {
                                viewModel.loadHistory(transactionId)
                            }
                        }
                    )
                }
            }
        }

        // Content
        Box(modifier = Modifier.fillMaxSize()) {
            when (val state = uiState) {
                is UiState.Loading -> {
                    LoadingOverlay(isLoading = true)
                }
                is UiState.Error -> {
                    ErrorView(
                        message = state.message ?: "Terjadi kesalahan",
                        onRetry = { viewModel.loadHistory(transactionId) }
                    )
                }
                is UiState.Success -> {
                    val historyList = state.data
                    if (historyList.isEmpty()) {
                        Box(
                            modifier = Modifier.fillMaxSize(),
                            contentAlignment = Alignment.Center
                        ) {
                            Text(
                                text = "Belum ada riwayat transaksi",
                                style = MaterialTheme.typography.bodyLarge,
                                color = MaterialTheme.colorScheme.onSurfaceVariant
                            )
                        }
                    } else {
                        LazyColumn(
                            modifier = Modifier.fillMaxSize(),
                            contentPadding = PaddingValues(16.dp),
                            verticalArrangement = Arrangement.spacedBy(8.dp)
                        ) {
                            items(historyList) { history ->
                                HistoryItem(history)
                            }
                        }
                    }
                }
                is UiState.Idle -> {
                    Box(
                        modifier = Modifier.fillMaxSize(),
                        contentAlignment = Alignment.Center
                    ) {
                        Text(
                            text = "Pilih transaksi untuk melihat riwayat",
                            style = MaterialTheme.typography.bodyLarge,
                            color = MaterialTheme.colorScheme.onSurfaceVariant
                        )
                    }
                }
            }
        }
    }
}

@Composable
private fun HistoryItem(history: TransactionHistory) {
    Card(
        modifier = Modifier.fillMaxWidth(),
        colors = CardDefaults.cardColors(
            containerColor = MaterialTheme.colorScheme.surface
        )
    ) {
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .padding(16.dp),
            verticalArrangement = Arrangement.spacedBy(4.dp)
        ) {
            Text(
                text = history.caption,
                style = MaterialTheme.typography.bodyLarge,
                fontWeight = FontWeight.Medium
            )
            Text(
                text = "${history.namaLengkap} (${history.noAnggota})",
                style = MaterialTheme.typography.bodySmall,
                color = MaterialTheme.colorScheme.onSurfaceVariant
            )
            Text(
                text = history.createdAt,
                style = MaterialTheme.typography.bodySmall,
                color = MaterialTheme.colorScheme.onSurfaceVariant
            )
        }
    }
}
