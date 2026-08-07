package com.esimko.mobile.ui.home

import androidx.compose.animation.AnimatedVisibility
import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
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
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.outlined.KeyboardArrowRight
import androidx.compose.material.icons.outlined.AccountBalance
import androidx.compose.material.icons.outlined.AssignmentReturn
import androidx.compose.material.icons.outlined.Campaign
import androidx.compose.material.icons.outlined.EventRepeat
import androidx.compose.material.icons.outlined.ExpandLess
import androidx.compose.material.icons.outlined.ExpandMore
import androidx.compose.material.icons.outlined.HourglassTop
import androidx.compose.material.icons.outlined.MoveDown
import androidx.compose.material.icons.outlined.ReceiptLong
import androidx.compose.material.icons.outlined.RequestQuote
import androidx.compose.material.icons.outlined.Savings
import androidx.compose.material3.Divider
import androidx.compose.material3.HorizontalDivider
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.res.painterResource
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.compose.ui.semantics.contentDescription
import androidx.compose.ui.semantics.semantics
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import coil.compose.AsyncImage
import coil.request.ImageRequest
import com.esimko.mobile.R
import com.esimko.mobile.domain.model.News
import com.esimko.mobile.domain.model.Profile
import com.esimko.mobile.ui.common.ActionGrid
import com.esimko.mobile.ui.common.ActionItem
import com.esimko.mobile.ui.common.EmptyStateView
import com.esimko.mobile.ui.common.ErrorView
import com.esimko.mobile.ui.common.HeroSurface
import com.esimko.mobile.ui.common.ListRow
import com.esimko.mobile.ui.common.Money
import com.esimko.mobile.ui.common.RowDivider
import com.esimko.mobile.ui.common.SectionHeader
import com.esimko.mobile.ui.common.SkeletonHero
import com.esimko.mobile.ui.common.SkeletonListRows
import com.esimko.mobile.ui.common.StaleBanner
import com.esimko.mobile.ui.common.EsimkoPreview
import com.esimko.mobile.ui.common.LightDarkPreview
import com.esimko.mobile.ui.dashboard.DashboardViewModel
import com.esimko.mobile.ui.news.NewsViewModel
import com.esimko.mobile.ui.theme.GoldOnHero
import com.esimko.mobile.ui.theme.HeroDivider
import com.esimko.mobile.ui.theme.MoneyHero
import com.esimko.mobile.ui.theme.MoneyRow
import com.esimko.mobile.ui.theme.MoneySmall
import com.esimko.mobile.ui.theme.OnHero
import com.esimko.mobile.util.HtmlToText
import com.esimko.mobile.util.MoneyFormatter

@Composable
fun DashboardTab(
    onNavigate: (String) -> Unit = {},
    onOpenNewsDetail: (Long) -> Unit = {},
    viewModel: DashboardViewModel = hiltViewModel(),
    newsViewModel: NewsViewModel = hiltViewModel()
) {
    val state by viewModel.state.collectAsStateWithLifecycle()
    val newsState by newsViewModel.state.collectAsStateWithLifecycle()
    val profile = state.profile
    var expanded by remember { mutableStateOf(false) }
    val actions = remember(onNavigate) {
        listOf(
            ActionItem("Setor", Icons.Outlined.Savings, onClick = { onNavigate("savings_form/setoran") }),
            ActionItem("Tarik", Icons.Outlined.MoveDown, onClick = { onNavigate("savings_form/penarikan") }),
            ActionItem("Pinjaman", Icons.Outlined.RequestQuote, onClick = { onNavigate("installment/apply") }),
            ActionItem("Angsuran", Icons.Outlined.EventRepeat, onClick = { onNavigate("installment") }),
            ActionItem("Angsuran Belanja", Icons.Outlined.ReceiptLong, onClick = { onNavigate("aktivitas?filter=angsuran_belanja") }),
            ActionItem("Retur Barang", Icons.Outlined.AssignmentReturn, onClick = { onNavigate("aktivitas?filter=retur") }),
            ActionItem("Simpanan", Icons.Outlined.AccountBalance, onClick = { onNavigate("savings") }),
            ActionItem("Berita", Icons.Outlined.Campaign, onClick = { onNavigate("news") })
        )
    }

    when {
        profile == null && state.isLoading -> SkeletonHero(Modifier.fillMaxWidth())
        profile == null && state.error != null -> ErrorView(state.error!!, onRetry = viewModel::load)
        else -> LazyColumn(modifier = Modifier.fillMaxSize()) {
            item {
                DashboardHero(
                    profile = profile,
                    expanded = expanded,
                    onToggleExpand = { expanded = !expanded },
                    onOpenAccount = { onNavigate("akun") },
                    onOpenInstallments = { onNavigate("installment") }
                )
            }
            if (state.error != null && profile != null) {
                item {
                    StaleBanner(
                        onRetry = viewModel::load,
                        modifier = Modifier.padding(horizontal = 16.dp, vertical = 8.dp)
                    )
                }
            }
            item {
                Spacer(Modifier.height(16.dp))
                ActionGrid(items = actions, modifier = Modifier.padding(horizontal = 8.dp))
            }
            if (state.pendingCount > 0) {
                item { PendingStrip(count = state.pendingCount, onClick = { onNavigate("aktivitas") }) }
            }
            item {
                Spacer(Modifier.height(24.dp))
                SectionHeader(
                    title = "Informasi",
                    actionLabel = "Lihat semua",
                    onAction = { onNavigate("news") },
                    modifier = Modifier.padding(horizontal = 16.dp)
                )
            }
            val two = newsState.news.items.take(2)
            when {
                newsState.isLoading && two.isEmpty() -> item {
                    SkeletonListRows(count = 2, modifier = Modifier.padding(horizontal = 16.dp))
                }
                two.isEmpty() -> item {
                    EmptyStateView(
                        message = "Belum ada informasi.",
                        modifier = Modifier.padding(16.dp)
                    )
                }
                else -> items(two, key = { it.id }) { news ->
                    ListRow(
                        title = news.judul,
                        subtitle = HtmlToText.strip(news.ringkasan).take(90),
                        leading = { NewsThumb(news.gambar) },
                        onClick = { onOpenNewsDetail(news.id) },
                        modifier = Modifier.padding(horizontal = 16.dp)
                    )
                    RowDivider(Modifier.padding(horizontal = 16.dp))
                }
            }
            item { Spacer(Modifier.height(24.dp)) }
        }
    }
}

