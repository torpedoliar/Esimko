package com.esimko.mobile.ui.update

import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.ui.unit.dp
import androidx.compose.foundation.layout.Column
import androidx.compose.material3.AlertDialog
import androidx.compose.material3.Button
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.runtime.Composable
import androidx.compose.runtime.DisposableEffect
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.ui.Modifier
import androidx.lifecycle.compose.LocalLifecycleOwner
import androidx.compose.ui.text.font.FontWeight
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.LifecycleEventObserver
import androidx.lifecycle.repeatOnLifecycle
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
    // Cek ulang saat app kembali ke depan — rilis baru tidak perlu menunggu cold start.
    val lifecycleOwner = LocalLifecycleOwner.current
    DisposableEffect(lifecycleOwner) {
        val observer = LifecycleEventObserver { _, event ->
            if (event == Lifecycle.Event.ON_RESUME) viewModel.cekJikaPerlu()
        }
        lifecycleOwner.lifecycle.addObserver(observer)
        onDispose { lifecycleOwner.lifecycle.removeObserver(observer) }
    }
    // Unduh selesai (path tersimpan di state) → buka installer dari foreground. Meluncurkan
    // activity dari broadcast receiver diblokir Android 10+, jadi pemicunya dipindah ke sini.
    val context = androidx.compose.ui.platform.LocalContext.current
    LaunchedEffect(lifecycleOwner, context, viewModel) {
        lifecycleOwner.lifecycle.repeatOnLifecycle(Lifecycle.State.RESUMED) {
            viewModel.state.collect { update ->
                if (update.siapPasang != null && update.otomatisPasang) {
                    viewModel.pasangSiap(context)
                }
            }
        }
    }
    val info = state.info ?: return

    // Pembaruan TIDAK wajib tidak lagi pakai dialog — banner lembut di Beranda
    // (UpdateBanner). Di sini hanya: force-update (mengunci) + dialog izin install.
    if (info.forceUpdate) {
        AlertDialog(
            onDismissRequest = { },
            title = { Text("Pembaruan Wajib") },
            text = {
                Column(
                    modifier = Modifier.verticalScroll(rememberScrollState()),
                    verticalArrangement = Arrangement.spacedBy(8.dp)
                ) {
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
                    Text(if (state.sedangUnduh) "Mengunduh…" else if (state.siapPasang != null) "Pasang Pembaruan" else "Perbarui Sekarang")
                }
            },
            // Pintu darurat: jalur browser tidak pernah terpengaruh bug in-app.
            dismissButton = {
                TextButton(onClick = viewModel::unduhViaBrowser) {
                    Text("Unduh via Browser")
                }
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
                        "Buka setelan, aktifkan \"Izinkan dari sumber ini\", lalu kembali dan tekan Perbarui lagi."
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
