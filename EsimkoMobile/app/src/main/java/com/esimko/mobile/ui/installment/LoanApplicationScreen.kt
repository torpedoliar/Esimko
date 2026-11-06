package com.esimko.mobile.ui.installment

import android.net.Uri
import androidx.activity.compose.rememberLauncherForActivityResult
import androidx.activity.result.contract.ActivityResultContracts
import androidx.compose.animation.AnimatedContent
import androidx.compose.animation.core.tween
import androidx.compose.animation.fadeIn
import androidx.compose.animation.fadeOut
import androidx.compose.animation.slideInHorizontally
import androidx.compose.animation.slideOutHorizontally
import androidx.compose.animation.togetherWith
import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.CheckCircle
import androidx.compose.material.icons.outlined.CloudUpload
import androidx.compose.material.icons.outlined.Description
import androidx.compose.material.icons.outlined.PhotoCamera
import androidx.compose.material.icons.outlined.PictureAsPdf
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.runtime.saveable.rememberSaveable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.asImageBitmap
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.core.content.FileProvider
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
import com.esimko.mobile.util.DocGroup
import com.esimko.mobile.util.LoanDocs
import com.esimko.mobile.util.MoneyFormatter
import com.esimko.mobile.util.compressForUpload
import com.esimko.mobile.util.decodeSampled
import java.io.File

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

