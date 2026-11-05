package com.esimko.mobile.ui.home

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Add
import androidx.compose.material.icons.filled.Remove
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import com.esimko.mobile.domain.model.Transaction
import com.esimko.mobile.ui.common.EmptyStateView
import com.esimko.mobile.ui.common.ErrorView
import com.esimko.mobile.ui.common.HeroSurface
import com.esimko.mobile.ui.common.ListRow
import com.esimko.mobile.ui.common.Money
import com.esimko.mobile.ui.common.RowDivider
import com.esimko.mobile.ui.common.SectionHeader
import com.esimko.mobile.ui.common.SkeletonListRows
import com.esimko.mobile.ui.common.StatusChip
import com.esimko.mobile.ui.savings.SavingsViewModel
import com.esimko.mobile.ui.theme.GoldOnHero
import com.esimko.mobile.ui.theme.MoneyHero
import com.esimko.mobile.ui.theme.MoneyRow
import com.esimko.mobile.ui.theme.OnHero

@Composable
fun SavingsTab(
    onOpenHistory: (Long, String) -> Unit = { _, _ -> },
    onOpenForm: (String) -> Unit = {},
    viewModel: SavingsViewModel = hiltViewModel()
) {
    val state by viewModel.state.collectAsState()

    when {
        state.isLoading && state.profile == null -> SkeletonListRows(count = 5, modifier = Modifier.fillMaxSize())
        state.profile == null && state.error != null -> ErrorView(
            message = state.error!!,
            onRetry = viewModel::load,
            modifier = Modifier.fillMaxSize()
        )
        else -> LazyColumn(
            modifier = Modifier.fillMaxSize(),
            contentPadding = PaddingValues(bottom = 24.dp)
        ) {
            if (state.error != null) {
                item {
                    com.esimko.mobile.ui.common.StaleBanner(
                        onRetry = viewModel::load,
                        modifier = Modifier.padding(horizontal = 16.dp, vertical = 8.dp)
                    )
                }
            }

            item {
                HeroSurface(modifier = Modifier.fillMaxWidth()) {
                    Text(
                        text = "Saldo Simpanan",
                        style = MaterialTheme.typography.labelMedium,
                        color = OnHero.copy(alpha = 0.85f)
                    )
                    Money(amount = state.profile?.saldoSimpanan ?: 0L, style = MoneyHero, color = GoldOnHero)
                    Row(
                        modifier = Modifier.fillMaxWidth().padding(top = 8.dp),
                        horizontalArrangement = Arrangement.spacedBy(16.dp)
                    ) {
                        Text(
                            "Pokok: " + com.esimko.mobile.util.MoneyFormatter.format(state.profile?.saldoSimpananPokok ?: 0L),
                            style = MaterialTheme.typography.bodySmall,
                            color = OnHero.copy(alpha = 0.85f)
                        )
                        Text(
                            "Wajib: " + com.esimko.mobile.util.MoneyFormatter.format(state.profile?.saldoSimpananWajib ?: 0L),
                            style = MaterialTheme.typography.bodySmall,
                            color = OnHero.copy(alpha = 0.85f)
                        )
                    }
                }
            }

            item {
                Row(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(horizontal = 16.dp, vertical = 12.dp),
                    horizontalArrangement = Arrangement.spacedBy(12.dp)
                ) {
                    FilledTonalButton(
                        onClick = { onOpenForm("setoran") },
                        modifier = Modifier.weight(1f).height(48.dp)
                    ) {
                        Icon(Icons.Default.Add, contentDescription = null, modifier = Modifier.size(18.dp))
                        Spacer(Modifier.width(6.dp))
                        Text("Setoran Hari Raya")
                    }
                    FilledTonalButton(
                        onClick = { onOpenForm("penarikan") },
                        modifier = Modifier.weight(1f).height(48.dp)
                    ) {
                        Icon(Icons.Default.Remove, contentDescription = null, modifier = Modifier.size(18.dp))
                        Spacer(Modifier.width(6.dp))
                        Text("Penarikan Sukarela")
                    }
                }
            }

            item { SectionHeader("Riwayat Simpanan", modifier = Modifier.padding(horizontal = 16.dp)) }

            if (state.transactions.isEmpty()) {
                item {
                    EmptyStateView(
                        message = "Belum ada transaksi simpanan.",
                        modifier = Modifier.fillMaxWidth().padding(vertical = 24.dp)
                    )
                }
            } else {
                items(state.transactions, key = { it.id }) { transaction ->
                    SavingsListRow(transaction, onOpenHistory)
                    RowDivider(Modifier.padding(horizontal = 16.dp))
                }
            }
        }
    }
}

@Composable
private fun SavingsListRow(
    transaction: Transaction,
    onOpenHistory: (Long, String) -> Unit
) {
    ListRow(
        title = transaction.jenis,
        subtitle = transaction.keterangan?.takeIf { it.isNotBlank() } ?: transaction.tanggal,
        trailing = {
            Column(horizontalAlignment = Alignment.End) {
                Money(amount = transaction.nominal, showSign = true, style = MoneyRow)
                if (transaction.status.isNotBlank()) {
                    StatusChip(status = transaction.status, color = transaction.color)
                }
            }
        },
        onClick = { onOpenHistory(transaction.id, "transaksi") },
        modifier = Modifier.padding(horizontal = 16.dp)
    )
}