package com.esimko.mobile.ui.installment

import android.graphics.BitmapFactory
import android.net.Uri
import androidx.activity.compose.rememberLauncherForActivityResult
import androidx.activity.result.contract.ActivityResultContracts
import androidx.compose.foundation.Image
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.CloudUpload
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.asImageBitmap
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import com.esimko.mobile.ui.common.ErrorView
import com.esimko.mobile.ui.common.LoadingOverlay
import com.esimko.mobile.util.AmountFormatter

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun LoanApplicationScreen(
    onBack: () -> Unit,
    onSubmitted: () -> Unit = {},
    viewModel: LoanApplicationViewModel = hiltViewModel()
) {
    val state by viewModel.state.collectAsState()

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Ajukan Pinjaman") },
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
                ErrorView(
                    message = error,
                    onRetry = { viewModel.load() }
                )
            }

            if (state.loanTypes.isNotEmpty()) {
                Column(
                    modifier = Modifier
                        .fillMaxSize()
                        .verticalScroll(rememberScrollState())
                        .padding(16.dp),
                    verticalArrangement = Arrangement.spacedBy(16.dp)
                ) {
                    // Card gaji pokok — user input sendiri (draft; resmi saat admin approve)
                    Card(
                        modifier = Modifier.fillMaxWidth(),
                        colors = CardDefaults.cardColors(
                            containerColor = MaterialTheme.colorScheme.primary
                        )
                    ) {
                        Column(modifier = Modifier.padding(20.dp)) {
                            Text(
                                text = "Gaji Pokok (Rp)",
                                style = MaterialTheme.typography.bodyMedium,
                                color = MaterialTheme.colorScheme.onPrimary.copy(alpha = 0.85f)
                            )
                            OutlinedTextField(
                                value = state.gajiPokok,
                                onValueChange = viewModel::onGajiChange,
                                placeholder = {
                                    Text(
                                        text = state.salary?.let { "Terdaftar: " + AmountFormatter.format(it.gajiPokok) } ?: "Isi gaji pokok anda",
                                        color = MaterialTheme.colorScheme.onPrimary.copy(alpha = 0.7f)
                                    )
                                },
                                keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Number),
                                singleLine = true,
                                enabled = !state.isSubmitting,
                                colors = OutlinedTextFieldDefaults.colors(
                                    focusedTextColor = MaterialTheme.colorScheme.onPrimary,
                                    unfocusedTextColor = MaterialTheme.colorScheme.onPrimary,
                                    cursorColor = MaterialTheme.colorScheme.onPrimary,
                                    focusedBorderColor = MaterialTheme.colorScheme.onPrimary.copy(alpha = 0.8f),
                                    unfocusedBorderColor = MaterialTheme.colorScheme.onPrimary.copy(alpha = 0.4f),
                                    focusedPlaceholderColor = MaterialTheme.colorScheme.onPrimary.copy(alpha = 0.7f),
                                    unfocusedPlaceholderColor = MaterialTheme.colorScheme.onPrimary.copy(alpha = 0.7f)
                                ),
                                modifier = Modifier.fillMaxWidth()
                            )
                        }
                    }

                    // Jenis pinjaman
                    Text(
                        text = "Jenis Pinjaman",
                        style = MaterialTheme.typography.titleMedium,
                        fontWeight = FontWeight.Bold
                    )
                    state.loanTypes.forEach { type ->
                        val selected = state.selectedJenis == type.id
                        Card(
                            modifier = Modifier.fillMaxWidth(),
                            onClick = { viewModel.selectJenis(type.id) },
                            colors = if (selected) {
                                CardDefaults.cardColors(
                                    containerColor = MaterialTheme.colorScheme.secondaryContainer
                                )
                            } else {
                                CardDefaults.cardColors()
                            }
                        ) {
                            Row(
                                modifier = Modifier.padding(16.dp),
                                verticalAlignment = Alignment.CenterVertically
                            ) {
                                Text(
                                    text = type.nama,
                                    style = MaterialTheme.typography.titleSmall,
                                    modifier = Modifier.weight(1f)
                                )
                                if (selected) {
                                    Text(
                                        text = "Maks ${viewModel.maxTenor()} bulan",
                                        style = MaterialTheme.typography.bodySmall,
                                        color = MaterialTheme.colorScheme.onSecondaryContainer
                                    )
                                }
                            }
                        }
                    }

                    // Nominal + tenor
                    OutlinedTextField(
                        value = state.nominal,
                        onValueChange = viewModel::onNominalChange,
                        label = { Text("Nominal Pinjaman") },
                        keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Number),
                        singleLine = true,
                        enabled = !state.isSubmitting,
                        modifier = Modifier.fillMaxWidth()
                    )

                    state.selectedJenis?.let { jenis ->
                        OutlinedTextField(
                            value = state.tenor,
                            onValueChange = viewModel::onTenorChange,
                            label = { Text("Tenor (bulan) — maks ${viewModel.maxTenor()}") },
                            keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Number),
                            singleLine = true,
                            enabled = !state.isSubmitting,
                            modifier = Modifier.fillMaxWidth()
                        )
                    }

                    // Slip gaji (wajib) — bukti gaji yang diverifikasi admin
                    val context = LocalContext.current
                    val slipPicker = rememberLauncherForActivityResult(
                        contract = ActivityResultContracts.GetContent()
                    ) { uri: Uri? ->
                        uri?.let {
                            context.contentResolver.openInputStream(it)?.use { stream ->
                                val bytes = stream.readBytes()
                                viewModel.onSlipPicked(
                                    bytes,
                                    context.contentResolver.getType(uri) ?: "image/jpeg",
                                    it.lastPathSegment ?: "slip_gaji"
                                )
                            }
                        }
                    }

                    Text(
                        text = "Slip Gaji",
                        style = MaterialTheme.typography.titleMedium,
                        fontWeight = FontWeight.Bold
                    )
                    Card(
                        modifier = Modifier.fillMaxWidth(),
                        colors = if (state.slipBytes != null) {
                            CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.secondaryContainer)
                        } else {
                            CardDefaults.cardColors()
                        }
                    ) {
                        Column(
                            modifier = Modifier.fillMaxWidth().padding(12.dp),
                            horizontalAlignment = Alignment.CenterHorizontally,
                            verticalArrangement = Arrangement.spacedBy(8.dp)
                        ) {
                            val slipBytes = state.slipBytes
                            if (slipBytes != null) {
                                val bitmap = slipBytes.toBitmap()
                                if (bitmap != null) {
                                    Image(
                                        bitmap = bitmap.asImageBitmap(),
                                        contentDescription = "Slip Gaji",
                                        modifier = Modifier.fillMaxWidth().height(160.dp),
                                        contentScale = ContentScale.Fit
                                    )
                                }
                                Text(
                                    text = state.slipName ?: "Slip Gaji",
                                    style = MaterialTheme.typography.bodySmall,
                                    color = MaterialTheme.colorScheme.onSecondaryContainer
                                )
                            } else {
                                Icon(
                                    imageVector = Icons.Default.CloudUpload,
                                    contentDescription = null,
                                    tint = MaterialTheme.colorScheme.onSurfaceVariant
                                )
                                Text(
                                    text = "Upload foto slip gaji (wajib)",
                                    style = MaterialTheme.typography.bodySmall,
                                    color = MaterialTheme.colorScheme.onSurfaceVariant
                                )
                            }
                            Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                                Button(
                                    onClick = { slipPicker.launch("image/*") },
                                    enabled = !state.isSubmitting
                                ) {
                                    Text(if (state.slipBytes != null) "Ganti" else "Pilih Foto")
                                }
                                if (state.slipBytes != null) {
                                    OutlinedButton(
                                        onClick = viewModel::clearSlip,
                                        enabled = !state.isSubmitting
                                    ) {
                                        Text("Hapus")
                                    }
                                }
                            }
                        }
                    }

                    // Error
                    state.submitError?.let { msg ->
                        Text(
                            text = msg,
                            color = MaterialTheme.colorScheme.error,
                            style = MaterialTheme.typography.bodySmall
                        )
                    }

                    // Submit
                    Button(
                        onClick = viewModel::submit,
                        enabled = !state.isSubmitting &&
                            state.selectedJenis != null &&
                            (state.nominal.toLongOrNull() ?: 0) > 0 &&
                            (state.tenor.toIntOrNull() ?: 0) in 1..viewModel.maxTenor() &&
                            (state.gajiPokok.toLongOrNull() ?: 0) > 0 &&
                            state.slipBytes != null,
                        modifier = Modifier.fillMaxWidth().height(52.dp)
                    ) {
                        if (state.isSubmitting) {
                            CircularProgressIndicator(
                                modifier = Modifier.size(24.dp),
                                color = MaterialTheme.colorScheme.onPrimary
                            )
                        } else {
                            Text("Ajukan Pinjaman", style = MaterialTheme.typography.labelLarge)
                        }
                    }

                    // Info angsuran per bulan
                    val nominal = state.nominal.toLongOrNull() ?: 0
                    val tenor = state.tenor.toIntOrNull() ?: 0
                    if (nominal > 0 && tenor > 0) {
                        val angsuran = nominal / tenor + (nominal * 1) / 100 // bunga ~1%
                        Text(
                            text = "Estimasi angsuran/bulan: ${AmountFormatter.format(angsuran)}",
                            style = MaterialTheme.typography.bodySmall,
                            color = MaterialTheme.colorScheme.onSurfaceVariant,
                            modifier = Modifier.align(Alignment.CenterHorizontally)
                        )
                    }
                }
            }

            // Sukses — dialog
            state.submitSuccess?.let { id ->
                AlertDialog(
                    onDismissRequest = { },
                    title = { Text("Pengajuan Terkirim") },
                    text = { Text("Pengajuan pinjaman berhasil dikirim. Nomor transaksi: $id. Menunggu verifikasi pengurus.") },
                    confirmButton = {
                        Button(onClick = onSubmitted) { Text("OK") }
                    }
                )
            }
        }
    }
}

// ponytail: decode langsung (bitmap full-size). Kalau memori jadi masalah di device kecil, decode dengan inSampleSize.
private fun ByteArray.toBitmap() = try { BitmapFactory.decodeByteArray(this, 0, size) } catch (e: Exception) { null }
