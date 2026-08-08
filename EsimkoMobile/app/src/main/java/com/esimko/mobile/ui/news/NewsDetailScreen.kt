package com.esimko.mobile.ui.news

import android.content.Context
import android.content.Intent
import android.graphics.Bitmap
import android.graphics.pdf.PdfRenderer
import android.net.Uri
import android.os.ParcelFileDescriptor
import android.widget.Toast
import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
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
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.automirrored.filled.ArrowForward
import androidx.compose.material.icons.filled.AttachFile
import androidx.compose.material.icons.filled.ChevronLeft
import androidx.compose.material.icons.filled.ChevronRight
import androidx.compose.material.icons.filled.Close
import androidx.compose.material.icons.filled.Description
import androidx.compose.material.icons.filled.Image
import androidx.compose.material.icons.filled.PictureAsPdf
import androidx.compose.material.icons.filled.TableChart
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
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.produceState
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.asImageBitmap
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.compose.ui.window.Dialog
import androidx.compose.ui.window.DialogProperties
import androidx.hilt.navigation.compose.hiltViewModel
import coil.compose.AsyncImage
import com.esimko.mobile.domain.model.NewsAttachment
import com.esimko.mobile.domain.model.NewsDetail
import com.esimko.mobile.ui.common.ErrorView
import com.esimko.mobile.ui.common.EsimkoPreview
import com.esimko.mobile.ui.common.LightDarkPreview
import com.esimko.mobile.ui.common.RowDivider
import com.esimko.mobile.ui.common.SectionHeader
import com.esimko.mobile.ui.common.SkeletonBox
import com.esimko.mobile.util.HtmlToText
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import okhttp3.OkHttpClient
import okhttp3.Request
import java.io.File

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun NewsDetailScreen(
    newsId: Long,
    onBack: () -> Unit,
    viewModel: NewsViewModel = hiltViewModel()
) {
    val state by viewModel.state.collectAsState()
    var previewUrl by remember { mutableStateOf<String?>(null) }

    LaunchedEffect(newsId) {
        if (state.detail?.id != newsId) {
            viewModel.loadDetail(newsId)
        }
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Berita") },
                navigationIcon = {
                    IconButton(onClick = onBack) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Kembali")
                    }
                }
            )
        }
    ) { padding ->
        Box(Modifier.fillMaxSize().padding(padding)) {
            when {
                state.detailLoading && state.detail == null -> DetailSkeleton(Modifier.fillMaxSize())
                state.detailError != null && state.detail == null -> ErrorView(
                    message = state.detailError!!,
                    onRetry = { viewModel.loadDetail(newsId) },
                    modifier = Modifier.fillMaxSize()
                )
                else -> state.detail?.let { DetailContent(it, Modifier.fillMaxSize(), onPreview = { previewUrl = it }) } ?: Spacer(Modifier)
            }
        }
    }

    previewUrl?.let { url ->
        AttachmentPreviewDialog(url = url, onDismiss = { previewUrl = null })
    }
}

@Composable
private fun DetailContent(
    detail: NewsDetail,
    modifier: Modifier = Modifier,
    onPreview: (String) -> Unit = {}
) {
    Column(modifier.fillMaxSize().verticalScroll(rememberScrollState())) {
        // Foto 16:9 tepi-ke-tepi, di luar padding konten.
        AsyncImage(
            model = detail.gambar,
            contentDescription = detail.judul,
            contentScale = ContentScale.Crop,
            modifier = Modifier.fillMaxWidth().aspectRatio(16f / 9f)
        )
        Column(
            Modifier.padding(16.dp),
            verticalArrangement = Arrangement.spacedBy(8.dp)
        ) {
            Text(detail.judul, style = MaterialTheme.typography.headlineSmall, fontWeight = FontWeight.Bold)
            Text(
                text = detail.tanggal,
                style = MaterialTheme.typography.bodySmall,
                color = MaterialTheme.colorScheme.onSurfaceVariant
            )
            Spacer(Modifier.height(8.dp))
            // isi 16sp lineHeight 1.6 — spec §4 :217
            Text(
                text = HtmlToText.strip(detail.konten),
                style = MaterialTheme.typography.bodyLarge.copy(lineHeight = 22.sp),
                modifier = Modifier.fillMaxWidth()
            )
            if (detail.attachments.isNotEmpty()) {
                Spacer(Modifier.height(16.dp))
                SectionHeader(title = "Lampiran")
                detail.attachments.forEach { att ->
                    AttachmentRow(att, onPreview = onPreview)
                    RowDivider()
                }
            }
        }
    }
}

