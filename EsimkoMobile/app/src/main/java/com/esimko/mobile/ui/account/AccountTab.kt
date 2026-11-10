package com.esimko.mobile.ui.account

import androidx.compose.runtime.Composable
import com.esimko.mobile.ui.profile.ProfileScreen

@Composable
fun AccountTab(
    onLogout: () -> Unit = {},
    onOpenSettings: () -> Unit = {},
    onOpenInstallments: () -> Unit = {}
) {
    ProfileScreen(
        onLogout = onLogout,
        onOpenSettings = onOpenSettings,
        onOpenInstallments = onOpenInstallments
    )
}
