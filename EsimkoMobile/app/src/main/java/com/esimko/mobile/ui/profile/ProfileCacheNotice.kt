package com.esimko.mobile.ui.profile

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.LinearProgressIndicator
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableLongStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import com.esimko.mobile.domain.model.ProfileCacheStatus
import kotlinx.coroutines.delay

/** Only overlays the screen during refresh/expiry/error; does not alter its cards or spacing. */
@Composable
internal fun ProfileCacheNotice(status: ProfileCacheStatus, onRefresh: () -> Unit, modifier: Modifier = Modifier) {
    var now by remember { mutableLongStateOf(System.currentTimeMillis()) }
    LaunchedEffect(status.updatedAtMillis) {
        now = System.currentTimeMillis()
        while (true) { delay(15_000); now = System.currentTimeMillis() }
    }
    val stale = status.updatedAtMillis?.let { now - it >= 60_000 } == true
    if (status.refreshing || status.error != null || stale) {
        Column(modifier.fillMaxWidth().background(MaterialTheme.colorScheme.surfaceVariant)) {
            if (status.refreshing) LinearProgressIndicator(Modifier.fillMaxWidth())
            Row(Modifier.fillMaxWidth().padding(start = 16.dp, end = 8.dp),
                verticalAlignment = Alignment.CenterVertically,
                horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                Text(
                    when {
                        status.refreshing -> "Memperbarui data… menampilkan data tersimpan."
                        status.error != null -> "Data tersimpan · belum berhasil diperbarui."
                        else -> "Data tersimpan · muat ulang untuk saldo terbaru."
                    },
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                    modifier = Modifier.weight(1f).padding(vertical = 8.dp)
                )
                TextButton(onClick = onRefresh, enabled = !status.refreshing) { Text("Muat ulang") }
            }
        }
    }
}
