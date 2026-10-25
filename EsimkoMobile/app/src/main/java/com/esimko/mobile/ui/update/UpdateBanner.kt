package com.esimko.mobile.ui.update

import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.Button
import androidx.compose.material3.LinearProgressIndicator
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.saveable.rememberSaveable
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.lifecycle.compose.collectAsStateWithLifecycle

/**
 * Notifikasi pembaruan non-blokir di Beranda (semua tab): muncul otomatis saat ada
 * versi baru yang TIDAK wajib. Bisa ditutup (muncul lagi next launch / versi baru).
 * Pembaruan wajib tetap lewat dialog pengunci di UpdateGate.
 */
@Composable
fun UpdateBanner(viewModel: UpdateViewModel = hiltViewModel()) {
    val state by viewModel.state.collectAsStateWithLifecycle()
    val info = state.info ?: return
    if (!info.updateAvailable || info.forceUpdate) return
    // Key versi: tutup utk v1.0.8 tidak menutup banner v1.0.9 berikutnya.
    var ditutup by rememberSaveable(info.latestVersion) { mutableStateOf(false) }
    if (ditutup) return

    Surface(
        color = MaterialTheme.colorScheme.secondaryContainer,
        modifier = Modifier.fillMaxWidth()
    ) {
        Column(Modifier.padding(horizontal = 16.dp, vertical = 10.dp)) {
            Text(
                text = "Versi terbaru v${info.latestVersion} tersedia",
                style = MaterialTheme.typography.titleSmall,
                color = MaterialTheme.colorScheme.onSecondaryContainer
            )
            if (state.sedangUnduh) {
                LinearProgressIndicator(
                    modifier = Modifier.fillMaxWidth().padding(top = 8.dp)
                )
                state.tahap?.let {
                    Text(
                        text = it,
                        style = MaterialTheme.typography.bodySmall,
                        color = MaterialTheme.colorScheme.onSecondaryContainer
                    )
                }
            } else {
                Row(verticalAlignment = Alignment.CenterVertically) {
                    Button(onClick = viewModel::unduhDanPasang) {
                        Text("Perbarui")
                    }
                    TextButton(onClick = { ditutup = true }) { Text("Nanti") }
                }
                state.error?.let { err ->
                    Text(
                        text = err,
                        style = MaterialTheme.typography.bodySmall,
                        color = MaterialTheme.colorScheme.error
                    )
                    TextButton(onClick = viewModel::unduhViaBrowser) {
                        Text("Unduh via Browser")
                    }
                }
            }
        }
    }
}
