package com.esimko.mobile.ui.installment

import android.graphics.BitmapFactory
import android.net.Uri
import androidx.activity.compose.rememberLauncherForActivityResult
import androidx.activity.result.contract.ActivityResultContracts
import androidx.compose.foundation.Image
import androidx.compose.foundation.border
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.outlined.CloudUpload
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.asImageBitmap
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import com.esimko.mobile.domain.model.Salary
import com.esimko.mobile.domain.model.TransactionType
import com.esimko.mobile.ui.common.AmountField
import com.esimko.mobile.ui.common.ErrorView
import com.esimko.mobile.ui.common.EsimkoPreview
import com.esimko.mobile.ui.common.HeroSurface
import com.esimko.mobile.ui.common.LightDarkPreview
import com.esimko.mobile.ui.common.ListRow
import com.esimko.mobile.ui.common.Money
import com.esimko.mobile.ui.common.RowDivider
import com.esimko.mobile.ui.common.SectionHeader
import com.esimko.mobile.ui.common.SkeletonListRows
import com.esimko.mobile.ui.common.StaleBanner
import com.esimko.mobile.ui.theme.GoldOnHero
import com.esimko.mobile.ui.theme.MoneyRow
import com.esimko.mobile.ui.theme.OnHero
import com.esimko.mobile.util.MoneyFormatter

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
        Column(modifier = Modifier.fillMaxSize().padding(padding)) {
            val error = state.error
            when {
                !state.hasContent && state.isLoading -> SkeletonListRows(count = 5, modifier = Modifier.padding(16.dp))
                !state.hasContent && error != null -> ErrorView(
                    message = error,
                    onRetry = viewModel::load,
                    modifier = Modifier.fillMaxSize()
                )
                else -> {
                    if (error != null) StaleBanner(onRetry = viewModel::load)
                    LoanFormBody(
                        state = state,
                        onGajiChange = viewModel::onGajiChange,
                        onNominalChange = viewModel::onNominalChange,
                        onTenorChange = viewModel::onTenorChange,
                        onSelectJenis = viewModel::selectJenis,
                        onSlipPicked = viewModel::onSlipPicked,
                        onClearSlip = viewModel::clearSlip,
                        onSubmit = viewModel::submit,
                        onSubmitted = onSubmitted
                    )
                }
            }
        }
    }
}

