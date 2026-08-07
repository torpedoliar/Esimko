package com.esimko.mobile.ui.news

import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.Article
import androidx.compose.material.icons.outlined.Close
import androidx.compose.material.icons.outlined.Search
import androidx.compose.material3.Badge
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.material3.TopAppBar
import androidx.compose.runtime.Composable
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.semantics.contentDescription
import androidx.compose.ui.semantics.semantics
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import com.esimko.mobile.domain.model.News
import com.esimko.mobile.ui.common.EmptyStateView
import com.esimko.mobile.ui.common.ErrorView
import com.esimko.mobile.ui.common.EsimkoPreview
import com.esimko.mobile.ui.common.LightDarkPreview
import com.esimko.mobile.ui.common.ListRow
import com.esimko.mobile.ui.common.RowDivider
import com.esimko.mobile.ui.common.SkeletonListRows
import com.esimko.mobile.ui.common.StaleBanner

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun NewsListScreen(
    onBack: () -> Unit,
    onNewsClick: (Long) -> Unit,
    viewModel: NewsViewModel = hiltViewModel()
) {
    val state by viewModel.state.collectAsState()

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Berita") },
                navigationIcon = {
                    IconButton(onClick = onBack) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Kembali")
                    }
                }
            )
        }
    ) { padding ->
        val items = state.news.items
        Column(Modifier.fillMaxSize().padding(padding)) {
            // Kepala pencarian menempel, di luar list.
            Surface(color = MaterialTheme.colorScheme.background, tonalElevation = 0.dp) {
                OutlinedTextField(
                    value = state.search,
                    onValueChange = viewModel::onSearchChange,
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(horizontal = 16.dp, vertical = 8.dp),
                    placeholder = { Text("Cari berita (judul)") },
                    leadingIcon = { Icon(Icons.Outlined.Search, contentDescription = null) },
                    trailingIcon = {
                        if (state.search.isNotEmpty()) {
                            IconButton(onClick = { viewModel.onSearchChange("") }) {
                                Icon(Icons.Outlined.Close, contentDescription = "Hapus pencarian")
                            }
                        }
                    },
                    singleLine = true,
                    shape = RoundedCornerShape(28.dp),
                    keyboardOptions = KeyboardOptions(imeAction = ImeAction.Search)
                )
            }
            Spacer(Modifier.height(8.dp))

            when {
                state.isLoading && items.isEmpty() -> SkeletonListRows(count = 5, modifier = Modifier.fillMaxSize())
                items.isEmpty() && state.error == null -> {
                    val pesan = if (state.search.isNotBlank())
                        "Tidak ada berita untuk \"${state.search}\"." else "Belum ada informasi."
                    val aksi = if (state.search.isNotBlank()) "Hapus pencarian" else null
                    val onAction = if (state.search.isNotBlank()) { { viewModel.onSearchChange("") } } else null
                    EmptyStateView(
                        message = pesan,
                        modifier = Modifier.fillMaxSize(),
                        icon = Icons.Default.Article,
                        actionLabel = aksi,
                        onAction = onAction
                    )
                }
                items.isEmpty() && state.error != null -> ErrorView(
                    message = state.error!!,
                    onRetry = viewModel::loadNews,
                    modifier = Modifier.fillMaxSize()
                )
                else -> Column(Modifier.fillMaxSize()) {
                    if (state.error != null) StaleBanner(onRetry = viewModel::loadNews)
                    NewsList(items = items, onNewsClick = onNewsClick, modifier = Modifier.fillMaxSize())
                }
            }
        }
    }
}

@Composable
private fun NewsList(
    items: List<News>,
    onNewsClick: (Long) -> Unit,
    modifier: Modifier = Modifier
) {
    LazyColumn(
        modifier = modifier,
        contentPadding = PaddingValues(vertical = 8.dp)
    ) {
        items(items, key = { it.id }) { news ->
            ListRow(
                title = news.judul,
                subtitle = news.tanggal,
                trailing = {
                    if (news.jumlahAttachment > 0) {
                        Badge(modifier = Modifier.semantics {
                            contentDescription = "${news.jumlahAttachment} lampiran"
                        }) {
                            Text("${news.jumlahAttachment}")
                        }
                    }
                },
                onClick = { onNewsClick(news.id) }
            )
            RowDivider()
        }
    }
}

@LightDarkPreview
@Composable
private fun NewsListPreview() {
    EsimkoPreview {
        NewsList(
            items = listOf(
                News(1, "Rapat Anggota Tahunan", null, null, "2026-08-06 09:00:00", 2),
                News(2, "Kenaikan Simpanan Sukarela", "Ringkasan singkat.", null, "2026-08-01 10:00:00", 0),
                News(3, "Libur Hari Raya", null, null, "2026-07-20 08:00:00", 0)
            ),
            onNewsClick = {}
        )
    }
}

@LightDarkPreview
@Composable
private fun NewsListEmptyPreview() {
    EsimkoPreview {
        EmptyStateView(
            message = "Belum ada informasi.",
            modifier = Modifier.fillMaxSize(),
            icon = Icons.Default.Article
        )
    }
}
