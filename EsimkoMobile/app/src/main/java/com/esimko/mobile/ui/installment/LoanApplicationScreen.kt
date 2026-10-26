package com.esimko.mobile.ui.installment

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
import androidx.compose.material.icons.outlined.Description
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
import com.esimko.mobile.domain.model.DokumenFile
import com.esimko.mobile.domain.model.Salary
import com.esimko.mobile.domain.model.SyaratPinjaman
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
import com.esimko.mobile.ui.theme.MoneyRow
import com.esimko.mobile.ui.theme.OnHero
import com.esimko.mobile.util.LoanDocs
import com.esimko.mobile.util.MoneyFormatter
import com.esimko.mobile.util.compressForUpload
import com.esimko.mobile.util.decodeSampled

// Fallback teks persetujuan kalau endpoint syarat gagal dimuat (sama dgn server v1).
private const val PERSETUJUAN_FALLBACK =
    "Dokumen yang saya lampirkan (slip gaji, KK, KTP, kartu anggota, ID Card, dan/atau " +
        "KTP pasangan) adalah data pribadi. Saya menyetujui pengumpulan dan pengolahannya " +
        "untuk verifikasi pengajuan pinjaman ini saja; dokumen dihapus otomatis bila " +
        "pengajuan ditolak, sesuai UU No. 27/2022 tentang Pelindungan Data Pribadi."

// Fallback persetujuan kedua: pernyataan pengajuan pinjaman (syarat & ketentuan).
private const val PERSETUJUAN_PINJAMAN_FALLBACK =
    "Saya menyatakan bahwa data dan dokumen dalam pengajuan pinjaman ini benar, serta " +
        "menyetujui syarat dan ketentuan pinjaman koperasi — termasuk bunga, jadwal " +
        "angsuran, dan konsekuensi keterlambatan pembayaran."

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
                        onDokumenPicked = viewModel::onDokumenPicked,
                        onClearDokumen = viewModel::clearDokumen,
                        onPersetujuanChange = viewModel::onPersetujuanChange,
                        onPersetujuanPinjamanChange = viewModel::onPersetujuanPinjamanChange,
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
    onDokumenPicked: (String, DokumenFile?) -> Unit = { _, _ -> },
    onClearDokumen: (String) -> Unit = {},
    onPersetujuanChange: (Boolean) -> Unit = {},
    onPersetujuanPinjamanChange: (Boolean) -> Unit = {},
    onSubmit: () -> Unit = {},
    onSubmitted: () -> Unit = {}
) {
    val context = LocalContext.current
    // Picker bersama untuk semua slot; pickTarget menandai slot yang sedang diisi.
    var pickTarget by remember { mutableStateOf<String?>(null) }
    val dokumenPicker = rememberLauncherForActivityResult(
        contract = ActivityResultContracts.GetContent()
    ) { uri: Uri? ->
        val key = pickTarget
        if (uri != null && key != null) {
            val mime = context.contentResolver.getType(uri)
            when {
                // Foto/gambar: kompres dulu (kamera 3-8MB → ~200KB) supaya aman upload.
                mime != null && mime.startsWith("image/") -> {
                    val compressed = context.contentResolver.openInputStream(uri)?.use { stream ->
                        stream.readBytes().compressForUpload()
                    }
                    if (compressed != null) {
                        onDokumenPicked(key, DokumenFile(compressed, "image/jpeg", uri.lastPathSegment ?: "dokumen.jpg"))
                    }
                }
                // PDF: dikirim apa adanya (sudah < 5 MB dicek ViewModel).
                mime == "application/pdf" -> {
                    val bytes = context.contentResolver.openInputStream(uri)?.use { it.readBytes() }
                    if (bytes != null) {
                        onDokumenPicked(key, DokumenFile(bytes, mime, uri.lastPathSegment ?: "dokumen.pdf"))
                    }
                }
                // Tipe tak dikenal: kirim dengan bytes kosong — ViewModel menolak dgn pesan.
                else -> onDokumenPicked(key, DokumenFile(ByteArray(0), mime ?: "", uri.lastPathSegment ?: ""))
            }
        }
    }

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
                imeAction = ImeAction.Next,
                hero = true
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

        // 6. Dokumen pengajuan — slip + KK/KTP/kartu, foto atau PDF. Slot KTP-2 hanya
        //    muncul saat nominal di atas threshold (server yang memutuskan via syarat).
        SectionHeader("Dokumen Pengajuan", modifier = Modifier.padding(horizontal = 16.dp))
        Text(
            text = "Lengkapi semua dokumen di bawah — semuanya boleh berupa foto atau PDF.",
            style = MaterialTheme.typography.bodySmall,
            color = MaterialTheme.colorScheme.onSurfaceVariant,
            modifier = Modifier.padding(horizontal = 16.dp)
        )
        Column(
            modifier = Modifier.fillMaxWidth().padding(horizontal = 16.dp),
            verticalArrangement = Arrangement.spacedBy(8.dp)
        ) {
            state.slotDokumen.forEach { key ->
                DokumenSlot(
                    label = LoanDocs.label(key),
                    info = LoanDocs.infoSlot(key, state.ktp2Threshold),
                    file = state.dokumen[key],
                    enabled = !state.isSubmitting,
                    onPick = {
                        pickTarget = key
                        dokumenPicker.launch("*/*")
                    },
                    onClear = { onClearDokumen(key) }
                )
            }
            state.dokumenError?.let { msg ->
                Text(
                    text = msg,
                    color = MaterialTheme.colorScheme.error,
                    style = MaterialTheme.typography.bodySmall
                )
            }
        }

        // 7. Persetujuan data pribadi — gerbang submit (bukti versi+waktu disimpan server).
        SectionHeader("Persetujuan Data Pribadi", modifier = Modifier.padding(horizontal = 16.dp))
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .padding(horizontal = 16.dp)
                .border(1.dp, MaterialTheme.colorScheme.outlineVariant, RoundedCornerShape(8.dp))
                .padding(12.dp),
            verticalArrangement = Arrangement.spacedBy(8.dp)
        ) {
            Text(
                text = state.syarat?.persetujuanTeks ?: PERSETUJUAN_FALLBACK,
                style = MaterialTheme.typography.bodySmall,
                color = MaterialTheme.colorScheme.onSurfaceVariant
            )
            Row(verticalAlignment = Alignment.CenterVertically) {
                Checkbox(
                    checked = state.persetujuanSetuju,
                    onCheckedChange = onPersetujuanChange,
                    enabled = !state.isSubmitting
                )
                Text(
                    text = "Saya menyetujui dan melanjutkan pengajuan",
                    style = MaterialTheme.typography.bodyMedium
                )
            }
        }

        // 7b. Persetujuan pengajuan pinjaman (checkbox kedua) — syarat & ketentuan.
        SectionHeader("Persetujuan Pengajuan Pinjaman", modifier = Modifier.padding(horizontal = 16.dp))
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .padding(horizontal = 16.dp)
                .border(1.dp, MaterialTheme.colorScheme.outlineVariant, RoundedCornerShape(8.dp))
                .padding(12.dp),
            verticalArrangement = Arrangement.spacedBy(8.dp)
        ) {
            Text(
                text = state.syarat?.persetujuanPinjamanTeks?.takeIf { it.isNotBlank() }
                    ?: PERSETUJUAN_PINJAMAN_FALLBACK,
                style = MaterialTheme.typography.bodySmall,
                color = MaterialTheme.colorScheme.onSurfaceVariant
            )
            Row(verticalAlignment = Alignment.CenterVertically) {
                Checkbox(
                    checked = state.persetujuanPinjamanSetuju,
                    onCheckedChange = onPersetujuanPinjamanChange,
                    enabled = !state.isSubmitting
                )
                Text(
                    text = "Saya menyetujui syarat & ketentuan pinjaman",
                    style = MaterialTheme.typography.bodyMedium
                )
            }
        }

        // 8. Error kirim.
        state.submitError?.let { msg ->
            Text(
                text = msg,
                color = MaterialTheme.colorScheme.error,
                style = MaterialTheme.typography.bodySmall,
                modifier = Modifier.padding(horizontal = 16.dp)
            )
        }

        // 9. Tombol kirim — enabled = state.bisaKirim (dokumen lengkap + persetujuan).
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

