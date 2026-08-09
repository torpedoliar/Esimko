package com.esimko.mobile.ui.savings

import android.net.Uri
import androidx.activity.compose.BackHandler
import androidx.activity.compose.rememberLauncherForActivityResult
import androidx.activity.result.contract.ActivityResultContracts
import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.heightIn
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.aspectRatio
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.CheckCircle
import androidx.compose.material.icons.filled.CloudUpload
import androidx.compose.material3.AlertDialog
import androidx.compose.material3.Button
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedButton
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.material3.TopAppBar
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.asImageBitmap
import com.esimko.mobile.util.compressForUpload
import com.esimko.mobile.util.decodeSampled
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import com.esimko.mobile.ui.common.AmountField
import com.esimko.mobile.ui.common.EsimkoPreview
import com.esimko.mobile.ui.common.LightDarkPreview

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun SavingsFormScreen(
    jenis: String,
    onBack: () -> Unit,
    onDone: () -> Unit,
    viewModel: SavingsFormViewModel = hiltViewModel()
) {
    val state by viewModel.state.collectAsState()
    LaunchedEffect(jenis) { viewModel.start(jenis) }

    var konfirmasiKeluar by remember { mutableStateOf(false) }
    BackHandler(enabled = state.punyaDraft) { konfirmasiKeluar = true }

    val judul = if (jenis == "setoran") "Setoran Simpanan" else "Penarikan Simpanan"

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text(judul) },
                navigationIcon = {
                    IconButton(onClick = { if (state.punyaDraft) konfirmasiKeluar = true else onBack() }) {
                        Icon(
                            Icons.AutoMirrored.Filled.ArrowBack,
                            contentDescription = "Kembali"
                        )
                    }
                }
            )
        }
    ) { padding ->
        Column(
            modifier = Modifier
                .padding(padding)
                .fillMaxSize()
        ) {
            // Indikator langkah: dua batang, tampil saat belum selesai.
            if (state.step != FormStep.SELESAI) {
                Row(
                    modifier = Modifier.padding(horizontal = 16.dp, vertical = 8.dp),
                    horizontalArrangement = Arrangement.spacedBy(8.dp)
                ) {
                    repeat(2) { i ->
                        val sudah = i <= (if (state.step == FormStep.NOMINAL) 0 else 1)
                        Box(
                            modifier = Modifier
                                .weight(1f)
                                .height(4.dp)
                                .clip(RoundedCornerShape(50))
                                .background(
                                    if (sudah) MaterialTheme.colorScheme.primary
                                    else MaterialTheme.colorScheme.outlineVariant
                                )
                        )
                    }
                }
                Text(
                    text = "Langkah ${if (state.step == FormStep.NOMINAL) 1 else 2} dari 2",
                    style = MaterialTheme.typography.labelMedium,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                    modifier = Modifier.padding(horizontal = 16.dp)
                )
            }

            when (state.step) {
                FormStep.NOMINAL -> NominalStep(
                    state = state,
                    onNominalChange = viewModel::onNominalChange,
                    onKeteranganChange = viewModel::onKeteranganChange,
                    onSubmit = viewModel::submitNominal
                )
                FormStep.BUKTI -> BuktiStep(
                    state = state,
                    onPick = viewModel::onBuktiPicked,
                    onClear = viewModel::clearBukti,
                    onUpload = viewModel::uploadBukti,
                    onSkip = viewModel::skipBukti
                )
                FormStep.SELESAI -> SelesaiStep(
                    transactionId = state.lastTransactionId,
                    onDone = onDone
                )
            }
        }
    }

    if (konfirmasiKeluar) {
        AlertDialog(
            onDismissRequest = { konfirmasiKeluar = false },
            title = { Text("Batalkan pengisian?") },
            text = {
                Text(
                    if (state.lastTransactionId != null)
                        "Pengajuan sudah terkirim. Bukti bisa diunggah lain waktu dari Aktivitas."
                    else "Nominal yang sudah diisi akan hilang."
                )
            },
            confirmButton = { Button(onClick = onBack) { Text("Keluar") } },
            dismissButton = {
                TextButton(onClick = { konfirmasiKeluar = false }) { Text("Lanjut Isi") }
            }
        )
    }
}

@Composable
private fun NominalStep(
    state: SavingsFormState,
    onNominalChange: (String) -> Unit,
    onKeteranganChange: (String) -> Unit,
    onSubmit: () -> Unit
) {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .verticalScroll(rememberScrollState())
            .padding(16.dp),
        verticalArrangement = Arrangement.spacedBy(12.dp)
    ) {
        AmountField(
            value = state.nominal,
            onValueChange = onNominalChange,
            label = "Nominal"
        )
        OutlinedTextField(
            value = state.keterangan,
            onValueChange = onKeteranganChange,
            label = { Text("Keterangan (opsional)") },
            modifier = Modifier.fillMaxWidth(),
            minLines = 2
        )
        state.error?.let {
            Text(it, color = MaterialTheme.colorScheme.error, style = MaterialTheme.typography.bodySmall)
        }
        Spacer(Modifier.weight(1f))
        Button(
            onClick = onSubmit,
            enabled = state.nominalValid && !state.isSubmitting,
            modifier = Modifier.fillMaxWidth().height(52.dp)
        ) {
            if (state.isSubmitting) {
                CircularProgressIndicator(Modifier.size(24.dp), color = Color.White)
            } else {
                Text("Kirim Pengajuan")
            }
        }
    }
}

