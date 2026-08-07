package com.esimko.mobile.ui.home

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
import androidx.compose.foundation.lazy.LazyRow
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.lazy.grid.GridCells
import androidx.compose.foundation.lazy.grid.GridItemSpan
import androidx.compose.foundation.lazy.grid.LazyVerticalGrid
import androidx.compose.foundation.lazy.grid.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowForward
import androidx.compose.material.icons.outlined.Close
import androidx.compose.material.icons.outlined.Search
import androidx.compose.material.icons.outlined.ShoppingCart
import androidx.compose.material3.Badge
import androidx.compose.material3.BadgedBox
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.FilterChip
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.material3.SnackbarHost
import androidx.compose.material3.SnackbarHostState
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.runtime.remember
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.unit.Dp
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import com.esimko.mobile.domain.model.Product
import com.esimko.mobile.ui.common.EmptyStateView
import com.esimko.mobile.ui.common.ErrorView
import com.esimko.mobile.ui.common.EsimkoPreview
import com.esimko.mobile.ui.common.LightDarkPreview
import com.esimko.mobile.ui.common.Money
import com.esimko.mobile.ui.common.SkeletonProductGrid
import com.esimko.mobile.ui.common.StaleBanner
import com.esimko.mobile.ui.shopping.ProductCard
import com.esimko.mobile.ui.shopping.ShoppingViewModel

@Composable
fun ShoppingTab(
    onOpenProduct: (String) -> Unit = {},
    onOpenCart: () -> Unit = {},
    viewModel: ShoppingViewModel = hiltViewModel()
) {
    val state by viewModel.state.collectAsState()

    // Refresh cart badge setiap tab masuk (checkout/add di layar lain mengubah isi keranjang)
    LaunchedEffect(Unit) { viewModel.loadCart() }

    val snackbarHostState = remember { SnackbarHostState() }
    LaunchedEffect(state.actionError) {
        state.actionError?.let {
            snackbarHostState.showSnackbar(it)
            viewModel.clearActionError()
        }
    }
    LaunchedEffect(state.addSuccess) {
        if (state.addSuccess) {
            snackbarHostState.showSnackbar("Ditambahkan ke keranjang")
            viewModel.resetAddSuccess()
        }
    }

    Box(Modifier.fillMaxSize()) {
        Column(Modifier.fillMaxSize()) {
            // Kepala menempel — di luar grid, tidak tergulir.
            Surface(color = MaterialTheme.colorScheme.background, tonalElevation = 0.dp) {
                Column(
                    Modifier.padding(horizontal = 16.dp, vertical = 8.dp),
                    verticalArrangement = Arrangement.spacedBy(8.dp)
                ) {
                    Row(
                        verticalAlignment = Alignment.CenterVertically,
                        horizontalArrangement = Arrangement.spacedBy(8.dp)
                    ) {
                        OutlinedTextField(
                            value = state.query,
                            onValueChange = viewModel::onQueryChange,
                            modifier = Modifier.weight(1f),
                            placeholder = { Text("Cari produk atau kode") },
                            leadingIcon = { Icon(Icons.Outlined.Search, contentDescription = null) },
                            trailingIcon = {
                                if (state.query.isNotEmpty()) {
                                    IconButton(onClick = { viewModel.onQueryChange("") }) {
                                        Icon(Icons.Outlined.Close, contentDescription = "Hapus pencarian")
                                    }
                                }
                            },
                            singleLine = true,
                            shape = RoundedCornerShape(28.dp),
                            keyboardOptions = KeyboardOptions(imeAction = ImeAction.Search)
                        )
                        BadgedBox(
                            badge = {
                                if (state.cartQty > 0) {
                                    Badge { Text("${state.cartQty}") }
                                }
                            }
                        ) {
                            IconButton(onClick = onOpenCart, modifier = Modifier.size(48.dp)) {
                                Icon(Icons.Outlined.ShoppingCart, contentDescription = "Keranjang")
                            }
                        }
                    }
                    if (state.kelompokList.isNotEmpty()) {
                        LazyRow(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                            items(state.kelompokList, key = { it }) { kelompok ->
                                FilterChip(
                                    selected = state.kelompokFilter == kelompok,
                                    onClick = { viewModel.onKelompokChange(kelompok) },
                                    label = { Text(kelompok) },
                                    modifier = Modifier.heightIn(min = 48.dp)
                                )
                            }
                        }
                    }
                }
            }

            Spacer(Modifier.height(8.dp))

            // Badan — empat keadaan saling eksklusif.
            when {
                state.isLoadingProducts && state.products.isEmpty() -> SkeletonProductGrid(count = 6)
                state.visibleProducts.isEmpty() && state.productsError == null -> {
                    val pesan: String
                    val aksi: String?
                    val onAction: (() -> Unit)?
                    when {
                        state.query.isNotBlank() -> {
                            pesan = "Tidak ada produk untuk \"${state.query}\"."
                            aksi = "Hapus pencarian"
                            onAction = { viewModel.onQueryChange("") }
                        }
                        state.kelompokFilter != null -> {
                            pesan = "Tidak ada produk di ${state.kelompokFilter}."
                            aksi = "Tampilkan semua"
                            onAction = { viewModel.onKelompokChange(state.kelompokFilter) }
                        }
                        else -> {
                            pesan = "Belum ada produk."
                            aksi = null
                            onAction = null
                        }
                    }
                    EmptyStateView(
                        message = pesan,
                        modifier = Modifier.fillMaxSize(),
                        icon = Icons.Outlined.Search,
                        actionLabel = aksi,
                        onAction = onAction
                    )
                }
                state.visibleProducts.isEmpty() && state.productsError != null -> ErrorView(
                    message = state.productsError!!,
                    onRetry = viewModel::retryProducts,
                    modifier = Modifier.fillMaxSize()
                )
                else -> Column(Modifier.fillMaxSize()) {
                    if (state.productsError != null) StaleBanner(onRetry = viewModel::retryProducts)
                    ProductGrid(
                        products = state.visibleProducts,
                        hasMore = state.hasMore && state.kelompokFilter == null,
                        page = state.page,
                        bottomPadding = if (state.cartQty > 0) 96.dp else 16.dp,
                        onOpenProduct = onOpenProduct,
                        onAddToCart = { viewModel.addToCart(it) },
                        onLoadMore = viewModel::loadMoreProducts
                    )
                }
            }
        }

        // Bar keranjang menempel di bawah.
        if (state.cartQty > 0) {
            CartBar(
                cartQty = state.cartQty,
                total = state.cart.total,
                onClick = onOpenCart,
                modifier = Modifier.align(Alignment.BottomCenter)
            )
        }

        SnackbarHost(snackbarHostState, modifier = Modifier.align(Alignment.BottomCenter))
    }
}