/** Satu slot dokumen: preview (gambar) / ikon (PDF), nama file, pilih-ganti-hapus. */
@Composable
private fun DokumenSlot(
    label: String,
    info: String,
    file: DokumenFile?,
    enabled: Boolean,
    onPick: () -> Unit,
    onClear: () -> Unit
) {
    Column(
        modifier = Modifier
            .fillMaxWidth()
            .border(1.dp, MaterialTheme.colorScheme.outlineVariant, RoundedCornerShape(8.dp))
            .padding(12.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.spacedBy(8.dp)
    ) {
        if (file != null) {
            if (file.isGambar) {
                val bitmap = remember(file) { file.bytes.decodeSampled() }
                if (bitmap != null) {
                    Image(
                        bitmap = bitmap.asImageBitmap(),
                        contentDescription = label,
                        modifier = Modifier.fillMaxWidth().aspectRatio(4f / 3f),
                        contentScale = ContentScale.Fit
                    )
                }
            } else {
                Icon(
                    imageVector = Icons.Outlined.Description,
                    contentDescription = null,
                    tint = MaterialTheme.colorScheme.primary
                )
            }
            Text(
                text = file.nama,
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
                text = info,
                style = MaterialTheme.typography.bodySmall,
                color = MaterialTheme.colorScheme.onSurfaceVariant
            )
        }
        Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
            Button(onClick = onPick, enabled = enabled) {
                Text(if (file != null) "Ganti" else "Pilih Foto/PDF")
            }
            if (file != null) {
                TextButton(onClick = onClear, enabled = enabled) { Text("Hapus") }
            }
        }
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
                gajiPokok = "4250000"
            )
        )
    }
}

@LightDarkPreview
@Composable
private fun LoanFormWithDocsPreview() {
    EsimkoPreview {
        LoanFormBody(
            state = LoanApplicationState(
                loanTypes = listOf(TransactionType(9, "Pinjaman Umum", "PJU")),
                selectedJenis = 9,
                nominal = "25000000", // > 10jt → slot KTP-2 ikut tampil
                tenor = "12",
                gajiPokok = "4250000",
                syarat = SyaratPinjaman(
                    ktp2Threshold = 10_000_000L,
                    dokumenWajib = listOf("kk", "ktp1", "kartu_anggota", "id_card"),
                    format = listOf("jpg", "jpeg", "png", "pdf"),
                    maksKb = 5120,
                    persetujuanVersi = "v1-2026-10-24",
                    persetujuanTeks = PERSETUJUAN_FALLBACK
                ),
                persetujuanSetuju = true
            )
        )
    }
}
