package com.esimko.mobile.ui.shopping

import androidx.compose.foundation.Image
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import coil.compose.rememberAsyncImagePainter
import coil.request.ImageRequest
import com.esimko.mobile.ui.common.EsimkoButton
import com.esimko.mobile.ui.common.ErrorView
import com.esimko.mobile.ui.common.LoadingOverlay
import com.esimko.mobile.util.AmountFormatter

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ShoppingDetailScreen(
    productKode: String,
    onBack: () -> Unit,
    onGoToCart: () -> Unit,
    viewModel: ShoppingViewModel = hiltViewModel()
) {
    val state by viewModel.state.collectAsState()
    var qty by remember { mutableStateOf(1) }
    val context = LocalContext.current

    LaunchedEffect(productKode) {
        if (state.selectedProduct?.kode != productKode) {
            viewModel.loadProductDetail(productKode)
        }
    }

    LaunchedEffect(state.addSuccess) {
        if (state.addSuccess) {
            viewModel.resetAddSuccess()
            onGoToCart()
        }
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Detail Produk") },
                navigationIcon = {
                    IconButton(onClick = onBack) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Kembali")
                    }
                }
            )
        }
    ) { padding ->
        Box(modifier = Modifier.fillMaxSize().padding(padding)) {
            if (state.isLoading) {
                LoadingOverlay(isLoading = true)
            }
            state.error?.let { error ->
                ErrorView(message = error, onRetry = { viewModel.loadProductDetail(productKode) })
            }
            state.selectedProduct?.let { product ->
                Column(
                    modifier = Modifier
                        .fillMaxSize()
                        .verticalScroll(rememberScrollState())
                        .padding(16.dp)
                ) {
                    product.gambar?.let { url ->
                        Image(
                            painter = rememberAsyncImagePainter(
                                model = ImageRequest.Builder(context).data(url).crossfade(true).build()
                            ),
                            contentDescription = product.nama,
                            modifier = Modifier.fillMaxWidth().height(200.dp),
                            contentScale = ContentScale.Crop
                        )
                        Spacer(modifier = Modifier.height(16.dp))
                    }
                    Text(
                        text = product.nama,
                        style = MaterialTheme.typography.headlineSmall,
                        fontWeight = FontWeight.Bold
                    )
                    Spacer(modifier = Modifier.height(8.dp))
                    Text(
                        text = AmountFormatter.format(product.harga),
                        style = MaterialTheme.typography.titleMedium,
                        color = MaterialTheme.colorScheme.primary
                    )
                    Spacer(modifier = Modifier.height(8.dp))
                    Text(
                        text = "Stok tersedia: ${product.sisa}",
                        style = MaterialTheme.typography.bodySmall,
                        color = MaterialTheme.colorScheme.onSurfaceVariant
                    )
                    product.deskripsi?.let {
                        Spacer(modifier = Modifier.height(12.dp))
                        Text(
                            text = it,
                            style = MaterialTheme.typography.bodyMedium
                        )
                    }

                    state.actionError?.let { err ->
                        Spacer(modifier = Modifier.height(8.dp))
                        Text(
                            text = err,
                            color = MaterialTheme.colorScheme.error,
                            style = MaterialTheme.typography.bodySmall
                        )
                    }

                    Spacer(modifier = Modifier.height(24.dp))

                    Row(
                        modifier = Modifier.fillMaxWidth(),
                        verticalAlignment = Alignment.CenterVertically,
                        horizontalArrangement = Arrangement.spacedBy(16.dp)
                    ) {
                        OutlinedButton(
                            onClick = { if (qty > 1) qty-- },
                            enabled = qty > 1,
                            modifier = Modifier.size(48.dp)
                        ) {
                            Text("-")
                        }
                        Text(
                            text = "$qty",
                            style = MaterialTheme.typography.titleMedium,
                            modifier = Modifier.widthIn(min = 32.dp),
                            textAlign = TextAlign.Center
                        )
                        OutlinedButton(
                            onClick = { qty++ },
                            enabled = qty < (product.sisa.takeIf { it > 0 } ?: 1),
                            modifier = Modifier.size(48.dp)
                        ) {
                            Text("+")
                        }
                        Spacer(modifier = Modifier.width(8.dp))
                        EsimkoButton(
                            text = "Tambah ke Keranjang",
                            onClick = {
                                viewModel.addToCart(product.id, qty)
                            },
                            modifier = Modifier.weight(1f)
                        )
                    }
                }
            }
        }
    }
}
