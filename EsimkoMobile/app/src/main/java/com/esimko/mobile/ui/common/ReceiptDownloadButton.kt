package com.esimko.mobile.ui.common

import android.content.ActivityNotFoundException
import android.content.ClipData
import android.content.Intent
import android.net.Uri
import android.widget.Toast
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.Button
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.runtime.Composable
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel

@Composable
fun ReceiptDownloadButton(
    jenis: String,
    id: Long,
    tersedia: Boolean,
    modifier: Modifier = Modifier,
    viewModel: ReceiptViewModel = hiltViewModel()
) {
    if (!tersedia) return
    val states by viewModel.states.collectAsState()
    val state = states["$jenis/$id"] ?: ReceiptState()
    val context = LocalContext.current
    fun launch(uri: String, share: Boolean) {
        try {
            val parsed = Uri.parse(uri)
            val intent = Intent(if (share) Intent.ACTION_SEND else Intent.ACTION_VIEW).apply {
                if (share) {
                    type = "application/pdf"
                    putExtra(Intent.EXTRA_STREAM, parsed)
                } else setDataAndType(parsed, "application/pdf")
                clipData = ClipData.newRawUri("Bukti PDF", parsed)
                addFlags(Intent.FLAG_GRANT_READ_URI_PERMISSION)
            }
            context.startActivity(if (share) Intent.createChooser(intent, "Bagikan / simpan bukti PDF") else intent)
        } catch (_: ActivityNotFoundException) {
            Toast.makeText(context, "Tidak ada aplikasi yang sesuai. Pasang pembaca PDF atau gunakan Bagikan untuk menyimpan.", Toast.LENGTH_LONG).show()
        } catch (_: Exception) {
            Toast.makeText(context, "Bukti tidak dapat dibuka. Unduh ulang atau gunakan Bagikan.", Toast.LENGTH_LONG).show()
        }
    }
    Column(modifier.padding(horizontal = 16.dp, vertical = 8.dp)) {
        Button(onClick = { viewModel.download(jenis, id) }, enabled = !state.downloading) {
            Text(if (state.downloading) "Mengunduh bukti…" else "Unduh Bukti PDF")
        }
        state.error?.let { Text(it) }
        state.saved?.let { saved ->
            Text(if (saved.inDownloads) "Tersimpan di Downloads/eSIMKO."
            else "PDF sementara siap. Ketuk Bagikan, lalu pilih Simpan ke File/Drive untuk menyimpan salinan permanen.")
            Row {
                TextButton(onClick = { viewModel.useSaved(saved) { launch(saved.uri, false) } }) { Text("Buka") }
                TextButton(onClick = { viewModel.useSaved(saved) { launch(saved.uri, true) } }) { Text("Bagikan / Simpan") }
            }
        }
    }
}
