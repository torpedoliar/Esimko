package com.esimko.mobile.ui.home

import android.net.Uri
import androidx.activity.compose.rememberLauncherForActivityResult
import androidx.activity.result.contract.ActivityResultContracts
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.AccountBalance
import androidx.compose.material.icons.filled.Add
import androidx.compose.material.icons.filled.Remove
import androidx.compose.material.icons.filled.Upload
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.platform.LocalContext
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

    val context = LocalContext.current
    val proofLauncher = rememberLauncherForActivityResult(
        contract = ActivityResultContracts.GetContent()
    ) { uri: Uri? ->
        uri?.let {
            context.contentResolver.openInputStream(it)?.use { inputStream ->
                val bytes = inputStream.readBytes()
                viewModel.uploadProof(bytes, context.contentResolver.getType(uri) ?: "image/jpeg")
            }
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
            // Saldo header — brand drenched
            item {
                Card(
                    modifier = Modifier.fillMaxWidth(),
                    colors = CardDefaults.cardColors(
                        containerColor = MaterialTheme.colorScheme.primary
                    )
                ) {
                    Column(
                        modifier = Modifier.padding(20.dp)
                    ) {
                        Text(
                            text = "Saldo Simpanan",
                            style = MaterialTheme.typography.bodyMedium,
                            color = MaterialTheme.colorScheme.onPrimary.copy(alpha = 0.85f)
                        )
                        Spacer(modifier = Modifier.height(8.dp))
                        Text(
                            text = AmountFormatter.format(state.profile?.saldoSimpanan ?: 0),
                            style = MaterialTheme.typography.headlineMedium,
                            fontWeight = FontWeight.Bold,
                            color = MaterialTheme.colorScheme.onPrimary
                        )
                        Spacer(modifier = Modifier.height(8.dp))
                        Row(horizontalArrangement = Arrangement.spacedBy(16.dp)) {
                            Text(
                                text = "Pokok: ${AmountFormatter.format(state.profile?.saldoSimpananPokok ?: 0)}",
                                style = MaterialTheme.typography.bodySmall,
                                color = MaterialTheme.colorScheme.onPrimary.copy(alpha = 0.85f)
                            )
                            Text(
                                text = "Wajib: ${AmountFormatter.format(state.profile?.saldoSimpananWajib ?: 0)}",
                                style = MaterialTheme.typography.bodySmall,
                                color = MaterialTheme.colorScheme.onPrimary.copy(alpha = 0.85f)
                            )
                        }
                    }
                }
            }

            // Action buttons — tonal (filled secondary surface)
            item {
                Row(modifier = Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(12.dp)) {
                    FilledTonalButton(
                        onClick = {
                            formJenis = "setoran"
                            nominal = ""
                            viewModel.clearSubmitFeedback()
                            showForm = true
                        },
                        modifier = Modifier.weight(1f).height(48.dp)
                    ) {
                        Icon(Icons.Default.Add, contentDescription = null, modifier = Modifier.size(18.dp))
                        Spacer(modifier = Modifier.width(6.dp))
                        Text("Setoran")
                    }
                    FilledTonalButton(
                        onClick = {
                            formJenis = "penarikan"
                            nominal = ""
                            viewModel.clearSubmitFeedback()
                            showForm = true
                        },
                        modifier = Modifier.weight(1f).height(48.dp)
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
                    if (state.lastTransactionId != null) {
                        Text(
                            text = state.submitSuccess ?: "Transaksi berhasil diajukan. Unggah bukti pembayaran:",
                            style = MaterialTheme.typography.bodyMedium
                        )
                    } else {
                        OutlinedTextField(
                            value = nominal,
                            onValueChange = { nominal = it.filter { c -> c.isDigit() } },
                            label = { Text("Nominal") },
                            keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Number),
                            singleLine = true,
                            modifier = Modifier.fillMaxWidth()
                        )
                    }
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
                if (state.lastTransactionId != null) {
                    Button(
                        onClick = { proofLauncher.launch("image/*") },
                        enabled = !state.isSubmitting
                    ) {
                        Icon(Icons.Default.Upload, contentDescription = null, modifier = Modifier.size(18.dp))
                        Spacer(modifier = Modifier.width(6.dp))
                        if (state.isSubmitting) {
                            CircularProgressIndicator(modifier = Modifier.size(16.dp), strokeWidth = 2.dp)
                        } else {
                            Text("Unggah Bukti")
                        }
                    }
                } else {
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
                }
            },
            dismissButton = {
                TextButton(
                    onClick = {
                        showForm = false
                        viewModel.clearSubmitFeedback()
                    },
                    enabled = !state.isSubmitting
                ) {
                    Text(if (state.lastTransactionId != null) "Selesai" else "Batal")
                }
            }
        )
    }
}