private enum class LoanStep(val label: String) {
    DATA("Data Pinjaman"),
    DOKUMEN("Dokumen"),
    PERSETUJUAN("Persetujuan")
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun LoanApplicationScreen(
    onBack: () -> Unit,
    onSubmitted: () -> Unit = {},
    pengajuanId: Long? = null,
    viewModel: LoanApplicationViewModel = hiltViewModel()
) {
    val state by viewModel.state.collectAsState()
    LaunchedEffect(pengajuanId) { if (pengajuanId != null) viewModel.load(pengajuanId) }

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
    var step by rememberSaveable { mutableStateOf(LoanStep.DATA) }
    var maxReached by rememberSaveable { mutableStateOf(LoanStep.DATA) }

    var pickTarget by remember { mutableStateOf<String?>(null) }
    val dokumenPicker = rememberLauncherForActivityResult(
        contract = ActivityResultContracts.GetContent()
    ) { uri: Uri? ->
        val key = pickTarget
        if (uri != null && key != null) {
            val mime = context.contentResolver.getType(uri)
            when {
                mime != null && mime.startsWith("image/") -> {
                    val compressed = context.contentResolver.openInputStream(uri)?.use { stream ->
                        stream.readBytes().compressForUpload()
                    }
                    if (compressed != null) {
                        onDokumenPicked(key, DokumenFile(compressed, "image/jpeg", uri.lastPathSegment ?: "dokumen.jpg"))
                    }
                }
                mime == "application/pdf" -> {
                    val bytes = context.contentResolver.openInputStream(uri)?.use { it.readBytes() }
                    if (bytes != null) {
                        onDokumenPicked(key, DokumenFile(bytes, mime, uri.lastPathSegment ?: "dokumen.pdf"))
                    }
                }
                else -> onDokumenPicked(key, DokumenFile(ByteArray(0), mime ?: "", uri.lastPathSegment ?: ""))
            }
        }
    }

    val dataValid = state.selectedJenis != null && state.nominalValue > 0L &&
        state.tenorValid && state.gajiValue > 0L
    val docsComplete = state.dokumenKurang.isEmpty()

    fun pick(key: String) {
        pickTarget = key
        dokumenPicker.launch("*/*")
    }

    var cameraTarget by remember { mutableStateOf<String?>(null) }
    val cameraUriState = remember { mutableStateOf<Uri?>(null) }
    val cameraLauncher = rememberLauncherForActivityResult(
        contract = ActivityResultContracts.TakePicture()
    ) { ok ->
        val key = cameraTarget
        val uri = cameraUriState.value
        if (ok && key != null && uri != null) {
            val bytes = context.contentResolver.openInputStream(uri)?.use { it.readBytes() }?.compressForUpload()
            if (bytes != null) {
                onDokumenPicked(key, DokumenFile(bytes, "image/jpeg", "foto.jpg"))
            }
        }
        cameraTarget = null
        cameraUriState.value = null
    }

    fun pickCamera(key: String) {
        val file = File(context.cacheDir, "camera_${System.currentTimeMillis()}.jpg")
        val uri = FileProvider.getUriForFile(context, context.packageName + ".provider", file)
        cameraUriState.value = uri
        cameraTarget = key
        cameraLauncher.launch(uri)
    }

    Column(modifier = Modifier.fillMaxSize()) {
        StepIndicator(
            current = step,
            maxReached = maxReached,
            onSelect = { target ->
                if (target.ordinal <= maxReached.ordinal) {
                    step = target
                }
            }
        )

        AnimatedContent(
            targetState = step,
            transitionSpec = {
                val dir = if (targetState.ordinal > initialState.ordinal) 1 else -1
                (slideInHorizontally(tween(260)) { it * dir } + fadeIn(tween(260))) togetherWith
                    (slideOutHorizontally(tween(260)) { -it * dir } + fadeOut(tween(200)))
            },
            label = "loanSteps",
            modifier = Modifier.weight(1f)
        ) { current ->
            when (current) {
                LoanStep.DATA -> StepData(
                    state = state,
                    onGajiChange = onGajiChange,
                    onNominalChange = onNominalChange,
                    onTenorChange = onTenorChange,
                    onSelectJenis = onSelectJenis
                )
                LoanStep.DOKUMEN -> StepDokumen(
                    state = state,
                    dokumenError = state.dokumenError,
                    enabled = !state.isSubmitting,
                    onPickGallery = ::pick,
                    onPickCamera = ::pickCamera,
                    onClear = onClearDokumen
                )
                LoanStep.PERSETUJUAN -> StepPersetujuan(
                    state = state,
                    onPersetujuanChange = onPersetujuanChange,
                    onPersetujuanPinjamanChange = onPersetujuanPinjamanChange,
                    submitError = state.submitError
                )
            }
        }

        WizardBar(
            step = step,
            canAdvanceNext = when (step) {
                LoanStep.DATA -> dataValid
                LoanStep.DOKUMEN -> docsComplete && state.dokumenError == null
                LoanStep.PERSETUJUAN -> false
            },
            canSubmit = state.bisaKirim,
            isSubmitting = state.isSubmitting,
            onBack = { if (step.ordinal > 0) step = LoanStep.entries[step.ordinal - 1] },
            onNext = {
                val next = LoanStep.entries[step.ordinal + 1]
                step = next
                if (next.ordinal > maxReached.ordinal) maxReached = next
            },
            onSubmit = onSubmit
        )
    }

    state.submitSuccess?.let { id ->
        AlertDialog(
            onDismissRequest = { },
            title = { Text("Pengajuan Terkirim") },
            text = { Text("Pengajuan pinjaman berhasil dikirim. Nomor transaksi: $id. Menunggu verifikasi pengurus.") },
            confirmButton = { Button(onClick = onSubmitted) { Text("OK") } }
        )
    }
}

@Composable
private fun StepIndicator(
    current: LoanStep,
    maxReached: LoanStep,
    onSelect: (LoanStep) -> Unit
) {
    Row(
        modifier = Modifier
            .fillMaxWidth()
            .padding(horizontal = 16.dp, vertical = 12.dp),
        horizontalArrangement = Arrangement.spacedBy(8.dp)
    ) {
        LoanStep.entries.forEachIndexed { index, step ->
            val number = index + 1
            val done = maxReached.ordinal > index
            val active = current == step
            val enabled = index <= maxReached.ordinal
            Row(
                modifier = Modifier
                    .weight(1f)
                    .clip(RoundedCornerShape(50))
                    .background(
                        when {
                            active -> MaterialTheme.colorScheme.primaryContainer
                            enabled -> MaterialTheme.colorScheme.surfaceVariant
                            else -> MaterialTheme.colorScheme.surfaceVariant.copy(alpha = 0.5f)
                        }
                    )
                    .clickable(enabled = enabled, onClick = { onSelect(step) })
                    .padding(horizontal = 10.dp, vertical = 8.dp),
                verticalAlignment = Alignment.CenterVertically,
                horizontalArrangement = Arrangement.Center
            ) {
                Box(
                    modifier = Modifier
                        .size(18.dp)
                        .clip(CircleShape)
                        .background(
                            if (active || done) MaterialTheme.colorScheme.primary
                            else MaterialTheme.colorScheme.outlineVariant
                        ),
                    contentAlignment = Alignment.Center
                ) {
                    if (done) {
                        Icon(
                            Icons.Filled.CheckCircle,
                            contentDescription = null,
                            tint = MaterialTheme.colorScheme.onPrimary,
                            modifier = Modifier.size(14.dp)
                        )
                    } else {
                        Text(
                            "$number",
                            style = MaterialTheme.typography.labelSmall,
                            color = if (active) MaterialTheme.colorScheme.onPrimary else MaterialTheme.colorScheme.onSurfaceVariant
                        )
                    }
                }
                Spacer(Modifier.width(6.dp))
                Text(
                    step.label,
                    style = MaterialTheme.typography.labelMedium,
                    maxLines = 1,
                    overflow = TextOverflow.Ellipsis,
                    color = if (enabled) MaterialTheme.colorScheme.onSurface else MaterialTheme.colorScheme.onSurfaceVariant.copy(alpha = 0.6f)
                )
            }
        }
    }
}

@Composable
private fun WizardBar(
    step: LoanStep,
    canAdvanceNext: Boolean,
    canSubmit: Boolean,
    isSubmitting: Boolean,
    onBack: () -> Unit,
    onNext: () -> Unit,
    onSubmit: () -> Unit
) {
    Surface(tonalElevation = 3.dp) {
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .padding(horizontal = 16.dp, vertical = 12.dp),
            horizontalArrangement = Arrangement.spacedBy(12.dp)
        ) {
            if (step != LoanStep.DATA) {
                OutlinedButton(
                    onClick = onBack,
                    enabled = !isSubmitting,
                    modifier = Modifier.heightIn(min = 48.dp)
                ) {
                    Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = null, modifier = Modifier.size(18.dp))
                    Spacer(Modifier.width(4.dp))
                    Text("Kembali")
                }
            }
            if (step == LoanStep.PERSETUJUAN) {
                Button(
                    onClick = onSubmit,
                    enabled = canSubmit && !isSubmitting,
                    modifier = Modifier.weight(1f).height(52.dp)
                ) {
                    if (isSubmitting) {
                        CircularProgressIndicator(Modifier.size(24.dp), color = MaterialTheme.colorScheme.onPrimary, strokeWidth = 2.dp)
                    } else {
                        Text("Ajukan Pinjaman")
                    }
                }
            } else {
                Button(
                    onClick = onNext,
                    enabled = canAdvanceNext && !isSubmitting,
                    modifier = Modifier.weight(1f).height(52.dp)
                ) {
                    Text("Lanjut")
                }
            }
        }
    }
}