@Composable
private fun BuktiStep(
    state: SavingsFormState,
    onPick: (ByteArray, String, String) -> Unit,
    onClear: () -> Unit,
    onUpload: () -> Unit,
    onSkip: () -> Unit
) {
    val context = LocalContext.current
    val picker = rememberLauncherForActivityResult(
        contract = ActivityResultContracts.GetContent()
    ) { uri: Uri? ->
        uri?.let {
            context.contentResolver.openInputStream(it)?.use { stream ->
                // Kompres dulu: foto kamera HP 3-8MB → ~200KB. Tanpa ini server 413.
                val compressed = stream.readBytes().compressForUpload()
                if (compressed != null) {
                    onPick(
                        compressed,
                        "image/jpeg",
                        it.lastPathSegment ?: "bukti"
                    )
                }
            }
        }
    }

    Column(
        modifier = Modifier
            .fillMaxSize()
            .verticalScroll(rememberScrollState())
            .padding(16.dp),
        verticalArrangement = Arrangement.spacedBy(12.dp)
    ) {
        Text(
            "Pengajuan terkirim. Unggah bukti supaya verifikasi lebih cepat.",
            style = MaterialTheme.typography.bodyMedium
        )

        if (state.buktiBytes == null) {
            OutlinedButton(
                onClick = { picker.launch("image/*") },
                modifier = Modifier.fillMaxWidth().heightIn(min = 48.dp)
            ) {
                Icon(Icons.Filled.CloudUpload, contentDescription = null)
                Spacer(Modifier.size(8.dp))
                Text("Pilih Foto")
            }
        } else {
            val bitmap = remember(state.buktiBytes) {
                state.buktiBytes!!.decodeSampled()
            }
            if (bitmap != null) {
                Image(
                    bitmap = bitmap.asImageBitmap(),
                    contentDescription = "Bukti transaksi",
                    modifier = Modifier.fillMaxWidth().aspectRatio(4f / 3f),
                    contentScale = ContentScale.Fit
                )
            }
            Text(
                state.buktiName ?: "Bukti",
                style = MaterialTheme.typography.bodySmall,
                color = MaterialTheme.colorScheme.onSurfaceVariant
            )
            Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                OutlinedButton(onClick = { picker.launch("image/*") }) { Text("Ganti") }
                TextButton(onClick = onClear) { Text("Hapus") }
            }
        }

        state.uploadError?.let {
            Text(it, color = MaterialTheme.colorScheme.error, style = MaterialTheme.typography.bodySmall)
        }

        Spacer(Modifier.weight(1f))
        Button(
            onClick = onUpload,
            enabled = state.buktiBytes != null && !state.isSubmitting,
            modifier = Modifier.fillMaxWidth().height(52.dp)
        ) {
            if (state.isSubmitting) {
                CircularProgressIndicator(Modifier.size(24.dp), color = Color.White)
            } else {
                Text("Kirim Bukti")
            }
        }
        TextButton(onClick = onSkip, modifier = Modifier.fillMaxWidth()) { Text("Nanti saja") }
    }
}

@Composable
private fun SelesaiStep(transactionId: Long?, onDone: () -> Unit) {
    Column(
        modifier = Modifier.fillMaxSize().padding(24.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center
    ) {
        Icon(
            Icons.Filled.CheckCircle,
            contentDescription = null,
            tint = MaterialTheme.colorScheme.primary,
            modifier = Modifier.size(64.dp)
        )
        Spacer(Modifier.height(16.dp))
        Text("Pengajuan Terkirim", style = MaterialTheme.typography.titleLarge)
        Spacer(Modifier.height(8.dp))
        transactionId?.let {
            Text(
                "Nomor transaksi: $it",
                style = MaterialTheme.typography.bodyMedium,
                color = MaterialTheme.colorScheme.onSurfaceVariant
            )
        }
        Text("Menunggu verifikasi pengurus.", style = MaterialTheme.typography.bodyMedium)
        Spacer(Modifier.height(24.dp))
        Button(onClick = onDone, modifier = Modifier.fillMaxWidth().height(52.dp)) {
            Text("Selesai")
        }
    }
}

@LightDarkPreview
@Composable
private fun NominalStepPreview() = EsimkoPreview {
    NominalStep(
        state = SavingsFormState(jenis = "setoran", nominal = "250000", keterangan = ""),
        onNominalChange = {}, onKeteranganChange = {}, onSubmit = {}
    )
}

@LightDarkPreview
@Composable
private fun SelesaiStepPreview() = EsimkoPreview {
    SelesaiStep(transactionId = 8123, onDone = {})
}
