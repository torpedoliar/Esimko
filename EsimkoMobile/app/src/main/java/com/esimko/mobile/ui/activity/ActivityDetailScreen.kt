package com.esimko.mobile.ui.activity

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.aspectRatio
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.outlined.Info
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.material3.TopAppBar
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.semantics.contentDescription
import androidx.compose.ui.semantics.semantics
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import coil.compose.AsyncImage
import com.esimko.mobile.domain.model.TransactionDetail
import com.esimko.mobile.domain.model.TransactionHistory
import com.esimko.mobile.ui.common.EmptyStateView
import com.esimko.mobile.ui.common.ErrorView
import com.esimko.mobile.ui.common.EsimkoPreview
import com.esimko.mobile.ui.common.LightDarkPreview
import com.esimko.mobile.ui.common.ListRow
import com.esimko.mobile.ui.common.Money
import com.esimko.mobile.ui.common.RowDivider
import com.esimko.mobile.ui.common.SectionHeader
import com.esimko.mobile.ui.common.SkeletonListRows
import com.esimko.mobile.ui.common.StaleBanner
import com.esimko.mobile.ui.common.StatusChip
import com.esimko.mobile.ui.theme.MoneyHero
import com.esimko.mobile.util.ActivityGrouping
import com.esimko.mobile.util.MoneyFormatter

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ActivityDetailScreen(
    transactionId: Long,
    modul: String,
    onBack: () -> Unit,
    viewModel: ActivityDetailViewModel = hiltViewModel()
) {
    val state by viewModel.state.collectAsState()
    LaunchedEffect(transactionId, modul) { viewModel.start(transactionId, modul) }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Detail " + judulModul(modul)) },
                navigationIcon = {
                    IconButton(onClick = onBack) {
                        Icon(
                            Icons.AutoMirrored.Filled.ArrowBack,
                            contentDescription = "Kembali"
                        )
                    }
                }
            )
        }
    ) { padding ->
        val error = state.error
        when {
            state.noDetail -> EmptyStateView(
                message = "Rincian retur tidak tersedia.",
                icon = Icons.Outlined.Info,
                modifier = Modifier.padding(padding)
            )
            state.isLoading && !state.hasContent -> SkeletonListRows(
                count = 6,
                modifier = Modifier.padding(padding)
            )
            error != null && !state.hasContent -> ErrorView(
                message = error,
                onRetry = viewModel::retry,
                modifier = Modifier.padding(padding)
            )
            else -> {
                if (error != null) {
                    StaleBanner(onRetry = viewModel::retry, modifier = Modifier.padding(padding))
                }
                DetailContent(state, Modifier.padding(padding))
            }
        }
    }
}

/** Nama modul backend tidak boleh sampai ke user (Global Constraint). */
private fun judulModul(modul: String): String = when (modul) {
    "retur" -> "Retur"
    "toko", "konsinyasi", "online" -> "Belanja"
    else -> "Transaksi"
}

