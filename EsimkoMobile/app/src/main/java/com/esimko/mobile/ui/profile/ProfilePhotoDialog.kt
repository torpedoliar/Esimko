package com.esimko.mobile.ui.profile

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.safeDrawingPadding
import androidx.compose.foundation.layout.width
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.Close
import androidx.compose.material.icons.outlined.PhotoCamera
import androidx.compose.material.icons.outlined.Visibility
import androidx.compose.material3.AlertDialog
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.FilledTonalButton
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedButton
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.key
import androidx.compose.runtime.mutableIntStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.unit.dp
import androidx.compose.ui.window.Dialog
import androidx.compose.ui.window.DialogProperties
import coil.compose.SubcomposeAsyncImage

@Composable
internal fun ProfilePhotoOptions(
    hasPhoto: Boolean,
    onOpen: () -> Unit,
    onChange: () -> Unit,
    onDismiss: () -> Unit
) {
    AlertDialog(
        onDismissRequest = onDismiss,
        title = { Text("Foto profil") },
        text = {
            Column(verticalArrangement = Arrangement.spacedBy(8.dp)) {
                OutlinedButton(onClick = onOpen, enabled = hasPhoto, modifier = Modifier.fillMaxWidth()) {
                    Icon(Icons.Outlined.Visibility, contentDescription = null)
                    Spacer(Modifier.width(8.dp))
                    Text("Buka foto")
                }
                if (!hasPhoto) {
                    Text("Belum ada foto profil.", style = MaterialTheme.typography.bodySmall)
                }
                FilledTonalButton(onClick = onChange, modifier = Modifier.fillMaxWidth()) {
                    Icon(Icons.Outlined.PhotoCamera, contentDescription = null)
                    Spacer(Modifier.width(8.dp))
                    Text("Ganti foto")
                }
            }
        },
        confirmButton = { TextButton(onClick = onDismiss) { Text("Tutup") } }
    )
}

@Composable
internal fun ProfilePhotoDialog(url: String, onDismiss: () -> Unit) {
    var retry by remember(url) { mutableIntStateOf(0) }
    Dialog(onDismissRequest = onDismiss, properties = DialogProperties(usePlatformDefaultWidth = false)) {
        Column(Modifier.fillMaxSize().background(Color(0xFF101510)).safeDrawingPadding()) {
            Row(Modifier.fillMaxWidth().padding(8.dp), verticalAlignment = Alignment.CenterVertically) {
                Text("Foto profil", color = Color.White, style = MaterialTheme.typography.titleMedium,
                    modifier = Modifier.weight(1f).padding(start = 8.dp))
                IconButton(onClick = onDismiss) {
                    Icon(Icons.Outlined.Close, contentDescription = "Tutup foto", tint = Color.White)
                }
            }
            key(url, retry) {
                SubcomposeAsyncImage(
                    model = url,
                    contentDescription = "Foto profil ukuran penuh",
                    contentScale = ContentScale.Fit,
                    modifier = Modifier.fillMaxWidth().weight(1f),
                    loading = {
                        Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                            CircularProgressIndicator()
                        }
                    },
                    error = {
                        Column(Modifier.fillMaxSize().padding(24.dp),
                            horizontalAlignment = Alignment.CenterHorizontally,
                            verticalArrangement = Arrangement.Center) {
                            Text("Foto belum dapat dibuka. Periksa koneksi Anda.", color = Color.White)
                            TextButton(onClick = { retry++ }) { Text("Coba lagi") }
                        }
                    }
                )
            }
        }
    }
}
