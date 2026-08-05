package com.esimko.mobile.ui.news

import androidx.compose.foundation.Image
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import coil.compose.rememberAsyncImagePainter
import coil.request.ImageRequest
import com.esimko.mobile.ui.common.ErrorView
import com.esimko.mobile.ui.common.LoadingOverlay

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun NewsDetailScreen(
    newsId: Long,
    onBack: () -> Unit,
    viewModel: NewsViewModel = hiltViewModel()
) {
    val state by viewModel.state.collectAsState()
    val context = LocalContext.current

    LaunchedEffect(newsId) {
        if (state.detail?.id != newsId) {
            viewModel.loadDetail(newsId)
        }
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Detail Berita") },
                navigationIcon = {
                    IconButton(onClick = onBack) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Kembali")
                    }
                }
            )
        }
    ) { padding ->
        Box(modifier = Modifier.fillMaxSize().padding(padding)) {
            if (state.detailLoading) {
                LoadingOverlay(isLoading = true)
            }
            state.detailError?.let { error ->
                ErrorView(message = error, onRetry = { viewModel.loadDetail(newsId) })
            }
            state.detail?.let { detail ->
                Column(
                    modifier = Modifier
                        .fillMaxSize()
                        .verticalScroll(rememberScrollState())
                        .padding(16.dp)
                ) {
                    Text(
                        text = detail.judul,
                        style = MaterialTheme.typography.headlineSmall,
                        fontWeight = FontWeight.Bold
                    )
                    Spacer(modifier = Modifier.height(8.dp))
                    Text(
                        text = detail.tanggal,
                        style = MaterialTheme.typography.bodySmall,
                        color = MaterialTheme.colorScheme.outline
                    )
                    detail.gambar?.let { url ->
                        Spacer(modifier = Modifier.height(12.dp))
                        Image(
                            painter = rememberAsyncImagePainter(
                                model = ImageRequest.Builder(context)
                                    .data(url)
                                    .crossfade(true)
                                    .build()
                            ),
                            contentDescription = detail.judul,
                            modifier = Modifier
                                .fillMaxWidth()
                                .height(200.dp),
                            contentScale = ContentScale.Crop
                        )
                    }
                    Spacer(modifier = Modifier.height(16.dp))
                    Text(
                        text = detail.konten,
                        style = MaterialTheme.typography.bodyLarge
                    )
                }
            }
        }
    }
}