@Composable
private fun CartBar(
    cartQty: Int,
    total: Long,
    onClick: () -> Unit,
    modifier: Modifier = Modifier
) {
    Surface(
        color = MaterialTheme.colorScheme.primary,
        modifier = modifier
            .fillMaxWidth()
            .padding(16.dp),
        shape = RoundedCornerShape(16.dp),
        shadowElevation = 8.dp
    ) {
        Row(
            modifier = Modifier
                .clickable(onClick = onClick)
                .heightIn(min = 56.dp)
                .padding(horizontal = 16.dp),
            verticalAlignment = Alignment.CenterVertically
        ) {
            Column(Modifier.weight(1f)) {
                Text(
                    text = "$cartQty barang",
                    style = MaterialTheme.typography.labelMedium,
                    color = MaterialTheme.colorScheme.onPrimary.copy(alpha = 0.85f)
                )
                Money(amount = total, color = MaterialTheme.colorScheme.onPrimary)
            }
            Text(
                text = "Lihat Keranjang",
                style = MaterialTheme.typography.labelLarge,
                color = MaterialTheme.colorScheme.onPrimary
            )
            Icon(
                Icons.AutoMirrored.Filled.ArrowForward,
                contentDescription = null,
                tint = MaterialTheme.colorScheme.onPrimary
            )
        }
    }
}

@Composable
private fun ProductGrid(
    products: List<Product>,
    hasMore: Boolean,
    page: Int,
    bottomPadding: Dp,
    onOpenProduct: (String) -> Unit,
    onAddToCart: (Long) -> Unit,
    onLoadMore: () -> Unit,
    modifier: Modifier = Modifier
) {
    LazyVerticalGrid(
        columns = GridCells.Fixed(2),
        contentPadding = androidx.compose.foundation.layout.PaddingValues(
            start = 16.dp, end = 16.dp, top = 8.dp, bottom = bottomPadding
        ),
        horizontalArrangement = Arrangement.spacedBy(12.dp),
        verticalArrangement = Arrangement.spacedBy(12.dp),
        modifier = modifier.fillMaxSize()
    ) {
        items(products, key = { it.id }) { product ->
            ProductCard(
                product = product,
                onClick = { onOpenProduct(product.kode) },
                onAdd = { onAddToCart(product.id) }
            )
        }
        if (hasMore) {
            item(key = "load-more", span = { GridItemSpan(maxLineSpan) }) {
                LaunchedEffect(page) { onLoadMore() }
                Box(Modifier.fillMaxWidth().padding(16.dp), contentAlignment = Alignment.Center) {
                    CircularProgressIndicator(Modifier.size(24.dp))
                }
            }
        }
    }
}

@LightDarkPreview
@Composable
private fun ProductGridPreview() {
    EsimkoPreview {
        Column {
            ProductGrid(
                products = listOf(
                    Product(1, "Beras 5kg", 75_000, 5, null, "pcs", "BR1", "", "Sembako"),
                    Product(2, "Minyak 2L", 38_000, 0, null, "botol", "MY1", "", "Sembako"),
                    Product(3, "Gula 1kg", 16_000, 24, null, "kg", "GU1", "", "Sembako"),
                    Product(4, "Telur 1kg", 28_000, 3, null, "kg", "TL1", "", "Sembako")
                ),
                hasMore = false,
                page = 1,
                bottomPadding = 16.dp,
                onOpenProduct = {},
                onAddToCart = {},
                onLoadMore = {}
            )
        }
    }
}

@LightDarkPreview
@Composable
private fun CartBarPreview() {
    EsimkoPreview {
        Box(Modifier.padding(16.dp)) {
            CartBar(cartQty = 3, total = 145_000L, onClick = {})
        }
    }
}