@Composable
private fun StepData(
    state: LoanApplicationState,
    onGajiChange: (String) -> Unit,
    onNominalChange: (String) -> Unit,
    onTenorChange: (String) -> Unit,
    onSelectJenis: (Int) -> Unit
) {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .verticalScroll(rememberScrollState())
            .padding(bottom = 24.dp),
        verticalArrangement = Arrangement.spacedBy(16.dp)
    ) {
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

        AmountField(
            value = state.nominal,
            onValueChange = onNominalChange,
            modifier = Modifier.fillMaxWidth().padding(horizontal = 16.dp),
            label = "Nominal pinjaman",
            enabled = !state.isSubmitting
        )

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

        if (state.estimasiAngsuran > 0L) {
            Column(modifier = Modifier.fillMaxWidth().padding(horizontal = 16.dp)) {
                Text(
                    text = "Estimasi angsuran per bulan",
                    style = MaterialTheme.typography.labelMedium,
                    color = MaterialTheme.colorScheme.onSurfaceVariant
                )
                Money(amount = state.estimasiAngsuran, style = MoneyRow, color = MaterialTheme.colorScheme.primary)
                Text(
                    text = "Bunga flat ${java.math.BigDecimal.valueOf(state.bungaRate ?: 0.0).multiply(java.math.BigDecimal(100)).stripTrailingZeros().toPlainString().replace('.', ',')}% per bulan sesuai pengaturan koperasi. Angka resmi ditentukan saat verifikasi.",
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.onSurfaceVariant
                )
            }
        }

        if (state.nominalValue > 0L && state.tenorValid && state.bungaRate == null) {
            Text(
                text = "Estimasi belum tersedia karena bunga koperasi belum berhasil dimuat. Buka ulang halaman untuk mencoba lagi.",
                style = MaterialTheme.typography.bodySmall,
                color = MaterialTheme.colorScheme.onSurfaceVariant,
                modifier = Modifier.padding(horizontal = 16.dp)
            )
        }
    }
}