@Composable
private fun AttachmentRow(
    attachment: NewsAttachment,
    modifier: Modifier = Modifier,
    onPreview: (String) -> Unit = {}
) {
    val icon = attachmentIcon(attachment.url)
    Row(
        modifier = modifier
            .fillMaxWidth()
            .clickable { onPreview(attachment.url) }
            .heightIn(min = 56.dp)
            .padding(horizontal = 16.dp, vertical = 8.dp),
        verticalAlignment = Alignment.CenterVertically,
        horizontalArrangement = Arrangement.spacedBy(12.dp)
    ) {
        Icon(icon, contentDescription = null, tint = MaterialTheme.colorScheme.primary)
        Column(Modifier.weight(1f)) {
            Text(
                text = attachment.judul,
                style = MaterialTheme.typography.bodyLarge,
                maxLines = 2,
                overflow = TextOverflow.Ellipsis
            )
            Text(
                text = "Ketuk untuk membuka",
                style = MaterialTheme.typography.labelMedium,
                color = MaterialTheme.colorScheme.onSurfaceVariant
            )
        }
        Icon(
            Icons.AutoMirrored.Filled.ArrowForward,
            contentDescription = null,
            tint = MaterialTheme.colorScheme.onSurfaceVariant
        )
    }
}

private fun attachmentIcon(url: String): ImageVector {
    val ext = url.substringAfterLast('.', "").lowercase()
    return when (ext) {
        "pdf" -> Icons.Default.PictureAsPdf
        "jpg", "jpeg", "png", "gif", "webp" -> Icons.Default.Image
        "doc", "docx" -> Icons.Default.Description
        "xls", "xlsx" -> Icons.Default.TableChart
        else -> Icons.Default.AttachFile
    }
}

@Composable
private fun AttachmentPreviewDialog(url: String, onDismiss: () -> Unit) {
    val ext = url.substringAfterLast('.', "").lowercase()
    val context = LocalContext.current
    Dialog(
        onDismissRequest = onDismiss,
        properties = DialogProperties(usePlatformDefaultWidth = false)
    ) {
        Column(
            Modifier.fillMaxSize().background(MaterialTheme.colorScheme.background)
        ) {
            Row(
                Modifier.fillMaxWidth().padding(8.dp),
                verticalAlignment = Alignment.CenterVertically
            ) {
                IconButton(onClick = onDismiss) {
                    Icon(Icons.Default.Close, contentDescription = "Tutup")
                }
                Text(
                    text = fileName(url),
                    style = MaterialTheme.typography.titleSmall,
                    maxLines = 1,
                    overflow = TextOverflow.Ellipsis,
                    modifier = Modifier.weight(1f).padding(horizontal = 8.dp)
                )
            }
            Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                when (ext) {
                    "jpg", "jpeg", "png", "gif", "webp" -> AsyncImage(
                        model = url,
                        contentDescription = fileName(url),
                        contentScale = ContentScale.Fit,
                        modifier = Modifier.fillMaxSize()
                    )
                    "pdf" -> PdfPreview(url)
                    else -> Column(
                        horizontalAlignment = Alignment.CenterHorizontally,
                        verticalArrangement = Arrangement.spacedBy(12.dp),
                        modifier = Modifier.padding(24.dp)
                    ) {
                        Icon(
                            Icons.Default.AttachFile,
                            contentDescription = null,
                            modifier = Modifier.size(48.dp),
                            tint = MaterialTheme.colorScheme.onSurfaceVariant
                        )
                        Text(
                            "Jenis file ini tidak bisa dipratinjau di app.",
                            style = MaterialTheme.typography.bodyMedium,
                            textAlign = androidx.compose.ui.text.style.TextAlign.Center
                        )
                        Text(
                            "Buka dengan aplikasi lain?",
                            color = MaterialTheme.colorScheme.primary,
                            modifier = Modifier.clickable {
                                openExternally(context, url, onDismiss)
                            }
                        )
                    }
                }
            }
        }
    }
}

@Composable
private fun PdfPreview(url: String) {
    val context = LocalContext.current
    // ponytail: unduh PDF ke cache lalu PdfRenderer native. File besar = unduh penuh dulu
    // (no streaming render). Cukup untuk berita koperasi; kalau PDF puluhan MB, ganti ke
    // download progresif + render page-on-demand.
    val state = produceState<PdfState>(initialValue = PdfState.Loading, url) {
        value = loadPdf(context, url)
    }
    when (val s = state.value) {
        is PdfState.Loading -> CircularProgressIndicator()
        is PdfState.Error -> Column(
            horizontalAlignment = Alignment.CenterHorizontally,
            verticalArrangement = Arrangement.spacedBy(8.dp),
            modifier = Modifier.padding(24.dp)
        ) {
            Text("Gagal memuat PDF", style = MaterialTheme.typography.bodyMedium)
            Text(
                s.message,
                style = MaterialTheme.typography.bodySmall,
                color = MaterialTheme.colorScheme.onSurfaceVariant
            )
        }
        is PdfState.Success -> PdfPages(s.bitmaps)
    }
}

