package com.esimko.mobile.ui.account

import androidx.compose.runtime.Composable
import com.esimko.mobile.ui.profile.ProfileScreen

// ponytail: pembungkus sementara di atas ProfileScreen lama. Task 26 mengganti isinya
// (divisi/bagian/status anggota, baris daftar, pesan error password).
@Composable
fun AccountTab(
    onLogout: () -> Unit = {},
    onOpenSettings: () -> Unit = {}
) {
    ProfileScreen(onLogout = onLogout, onOpenSettings = onOpenSettings)
}