@Composable
private fun StepDokumen(
    state: LoanApplicationState,
    dokumenError: String?,
    enabled: Boolean,
    onPickGallery: (String) -> Unit,
    onPickCamera: (String) -> Unit,
    onClear: (String) -> Unit
) {
    val groups = LoanDocs.groupedDocuments(state.selectedJenis, state.nominalValue, state.ktp2Threshold)
    val total = state.slotDokumen.size
    val filled = state.slotDokumen.count { state.dokumen.containsKey(it) || it in state.existingDokumen }
    val progress = if (total == 0) 0f else filled.toFloat() / total.toFloat()

    Column(
        modifier = Modifier
            .fillMaxSize()
            .verticalScroll(rememberScrollState())
            .padding(bottom = 24.dp)
    ) {
        // Banner ajukan ulang: tampilkan alasan & tenggat bila dokumen lama dipakai.
        if (state.pengajuanId != null) {
            Column(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(16.dp)
                    .background(MaterialTheme.colorScheme.secondaryContainer, RoundedCornerShape(12.dp))
                    .padding(16.dp),
                verticalArrangement = Arrangement.spacedBy(6.dp)
            ) {
                Text("Ajukan Ulang", style = MaterialTheme.typography.titleMedium, color = MaterialTheme.colorScheme.onSecondaryContainer)
                state.alasanPenolakan?.takeIf { it.isNotBlank() }?.let {
                    Text("Alasan ditolak: $it", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSecondaryContainer)
                }
                state.batasAjukanUlang?.let {
                    Text("Ajukan ulang sebelum $it. Jika lewat, dokumen lama akan dihapus otomatis.", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSecondaryContainer)
                }
                Text("Dokumen lama tetap terpakai — cukup ganti dokumen yang perlu diperbaiki.", style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSecondaryContainer)
            }
        }

        // Ringkasan progres — jelas di atas, tidak menggulir jauh.
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .padding(16.dp)
                .border(1.dp, MaterialTheme.colorScheme.outlineVariant, RoundedCornerShape(12.dp))
                .padding(16.dp),
            verticalArrangement = Arrangement.spacedBy(8.dp)
        ) {
            Row(Modifier.fillMaxWidth(), verticalAlignment = Alignment.CenterVertically) {
                Text(
                    "Dokumen dilengkapi",
                    style = MaterialTheme.typography.titleMedium,
                    modifier = Modifier.weight(1f)
                )
                Text(
                    "$filled dari $total",
                    style = MaterialTheme.typography.labelLarge,
                    color = MaterialTheme.colorScheme.primary
                )
            }
            LinearProgressIndicator(
                progress = { progress },
                modifier = Modifier.fillMaxWidth().clip(CircleShape)
            )
            groups.forEach { group ->
                val gFilled = group.keys.count { state.dokumen.containsKey(it) || it in state.existingDokumen }
                Text(
                    "${group.title}: $gFilled/${group.keys.size}",
                    style = MaterialTheme.typography.labelMedium,
                    color = MaterialTheme.colorScheme.onSurfaceVariant
                )
            }
        }

        dokumenError?.let { msg ->
            Text(
                text = msg,
                color = MaterialTheme.colorScheme.error,
                style = MaterialTheme.typography.bodySmall,
                modifier = Modifier.padding(horizontal = 16.dp)
            )
        }

        groups.forEach { group ->
            SectionHeader(
                title = group.title,
                modifier = Modifier.padding(horizontal = 16.dp, vertical = 8.dp)
            )
            Column(
                modifier = Modifier.fillMaxWidth().padding(horizontal = 16.dp),
                verticalArrangement = Arrangement.spacedBy(8.dp)
            ) {
                group.keys.forEach { key ->
                    DokumenRow(
                        label = LoanDocs.label(key),
                        info = LoanDocs.infoSlot(key, state.ktp2Threshold),
                        file = state.dokumen[key],
                        hasExisting = key in state.existingDokumen,
                        enabled = enabled,
                        onPickGallery = { onPickGallery(key) },
                        onPickCamera = { onPickCamera(key) },
                        onClear = { onClear(key) }
                    )
                }
            }
        }
    }
}