@Composable
private fun PdfPages(bitmaps: List<Bitmap>) {
    var index by remember { mutableStateOf(0) }
    val current = bitmaps.getOrNull(index)
    if (current == null) {
        Text("PDF kosong", modifier = Modifier.padding(24.dp))
        return
    }
    Column(Modifier.fillMaxSize()) {
        Image(
            bitmap = current.asImageBitmap(),
            contentDescription = "Halaman ${index + 1}",
            contentScale = ContentScale.Fit,
            modifier = Modifier.weight(1f).fillMaxWidth()
        )
        Row(
            Modifier.fillMaxWidth().padding(8.dp),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically
        ) {
            IconButton(onClick = { if (index > 0) index-- }, enabled = index > 0) {
                Icon(Icons.Default.ChevronLeft, contentDescription = "Sebelumnya")
            }
            Text(
                "Halaman ${index + 1} / ${bitmaps.size}",
                style = MaterialTheme.typography.bodySmall
            )
            IconButton(onClick = { if (index < bitmaps.size - 1) index++ }, enabled = index < bitmaps.size - 1) {
                Icon(Icons.Default.ChevronRight, contentDescription = "Berikutnya")
            }
        }
    }
}

private sealed interface PdfState {
    data object Loading : PdfState
    data class Success(val bitmaps: List<Bitmap>) : PdfState
    data class Error(val message: String) : PdfState
}

// ponytail: unduh PDF ke cacheDir, PdfRenderer render SEMUA halaman ke bitmap.
// O(N memori) untuk N halaman — berita koperasi jarang >10 hal. Kalau pernah
// besar, render on-demand per halaman (loadPdfCurrentPage) + jangan simpan list.
private suspend fun loadPdf(context: Context, url: String): PdfState = withContext(Dispatchers.IO) {
    try {
        val client = OkHttpClient()
        val req = Request.Builder().url(url).build()
        client.newCall(req).execute().use { resp ->
            if (!resp.isSuccessful) return@withContext PdfState.Error("HTTP ${resp.code}")
            val file = File(context.cacheDir, "preview_${System.currentTimeMillis()}.pdf")
            file.outputStream().use { out -> resp.body?.byteStream()?.copyTo(out) }
            if (!file.exists() || file.length() == 0L)
                return@withContext PdfState.Error("File kosong")
            val bitmaps = renderPdf(file)
            file.delete()
            if (bitmaps.isEmpty()) PdfState.Error("Tidak ada halaman")
            else PdfState.Success(bitmaps)
        }
    } catch (e: Exception) {
        PdfState.Error(e.message ?: "Gagal mengunduh")
    }
}

private fun renderPdf(file: File): List<Bitmap> {
    val result = mutableListOf<Bitmap>()
    ParcelFileDescriptor.open(file, ParcelFileDescriptor.MODE_READ_ONLY).use { pfd ->
        PdfRenderer(pfd).use { renderer ->
            for (i in 0 until renderer.pageCount) {
                renderer.openPage(i).use { page ->
                    val w = (page.width * 2).coerceAtMost(2048)
                    val h = (page.height * 2).coerceAtMost(2048)
                    val bmp = Bitmap.createBitmap(w, h, Bitmap.Config.ARGB_8888)
                    page.render(bmp, null, null, PdfRenderer.Page.RENDER_MODE_FOR_DISPLAY)
                    result.add(bmp)
                }
            }
        }
    }
    return result
}

private fun fileName(url: String): String =
    url.substringAfterLast('/', "lampiran").substringAfter('?').ifBlank { "lampiran" }

private fun openExternally(context: Context, url: String, onDismiss: () -> Unit) {
    try {
        val intent = Intent(Intent.ACTION_VIEW, Uri.parse(url))
            .addFlags(Intent.FLAG_ACTIVITY_NEW_TASK)
        context.startActivity(intent)
    } catch (e: Exception) {
        Toast.makeText(context, "Tidak ada aplikasi untuk membuka lampiran", Toast.LENGTH_SHORT).show()
    }
    onDismiss()
}

@Composable
private fun DetailSkeleton(modifier: Modifier = Modifier) {
    Column(modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
        SkeletonBox(Modifier.fillMaxWidth().aspectRatio(16f / 9f))
        Spacer(Modifier.height(8.dp))
        SkeletonBox(Modifier.fillMaxWidth(0.6f).height(24.dp))
        SkeletonBox(Modifier.fillMaxWidth(0.3f).height(14.dp))
        Spacer(Modifier.height(8.dp))
        SkeletonBox(Modifier.fillMaxWidth().height(14.dp))
        SkeletonBox(Modifier.fillMaxWidth(0.85f).height(14.dp))
    }
}

@LightDarkPreview
@Composable
private fun DetailContentPreview() {
    EsimkoPreview {
        DetailContent(
            NewsDetail(
                id = 1,
                judul = "Rapat Anggota Tahunan 2026",
                konten = "<p>Halo <strong>dunia</strong></p><p>Acara akan diadakan besok.</p>",
                gambar = null,
                tanggal = "2026-08-06 09:00:00",
                attachments = listOf(
                    NewsAttachment(1, "Azas - azas koperasi", "https://example.com/file.pdf"),
                    NewsAttachment(2, "Foto Rapat", "https://example.com/foto.png")
                )
            )
        )
    }
}

@LightDarkPreview
@Composable
private fun AttachmentRowPreview() {
    EsimkoPreview {
        AttachmentRow(NewsAttachment(1, "Form Pendaftaran Anggota Baru", "https://example.com/form.pdf"))
    }
}
