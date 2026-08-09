package com.esimko.mobile.ui.shopping

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.aspectRatio
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Add
import androidx.compose.material3.Card
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import coil.compose.AsyncImage
import com.esimko.mobile.domain.model.Product
import com.esimko.mobile.ui.common.EsimkoPreview
import com.esimko.mobile.ui.common.LightDarkPreview
import com.esimko.mobile.ui.common.Money

@Composable
fun ProductCard(
    product: Product,
    onClick: () -> Unit,
    onAdd: () -> Unit,
    modifier: Modifier = Modifier
) {
    val habis = product.stok <= 0
    Card(
        modifier = modifier.fillMaxWidth(),
        onClick = onClick
    ) {
        Box(Modifier.fillMaxWidth()) {
            AsyncImage(
                model = product.gambar,
                contentDescription = null,
                contentScale = ContentScale.Crop,
                modifier = Modifier
                    .fillMaxWidth()
                    .aspectRatio(1f)
                    .clip(RoundedCornerShape(topStart = 12.dp, topEnd = 12.dp))
            )
            if (habis) {
                Box(Modifier.matchParentSize().background(MaterialTheme.colorScheme.surface.copy(alpha = 0.6f)))
                Surface(
                    color = MaterialTheme.colorScheme.errorContainer,
                    shape = RoundedCornerShape(6.dp),
                    modifier = Modifier.align(Alignment.TopStart).padding(8.dp)
                ) {
                    Text(
                        "Habis",
                        style = MaterialTheme.typography.labelMedium,
                        color = MaterialTheme.colorScheme.onErrorContainer,
                        modifier = Modifier.padding(horizontal = 8.dp, vertical = 2.dp)
                    )
                }
            }
            IconButton(
                onClick = onAdd,
                enabled = !habis,
                modifier = Modifier
                    .align(Alignment.BottomEnd)
                    .padding(8.dp)
                    .size(48.dp)
            ) {
                Surface(
                    color = if (habis) MaterialTheme.colorScheme.surfaceVariant
                            else MaterialTheme.colorScheme.primary,
                    shape = CircleShape,
                    modifier = Modifier.size(32.dp)
                ) {
                    Icon(
                        imageVector = Icons.Default.Add,
                        contentDescription = "Tambah ${product.nama} ke keranjang",
                        tint = if (habis) MaterialTheme.colorScheme.onSurfaceVariant
                               else MaterialTheme.colorScheme.onPrimary,
                        modifier = Modifier.padding(6.dp)
                    )
                }
            }
        }
        Column(
            Modifier.padding(horizontal = 12.dp, vertical = 8.dp),
            verticalArrangement = androidx.compose.foundation.layout.Arrangement.spacedBy(4.dp)
        ) {
            Text(
                product.nama,
                style = MaterialTheme.typography.titleSmall,
                maxLines = 2,
                overflow = TextOverflow.Ellipsis,
                minLines = 2
            )
            Row(verticalAlignment = Alignment.Bottom) {
                Money(amount = product.harga)
                if (product.satuan.isNotBlank()) {
                    Text(
                        "/${product.satuan}",
                        style = MaterialTheme.typography.labelMedium,
                        color = MaterialTheme.colorScheme.onSurfaceVariant
                    )
                }
            }
            if (!habis && product.stok <= 5) {
                Text(
                    "Sisa ${product.stok}",
                    style = MaterialTheme.typography.labelMedium,
                    color = MaterialTheme.colorScheme.error
                )
            }
        }
    }
}

// ponytail: stok < 6 dianggap "hampir habis". Angkanya tebakan, bukan aturan koperasi. Kalau
// pengurus punya ambang sendiri, jadikan konstanta di satu tempat.

@LightDarkPreview
@Composable
private fun ProductCardPreview() {
    EsimkoPreview {
        ProductCard(
            product = Product(
                id = 1, nama = "Beras Premium 5kg", harga = 75_000, stok = 5,
                gambar = null, satuan = "pcs", kode = "BR001", kategori = "", kelompok = "Sembako"
            ),
            onClick = {},
            onAdd = {}
        )
    }
}

@LightDarkPreview
@Composable
private fun ProductCardHabisPreview() {
    EsimkoPreview {
        ProductCard(
            product = Product(
                id = 2, nama = "Minyak Goreng 2L", harga = 38_000, stok = 0,
                gambar = null, satuan = "botol", kode = "MY001", kategori = "", kelompok = "Sembako"
            ),
            onClick = {},
            onAdd = {}
        )
    }
}
