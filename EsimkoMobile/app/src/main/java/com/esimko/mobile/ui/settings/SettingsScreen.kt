package com.esimko.mobile.ui.settings

import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.automirrored.filled.ArrowForward
import androidx.compose.material.icons.filled.SystemUpdate
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.material3.TopAppBar
import androidx.compose.runtime.Composable
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import com.esimko.mobile.BuildConfig
import com.esimko.mobile.ui.common.EsimkoPreview
import com.esimko.mobile.ui.common.LightDarkPreview
import com.esimko.mobile.ui.common.ListRow
import com.esimko.mobile.ui.common.RowDivider
import com.esimko.mobile.ui.common.SectionHeader

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun SettingsScreen(
    onBack: () -> Unit = {},
    viewModel: SettingsViewModel = hiltViewModel()
) {
    val state by viewModel.state.collectAsState()

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Pengaturan") },
                navigationIcon = {
                    IconButton(onClick = onBack) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Kembali")
                    }
                }
            )
        }
    ) { padding ->
        Column(
            Modifier.fillMaxSize()
                .padding(padding)
                .verticalScroll(rememberScrollState())
        ) {
            // Informasi Aplikasi
            SectionHeader(title = "Informasi Aplikasi", modifier = Modifier.padding(top = 16.dp))
            ListRow(title = "Versi", trailing = { Text(BuildConfig.VERSION_NAME) })
            RowDivider()
            ListRow(title = "Build", trailing = { Text(BuildConfig.VERSION_CODE.toString()) })

            // Periksa Pembaruan
            SectionHeader(title = "Periksa Pembaruan", modifier = Modifier.padding(top = 24.dp))
            ListRow(
                title = if (state.checking) "Memeriksa..." else "Cek Versi",
                leading = { Icon(Icons.Default.SystemUpdate, contentDescription = null) },
                trailing = {
                    if (state.checking) {
                        CircularProgressIndicator(Modifier.size(20.dp), strokeWidth = 2.dp)
                    } else {
                        Icon(
                            Icons.AutoMirrored.Filled.ArrowForward,
                            contentDescription = null,
                            tint = MaterialTheme.colorScheme.onSurfaceVariant
                        )
                    }
                },
                onClick = { if (!state.checking) viewModel.checkVersion() }
            )
            state.error?.let { err ->
                Text(
                    text = err,
                    color = MaterialTheme.colorScheme.error,
                    style = MaterialTheme.typography.bodySmall,
                    modifier = Modifier.padding(horizontal = 16.dp, vertical = 8.dp)
                )
            }
            state.versionInfo?.let { info ->
                Text(
                    text = when {
                        info.forceUpdate -> "Versi usang. Pembaruan wajib: ${info.message ?: ""}"
                        info.updateAvailable -> "Pembaruan tersedia: ${info.message ?: ""}"
                        else -> "Aplikasi sudah versi terbaru."
                    },
                    style = MaterialTheme.typography.bodyMedium,
                    color = MaterialTheme.colorScheme.primary,
                    modifier = Modifier.padding(horizontal = 16.dp, vertical = 8.dp)
                )
            }

            // Tentang
            SectionHeader(title = "Tentang", modifier = Modifier.padding(top = 24.dp))
            ListRow(title = "eSIMKO Mobile", subtitle = "Aplikasi mobile untuk koperasi SIMKO")
            Spacer(Modifier.height(24.dp))
        }
    }
}

@LightDarkPreview
@Composable
private fun SettingsScreenPreview() {
    EsimkoPreview {
        Column(Modifier.fillMaxWidth().verticalScroll(rememberScrollState())) {
            SectionHeader(title = "Informasi Aplikasi", modifier = Modifier.padding(top = 16.dp))
            ListRow(title = "Versi", trailing = { Text("1.0.0") })
            RowDivider()
            ListRow(title = "Build", trailing = { Text("1") })
            SectionHeader(title = "Periksa Pembaruan", modifier = Modifier.padding(top = 24.dp))
            ListRow(
                title = "Cek Versi",
                leading = { Icon(Icons.Default.SystemUpdate, contentDescription = null) },
                trailing = {
                    Icon(
                        Icons.AutoMirrored.Filled.ArrowForward,
                        contentDescription = null,
                        tint = MaterialTheme.colorScheme.onSurfaceVariant
                    )
                },
                onClick = {}
            )
            SectionHeader(title = "Tentang", modifier = Modifier.padding(top = 24.dp))
            ListRow(title = "eSIMKO Mobile", subtitle = "Aplikasi mobile untuk koperasi SIMKO")
        }
    }
}
