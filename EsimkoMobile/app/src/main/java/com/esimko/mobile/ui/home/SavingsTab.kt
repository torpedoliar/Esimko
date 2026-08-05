package com.esimko.mobile.ui.home

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.AccountBalance
import androidx.compose.material.icons.filled.Add
import androidx.compose.material.icons.filled.Remove
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import com.esimko.mobile.ui.common.LoadingOverlay
import com.esimko.mobile.ui.common.ErrorView
import com.esimko.mobile.ui.savings.SavingsViewModel
import com.esimko.mobile.util.AmountFormatter

@Composable
fun SavingsTab(
    onOpenHistory: (Long, String) -> Unit = { _, _ -> },
    viewModel: SavingsViewModel = hiltViewModel()
) {
    val state by viewModel.state.collectAsState()
    var showForm by remember { mutableStateOf(false) }
    var formJenis by remember { mutableStateOf("setoran") }
    var nominal by remember { mutableStateOf("") }

    LaunchedEffect(state.submitSuccess) {
        if (state.submitSuccess != null) {
            showForm = false
            nominal = ""
            viewModel.clearSubmitFeedback()
        }
    }

    Box(modifier = Modifier.fillMaxSize()) {
        if (state.isLoading) {
            LoadingOverlay(isLoading = true)
        }

        state.error?.let { error ->
            ErrorView(
                message = error,
                onRetry = { viewModel.load() }
            )
        }

        LazyColumn(
            modifier = Modifier
                .fillMaxSize()
                .padding(16.dp),
            verticalArrangement = Arrangement.spacedBy(8.dp)
        ) {
            // Saldo header
            item {
                Card(
                    modifier = Modifier.fillMaxWidth(),
                    colors = CardDefaults.cardColors(
                        containerColor = MaterialTheme.colorScheme.primaryContainer
                    )
                ) {
                    Column(
                        modifier = Modifier.padding(20.dp)
                    ) {
                        Text(
                            text = "Saldo Simpanan",
                            style = MaterialTheme.typography.bodyMedium,
                            color = MaterialTheme.colorScheme.onPrimaryContainer
                        )
                        Spacer(modifier = Modifier.height(8.dp))
                        Text(
                            text = AmountFormatter.format(state.profile?.saldoSimpanan ?: 0),
                            style = MaterialTheme.typography.headlineMedium,
                            fontWeight = FontWeight.Bold,
                            color = MaterialTheme.colorScheme.onPrimaryContainer
                        )
                        Spacer(modifier = Modifier.height(8.dp))
                        Row(horizontalArrangement = Arrangement.spacedBy(12.dp)) {
                            Text(
                                text = "Pokok: ${AmountFormatter.format(state.profile?.saldoSimpananPokok ?: 0)}",
                                style = MaterialTheme.typography.bodySmall,
                                color = MaterialTheme.colorScheme.onPrimaryContainer
                            )
                            Text(
                                text = "Wajib: ${AmountFormatter.format(state.profile?.saldoSimpananWajib ?: 0)}",
                                style = MaterialTheme.typography.bodySmall,
                                color = MaterialTheme.colorScheme.onPrimaryContainer
                            )
                        }
                    }
                }
            }

            // Action buttons
            item {
                Row(modifier = Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(12.dp)) {
                    OutlinedButton(
                        onClick = {
                            formJenis = "setoran"
                            showForm = true
                        },
                        modifier = Modifier.weight(1f)
                    ) {
                        Icon(Icons.Default.Add, contentDescription = null, modifier = Modifier.size(18.dp))
                        Spacer(modifier = Modifier.width(6.dp))
                        Text("Setoran")
                    }
                    OutlinedButton(
                        onClick = {
                            formJenis = "penarikan"
                            showForm = true
                        },
                        modifier = Modifier.weight(1f)
                    ) {
                        Icon(Icons.Default.Remove, contentDescription = null, modifier = Modifier.size(18.dp))
                        Spacer(modifier = Modifier.width(6.dp))
                        Text("Penarikan")
                    }
                }
            }

            // Feedback
            state.submitSuccess?.let { msg ->
                item {
                    Text(
                        text = msg,
                        color = MaterialTheme.colorScheme.primary,
                        style = MaterialTheme.typography.bodyMedium
                    )
                }
            }
            state.submitError?.let { msg ->
                item {
                    Text(
                        text = msg,
                        color = MaterialTheme.colorScheme.error,
                        style = MaterialTheme.typography.bodyMedium
                    )
                }
            }

            // History
            item {
                Text(
                    text = "Riwayat Simpanan",
                    style = MaterialTheme.typography.titleMedium,
                    fontWeight = FontWeight.Bold,
                    modifier = Modifier.padding(top = 8.dp)
                )
            }

            if (state.transactions.isEmpty()) {
                item {
                    Text(
                        text = "Belum ada transaksi simpanan",
                        style = MaterialTheme.typography.bodyMedium,
                        color = MaterialTheme.colorScheme.onSurfaceVariant,
                        textAlign = TextAlign.Center,
                        modifier = Modifier.fillMaxWidth().padding(24.dp)
                    )
                }
            }

            items(state.transactions) { transaction ->
                Card(
                    modifier = Modifier.fillMaxWidth(),
                    onClick = { onOpenHistory(transaction.id, "transaksi") }
                ) {
                    Column(modifier = Modifier.padding(16.dp)) {
                        Text(
                            text = transaction.jenis,
                            style = MaterialTheme.typography.titleMedium
                        )
                        Spacer(modifier = Modifier.height(8.dp))
                        Text(
                            text = AmountFormatter.format(transaction.nominal),
                            style = MaterialTheme.typography.bodyLarge,
                            color = MaterialTheme.colorScheme.primary
                        )
                        Spacer(modifier = Modifier.height(4.dp))
                        Text(
                            text = transaction.tanggal,
                            style = MaterialTheme.typography.bodySmall,
                            color = MaterialTheme.colorScheme.onSurfaceVariant
                        )
                    }
                }
            }
        }
    }

    if (showForm) {
        AlertDialog(
            onDismissRequest = {
                if (!state.isSubmitting) {
                    showForm = false
                    viewModel.clearSubmitFeedback()
                }
            },
            title = { Text(if (formJenis == "setoran") "Setoran Simpanan" else "Penarikan Simpanan") },
            text = {
                Column(verticalArrangement = Arrangement.spacedBy(12.dp)) {
                    OutlinedTextField(
                        value = nominal,
                        onValueChange = { nominal = it.filter { c -> c.isDigit() } },
                        label = { Text("Nominal") },
                        keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Number),
                        singleLine = true,
                        modifier = Modifier.fillMaxWidth()
                    )
                    state.submitError?.let { msg ->
                        Text(
                            text = msg,
                            color = MaterialTheme.colorScheme.error,
                            style = MaterialTheme.typography.bodySmall
                        )
                    }
                }
            },
            confirmButton = {
                Button(
                    onClick = {
                        viewModel.submitTransaction(formJenis, nominal.toLongOrNull() ?: 0)
                    },
                    enabled = !state.isSubmitting && nominal.toLongOrNull()?.let { it > 0 } == true
                ) {
                    if (state.isSubmitting) {
                        CircularProgressIndicator(modifier = Modifier.size(16.dp), strokeWidth = 2.dp)
                    } else {
                        Text("Ajukan")
                    }
                }
            },
            dismissButton = {
                TextButton(
                    onClick = { showForm = false },
                    enabled = !state.isSubmitting
                ) {
                    Text("Batal")
                }
            }
        )
    }
}
