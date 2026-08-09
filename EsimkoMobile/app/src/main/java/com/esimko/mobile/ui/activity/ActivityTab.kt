package com.esimko.mobile.ui.activity

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.heightIn
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.ExperimentalFoundationApi
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.LazyRow
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.lazy.rememberLazyListState
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.FilterChip
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.material3.pulltorefresh.PullToRefreshBox
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import com.esimko.mobile.domain.model.Transaction
import com.esimko.mobile.ui.common.EmptyStateView
import com.esimko.mobile.ui.common.ErrorView
import com.esimko.mobile.ui.common.EsimkoPreview
import com.esimko.mobile.ui.common.LightDarkPreview
import com.esimko.mobile.ui.common.ListRow
import com.esimko.mobile.ui.common.Money
import com.esimko.mobile.ui.common.RowDivider
import com.esimko.mobile.ui.common.SectionHeader
import com.esimko.mobile.ui.common.SkeletonListRows
import com.esimko.mobile.ui.common.StatusChip
import com.esimko.mobile.ui.common.StaleBanner
import com.esimko.mobile.util.ActivityGrouping
import com.esimko.mobile.util.ActivityRow
import com.esimko.mobile.util.MoneyFormatter

@OptIn(ExperimentalMaterial3Api::class, ExperimentalFoundationApi::class)
@Composable
fun ActivityTab(
    initialFilter: String? = null,
    onOpenDetail: (Long, String) -> Unit = { _, _ -> },
    viewModel: ActivityViewModel = hiltViewModel()
) {
    val state by viewModel.state.collectAsState()
    LaunchedEffect(Unit) { viewModel.start(initialFilter) }

    Column(Modifier.fillMaxSize()) {
        Text(
            text = "Aktivitas",
            style = MaterialTheme.typography.headlineMedium,
            modifier = Modifier.padding(start = 16.dp, end = 16.dp, top = 16.dp, bottom = 8.dp)
        )

        LazyRow(
            contentPadding = PaddingValues(horizontal = 16.dp),
            horizontalArrangement = Arrangement.spacedBy(8.dp)
        ) {
            items(ActivitySegment.values()) { segment ->
                FilterChip(
                    selected = state.segment == segment,
                    onClick = { viewModel.selectSegment(segment) },
                    label = { Text(segment.label) },
                    modifier = Modifier.heightIn(min = 48.dp)
                )
            }
        }

        if (state.showStatusFilter && !(state.statusError != null && state.statuses.isEmpty())) {
            Spacer(Modifier.height(8.dp))
            LazyRow(
                contentPadding = PaddingValues(horizontal = 16.dp),
                horizontalArrangement = Arrangement.spacedBy(8.dp)
            ) {
                item {
                    FilterChip(
                        selected = state.selectedStatusId == null,
                        onClick = { viewModel.selectStatus(null) },
                        label = { Text("Semua") },
                        modifier = Modifier.heightIn(min = 48.dp)
                    )
                }
                items(state.statuses) { status ->
                    FilterChip(
                        selected = state.selectedStatusId == status.id,
                        onClick = { viewModel.selectStatus(status.id) },
                        label = { Text(status.nama) },
                        modifier = Modifier.heightIn(min = 48.dp)
                    )
                }
            }
        }

        Spacer(Modifier.height(16.dp))

        val error = state.error
        when {
            state.isFirstLoad -> SkeletonListRows(count = 6)
            state.rows.isEmpty() && error == null -> EmptyStateView(
                message = "Belum ada pengajuan. Mulai dari Beranda."
            )
            state.rows.isEmpty() && error != null -> ErrorView(
                message = error,
                onRetry = viewModel::retry
            )
            else -> {
                if (error != null) {
                    StaleBanner(onRetry = viewModel::retry)
                }
                ActivityList(
                    rows = state.rows,
                    hasMore = state.hasMore,
                    page = state.page,
                    isRefreshing = state.isRefreshing,
                    isLoadingMore = state.isLoadingMore,
                    onOpenDetail = onOpenDetail,
                    onRefresh = viewModel::refresh,
                    onLoadMore = viewModel::loadMore,
                    modifier = Modifier.weight(1f)
                )
            }
        }
    }
}

