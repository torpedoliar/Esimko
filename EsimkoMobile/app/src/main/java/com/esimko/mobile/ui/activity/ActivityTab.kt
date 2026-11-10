package com.esimko.mobile.ui.activity

import androidx.compose.foundation.ExperimentalFoundationApi
import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.BoxWithConstraints
import androidx.compose.ui.platform.LocalDensity
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.rememberLazyListState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.outlined.KeyboardArrowRight
import androidx.compose.material.icons.outlined.ReceiptLong
import androidx.compose.material.icons.outlined.SearchOff
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.material3.pulltorefresh.PullToRefreshBox
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import com.esimko.mobile.domain.model.Transaction
import com.esimko.mobile.ui.common.EmptyStateView
import com.esimko.mobile.ui.common.ErrorView
import com.esimko.mobile.ui.common.EsimkoPreview
import com.esimko.mobile.ui.common.IconBadge
import com.esimko.mobile.ui.common.LightDarkPreview
import com.esimko.mobile.ui.common.PremiumCard
import com.esimko.mobile.ui.common.SkeletonListRows
import com.esimko.mobile.ui.common.StaleBanner
import com.esimko.mobile.ui.common.StatusChip
import com.esimko.mobile.ui.theme.MoneyRow
import com.esimko.mobile.util.ActivityGrouping
import com.esimko.mobile.util.ActivityRow

@OptIn(ExperimentalMaterial3Api::class, ExperimentalFoundationApi::class)
@Composable
fun ActivityTab(
    initialFilter: String? = null,
    onOpenDetail: (Long, String) -> Unit = { _, _ -> },
    viewModel: ActivityViewModel = hiltViewModel()
) {
    val state by viewModel.state.collectAsState()
    LaunchedEffect(Unit) { viewModel.start(initialFilter) }

    // Inset atas sudah dipegang HomeScreen — jangan tambah statusBarsPadding lagi.
    Column(Modifier.fillMaxSize().background(MaterialTheme.colorScheme.background)) {
        ActivityHeader()
        ActivityFilters(state, viewModel::selectSegment, viewModel::selectStatus, viewModel::onSearchChange, viewModel::toggleSort)
        Spacer(Modifier.height(8.dp))

        val error = state.error
        when {
            state.isFirstLoad -> SkeletonListRows(count = 6, modifier = Modifier.padding(horizontal = 16.dp))
            state.rows.isEmpty() && error == null -> {
                val filtered = state.search.isNotBlank() || state.selectedStatusId != null
                EmptyStateView(
                    message = if (filtered)
                        "Tidak ada transaksi yang cocok. Coba kata kunci atau status lain."
                    else "Belum ada aktivitas ${state.segment.label.lowercase()}.",
                    icon = if (filtered) Icons.Outlined.SearchOff else Icons.Outlined.ReceiptLong
                )
            }
            state.rows.isEmpty() && error != null -> ErrorView(
                message = error,
                onRetry = viewModel::retry
            )
            else -> {
                if (error != null) {
                    StaleBanner(onRetry = viewModel::retry, modifier = Modifier.padding(horizontal = 16.dp))
                }
                ActivityList(
                    rows = state.rows,
                    hasMore = state.hasMore,
                    page = state.page,
                    isRefreshing = state.isRefreshing,
                    onOpenDetail = onOpenDetail,
                    onRefresh = viewModel::refresh,
                    onLoadMore = viewModel::loadMore,
                    modifier = Modifier.weight(1f)
                )
            }
        }
    }
}

@Composable
private fun ActivityHeader() {
    Column(Modifier.fillMaxWidth().padding(start = 20.dp, end = 20.dp, top = 20.dp, bottom = 12.dp)) {
        Text(
            text = "Aktivitas",
            style = MaterialTheme.typography.headlineMedium,
            fontWeight = FontWeight.Bold
        )
        Text(
            text = "Riwayat transaksi koperasi Anda",
            style = MaterialTheme.typography.bodyMedium,
            color = MaterialTheme.colorScheme.onSurfaceVariant
        )
    }
}

@OptIn(ExperimentalMaterial3Api::class, ExperimentalFoundationApi::class)
@Composable
private fun ActivityList(
    rows: List<ActivityRow>,
    hasMore: Boolean,
    page: Int,
    isRefreshing: Boolean,
    onOpenDetail: (Long, String) -> Unit,
    onRefresh: () -> Unit,
    onLoadMore: () -> Unit,
    modifier: Modifier = Modifier
) {
    val listState = rememberLazyListState()
    PullToRefreshBox(
        isRefreshing = isRefreshing,
        onRefresh = onRefresh,
        modifier = modifier.fillMaxSize()
    ) {
        LazyColumn(
            state = listState,
            modifier = Modifier.fillMaxSize(),
            contentPadding = PaddingValues(bottom = 24.dp)
        ) {
            rows.forEach { row ->
                when (row) {
                    is ActivityRow.SectionHeaderRow -> item(key = row.key) {
                        GroupTitle(row.title)
                    }
                    is ActivityRow.MonthHeaderRow -> stickyHeader(key = row.key) {
                        MonthTitle(row.title)
                    }
                    is ActivityRow.ItemRow -> item(key = row.key) {
                        ActivityCard(
                            trx = row.transaction,
                            onClick = { onOpenDetail(row.transaction.id, row.transaction.modul) },
                            modifier = Modifier.padding(horizontal = 16.dp, vertical = 5.dp)
                        )
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
                        CircularProgressIndicator(Modifier.size(24.dp), strokeWidth = 2.dp)
                    }
                }
            }
        }
    }
}

