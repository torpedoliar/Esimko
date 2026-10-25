package com.esimko.mobile

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.core.view.WindowCompat
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.compose.rememberNavController
import com.esimko.mobile.data.local.AuthEvents
import com.esimko.mobile.data.local.ThemeMode
import com.esimko.mobile.ui.navigation.EsimkoNavHost
import com.esimko.mobile.ui.theme.EsimkoTheme
import com.esimko.mobile.ui.update.UpdateGate
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        WindowCompat.setDecorFitsSystemWindows(window, false)
        setContent {
            // Mode tampilan: Ikuti Sistem / Terang / Gelap — setelan anggota (Pengaturan).
            val app = applicationContext as EsimkoApp
            val themeMode by app.themeMode.collectAsStateWithLifecycle()
            val systemDark = androidx.compose.foundation.isSystemInDarkTheme()
            val darkTheme = when (themeMode) {
                ThemeMode.LIGHT -> false
                ThemeMode.DARK -> true
                ThemeMode.SYSTEM -> systemDark
            }
            EsimkoTheme(darkTheme = darkTheme) {
                val vm: MainViewModel = viewModel()
                val loggedIn by vm.isLoggedIn.collectAsStateWithLifecycle()
                val navController = rememberNavController()

                // Sesi invalid (401 dari interceptor) → paksa balik ke login
                LaunchedEffect(AuthEvents) {
                    AuthEvents.loggedOut.collect {
                        navController.navigate("login") {
                            popUpTo(0) { inclusive = true }
                        }
                    }
                }

                UpdateGate {
                    EsimkoNavHost(
                        navController = navController,
                        startDestination = if (loggedIn) "home" else "login",
                        onLogout = { vm.logout() }
                    )
                }
            }
        }
    }
}