@OptIn(ExperimentalMaterial3Api::class, ExperimentalFoundationApi::class)
@Composable
private fun ActivityList(
    rows: List<ActivityRow>,
    hasMore: Boolean,
    page: Int,
    isRefreshing: Boolean,
    isLoadingMore: Boolean,
    onOpenDetail: (Long, String) -> Unit,
    onRefresh: () -> Unit,
    onLoadMore: () -> Unit,
    modifier: Modifier = Modifier
) {
    // ponytail: BOM 2024.09 dipastikan kompilasi → PullToRefreshBox dipakai langsung, tanpa probe.
    // Kalau BOM diturunkan dan simbol hilang, ganti ke SectionHeader(actionLabel="Muat ulang").
    val listState = rememberLazyListState()
    PullToRefreshBox(
        isRefreshing = isRefreshing,
        onRefresh = onRefresh,
        modifier = modifier.fillMaxSize()
    ) {
        LazyColumn(
            state = listState,
            modifier = Modifier.fillMaxSize()
        ) {
            rows.forEach { row ->
                when (row) {
                    is ActivityRow.SectionHeaderRow -> stickyHeader(key = row.key) {
                        SectionHeader(
                            title = row.title,
                            modifier = Modifier
                                .background(MaterialTheme.colorScheme.background)
                                .padding(horizontal = 16.dp, vertical = 8.dp)
                        )
                    }
                    is ActivityRow.MonthHeaderRow -> stickyHeader(key = row.key) {
                        Text(
                            text = row.title,
                            style = MaterialTheme.typography.labelMedium,
                            color = MaterialTheme.colorScheme.onSurfaceVariant,
                            modifier = Modifier
                                .fillMaxWidth()
                                .background(MaterialTheme.colorScheme.background)
                                .padding(horizontal = 16.dp, vertical = 8.dp)
                        )
                    }
                    is ActivityRow.ItemRow -> item(key = row.key) {
                        ActivityListItem(row.transaction, onOpenDetail)
                        RowDivider(Modifier.padding(horizontal = 16.dp))
                    }
                }
            }

            if (hasMore) {
                item(key = "load-more") {
                    LaunchedEffect(page) { onLoadMore() }
                    Box(
                        Modifier.fillMaxWidth().padding(16.dp),
                        contentAlignment = Alignment.Center
                    ) {
                        CircularProgressIndicator(Modifier.size(24.dp))
                    }
                }
            }

            if (isLoadingMore) {
                // ponytail: indikator eksplisit saat loadMore dipicu manual (jarang terjadi
                // karena LaunchedEffect di atas sudah menanganinya). Add ketika terlihat perlu.
            }
        }
    }
}

@Composable
private fun ActivityListItem(
    trx: Transaction,
    onOpenDetail: (Long, String) -> Unit
) {
    val subtitle = listOfNotNull(
        trx.monthOverride ?: ActivityGrouping.dayLabel(trx.tanggal).takeIf { it != "-" },
        trx.subtitleOverride ?: trx.keterangan?.takeIf { it.isNotBlank() }
    ).joinToString(" · ").takeIf { it.isNotEmpty() }

    ListRow(
        title = trx.jenis,
        subtitle = subtitle,
        trailing = {
            Column(
                horizontalAlignment = Alignment.End,
                verticalArrangement = Arrangement.spacedBy(4.dp)
            ) {
                if (trx.nominalTampil != null) {
                    Text(
                        text = trx.nominalTampil,
                        style = MaterialTheme.typography.titleSmall,
                        maxLines = 1
                    )
                } else {
                    Money(amount = trx.nominal, showSign = trx.isDebit)
                }
                if (trx.status.isNotBlank()) {
                    StatusChip(status = trx.status, color = trx.color)
                }
            }
        },
        onClick = { onOpenDetail(trx.id, trx.modul) },
        modifier = Modifier.padding(horizontal = 16.dp)
    )
}

@LightDarkPreview
@Composable
private fun ActivityTabPreview() = EsimkoPreview {
    ActivityListPreviewBody(
        rows = ActivityGrouping.build(
            listOf(
                Transaction(
                    id = 1, jenis = "Simpanan Wajib", modul = "transaksi", nominal = 150_000,
                    tanggal = "2026-08-05", status = "Menunggu Verifikasi",
                    statusLabel = "Menunggu Verifikasi", keterangan = null, color = "#F2C230"
                ),
                Transaction(
                    id = 2, jenis = "Belanja Toko", modul = "toko", nominal = 145_000,
                    tanggal = "2026-07-28", status = "Selesai", statusLabel = "Selesai",
                    keterangan = "TRX-00281", color = "#118334", isDebit = true,
                    subtitleOverride = "3 barang"
                )
            )
        )
    )
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
private fun ActivityListPreviewBody(rows: List<ActivityRow>) {
    ActivityList(
        rows = rows,
        hasMore = false,
        page = 1,
        isRefreshing = false,
        isLoadingMore = false,
        onOpenDetail = { _, _ -> },
        onRefresh = {},
        onLoadMore = {}
    )
}
