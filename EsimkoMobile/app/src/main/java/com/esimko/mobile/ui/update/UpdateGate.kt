package com.esimko.mobile.ui.update

import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.material3.AlertDialog
import androidx.compose.material3.Button
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.saveable.rememberSaveable
import androidx.compose.runtime.setValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.esimko.mobile.BuildConfig

/**
 * Gerbang pembaruan di root app: cek versi tiap app dibuka.
 * - forceUpdate → dialog MENGUNCI (satu-satunya jalan: perbarui).
 * - updateAvailable → dialog biasa (bisa "Nanti", muncul sekali per sesi).
 * Dialog izin "install unknown apps" ditangani di sini juga.
 */
@Composable
fun UpdateGate(
    viewModel: UpdateViewModel = hiltViewModel(),
    content: @Composable () -> Unit
) {
    content()
    val state by viewModel.state.collectAsStateWithLifecycle()
    val info = state.info ?: return
    var lewati by rememberSaveable { mutableStateOf(false) }

    if (info.forceUpdate) {
        AlertDialog(
            onDismissRequest = { },
            title = { Text("Pembaruan Wajib") },
            text = {
                Column {
                    Text(
                        "Versi ${BuildConfig.VERSION_NAME} sudah tidak didukung. " +
                            "Perbarui ke v${info.latestVersion ?: "-"} untuk melanjutkan menggunakan aplikasi."
                    )
                    state.tahap?.let {
                        Text(
                            it,
                            style = MaterialTheme.typography.bodySmall,
                            color = MaterialTheme.colorScheme.onSurfaceVariant
                        )
                    }
                    state.error?.let {
                        Text(it, color = MaterialTheme.colorScheme.error, style = MaterialTheme.typography.bodySmall)
                    }
                }
            },
            confirmButton = {
                Button(onClick = viewModel::unduhDanPasang, enabled = !state.sedangUnduh) {
                    Text(if (state.sedangUnduh) "Mengunduh…" else "Perbarui Sekarang")
                }
            }
        )
    } else if (info.updateAvailable && !lewati && !state.sedangUnduh) {
        AlertDialog(
            onDismissRequest = { lewati = true },
            title = { Text("Pembaruan Tersedia") },
            text = {
                Column {
                    Text(
                        "Versi terbaru v${info.latestVersion ?: "-"} tersedia. " +
                            "Perbarui agar aplikasi tetap aman dan dapat semua fitur terbaru."
                    )
                    state.error?.let {
                        Text(it, color = MaterialTheme.colorScheme.error, style = MaterialTheme.typography.bodySmall)
                    }
                }
            },
            dismissButton = {
                TextButton(onClick = { lewati = true }) { Text("Nanti") }
            },
            confirmButton = {
                Button(onClick = viewModel::unduhDanPasang) { Text("Perbarui") }
            }
        )
    }

    // Izin "install dari aplikasi ini" (Android 8+, sekali per HP): arahkan ke setelan,
    // user kembali lalu menekan tombol perbarui lagi.
    if (state.mintaIzin) {
        AlertDialog(
            onDismissRequest = viewModel::tutupMintaIzin,
            title = { Text("Izinkan Pemasangan", fontWeight = FontWeight.SemiBold) },
            text = {
                Text(
                    "Untuk memasang pembaruan, izinkan eSIMKO memasang aplikasi. " +
                        "Buka setelan, aktifkan \"Izinkan dari sumber ini\", lalu kembali dan tean Perbarui lagi."
                )
            },
            dismissButton = {
                TextButton(onClick = viewModel::tutupMintaIzin) { Text("Nanti") }
            },
            confirmButton = {
                Button(onClick = {
                    viewModel.bukaIzinInstall()
                    viewModel.tutupMintaIzin()
                }) { Text("Buka Setelan") }
            }
        )
    }
}