@Composable
private fun LoanFormBody(
    state: LoanApplicationState,
    onGajiChange: (String) -> Unit = {},
    onNominalChange: (String) -> Unit = {},
    onTenorChange: (String) -> Unit = {},
    onSelectJenis: (Int) -> Unit = {},
    onSlipPicked: (ByteArray, String, String) -> Unit = { _, _, _ -> },
    onClearSlip: () -> Unit = {},
    onSubmit: () -> Unit = {},
    onSubmitted: () -> Unit = {}
) {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .verticalScroll(rememberScrollState())
            .padding(bottom = 24.dp),
        verticalArrangement = Arrangement.spacedBy(16.dp)
    ) {
        // 1. Hero gaji — draft, resmi saat admin approve (commit b7182d743, Opsi 1).
        HeroSurface(modifier = Modifier.fillMaxWidth()) {
            Text(
                text = "Gaji Pokok per Bulan",
                style = MaterialTheme.typography.labelMedium,
                color = OnHero.copy(alpha = 0.85f)
            )
            AmountField(
                value = state.gajiPokok,
                onValueChange = onGajiChange,
                modifier = Modifier.fillMaxWidth(),
                label = "Gaji pokok",
                supportingText = state.salary?.let { "Terdaftar: " + MoneyFormatter.format(it.gajiPokok) },
                enabled = !state.isSubmitting,
                imeAction = ImeAction.Next
            )
        }

        // 2. Jenis pinjaman.
        SectionHeader("Jenis Pinjaman", modifier = Modifier.padding(horizontal = 16.dp))
        state.loanTypes.forEachIndexed { index, type ->
            val selected = state.selectedJenis == type.id
            ListRow(
                title = type.nama,
                subtitle = if (selected) "Maks ${state.maxTenor} bulan" else null,
                trailing = { RadioButton(selected = selected, onClick = null) },
                onClick = { onSelectJenis(type.id) },
                modifier = Modifier.fillMaxWidth()
            )
            if (index < state.loanTypes.lastIndex) RowDivider(modifier = Modifier.padding(horizontal = 16.dp))
        }

        // 3. Nominal.
        AmountField(
            value = state.nominal,
            onValueChange = onNominalChange,
            modifier = Modifier.fillMaxWidth().padding(horizontal = 16.dp),
            label = "Nominal pinjaman",
            enabled = !state.isSubmitting
        )

        // 4. Tenor — hanya jika jenis sudah dipilih; bukan uang, jadi bukan AmountField.
        if (state.selectedJenis != null) {
            OutlinedTextField(
                value = state.tenor,
                onValueChange = onTenorChange,
                label = { Text("Tenor (bulan)") },
                supportingText = { Text("Maksimal ${state.maxTenor} bulan") },
                isError = state.tenor.isNotEmpty() && !state.tenorValid,
                keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Number, imeAction = ImeAction.Done),
                singleLine = true,
                enabled = !state.isSubmitting,
                modifier = Modifier.fillMaxWidth().padding(horizontal = 16.dp)
            )
        }

        // 5. Estimasi angsuran — di atas tombol. Wajib label "Estimasi" + disclaimer (LoanMath ponytail).
        if (state.estimasiAngsuran > 0L) {
            Column(modifier = Modifier.fillMaxWidth().padding(horizontal = 16.dp)) {
                Text(
                    text = "Estimasi angsuran per bulan",
                    style = MaterialTheme.typography.labelMedium,
                    color = MaterialTheme.colorScheme.onSurfaceVariant
                )
                Money(
                    amount = state.estimasiAngsuran,
                    style = MoneyRow,
                    color = MaterialTheme.colorScheme.primary
                )
                Text(
                    text = "Angka resmi ditentukan pengurus saat verifikasi.",
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.onSurfaceVariant
                )
            }
        }

        // 6. Slip gaji (wajib) — bukti gaji yang diverifikasi admin.
        SectionHeader("Slip Gaji", modifier = Modifier.padding(horizontal = 16.dp))
        val context = LocalContext.current
        val slipPicker = rememberLauncherForActivityResult(
            contract = ActivityResultContracts.GetContent()
        ) { uri: Uri? ->
            uri?.let {
                context.contentResolver.openInputStream(it)?.use { stream ->
                    val bytes = stream.readBytes()
                    onSlipPicked(
                        bytes,
                        context.contentResolver.getType(uri) ?: "image/jpeg",
                        it.lastPathSegment ?: "slip_gaji"
                    )
                }
            }
        }
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .padding(horizontal = 16.dp)
                .border(1.dp, MaterialTheme.colorScheme.outlineVariant, RoundedCornerShape(8.dp))
                .padding(12.dp),
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
                        modifier = Modifier.fillMaxWidth().aspectRatio(4f / 3f),
                        contentScale = ContentScale.Fit
                    )
                }
                Text(
                    text = state.slipName ?: "Slip Gaji",
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.onSurfaceVariant
                )
            } else {
                Icon(
                    imageVector = Icons.Outlined.CloudUpload,
                    contentDescription = null,
                    tint = MaterialTheme.colorScheme.onSurfaceVariant
                )
                Text(
                    text = "Unggah foto slip gaji (wajib)",
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
                    TextButton(
                        onClick = onClearSlip,
                        enabled = !state.isSubmitting
                    ) { Text("Hapus") }
                }
            }
        }

        // 7. Error kirim.
        state.submitError?.let { msg ->
            Text(
                text = msg,
                color = MaterialTheme.colorScheme.error,
                style = MaterialTheme.typography.bodySmall,
                modifier = Modifier.padding(horizontal = 16.dp)
            )
        }

        // 8. Tombol kirim — enabled = state.bisaKirim (satu ekspresi, bukan lima kondisi).
        Button(
            onClick = onSubmit,
            enabled = state.bisaKirim,
            modifier = Modifier.fillMaxWidth().heightIn(min = 48.dp).padding(horizontal = 16.dp)
        ) {
            if (state.isSubmitting) {
                CircularProgressIndicator(
                    modifier = Modifier.size(24.dp),
                    color = MaterialTheme.colorScheme.onPrimary,
                    strokeWidth = 2.dp
                )
            } else {
                Text("Ajukan Pinjaman")
            }
        }
    }

    // Sukses — dialog konfirmasi hasil, bukan input. Satu-satunya jalan keluar: OK -> onSubmitted().
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

@LightDarkPreview
@Composable
private fun LoanFormFilledPreview() {
    EsimkoPreview {
        LoanFormBody(
            state = LoanApplicationState(
                loanTypes = listOf(
                    TransactionType(9, "Pinjaman Umum", "PJU"),
                    TransactionType(10, "Pinjaman Khusus", "PJK")
                ),
                salary = Salary(gajiPokok = 4_250_000),
                selectedJenis = 9,
                nominal = "5000000",
                tenor = "12",
                gajiPokok = "4250000",
                slipBytes = null
            )
        )
    }
}

@LightDarkPreview
@Composable
private fun LoanFormEmptyPreview() {
    EsimkoPreview {
        LoanFormBody(
            state = LoanApplicationState(
                loanTypes = listOf(TransactionType(9, "Pinjaman Umum", "PJU"))
            )
        )
    }
}

// ponytail: decode langsung (bitmap full-size). Kalau memori jadi masalah di device kecil, decode dengan inSampleSize.
private fun ByteArray.toBitmap() = try { BitmapFactory.decodeByteArray(this, 0, size) } catch (e: Exception) { null }
