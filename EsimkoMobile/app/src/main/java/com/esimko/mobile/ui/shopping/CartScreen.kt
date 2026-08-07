package com.esimko.mobile.ui.shopping

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
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.Delete
import androidx.compose.material.icons.outlined.CheckCircle
import androidx.compose.material.icons.outlined.ShoppingCart
import androidx.compose.material3.Button
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.material3.TopAppBar
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import com.esimko.mobile.domain.model.Cart
import com.esimko.mobile.domain.model.CartItem
import com.esimko.mobile.domain.model.FailedItemInfo
import com.esimko.mobile.ui.common.EmptyStateView
import com.esimko.mobile.ui.common.ErrorView
import com.esimko.mobile.ui.common.EsimkoPreview
import com.esimko.mobile.ui.common.LightDarkPreview
import com.esimko.mobile.ui.common.Money
import com.esimko.mobile.ui.common.RowDivider
import com.esimko.mobile.ui.common.SkeletonListRows
import com.esimko.mobile.ui.common.StaleBanner
import com.esimko.mobile.ui.theme.MoneyRow
import com.esimko.mobile.util.MoneyFormatter

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun CartScreen(
    onBack: () -> Unit,
    viewModel: ShoppingViewModel = hiltViewModel()
) {
    val state by viewModel.state.collectAsState()

    LaunchedEffect(Unit) { viewModel.loadCart() }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Keranjang") },
                navigationIcon = {
                    IconButton(onClick = onBack) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Kembali")
                    }
                }
            )
        }
    ) { padding ->
        val cartError = state.cartError
        val items = state.cart.items
        val hasContent = items.isNotEmpty()

        Box(Modifier.fillMaxSize().padding(padding)) {
            when {
                state.checkedOut -> CheckoutSuccessColumn(
                    failedItems = state.checkoutFailedItems,
                    onSelesai = {
                        viewModel.resetCheckout()
                        onBack()
                    },
                    modifier = Modifier.fillMaxSize()
                )
                !hasContent && state.isLoadingCart -> SkeletonListRows(
                    count = 6,
                    modifier = Modifier.fillMaxSize()
                )
                !hasContent && cartError != null -> ErrorView(
                    message = cartError,
                    onRetry = viewModel::loadCart,
                    modifier = Modifier.fillMaxSize()
                )
                !hasContent -> EmptyStateView(
                    message = "Keranjang masih kosong.",
                    modifier = Modifier.fillMaxSize(),
                    icon = Icons.Outlined.ShoppingCart
                )
                else -> CartContent(
                    items = items,
                    total = state.cart.total,
                    cartError = cartError,
                    actionError = state.actionError,
                    isCheckingOut = state.isCheckingOut,
                    onRemove = viewModel::removeFromCart,
                    onCheckout = viewModel::checkout,
                    modifier = Modifier.fillMaxSize()
                )
            }
        }
    }
}

@Composable
private fun CartContent(
    items: List<CartItem>,
    total: Long,
    cartError: String?,
    actionError: String?,
    isCheckingOut: Boolean,
    onRemove: (Long) -> Unit,
    onCheckout: () -> Unit,
    modifier: Modifier = Modifier
) {
    Column(modifier) {
        LazyColumn(
            modifier = Modifier.weight(1f).fillMaxWidth(),
            verticalArrangement = Arrangement.spacedBy(0.dp)
        ) {
            if (cartError != null) {
                item { StaleBanner(onRetry = { /* diluar scope: muat ulang */ }) }
            }
            items(items, key = { it.id }) { item ->
                CartRow(item = item, onRemove = onRemove)
                RowDivider()
            }
            item { Spacer(Modifier.height(24.dp)) }
        }
        Column(Modifier.padding(16.dp)) {
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                Text("Total", style = MaterialTheme.typography.titleMedium)
                Money(amount = total, color = MaterialTheme.colorScheme.primary)
            }
            actionError?.let {
                Spacer(Modifier.height(8.dp))
                Text(
                    text = it,
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.error
                )
            }
            Spacer(Modifier.height(12.dp))
            Button(
                onClick = onCheckout,
                enabled = !isCheckingOut,
                modifier = Modifier.fillMaxWidth().heightIn(min = 48.dp)
            ) {
                if (isCheckingOut) {
                    CircularProgressIndicator(
                        modifier = Modifier.size(24.dp),
                        color = MaterialTheme.colorScheme.onPrimary,
                        strokeWidth = 2.dp
                    )
                } else {
                    Text("Checkout")
                }
            }
        }
    }
}