@Composable
private fun StepPersetujuan(
    state: LoanApplicationState,
    onPersetujuanChange: (Boolean) -> Unit,
    onPersetujuanPinjamanChange: (Boolean) -> Unit,
    submitError: String?
) {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .verticalScroll(rememberScrollState())
            .padding(bottom = 24.dp),
        verticalArrangement = Arrangement.spacedBy(16.dp)
    ) {
        ConsentBlock(
            title = "Persetujuan Data Pribadi",
            text = state.syarat?.persetujuanTeks ?: PERSETUJUAN_FALLBACK,
            checked = state.persetujuanSetuju,
            onCheckedChange = onPersetujuanChange,
            enabled = !state.isSubmitting,
            label = "Saya menyetujui dan melanjutkan pengajuan"
        )
        ConsentBlock(
            title = "Persetujuan Pengajuan Pinjaman",
            text = state.syarat?.persetujuanPinjamanTeks?.takeIf { it.isNotBlank() }
                ?: PERSETUJUAN_PINJAMAN_FALLBACK,
            checked = state.persetujuanPinjamanSetuju,
            onCheckedChange = onPersetujuanPinjamanChange,
            enabled = !state.isSubmitting,
            label = "Saya menyetujui syarat & ketentuan pinjaman"
        )
        submitError?.let { msg ->
            Text(
                text = msg,
                color = MaterialTheme.colorScheme.error,
                style = MaterialTheme.typography.bodySmall,
                modifier = Modifier.padding(horizontal = 16.dp)
            )
        }
    }
}

