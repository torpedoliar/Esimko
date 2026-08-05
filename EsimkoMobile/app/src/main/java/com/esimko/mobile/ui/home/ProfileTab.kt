package com.esimko.mobile.ui.home

import androidx.compose.runtime.Composable
import com.esimko.mobile.ui.profile.ProfileScreen

@Composable
fun ProfileTab(onLogout: () -> Unit = {}) {
    ProfileScreen(
        onLogout = onLogout
    )
}