@Composable
private fun CartRow(
    item: CartItem,
    onRemove: (Long) -> Unit,
    modifier: Modifier = Modifier
) {
    Row(
        modifier = modifier
            .fillMaxWidth()
            .heightIn(min = 56.dp)
            .padding(horizontal = 16.dp, vertical = 8.dp),
        verticalAlignment = Alignment.CenterVertically
    ) {
        Column(Modifier.weight(1f)) {
            Text(
                text = item.nama,
                style = MaterialTheme.typography.titleSmall,
                maxLines = 2,
                overflow = TextOverflow.Ellipsis
            )
            Text(
                text = "${item.qty} × ${MoneyFormatter.format(item.harga)}",
                style = MaterialTheme.typography.bodySmall,
                color = MaterialTheme.colorScheme.onSurfaceVariant
            )
        }
        Column(horizontalAlignment = Alignment.End) {
            Money(
                amount = item.subtotal,
                style = MoneyRow,
                color = MaterialTheme.colorScheme.primary
            )
            IconButton(onClick = { onRemove(item.produkId) }) {
                Icon(
                    imageVector = Icons.Default.Delete,
                    contentDescription = "Hapus ${item.nama}",
                    tint = MaterialTheme.colorScheme.error,
                    modifier = Modifier.size(20.dp)
                )
            }
        }
    }
}

@Composable
private fun CheckoutSuccessColumn(
    failedItems: List<FailedItemInfo>,
    onSelesai: () -> Unit,
    modifier: Modifier = Modifier
) {
    Column(
        modifier = modifier.padding(24.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center
    ) {
        Icon(
            imageVector = Icons.Outlined.CheckCircle,
            contentDescription = null,
            modifier = Modifier.size(48.dp),
            tint = MaterialTheme.colorScheme.primary
        )
        Spacer(Modifier.height(12.dp))
        Text("Checkout berhasil", style = MaterialTheme.typography.titleMedium)
        if (failedItems.isNotEmpty()) {
            Spacer(Modifier.height(8.dp))
            Text(
                text = "Beberapa item gagal (stok tidak cukup):",
                style = MaterialTheme.typography.bodySmall,
                color = MaterialTheme.colorScheme.onSurfaceVariant
            )
            failedItems.forEach {
                Text(
                    text = "• ${it.nama ?: ""}",
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.error
                )
            }
        }
        Spacer(Modifier.height(16.dp))
        Button(
            onClick = onSelesai,
            modifier = Modifier.fillMaxWidth().heightIn(min = 48.dp)
        ) {
            Text("Selesai")
        }
    }
}

@LightDarkPreview
@Composable
private fun CartContentPreview() {
    EsimkoPreview {
        CartContent(
            items = listOf(
                CartItem(1, 1, "Beras 5kg", 75_000, 2, 150_000),
                CartItem(2, 2, "Minyak 2L", 38_000, 1, 38_000),
                CartItem(3, 3, "Gula 1kg", 16_000, 3, 48_000)
            ),
            total = 236_000,
            cartError = null,
            actionError = null,
            isCheckingOut = false,
            onRemove = {},
            onCheckout = {}
        )
    }
}

@LightDarkPreview
@Composable
private fun CartEmptyPreview() {
    EsimkoPreview {
        EmptyStateView(
            message = "Keranjang masih kosong.",
            modifier = Modifier.fillMaxSize(),
            icon = Icons.Outlined.ShoppingCart
        )
    }
}

@LightDarkPreview
@Composable
private fun CheckoutSuccessPreview() {
    EsimkoPreview {
        CheckoutSuccessColumn(
            failedItems = listOf(FailedItemInfo(nama = "Telur 1kg")),
            onSelesai = {}
        )
    }
}
