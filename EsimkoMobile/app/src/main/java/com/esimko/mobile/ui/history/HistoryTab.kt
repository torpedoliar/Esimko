package com.esimko.mobile.ui.history

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import com.esimko.mobile.domain.model.Transaction
import com.esimko.mobile.domain.model.TransactionHistory
import com.esimko.mobile.ui.common.EmptyStateView
import com.esimko.mobile.ui.common.ErrorView
import com.esimko.mobile.ui.common.LoadingOverlay
import com.esimko.mobile.ui.common.UiState
import com.esimko.mobile.util.AmountFormatter

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun HistoryTab(
    transactionId: Long = 0L,
    initialModule: String = "transaksi",
    onBack: () -> Unit = {},
    viewModel: HistoryViewModel = hiltViewModel()
) {
    val uiState by viewModel.uiState.collectAsState()
    val transactionsState by viewModel.transactionsState.collectAsState()
    val selectedModule by viewModel.selectedModule.collectAsState()
    val selectedId by viewModel.selectedTransactionId.collectAsState()

    val modules = listOf("transaksi", "penjualan")
    val directMode = transactionId > 0L
    val showBack = onBack != {}

    LaunchedEffect(transactionId, initialModule) {
        viewModel.selectModule(initialModule)
        if (directMode) {
            viewModel.selectTransaction(transactionId)
        } else {
            viewModel.loadTransactions()
        }
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text(if (directMode) "Riwayat" else "Riwayat Transaksi") },
                navigationIcon = {
                    if (showBack) {
                        IconButton(onClick = onBack) {
                            Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Kembali")
                        }
                    }
                }
            )
        }
    ) { padding ->
        Column(modifier = Modifier.fillMaxSize().padding(padding)) {
            // Module filter
            var expanded by remember { mutableStateOf(false) }
            Row(
                modifier = Modifier.padding(16.dp),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                Text(
                    text = modules.find { it == selectedModule }?.replaceFirstChar { it.uppercase() } ?: "",
                    style = MaterialTheme.typography.titleMedium,
                    fontWeight = FontWeight.SemiBold
                )
                OutlinedButton(
                    onClick = { expanded = true },
                    modifier = Modifier.height(40.dp)
                ) {
                    Text("Ganti Jenis")
                }
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
                            if (directMode) {
                                viewModel.loadHistory(transactionId)
                            } else {
                                viewModel.loadTransactions()
                            }
                        }
                    )
                }
            }

            Box(modifier = Modifier.fillMaxSize()) {
                if (directMode) {
                    HistoryDetailContent(uiState = uiState, onRetry = { viewModel.loadHistory(transactionId) })
                } else {
                    TransactionsListContent(
                        state = transactionsState,
                        selectedId = selectedId,
                        onSelect = { viewModel.selectTransaction(it) },
                        onBackToList = { viewModel.selectTransaction(-1L) },
                        onRetry = { viewModel.loadTransactions() }
                    )
                }
            }
        }
    }
}

@Composable
private fun HistoryDetailContent(
    uiState: UiState<List<TransactionHistory>>,
    onRetry: () -> Unit
) {
    when (uiState) {
        is UiState.Loading -> LoadingOverlay(isLoading = true)
        is UiState.Error -> ErrorView(message = uiState.message ?: "Terjadi kesalahan", onRetry = onRetry)
        is UiState.Success -> {
            if (uiState.data.isEmpty()) {
                EmptyStateView(message = "Belum ada riwayat transaksi")
            } else {
                LazyColumn(
                    modifier = Modifier.fillMaxSize(),
                    contentPadding = PaddingValues(16.dp),
                    verticalArrangement = Arrangement.spacedBy(8.dp)
                ) {
                    items(uiState.data) { history ->
                        HistoryItem(history)
                    }
                }
            }
        }
        is UiState.Idle -> Unit
    }
}

@Composable
private fun TransactionsListContent(
    state: UiState<List<Transaction>>,
    selectedId: Long?,
    onSelect: (Long) -> Unit,
    onBackToList: () -> Unit,
    onRetry: () -> Unit
) {
    when (state) {
        is UiState.Loading -> LoadingOverlay(isLoading = true)
        is UiState.Error -> ErrorView(message = state.message ?: "Terjadi kesalahan", onRetry = onRetry)
        is UiState.Success -> {
            if (state.data.isEmpty()) {
                EmptyStateView(message = "Belum ada transaksi")
            } else {
                LazyColumn(
                    modifier = Modifier.fillMaxSize(),
                    contentPadding = PaddingValues(16.dp),
                    verticalArrangement = Arrangement.spacedBy(8.dp)
                ) {
                    items(state.data) { transaction ->
                        TransactionItem(
                            transaction = transaction,
                            selected = transaction.id == selectedId,
                            onClick = { onSelect(transaction.id) }
                        )
                    }
                }
            }
        }
        is UiState.Idle -> Unit
    }
}

@Composable
private fun TransactionItem(
    transaction: Transaction,
    selected: Boolean,
    onClick: () -> Unit
) {
    Card(
        modifier = Modifier.fillMaxWidth(),
        onClick = onClick,
        colors = if (selected) {
            CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.primaryContainer)
        } else {
            CardDefaults.cardColors()
        }
    ) {
        Row(
            modifier = Modifier.padding(16.dp),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically
        ) {
            Column(modifier = Modifier.weight(1f)) {
                Text(
                    text = transaction.jenis,
                    style = MaterialTheme.typography.titleSmall,
                    fontWeight = FontWeight.Medium
                )
                Spacer(modifier = Modifier.height(4.dp))
                Text(
                    text = transaction.tanggal,
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.onSurfaceVariant
                )
            }
            Text(
                text = AmountFormatter.format(transaction.nominal),
                style = MaterialTheme.typography.titleMedium,
                color = MaterialTheme.colorScheme.primary
            )
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
