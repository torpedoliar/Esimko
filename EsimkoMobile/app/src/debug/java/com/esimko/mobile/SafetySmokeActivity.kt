package com.esimko.mobile

import android.content.ActivityNotFoundException
import android.content.Intent
import android.graphics.Color
import android.graphics.Paint
import android.graphics.pdf.PdfDocument
import android.net.Uri
import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedButton
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.material3.TopAppBar
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import androidx.lifecycle.lifecycleScope
import com.esimko.mobile.data.local.PendingSavings
import com.esimko.mobile.data.local.PendingSavingsStore
import com.esimko.mobile.data.repository.ReceiptFileStore
import com.esimko.mobile.data.repository.SavedReceipt
import com.esimko.mobile.ui.theme.EsimkoTheme
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import java.io.File
import java.util.UUID

/**
 * Debug-only offline device checks. Uses synthetic labels and deletes only the
 * exports it creates. Never signs in, calls the API, or touches member data.
 */
class SafetySmokeActivity : ComponentActivity() {

    private val receiptStore by lazy { ReceiptFileStore(applicationContext) }

    @OptIn(androidx.compose.material3.ExperimentalMaterial3Api::class)
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContent {
            EsimkoTheme {
                var lines by remember { mutableStateOf(listOf("Menjalankan pemeriksaan…")) }
                var saved by remember { mutableStateOf<SavedReceipt?>(null) }
                var busy by remember { mutableStateOf(false) }

                LaunchedEffect(Unit) {
                    lines = runChecks()
                    saved = lastReceipt
                }

                Scaffold(topBar = { TopAppBar(title = { Text("Smoke perangkat (debug)") }) }) { padding ->
                    Column(
                        Modifier.fillMaxSize().padding(padding).padding(16.dp).verticalScroll(rememberScrollState()),
                        verticalArrangement = Arrangement.spacedBy(10.dp)
                    ) {
                        Text(
                            "Offline; data sintetis. PDF hanya dibuat dan dihapus di perangkat ini.",
                            style = MaterialTheme.typography.bodySmall
                        )
                        lines.forEach { line ->
                            Text(
                                line,
                                color = if (line.startsWith("FAIL")) MaterialTheme.colorScheme.error
                                else MaterialTheme.colorScheme.onSurface
                            )
                        }
                        Spacer(Modifier.height(8.dp))
                        Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                            OutlinedButton(
                                onClick = {
                                    if (!busy) {
                                        busy = true
                                        lifecycleScope.launch {
                                            lines = runChecks()
                                            saved = lastReceipt
                                            busy = false
                                        }
                                    }
                                },
                                enabled = !busy
                            ) { Text("Jalankan ulang") }
                            OutlinedButton(
                                onClick = { openPdf(saved) },
                                enabled = saved != null
                            ) { Text("Buka PDF") }
                        }
                        Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                            OutlinedButton(
                                onClick = { sharePdf(saved) },
                                enabled = saved != null
                            ) { Text("Bagikan PDF") }
                            TextButton(
                                onClick = {
                                    saved?.let { receiptStore.discard(it) }
                                    saved = null
                                    lastReceipt = null
                                },
                                enabled = saved != null
                            ) { Text("Hapus ekspor contoh") }
                        }
                    }
                }
            }
        }
    }

    private var lastReceipt: SavedReceipt? = null

    private suspend fun runChecks(): List<String> = withContext(Dispatchers.IO) {
        val results = mutableListOf<String>()
        results += pendingStoreChecks()
        val pdfResult = receiptChecks()
        results += pdfResult.first
        lastReceipt = pdfResult.second
        results
    }

    private fun pendingStoreChecks(): String = runCatching {
        val member = "SMOKE-TEST"
        val jenis = "setoran"
        val draft = PendingSavings(
            member = member,
            jenis = jenis,
            key = UUID.randomUUID().toString(),
            nominal = 10_000L,
            keterangan = "synthetic smoke note",
            note = "synthetic smoke note"
        )
        val store = PendingSavingsStore(applicationContext)
        check(store.createOrLoad(draft) == draft) { "draft awal tidak sama" }
        // Simulates process recreation: a new store instance must decrypt the same draft.
        check(PendingSavingsStore(applicationContext).load(member, jenis) == draft) { "gagal memuat ulang terenkripsi" }
        check(PendingSavingsStore(applicationContext).load("OTHER-MEMBER", jenis) == null) { "ikatan anggota gagal" }
        check(PendingSavingsStore(applicationContext).load(member, "penarikan") == null) { "ikatan jenis gagal" }
        val resolved = store.recordSuccess(draft, 987_654_321L)
        check(resolved.transactionId == 987_654_321L)
        store.complete(resolved)
        check(store.load(member, jenis) == null) { "draft tidak terhapus" }
        "PASS pending store: simpan/muat ulang/AAD anggota-jenis/sukses/hapus"
    }.getOrElse { "FAIL pending store: ${it.javaClass.simpleName}" }

    private fun receiptChecks(): Pair<String, SavedReceipt?> = runCatching {
        val pdf = File(cacheDir, "synthetic-smoke.pdf")
        val document = PdfDocument()
        val page = document.startPage(PdfDocument.PageInfo.Builder(300, 200, 1).create())
        page.canvas.drawText("eSIMKO synthetic smoke test", 20f, 40f, Paint().apply {
            color = Color.BLACK
            textSize = 14f
        })
        document.finishPage(page)
        pdf.outputStream().use(document::writeTo)
        document.close()
        val saved = receiptStore.publish(pdf, "esimko-smoke-${System.currentTimeMillis()}.pdf") { }
        val bytes = contentResolver.openInputStream(Uri.parse(saved.uri))?.use { it.readBytes() }
            ?: error("tidak dapat membaca kembali")
        check(bytes.size > 4 && bytes[0] == '%'.code.toByte() && bytes[1] == 'P'.code.toByte() &&
            bytes[2] == 'D'.code.toByte() && bytes[3] == 'F'.code.toByte()) { "magic PDF salah" }
        pdf.delete()
        lastReceipt = saved
        "PASS receipt store: tulis/baca ulang PDF (${bytes.size} byte), URI siap buka/bagikan" to saved
    }.getOrElse { "FAIL receipt store: ${it.javaClass.simpleName}" to null }

    private fun openPdf(saved: SavedReceipt?) {
        val receipt = saved ?: return
        try {
            startActivity(
                Intent(Intent.ACTION_VIEW)
                    .setDataAndType(Uri.parse(receipt.uri), "application/pdf")
                    .addFlags(Intent.FLAG_GRANT_READ_URI_PERMISSION)
            )
        } catch (_: ActivityNotFoundException) {
            showMessage("Tidak ada penampil PDF di perangkat ini")
        }
    }

    private fun sharePdf(saved: SavedReceipt?) {
        val receipt = saved ?: return
        val uri = Uri.parse(receipt.uri)
        val send = Intent(Intent.ACTION_SEND).apply {
            type = "application/pdf"
            putExtra(Intent.EXTRA_STREAM, uri)
            addFlags(Intent.FLAG_GRANT_READ_URI_PERMISSION)
            clipData = android.content.ClipData.newRawUri("synthetic receipt", uri)
        }
        try {
            startActivity(Intent.createChooser(send, "Bagikan PDF contoh"))
        } catch (_: ActivityNotFoundException) {
            showMessage("Tidak ada aplikasi untuk membagikan PDF")
        }
    }

    private fun showMessage(message: String) {
        android.widget.Toast.makeText(this, message, android.widget.Toast.LENGTH_SHORT).show()
    }
}
