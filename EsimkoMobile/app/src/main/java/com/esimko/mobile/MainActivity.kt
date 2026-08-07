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
import com.esimko.mobile.ui.navigation.EsimkoNavHost
import com.esimko.mobile.ui.theme.EsimkoTheme
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        WindowCompat.setDecorFitsSystemWindows(window, false)
        setContent {
            EsimkoTheme {
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

                EsimkoNavHost(
                    navController = navController,
                    startDestination = if (loggedIn) "home" else "login",
                    onLogout = { vm.logout() }
                )
            }
        }
    }
}