@Composable
private fun ConsentBlock(
    title: String,
    text: String,
    checked: Boolean,
    onCheckedChange: (Boolean) -> Unit,
    enabled: Boolean,
    label: String
) {
    Column(Modifier.fillMaxWidth()) {
        SectionHeader(title, modifier = Modifier.padding(horizontal = 16.dp))
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .padding(horizontal = 16.dp)
                .border(1.dp, MaterialTheme.colorScheme.outlineVariant, RoundedCornerShape(8.dp))
                .padding(12.dp),
            verticalArrangement = Arrangement.spacedBy(8.dp)
        ) {
            Text(
                text = text,
                style = MaterialTheme.typography.bodySmall,
                color = MaterialTheme.colorScheme.onSurfaceVariant
            )
            Row(verticalAlignment = Alignment.CenterVertically) {
                Checkbox(checked = checked, onCheckedChange = onCheckedChange, enabled = enabled)
                Text(label, style = MaterialTheme.typography.bodyMedium)
            }
        }
    }
}

/** Slot dokumen ringkas: baris sentuh penuh, bukan kartu besar. */
@Composable
private fun DokumenRow(
    label: String,
    info: String,
    file: DokumenFile?,
    hasExisting: Boolean,
    enabled: Boolean,
    onPickGallery: () -> Unit,
    onPickCamera: () -> Unit,
    onClear: () -> Unit
) {
    val filled = file != null || hasExisting
    Row(
        modifier = Modifier
            .fillMaxWidth()
            .clip(RoundedCornerShape(12.dp))
            .border(
                1.dp,
                if (filled) MaterialTheme.colorScheme.primary.copy(alpha = 0.4f)
                else MaterialTheme.colorScheme.outlineVariant,
                RoundedCornerShape(12.dp)
            )
            .padding(12.dp),
        verticalAlignment = Alignment.CenterVertically
    ) {
        Box(
            modifier = Modifier
                .size(40.dp)
                .clip(RoundedCornerShape(8.dp))
                .background(
                    if (filled) MaterialTheme.colorScheme.primaryContainer
                    else MaterialTheme.colorScheme.surfaceVariant
                ),
            contentAlignment = Alignment.Center
        ) {
            when {
                file?.isGambar == true -> {
                    val bitmap = remember(file) { file.bytes.decodeSampled() }
                    if (bitmap != null) {
                        Image(
                            bitmap = bitmap.asImageBitmap(),
                            contentDescription = label,
                            modifier = Modifier.fillMaxSize().padding(4.dp),
                            contentScale = ContentScale.Crop
                        )
                    } else {
                        Icon(Icons.Outlined.PictureAsPdf, null, tint = MaterialTheme.colorScheme.primary)
                    }
                }
                file != null -> Icon(Icons.Outlined.Description, null, tint = MaterialTheme.colorScheme.primary)
                hasExisting -> Icon(Icons.Filled.CheckCircle, null, tint = MaterialTheme.colorScheme.primary)
                else -> Icon(Icons.Outlined.CloudUpload, null, tint = MaterialTheme.colorScheme.onSurfaceVariant)
            }
        }
        Spacer(Modifier.width(12.dp))
        Column(Modifier.weight(1f)) {
            Text(label, style = MaterialTheme.typography.titleSmall)
            Text(
                text = when {
                    file != null -> (file.nama ?: "Terlampir")
                    hasExisting -> "Sudah dilampirkan — ganti bila perlu"
                    else -> info
                },
                style = MaterialTheme.typography.bodySmall,
                color = if (filled) MaterialTheme.colorScheme.primary else MaterialTheme.colorScheme.onSurfaceVariant,
                maxLines = 2,
                overflow = TextOverflow.Ellipsis
            )
        }
        Spacer(Modifier.width(8.dp))
        if (file != null) {
            TextButton(onClick = onClear, enabled = enabled) { Text("Hapus") }
        } else {
            Column(horizontalAlignment = Alignment.End) {
                TextButton(onClick = onPickCamera, enabled = enabled) {
                    Icon(Icons.Outlined.PhotoCamera, contentDescription = null, modifier = Modifier.size(18.dp))
                    Spacer(Modifier.width(4.dp))
                    Text("Foto")
                }
                TextButton(onClick = onPickGallery, enabled = enabled) { Text(if (hasExisting) "Ganti" else "Galeri") }
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