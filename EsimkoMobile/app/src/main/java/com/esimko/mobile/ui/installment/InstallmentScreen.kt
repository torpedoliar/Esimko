package com.esimko.mobile.ui.installment

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.itemsIndexed
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.outlined.EventRepeat
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.semantics.contentDescription
import androidx.compose.ui.semantics.semantics
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import com.esimko.mobile.domain.model.Installment
import com.esimko.mobile.domain.model.Salary
import com.esimko.mobile.ui.common.EmptyStateView
import com.esimko.mobile.ui.common.ErrorView
import com.esimko.mobile.ui.common.EsimkoPreview
import com.esimko.mobile.ui.common.HeroSurface
import com.esimko.mobile.ui.common.LightDarkPreview
import com.esimko.mobile.ui.common.ListRow
import com.esimko.mobile.ui.common.Money
import com.esimko.mobile.ui.common.RowDivider
import com.esimko.mobile.ui.common.SectionHeader
import com.esimko.mobile.ui.common.SkeletonHero
import com.esimko.mobile.ui.common.SkeletonListRows
import com.esimko.mobile.ui.common.StaleBanner
import com.esimko.mobile.ui.common.StatusChip
import com.esimko.mobile.ui.theme.GoldOnHero
import com.esimko.mobile.ui.theme.HeroDivider
import com.esimko.mobile.ui.theme.MoneyHero
import com.esimko.mobile.ui.theme.MoneyRow
import com.esimko.mobile.ui.theme.OnHero
import com.esimko.mobile.util.MoneyFormatter

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun InstallmentScreen(
    onBack: () -> Unit,
    onApplyLoan: () -> Unit = {},
    viewModel: InstallmentViewModel = hiltViewModel()
) {
    val state by viewModel.state.collectAsState()

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Angsuran Pinjaman") },
                navigationIcon = {
                    IconButton(onClick = onBack) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Kembali")
                    }
                }
            )
        }
    ) { padding ->
        Column(modifier = Modifier.fillMaxSize().padding(padding)) {
            InstallmentBody(
                state = state,
                onApplyLoan = onApplyLoan,
                onRetry = viewModel::load
            )
        }
    }
}

@Composable
private fun InstallmentBody(
    state: InstallmentState,
    onApplyLoan: () -> Unit = {},
    onRetry: () -> Unit = {}
) {
    when {
        !state.hasContent && state.isLoading -> Column(Modifier.fillMaxSize()) {
            SkeletonHero()
            SkeletonListRows(count = 6)
        }
        !state.hasContent && state.error != null -> ErrorView(
            message = state.error,
            onRetry = onRetry,
            modifier = Modifier.fillMaxSize()
        )
        !state.hasContent -> EmptyStateView(
            message = "Tidak ada angsuran berjalan.",
            icon = Icons.Outlined.EventRepeat,
            actionLabel = "Ajukan Pinjaman",
            onAction = onApplyLoan,
            modifier = Modifier.fillMaxSize()
        )
        else -> {
            val error = state.error
            LazyColumn(modifier = Modifier.fillMaxSize()) {
                if (error != null) {
                    item { StaleBanner(onRetry = onRetry) }
                }
                item {
                    val gaji = state.salary?.gajiPokok ?: 0L
                    HeroSurface(modifier = Modifier.fillMaxWidth()) {
                        Column(
                            modifier = Modifier.semantics {
                                contentDescription = "Gaji pokok " + MoneyFormatter.format(gaji)
                            }
                        ) {
                            Text(
                                text = "Gaji Pokok",
                                style = MaterialTheme.typography.labelMedium,
                                color = OnHero.copy(alpha = 0.85f)
                            )
                            Money(amount = gaji, style = MoneyHero, color = GoldOnHero)
                            if (state.totalBerjalan > 0L) {
                                HorizontalDivider(color = HeroDivider, modifier = Modifier.padding(vertical = 12.dp))
                                Text(
                                    text = "Angsuran berjalan",
                                    style = MaterialTheme.typography.labelMedium,
                                    color = OnHero.copy(alpha = 0.85f)
                                )
                                Money(amount = state.totalBerjalan, style = MoneyRow, color = OnHero)
                            }
                        }
                    }
                }
                item {
                    FilledTonalButton(
                        onClick = onApplyLoan,
                        modifier = Modifier
                            .fillMaxWidth()
                            .heightIn(min = 48.dp)
                            .padding(horizontal = 16.dp, vertical = 16.dp)
                    ) {
                        Text("Ajukan Pinjaman")
                    }
                }
                item { SectionHeader("Daftar Angsuran") }
                itemsIndexed(state.installments, key = { _, it -> it.id }) { index, inst ->
                    val pokokBunga = "Pokok ${MoneyFormatter.format(inst.pokok)} · Bunga ${MoneyFormatter.format(inst.bunga)}"
                    val subtitle = inst.namaBulan?.takeIf { it.isNotBlank() }?.let { "$it · $pokokBunga" } ?: pokokBunga
                    ListRow(
                        title = "Angsuran ke-${inst.ke}",
                        subtitle = subtitle,
                        trailing = {
                            Column(horizontalAlignment = Alignment.End) {
                                Money(amount = inst.pokok + inst.bunga, style = MoneyRow)
                                StatusChip(status = inst.status, color = null)
                            }
                        }
                    )
                    if (index < state.installments.lastIndex) RowDivider()
                }
                item { Spacer(Modifier.height(24.dp)) }
            }
        }
    }
}

@LightDarkPreview
@Composable
private fun InstallmentScreenPreview() {
    EsimkoPreview {
        InstallmentBody(
            state = InstallmentState(
                salary = Salary(gajiPokok = 4_250_000),
                installments = listOf(
                    Installment(1, 1, 400_000, 50_000, "Dibayar", namaBulan = "Januari 2026"),
                    Installment(2, 2, 400_000, 50_000, "Belum Verifikasi", namaBulan = "Februari 2026"),
                    Installment(3, 3, 400_000, 50_000, "Belum Bayar", namaBulan = "Maret 2026")
                )
            ),
            onApplyLoan = {},
            onRetry = {}
        )
    }
}

@LightDarkPreview
@Composable
private fun InstallmentEmptyPreview() {
    EsimkoPreview {
        InstallmentBody(state = InstallmentState(), onApplyLoan = {}, onRetry = {})
    }
}