@Composable
private fun GroupTitle(title: String) {
    val running = title == "Berjalan"
    Row(
        Modifier.fillMaxWidth().padding(start = 20.dp, end = 20.dp, top = 12.dp, bottom = 6.dp),
        verticalAlignment = Alignment.CenterVertically
    ) {
        Box(
            Modifier
                .size(8.dp)
                .background(
                    if (running) MaterialTheme.colorScheme.tertiary else MaterialTheme.colorScheme.primary,
                    CircleShape
                )
        )
        Spacer(Modifier.width(8.dp))
        Text(
            text = if (running) "Sedang Berjalan" else "Selesai",
            style = MaterialTheme.typography.titleSmall,
            fontWeight = FontWeight.Bold
        )
    }
}

@Composable
private fun MonthTitle(title: String) {
    Text(
        text = title.uppercase(),
        style = MaterialTheme.typography.labelMedium,
        fontWeight = FontWeight.SemiBold,
        color = MaterialTheme.colorScheme.onSurfaceVariant,
        modifier = Modifier
            .fillMaxWidth()
            .background(MaterialTheme.colorScheme.background)
            .padding(start = 20.dp, end = 20.dp, top = 10.dp, bottom = 6.dp)
    )
}

@Composable
internal fun ActivityCard(
    trx: Transaction,
    onClick: () -> Unit,
    modifier: Modifier = Modifier
) {
    val kind = ActivityKinds.of(trx.jenis, trx.modul)
    val accent = kind.accent()
    val subtitle = listOfNotNull(
        trx.monthOverride ?: ActivityGrouping.dayLabel(trx.tanggal).takeIf { it != "-" },
        trx.subtitleOverride ?: trx.keterangan?.takeIf { it.isNotBlank() }
    ).joinToString(" · ").takeIf { it.isNotEmpty() }

    PremiumCard(modifier = modifier, shape = RoundedCornerShape(18.dp)) {
        BoxWithConstraints {
            val stacked = maxWidth < 380.dp || LocalDensity.current.fontScale > 1.15f
            Row(
                Modifier
                    .fillMaxWidth()
                    .clickable(onClick = onClick)
                    .padding(horizontal = 14.dp, vertical = 14.dp),
                verticalAlignment = Alignment.CenterVertically
            ) {
                IconBadge(icon = kind.icon(), tint = accent)
                Spacer(Modifier.width(12.dp))
                Column(Modifier.weight(1f), verticalArrangement = Arrangement.spacedBy(2.dp)) {
                    Text(
                        text = trx.jenis,
                        style = MaterialTheme.typography.titleSmall,
                        fontWeight = FontWeight.SemiBold,
                        maxLines = if (stacked) 3 else 1,
                        overflow = TextOverflow.Ellipsis
                    )
                    if (subtitle != null) {
                        Text(
                            text = subtitle,
                            style = MaterialTheme.typography.bodySmall,
                            color = MaterialTheme.colorScheme.onSurfaceVariant,
                            maxLines = 1,
                            overflow = TextOverflow.Ellipsis
                        )
                    }
                    if (stacked) {
                        Spacer(Modifier.height(6.dp))
                        ActivityAmount(trx)
                    }
                    if (trx.status.isNotBlank()) {
                        Spacer(Modifier.height(4.dp))
                        StatusChip(status = trx.status, color = trx.color)
                    }
                }
                Spacer(Modifier.width(8.dp))
                Column(horizontalAlignment = Alignment.End) {
                    if (!stacked) ActivityAmount(trx)
                    Spacer(Modifier.height(6.dp))
                    Icon(
                        Icons.AutoMirrored.Outlined.KeyboardArrowRight,
                        contentDescription = null,
                        tint = MaterialTheme.colorScheme.onSurfaceVariant.copy(alpha = 0.6f),
                        modifier = Modifier.size(20.dp)
                    )
                }
            }
        }
    }
}

@Composable
private fun ActivityAmount(trx: Transaction) {
    val amountColor = if (!trx.isDebit && trx.nominalTampil?.trimStart()?.startsWith('+') == true)
        MaterialTheme.colorScheme.primary else MaterialTheme.colorScheme.onSurface
    if (trx.nominalTampil != null) {
        Text(trx.nominalTampil, style = MoneyRow, color = amountColor)
    } else {
        // Keep the backend sign: an outgoing transaction is not a positive credit.
        Text(com.esimko.mobile.util.MoneyFormatter.format(trx.nominal), style = MoneyRow, color = amountColor)
    }
}

@LightDarkPreview
@Composable
private fun ActivityTabPreview() = EsimkoPreview {
    ActivityList(
        rows = ActivityGrouping.build(
            listOf(
                Transaction(
                    id = 1, jenis = "Simpanan Sukarela", modul = "transaksi", nominal = 150_000,
                    tanggal = "2026-08-05", status = "Belum Verifikasi",
                    statusLabel = "Belum Verifikasi", keterangan = null, color = "#e67e22",
                    nominalTampil = "+Rp 150.000"
                ),
                Transaction(
                    id = 2, jenis = "Belanja Toko", modul = "toko", nominal = 145_000,
                    tanggal = "2026-07-28", status = "Selesai", statusLabel = "Selesai",
                    keterangan = "TRX-00281", color = "#27ae60", isDebit = true,
                    subtitleOverride = "3 barang"
                )
            )
        ),
        hasMore = false,
        page = 1,
        isRefreshing = false,
        onOpenDetail = { _, _ -> },
        onRefresh = {},
        onLoadMore = {}
    )
}
