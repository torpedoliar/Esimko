package com.esimko.mobile.ui.shopping

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.Receipt
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import com.esimko.mobile.ui.common.EsimkoButton
import com.esimko.mobile.ui.common.ErrorView
import com.esimko.mobile.ui.common.LoadingOverlay
import com.esimko.mobile.util.AmountFormatter

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ShoppingHistoryScreen(
    onBack: () -> Unit,
    viewModel: ShoppingViewModel = hiltViewModel()
) {
    val state by viewModel.state.collectAsState()
    var jenis by remember { mutableStateOf("toko") }
    var selectedId by remember { mutableStateOf<Long?>(null) }

    LaunchedEffect(jenis) { viewModel.loadHistory(jenis) }
    LaunchedEffect(selectedId) {
        selectedId?.let { viewModel.loadHistoryDetail(jenis, it) }
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Riwayat Belanja") },
                navigationIcon = {
                    IconButton(onClick = onBack) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Kembali")
                    }
                }
            )
        }
    ) { padding ->
        Box(modifier = Modifier.fillMaxSize().padding(padding)) {
            if (state.isLoading) {
                LoadingOverlay(isLoading = true)
            }
            state.error?.let { error ->
                ErrorView(message = error, onRetry = { viewModel.loadHistory(jenis) })
            }

            Column(modifier = Modifier.fillMaxSize()) {
                TabRow(selectedTabIndex = listOf("toko", "konsinyasi", "online").indexOf(jenis)) {
                    Tab(selected = jenis == "toko", onClick = { jenis = "toko"; selectedId = null }, text = { Text("Toko") })
                    Tab(selected = jenis == "konsinyasi", onClick = { jenis = "konsinyasi"; selectedId = null }, text = { Text("Konsinyasi") })
                    Tab(selected = jenis == "online", onClick = { jenis = "online"; selectedId = null }, text = { Text("Online") })
                }

                if (selectedId != null && state.historyDetail != null) {
                    // Detail view
                    val detail = state.historyDetail!!
                    LazyColumn(
                        modifier = Modifier.fillMaxSize().padding(16.dp),
                        verticalArrangement = Arrangement.spacedBy(8.dp)
                    ) {
                        item {
                            Card(modifier = Modifier.fillMaxWidth()) {
                                Column(modifier = Modifier.padding(16.dp)) {
                                    Text("Detail Transaksi", style = MaterialTheme.typography.titleMedium)
                                    Spacer(modifier = Modifier.height(8.dp))
                                    DetailRow("Tanggal", detail.tanggal)
                                    DetailRow("Status", detail.status)
                                    DetailRow("Total", AmountFormatter.format(detail.total))
                                }
                            }
                        }
                        items(detail.items) { item ->
                            Card(modifier = Modifier.fillMaxWidth()) {
                                Column(modifier = Modifier.padding(16.dp)) {
                                    Text(item.nama, style = MaterialTheme.typography.titleSmall)
                                    Spacer(modifier = Modifier.height(4.dp))
                                    Text(
                                        "${item.qty} x ${AmountFormatter.format(item.harga)} = ${AmountFormatter.format(item.subtotal)}",
                                        style = MaterialTheme.typography.bodySmall,
                                        color = MaterialTheme.colorScheme.onSurfaceVariant
                                    )
                                }
                            }
                        }
                        item {
                            EsimkoButton(text = "Kembali", onClick = { selectedId = null })
                        }
                    }
                } else {
                    LazyColumn(
                        modifier = Modifier.fillMaxSize().padding(16.dp),
                        verticalArrangement = Arrangement.spacedBy(8.dp)
                    ) {
                        if (state.history.isEmpty() && !state.isLoading && state.error == null) {
                            item {
                                Column(
                                    modifier = Modifier.fillMaxWidth().padding(32.dp),
                                    horizontalAlignment = Alignment.CenterHorizontally
                                ) {
                                    Icon(
                                        imageVector = Icons.Default.Receipt,
                                        contentDescription = null,
                                        modifier = Modifier.size(48.dp),
                                        tint = MaterialTheme.colorScheme.primary
                                    )
                                    Spacer(modifier = Modifier.height(12.dp))
                                    Text(
                                        text = "Belum ada riwayat belanja",
                                        style = MaterialTheme.typography.bodyMedium,
                                        color = MaterialTheme.colorScheme.onSurfaceVariant
                                    )
                                }
                            }
                        }
                        items(state.history) { item ->
                            Card(
                                modifier = Modifier.fillMaxWidth(),
                                onClick = { selectedId = item.id }
                            ) {
                                Row(
                                    modifier = Modifier.padding(16.dp),
                                    horizontalArrangement = Arrangement.SpaceBetween,
                                    verticalAlignment = Alignment.CenterVertically
                                ) {
                                    Column {
                                        Text(
                                            text = item.status,
                                            style = MaterialTheme.typography.titleSmall
                                        )
                                        Spacer(modifier = Modifier.height(4.dp))
                                        Text(
                                            text = item.tanggal,
                                            style = MaterialTheme.typography.bodySmall,
                                            color = MaterialTheme.colorScheme.onSurfaceVariant
                                        )
                                    }
                                    Text(
                                        text = AmountFormatter.format(item.total),
                                        style = MaterialTheme.typography.titleMedium,
                                        color = MaterialTheme.colorScheme.primary
                                    )
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

@Composable
private fun DetailRow(label: String, value: String) {
    Row(
        modifier = Modifier.fillMaxWidth(),
        horizontalArrangement = Arrangement.SpaceBetween
    ) {
        Text(label, style = MaterialTheme.typography.bodyMedium, color = MaterialTheme.colorScheme.onSurfaceVariant)
        Text(value, style = MaterialTheme.typography.bodyMedium, fontWeight = FontWeight.Medium)
    }
}
