package com.esimko.mobile.ui.home

import androidx.compose.foundation.layout.padding
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.AccountCircle
import androidx.compose.material.icons.outlined.Home
import androidx.compose.material.icons.outlined.Receipt
import androidx.compose.material.icons.outlined.Storefront
import androidx.compose.material3.Icon
import androidx.compose.material3.NavigationBar
import androidx.compose.material3.NavigationBarItem
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.navigation.NavType
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.currentBackStackEntryAsState
import androidx.navigation.compose.rememberNavController
import androidx.navigation.navArgument
import com.esimko.mobile.BuildConfig
import com.esimko.mobile.ui.account.AccountTab
import com.esimko.mobile.ui.activity.ActivityTab
import com.esimko.mobile.ui.shopping.ShoppingViewModel

private enum class Tab(val label: String, val icon: ImageVector, val route: String) {
    BERANDA("Beranda", Icons.Outlined.Home, "beranda"),
    AKTIVITAS("Aktivitas", Icons.Outlined.Receipt, "aktivitas"),
    BELANJA("Belanja", Icons.Outlined.Storefront, "belanja"),
    AKUN("Akun", Icons.Outlined.AccountCircle, "akun")
}

@Composable
fun HomeScreen(
    onLogout: () -> Unit = {},
    onNavigateRoot: (String) -> Unit = {}
) {
    val tabNav = rememberNavController()
    val entry by tabNav.currentBackStackEntryAsState()
    // Cocokkan lewat prefix: rute "aktivitas?filter=retur" harus tetap menyorot tab AKTIVITAS
    val current = entry?.destination?.route?.substringBefore('?')

    // ponytail: satu ShoppingViewModel untuk area tab; CartScreen di NavHost akar tetap punya
    // instance sendiri dan disinkronkan dengan loadCart() saat Belanja aktif kembali.
    // Kalau nanti butuh badge yang selalu sinkron lintas NavHost, pindahkan keranjang ke
    // repository ber-state (StateFlow) — bukan menambah CompositionLocal.
    // ENABLE_BELANJA=false → VM tidak di-request (Hilt tidak instantiate), tab Belanja hidden.
    val cartViewModel: ShoppingViewModel? = if (BuildConfig.ENABLE_BELANJA) hiltViewModel() else null
    val tabs = Tab.values().filter { it != Tab.BELANJA || BuildConfig.ENABLE_BELANJA }

    Scaffold(
        bottomBar = {
            NavigationBar {
                tabs.forEach { tab ->
                    NavigationBarItem(
                        selected = current == tab.route,
                        onClick = {
                            // ponytail: navigation-compose 2.7.7 — saveState+restoreState+launchSingleTop
                            // menyorot tab benar tapi kadang tidak recompose isi (entry lama dipertahankan).
                            // popBackStack ke start lalu navigate polos menjamin tab baru benar-benar tampil.
                            // State scroll hilang; ditukar dengan keandalan. Balik ke saveState bila versi
                            // navigation-compose sudah stabil terhadap bug ini.
                            if (current != tab.route) {
                                tabNav.popBackStack(
                                    tabNav.graph.startDestinationId,
                                    inclusive = false
                                )
                                tabNav.navigate(tab.route) {
                                    launchSingleTop = true
                                }
                            }
                        },
                        icon = { Icon(tab.icon, contentDescription = null) },
                        label = { Text(tab.label) },
                        alwaysShowLabel = true
                    )
                }
            }
        }
    ) { padding ->
        NavHost(
            navController = tabNav,
            startDestination = Tab.BERANDA.route,
            modifier = Modifier.padding(bottom = padding.calculateBottomPadding())
        ) {
            composable(Tab.BERANDA.route) {
                DashboardTab(
                    onNavigate = { route ->
                        if (route.substringBefore('?') in Tab.values().map { it.route }) {
                            tabNav.navigate(route) { launchSingleTop = true }
                        } else {
                            onNavigateRoot(route)
                        }
                    },
                    onOpenNewsDetail = { id -> onNavigateRoot("news/$id") }
                )
            }
            composable(
                route = "${Tab.AKTIVITAS.route}?filter={filter}",
                arguments = listOf(navArgument("filter") {
                    type = NavType.StringType
                    nullable = true
                    defaultValue = null
                })
            ) { e ->
                ActivityTab(
                    initialFilter = e.arguments?.getString("filter"),
                    onOpenDetail = { id, modul -> onNavigateRoot("history/$modul/$id") }
                )
            }
            if (BuildConfig.ENABLE_BELANJA) {
                composable(Tab.BELANJA.route) {
                    // cartViewModel non-null di blok ini (flag true → hiltViewModel dipanggil).
                    ShoppingTab(
                        onOpenProduct = { kode -> onNavigateRoot("shopping_detail/$kode") },
                        onOpenCart = { onNavigateRoot("cart") },
                        viewModel = cartViewModel!!
                    )
                }
            }
            composable(Tab.AKUN.route) {
                AccountTab(
                    onLogout = onLogout,
                    onOpenSettings = { onNavigateRoot("settings") }
                )
            }
        }
    }
}
