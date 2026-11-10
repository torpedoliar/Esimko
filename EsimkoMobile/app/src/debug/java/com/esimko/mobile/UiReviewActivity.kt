package com.esimko.mobile

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import com.esimko.mobile.domain.model.Profile
import com.esimko.mobile.domain.model.Transaction
import com.esimko.mobile.ui.activity.*
import com.esimko.mobile.ui.common.UiState
import com.esimko.mobile.ui.profile.ProfileContent
import com.esimko.mobile.ui.theme.EsimkoTheme

/** Offline visual fixture. Debug source set only; no repository or production API calls. */
class UiReviewActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        val dark = intent.getBooleanExtra("dark", false)
        setContent {
            EsimkoTheme(darkTheme = dark) {
                var account by remember { mutableStateOf(true) }
                var hideBalance by remember { mutableStateOf(false) }
                var photoOptions by remember { mutableStateOf(false) }
                var photoPreview by remember { mutableStateOf(false) }
                val photo = "android.resource://com.esimko.mobile/${R.drawable.logo_esimko_wordmark}"
                if (photoOptions) com.esimko.mobile.ui.profile.ProfilePhotoOptions(
                    hasPhoto = true,
                    onOpen = { photoOptions = false; photoPreview = true },
                    onChange = { photoOptions = false },
                    onDismiss = { photoOptions = false }
                )
                if (photoPreview) com.esimko.mobile.ui.profile.ProfilePhotoDialog(photo, onDismiss = { photoPreview = false })
                com.esimko.mobile.ui.common.StatusBarAppearance(darkIcons = !dark)
                Surface {
                    Column(Modifier.fillMaxSize().statusBarsPadding().navigationBarsPadding()) {
                        Row {
                            TextButton(onClick = { account = true }) { Text("Akun contoh") }
                            TextButton(onClick = { account = false }) { Text("Aktivitas contoh") }
                        }
                        if (account) {
                            ProfileContent(
                                profile = Profile(
                                    "K 0000", "Anggota Contoh Koperasi", "3201234567890001",
                                    "Jl. Contoh No. 10, Jakarta", "081234567890", "anggota@example.invalid", null,
                                    12500000, 3000000, 500000,
                                    saldoSimpananPokok = 500000, saldoSimpananWajib = 2000000,
                                    saldoSimpananSukarela = 8000000, saldoSimpananHariRaya = 2000000,
                                    statusAnggota = "Aktif", divisi = "Operasional", bagian = "Administrasi"
                                ),
                                avatarUploadState = UiState.Idle,
                                onPickAvatar = { photoOptions = true }, onOpenSettings = {}, onOpenInstallments = {},
                                onChangePassword = {}, onLogout = {},
                                hideBalance = hideBalance, onToggleBalance = { hideBalance = !hideBalance }
                            )
                        } else {
                            var state by remember { mutableStateOf(ActivityState(isFirstLoad = false)) }
                            Column(Modifier.verticalScroll(rememberScrollState())) {
                                Text("Aktivitas", style = MaterialTheme.typography.headlineMedium, modifier = Modifier.padding(20.dp))
                                ActivityFilters(state,
                                    { state = state.copy(segment = it) },
                                    { state = state.copy(selectedStatusId = it) },
                                    { state = state.copy(search = it) },
                                    { state = state.copy(sortTerbaru = !state.sortTerbaru) })
                                listOf(
                                    Transaction(1, "Simpanan Sukarela", "transaksi", 1000000, "2026-11-09", "Belum Verifikasi", "Belum Verifikasi", null, "#e67e22", "+Rp 1.000.000"),
                                    Transaction(2, "Penarikan Simpanan Sukarela", "transaksi", 250000, "2026-11-08", "Dibatalkan", "Dibatalkan", "Pengajuan dibatalkan", "#95a5a6", "-Rp 250.000", true),
                                    Transaction(3, "Pinjaman Jangka Panjang", "transaksi", 15000000, "2026-11-07", "Disetujui", "Disetujui", null, "#2ea3cc", "Rp 15.000.000")
                                ).forEach { transaction ->
                                    ActivityCard(transaction, {}, Modifier.padding(horizontal = 16.dp, vertical = 5.dp))
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
