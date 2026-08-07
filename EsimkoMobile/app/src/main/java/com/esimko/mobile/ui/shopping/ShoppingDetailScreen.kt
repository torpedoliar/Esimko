package com.esimko.mobile.ui.shopping

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.aspectRatio
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.heightIn
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.widthIn
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.Add
import androidx.compose.material.icons.filled.Remove
import androidx.compose.material3.Button
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.FilledIconButton
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedIconButton
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.material3.TopAppBar
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import coil.compose.AsyncImage
import com.esimko.mobile.domain.model.ProductDetail
import com.esimko.mobile.ui.common.EsimkoPreview
import com.esimko.mobile.ui.common.ErrorView
import com.esimko.mobile.ui.common.LightDarkPreview
import com.esimko.mobile.ui.common.Money
import com.esimko.mobile.ui.common.SkeletonBox
import com.esimko.mobile.ui.theme.MoneyRow
import com.esimko.mobile.util.MoneyFormatter

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
        val error = state.detailError
        val product = state.selectedProduct
        Box(Modifier.fillMaxSize().padding(padding)) {
            when {
                product != null -> DetailContent(
                    product = product,
                    qty = qty,
                    onQtyChange = { qty = it },
                    isAdding = state.isAdding,
                    actionError = state.actionError,
                    onAddToCart = { viewModel.addToCart(product.id, qty) },
                    modifier = Modifier.fillMaxSize()
                )
                error != null -> ErrorView(
                    message = error,
                    onRetry = { viewModel.loadProductDetail(productKode) },
                    modifier = Modifier.fillMaxSize()
                )
                else -> DetailSkeleton(Modifier.fillMaxSize())
            }
        }
    }
}

@Composable
private fun DetailContent(
    product: ProductDetail,
    qty: Int,
    onQtyChange: (Int) -> Unit,
    isAdding: Boolean,
    actionError: String?,
    onAddToCart: () -> Unit,
    modifier: Modifier = Modifier
) {
    val habis = product.sisa <= 0
    val maxQty = if (habis) 0 else product.sisa.coerceAtLeast(1)

    Column(
        modifier = modifier.verticalScroll(rememberScrollState())
    ) {
        // Foto 16:9 menempel tepi.
        AsyncImage(
            model = product.gambar,
            contentDescription = product.nama,
            contentScale = ContentScale.Crop,
            modifier = Modifier
                .fillMaxWidth()
                .aspectRatio(16f / 9f)
        )
        Column(Modifier.padding(16.dp)) {
            Text(
                text = product.nama,
                style = MaterialTheme.typography.headlineSmall,
                maxLines = 2,
                overflow = TextOverflow.Ellipsis
            )
            Spacer(Modifier.height(8.dp))
            // tnum nominal, bukan titleLarge yang proporsional.
            Money(
                amount = product.harga,
                style = MoneyRow,
                color = MaterialTheme.colorScheme.primary
            )
            Spacer(Modifier.height(8.dp))
            Text(
                text = if (habis) "Stok habis" else "Sisa ${product.sisa}",
                style = MaterialTheme.typography.bodySmall,
                color = if (habis) MaterialTheme.colorScheme.error
                        else MaterialTheme.colorScheme.onSurfaceVariant
            )
            if (product.satuan.isNotBlank()) {
                Text(
                    text = "Satuan: ${product.satuan}",
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.onSurfaceVariant
                )
            }
            product.deskripsi?.takeIf { it.isNotBlank() }?.let {
                Spacer(Modifier.height(12.dp))
                Text(it, style = MaterialTheme.typography.bodyMedium)
            }
            actionError?.let {
                Spacer(Modifier.height(8.dp))
                Text(
                    text = it,
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.error
                )
            }
        }

        Spacer(Modifier.weight(1f))

        // Panel aksi menempel bawah, tidak ikut tergulir dengan mulus.
        Surface(tonalElevation = 0.dp) {
            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(16.dp),
                verticalAlignment = Alignment.CenterVertically,
                horizontalArrangement = Arrangement.spacedBy(12.dp)
            ) {
                Row(verticalAlignment = Alignment.CenterVertically) {
                    OutlinedIconButton(
                        onClick = { if (qty > 1) onQtyChange(qty - 1) },
                        enabled = !habis && qty > 1,
                        modifier = Modifier.size(48.dp)
                    ) {
                        Icon(Icons.Default.Remove, contentDescription = "Kurangi jumlah")
                    }
                    Text(
                        text = "$qty",
                        style = MaterialTheme.typography.titleMedium,
                        modifier = Modifier.widthIn(min = 32.dp),
                        maxLines = 1
                    )
                    OutlinedIconButton(
                        onClick = { if (qty < maxQty) onQtyChange(qty + 1) },
                        enabled = !habis && qty < maxQty,
                        modifier = Modifier.size(48.dp)
                    ) {
                        Icon(Icons.Default.Add, contentDescription = "Tambah jumlah")
                    }
                }
                Button(
                    onClick = onAddToCart,
                    enabled = !habis && !isAdding,
                    modifier = Modifier.weight(1f).heightIn(min = 48.dp)
                ) {
                    if (isAdding) {
                        CircularProgressIndicator(
                            modifier = Modifier.size(24.dp),
                            color = MaterialTheme.colorScheme.onPrimary,
                            strokeWidth = 2.dp
                        )
                    } else {
                        Text(if (habis) "Stok Habis" else "Tambah ke Keranjang")
                    }
                }
            }
        }
    }
}

@Composable
private fun DetailSkeleton(modifier: Modifier = Modifier) {
    Column(modifier) {
        SkeletonBox(
            modifier = Modifier
                .fillMaxWidth()
                .aspectRatio(16f / 9f)
        )
        Column(Modifier.padding(16.dp)) {
            SkeletonBox(modifier = Modifier.fillMaxWidth().height(28.dp))
            Spacer(Modifier.height(12.dp))
            SkeletonBox(modifier = Modifier.fillMaxWidth(0.4f).height(20.dp))
            Spacer(Modifier.height(12.dp))
            SkeletonBox(modifier = Modifier.fillMaxWidth().height(16.dp))
            Spacer(Modifier.height(8.dp))
            SkeletonBox(modifier = Modifier.fillMaxWidth(0.7f).height(16.dp))
        }
    }
}

@LightDarkPreview
@Composable
private fun DetailContentPreview() {
    EsimkoPreview {
        DetailContent(
            product = ProductDetail(
                id = 1, nama = "Beras Premium 5kg", harga = 75_000, stok = 10,
                gambar = null, deskripsi = "Beras premium kualitas terbaik, pulen dan wangi.",
                satuan = "pcs", kode = "BR001", kategori = "", kelompok = "Sembako",
                terjual = 24, sisa = 10
            ),
            qty = 2,
            onQtyChange = {},
            isAdding = false,
            actionError = null,
            onAddToCart = {}
        )
    }
}

@LightDarkPreview
@Composable
private fun DetailContentHabisPreview() {
    EsimkoPreview {
        DetailContent(
            product = ProductDetail(
                id = 2, nama = "Minyak Goreng 2L", harga = 38_000, stok = 0,
                gambar = null, deskripsi = null,
                satuan = "botol", kode = "MY001", kategori = "", kelompok = "Sembako",
                terjual = 50, sisa = 0
            ),
            qty = 1,
            onQtyChange = {},
            isAdding = false,
            actionError = null,
            onAddToCart = {}
        )
    }
}