@Composable
private fun DashboardHero(
    profile: Profile?,
    expanded: Boolean,
    onToggleExpand: () -> Unit,
    onOpenAccount: () -> Unit,
    onOpenInstallments: () -> Unit
) {
    HeroSurface {
        Row(Modifier.fillMaxWidth(), verticalAlignment = Alignment.CenterVertically) {
            Image(
                painter = painterResource(R.drawable.logo_esimko_wordmark_light),
                contentDescription = "esimko",
                modifier = Modifier.height(20.dp)
            )
            Spacer(Modifier.weight(1f))
            IconButton(
                onClick = onOpenAccount,
                modifier = Modifier.size(48.dp)
            ) {
                val avatar = profile?.avatar
                if (!avatar.isNullOrBlank()) {
                    AsyncImage(
                        model = avatar,
                        contentDescription = "Akun saya",
                        modifier = Modifier.size(36.dp).clip(CircleShape),
                        contentScale = ContentScale.Crop
                    )
                } else {
                    Box(
                        modifier = Modifier
                            .size(36.dp)
                            .clip(CircleShape)
                            .background(OnHero.copy(alpha = 0.15f)),
                        contentAlignment = Alignment.Center
                    ) {
                        Text(
                            text = profile?.nama?.take(1)?.uppercase() ?: "?",
                            style = MaterialTheme.typography.labelLarge,
                            color = OnHero
                        )
                    }
                }
            }
        }
        Spacer(Modifier.height(24.dp))
        Text(
            "Saldo Simpanan",
            style = MaterialTheme.typography.labelMedium,
            color = OnHero.copy(alpha = 0.85f)
        )
        Money(
            amount = profile?.saldoSimpanan ?: 0L,
            style = MoneyHero,
            color = GoldOnHero,
            modifier = Modifier.semantics {
                contentDescription = "Saldo simpanan, " + MoneyFormatter.format(profile?.saldoSimpanan ?: 0L)
            }
        )
        Spacer(Modifier.height(8.dp))
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .heightIn(min = 48.dp)
                .clickable(onClick = onToggleExpand),
            verticalAlignment = Alignment.CenterVertically
        ) {
            Text(
                if (expanded) "Sembunyikan rincian" else "Lihat rincian",
                style = MaterialTheme.typography.labelMedium,
                color = OnHero
            )
            Icon(
                imageVector = if (expanded) Icons.Outlined.ExpandLess else Icons.Outlined.ExpandMore,
                contentDescription = null,
                tint = OnHero
            )
        }
        AnimatedVisibility(visible = expanded) {
            Column {
                Divider(color = HeroDivider, modifier = Modifier.padding(vertical = 8.dp))
                DetailRow("Pokok", profile?.saldoSimpananPokok ?: 0L)
                DetailRow("Wajib", profile?.saldoSimpananWajib ?: 0L)
                DetailRow("Sukarela", profile?.saldoSimpananSukarela ?: 0L)
                DetailRow("Hari Raya", profile?.saldoSimpananHariRaya ?: 0L)
            }
        }
        Spacer(Modifier.height(16.dp))
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .background(OnHero.copy(alpha = 0.08f))
                .clip(MaterialTheme.shapes.medium)
                .clickable(onClick = onOpenInstallments)
                .padding(12.dp),
            verticalAlignment = Alignment.CenterVertically
        ) {
            Column(Modifier.weight(1f)) {
                Text("Saldo Pinjaman", style = MaterialTheme.typography.labelMedium, color = OnHero.copy(alpha = 0.85f))
                Money(amount = profile?.saldoPinjaman ?: 0L, style = MoneyRow, color = OnHero)
            }
            Column(horizontalAlignment = Alignment.End) {
                Text("Angsuran bulan ini", style = MaterialTheme.typography.labelMedium, color = OnHero.copy(alpha = 0.85f))
                Money(amount = profile?.angsuranBulan ?: 0L, style = MoneyRow, color = OnHero)
            }
        }
    }
}