@Composable
private fun DetailContent(
    state: ActivityDetailState,
    modifier: Modifier = Modifier
) {
    val transaksi = state.transaksi
    val belanja = state.belanja
    val nominal = transaksi?.nominal ?: belanja?.total ?: 0L
    val status = transaksi?.statusLabel?.ifBlank { null } ?: belanja?.statusTampil?.ifBlank { null } ?: ""
    val color = transaksi?.color
    val statusSentence = transaksi?.statusKeterangan ?: belanja?.keteranganStatus

    LazyColumn(
        modifier = modifier.fillMaxSize(),
        contentPadding = PaddingValues(bottom = 24.dp)
    ) {
        // Kepala: nominal besar + status.
        item(key = "head") {
            Column(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(16.dp)
                    .semantics {
                        contentDescription = "Nominal ${MoneyFormatter.plain(nominal)}, status $status"
                    },
                horizontalAlignment = Alignment.CenterHorizontally,
                verticalArrangement = Arrangement.spacedBy(8.dp)
            ) {
                Money(amount = nominal, style = MoneyHero)
                if (status.isNotBlank()) {
                    StatusChip(status = status, color = color)
                }
            }
        }

        // Kalimat penjelas status.
        if (!statusSentence.isNullOrBlank()) {
            item(key = "status-sentence") {
                Text(
                    text = statusSentence,
                    style = MaterialTheme.typography.bodyMedium,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                    modifier = Modifier.padding(horizontal = 16.dp)
                )
            }
        }

        // Rincian.
        item(key = "rincian-header") {
            SectionHeader(title = "Rincian")
        }

        val rincian: List<Pair<String, String>> = buildList {
            transaksi?.let { t ->
                ActivityGrouping.dayLabel(t.tanggal).takeIf { it != "-" }?.let { add("Tanggal" to it) }
                t.jenis.takeIf { it.isNotBlank() }?.let { add("Jenis" to it) }
                t.metodePembayaran?.takeIf { it.isNotBlank() }?.let { add("Metode Pembayaran" to it) }
                t.namaPetugas?.takeIf { it.isNotBlank() }?.let { add("Petugas" to it) }
                t.keterangan?.takeIf { it.isNotBlank() }?.let { add("Keterangan" to it) }
            }
            belanja?.let { b ->
                ActivityGrouping.dayLabel(b.tanggal).takeIf { it != "-" }?.let { add("Tanggal" to it) }
                b.noTransaksi.takeIf { it.isNotBlank() }?.let { add("No. Transaksi" to it) }
                b.metodePembayaran.takeIf { it.isNotBlank() }?.let { add("Metode Pembayaran" to it) }
                if (b.jumlah > 0) add("Jumlah Barang" to "${b.jumlah} barang")
                if (b.subtotal > 0L) add("Subtotal" to MoneyFormatter.format(b.subtotal))
                if (b.diskonNominal > 0L) add("Diskon" to MoneyFormatter.format(b.diskonNominal))
                if (b.sisaAngsuran > 0L) add("Sisa Angsuran" to MoneyFormatter.format(b.sisaAngsuran))
                if (b.sisaTenor > 0) add("Sisa Tenor" to "${b.sisaTenor} bulan")
            }
        }

        rincian.forEachIndexed { index, (label, value) ->
            item(key = "rincian-$index") {
                ListRow(title = label, trailing = {
                    Text(
                        text = value,
                        style = MaterialTheme.typography.bodyLarge,
                        maxLines = 2
                    )
                })
                RowDivider()
            }
        }

        // Barang (hanya belanja).
        val items = belanja?.items
        if (!items.isNullOrEmpty()) {
            item(key = "barang-header") { SectionHeader(title = "Barang") }
            items.forEachIndexed { index, item ->
                item(key = "barang-$index") {
                    ListRow(
                        title = item.nama,
                        subtitle = "${item.qty} × ${MoneyFormatter.format(item.harga)}",
                        trailing = { Money(amount = item.subtotal) }
                    )
                    RowDivider()
                }
            }
        }

        // Bukti transaksi (hanya transaksi).
        val bukti = transaksi?.buktiTransaksi
        if (!bukti.isNullOrBlank()) {
            item(key = "bukti-header") { SectionHeader(title = "Bukti Transaksi") }
            item(key = "bukti-image") {
                AsyncImage(
                    model = bukti,
                    contentDescription = "Bukti transaksi",
                    contentScale = ContentScale.Fit,
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(horizontal = 16.dp)
                        .aspectRatio(4f / 3f)
                        .clip(RoundedCornerShape(8.dp))
                )
            }
        }

        // Timeline.
        if (state.timeline.isNotEmpty()) {
            item(key = "timeline-header") { SectionHeader(title = "Riwayat Verifikasi") }
            state.timeline.forEachIndexed { index, entry ->
                item(key = "timeline-$index") {
                    ListRow(
                        title = entry.caption,
                        subtitle = ActivityGrouping.dayLabel(entry.createdAt) + " · " + entry.pelaku
                    )
                    RowDivider()
                }
            }
        }
    }
}

@LightDarkPreview
@Composable
private fun ActivityDetailPreview() = EsimkoPreview {
    DetailContent(
        state = ActivityDetailState(
            isLoading = false,
            transaksi = TransactionDetail(
                id = 812, jenis = "Simpanan Sukarela", nominal = 500_000,
                tanggal = "2026-08-05", status = "Menunggu Verifikasi",
                statusLabel = "Menunggu Verifikasi", keterangan = "Setoran bulanan",
                buktiTransaksi = null, items = null, color = "#F2C230",
                statusKeterangan = "Pengajuan sedang diperiksa pengurus.",
                namaPetugas = "Sistem", metodePembayaran = "Transfer BRI",
                noAnggota = "A-0142", namaLengkap = "Budi Santoso"
            ),
            timeline = listOf(
                TransactionHistory(
                    createdAt = "2026-08-05 09:12:00",
                    caption = "Transaksi dibuat oleh",
                    noAnggota = "A-0142",
                    namaLengkap = "Budi Santoso"
                )
            )
        )
    )
}
