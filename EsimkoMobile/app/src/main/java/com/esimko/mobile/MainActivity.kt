package com.esimko.mobile

import android.Manifest
import android.content.pm.PackageManager
import android.os.Build
import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.activity.result.contract.ActivityResultContracts
import androidx.compose.runtime.DisposableEffect
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.core.content.ContextCompat
import androidx.core.view.WindowCompat
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.compose.rememberNavController
import com.esimko.mobile.data.local.TokenStore
import com.esimko.mobile.data.local.FcmTokenStore
import com.esimko.mobile.data.local.ThemeMode
import com.esimko.mobile.domain.repository.DeviceRepository
import com.esimko.mobile.ui.navigation.EsimkoNavHost
import com.esimko.mobile.ui.theme.EsimkoTheme
import com.esimko.mobile.ui.update.UpdateGate
import com.google.firebase.messaging.FirebaseMessaging
import dagger.hilt.android.AndroidEntryPoint
import javax.inject.Inject
import kotlinx.coroutines.launch

@AndroidEntryPoint
class MainActivity : ComponentActivity() {

    @Inject
    lateinit var deviceRepository: DeviceRepository

    @Inject
    lateinit var tokenStore: TokenStore

    private val requestNotifPermission =
        registerForActivityResult(ActivityResultContracts.RequestPermission()) { }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        mintaIzinNotifikasi()
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
                val sessionRevision by vm.sessionChanges.collectAsStateWithLifecycle()
                val navController = rememberNavController()

                // State, not a lossy one-shot event: 401 while stopped is handled on resume too.
                LaunchedEffect(sessionRevision) {
                    if (!tokenStore.isLoggedIn() && sessionRevision != 0L) {
                        navController.navigate("login") {
                            popUpTo(0) { inclusive = true }
                        }
                    } else if (tokenStore.isLoggedIn()) {
                        daftarkanToken()
                    }
                }

                // Cadangan: kalau token FCM baru tersedia SETELAH login (perangkat baru
                // sering begini), daftarkan lagi saat aplikasi kembali ke depan.
                val lifecycleOwner = androidx.lifecycle.compose.LocalLifecycleOwner.current
                DisposableEffect(lifecycleOwner, loggedIn) {
                    val observer = androidx.lifecycle.LifecycleEventObserver { _, event ->
                        if (event == androidx.lifecycle.Lifecycle.Event.ON_RESUME && loggedIn) {
                            daftarkanToken()
                        }
                    }
                    lifecycleOwner.lifecycle.addObserver(observer)
                    onDispose { lifecycleOwner.lifecycle.removeObserver(observer) }
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

    private fun mintaIzinNotifikasi() {
        if (Build.VERSION.SDK_INT >= 33 &&
            ContextCompat.checkSelfPermission(this, Manifest.permission.POST_NOTIFICATIONS) != PackageManager.PERMISSION_GRANTED
        ) {
            requestNotifPermission.launch(Manifest.permission.POST_NOTIFICATIONS)
        }
    }

    private fun daftarkanToken() {
        val session = tokenStore.currentSession() ?: return
        FirebaseMessaging.getInstance().token.addOnCompleteListener { task ->
            val token = if (task.isSuccessful) task.result else FcmTokenStore.get(applicationContext)
            token?.let { t ->
                FcmTokenStore.save(applicationContext, t)
                lifecycleScope.launch {
                    deviceRepository.registerToken(t, session.revision)
                }
            }
        }
    }
}