@Composable
private fun DetailRow(label: String, amount: Long) {
    Row(
        modifier = Modifier.fillMaxWidth().padding(vertical = 4.dp),
        horizontalArrangement = Arrangement.SpaceBetween
    ) {
        Text(label, style = MaterialTheme.typography.bodySmall, color = OnHero.copy(alpha = 0.85f))
        Money(amount = amount, style = MoneySmall, color = OnHero)
    }
}

@Composable
private fun PendingStrip(count: Int, onClick: () -> Unit) {
    Row(
        modifier = Modifier
            .fillMaxWidth()
            .padding(horizontal = 16.dp, vertical = 8.dp)
            .clip(MaterialTheme.shapes.medium)
            .background(MaterialTheme.colorScheme.secondaryContainer)
            .clickable(onClick = onClick)
            .padding(horizontal = 16.dp)
            .heightIn(min = 48.dp),
        verticalAlignment = Alignment.CenterVertically
    ) {
        Icon(
            Icons.Outlined.HourglassTop,
            contentDescription = null,
            tint = MaterialTheme.colorScheme.onSecondaryContainer,
            modifier = Modifier.size(20.dp)
        )
        Spacer(Modifier.width(8.dp))
        Text(
            "$count pengajuan menunggu verifikasi",
            style = MaterialTheme.typography.bodyMedium,
            color = MaterialTheme.colorScheme.onSecondaryContainer,
            modifier = Modifier.weight(1f)
        )
        Icon(
            Icons.AutoMirrored.Outlined.KeyboardArrowRight,
            contentDescription = null,
            tint = MaterialTheme.colorScheme.onSecondaryContainer
        )
    }
}

@Composable
private fun NewsThumb(gambar: String?) {
    if (!gambar.isNullOrBlank()) {
        AsyncImage(
            model = ImageRequest.Builder(LocalContext.current).data(gambar).crossfade(true).build(),
            contentDescription = null,
            contentScale = ContentScale.Crop,
            modifier = Modifier.size(56.dp).clip(MaterialTheme.shapes.small)
        )
    } else {
        Box(
            modifier = Modifier
                .size(56.dp)
                .clip(MaterialTheme.shapes.small)
                .background(MaterialTheme.colorScheme.surfaceVariant),
            contentAlignment = Alignment.Center
        ) {
            Icon(
                Icons.Outlined.Campaign,
                contentDescription = null,
                tint = MaterialTheme.colorScheme.onSurfaceVariant
            )
        }
    }
}

@LightDarkPreview
@Composable
private fun DashboardHeroPreview() {
    EsimkoPreview {
        Column {
            DashboardHero(
                profile = Profile(
                    noAnggota = "A-00123", nama = "Siti Rahmawati", ktp = "", alamat = "",
                    telepon = "", email = null, avatar = null,
                    saldoSimpanan = 4_250_000, saldoPinjaman = 12_000_000, angsuranBulan = 850_000,
                    saldoSimpananPokok = 500_000, saldoSimpananWajib = 2_400_000,
                    saldoSimpananSukarela = 1_100_000, saldoSimpananHariRaya = 250_000
                ),
                expanded = true,
                onToggleExpand = {}, onOpenAccount = {}, onOpenInstallments = {}
            )
        }
    }
}

@LightDarkPreview
@Composable
private fun PendingStripPreview() {
    EsimkoPreview {
        PendingStrip(count = 3, onClick = {})
    }
}
