package com.esimko.mobile.ui.home

import androidx.compose.foundation.layout.*
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.AccountBalance
import androidx.compose.material.icons.filled.History
import androidx.compose.material.icons.filled.Home
import androidx.compose.material.icons.filled.Person
import androidx.compose.material.icons.filled.ShoppingCart
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.vector.ImageVector

enum class BottomNavItem(val label: String, val icon: ImageVector, val route: String) {
    HOME("Home", Icons.Default.Home, "dashboard"),
    SAVINGS("Simpanan", Icons.Default.AccountBalance, "savings"),
    SHOPPING("Belanja", Icons.Default.ShoppingCart, "shopping"),
    HISTORY("Riwayat", Icons.Default.History, "history"),
    PROFILE("Profil", Icons.Default.Person, "profile")
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun HomeScreen(onLogout: () -> Unit = {}) {
    var selectedTab by remember { mutableStateOf(BottomNavItem.HOME) }

    Scaffold(
        bottomBar = {
            NavigationBar {
                BottomNavItem.entries.forEach { item ->
                    NavigationBarItem(
                        icon = { Icon(item.icon, contentDescription = item.label) },
                        label = { Text(item.label) },
                        selected = selectedTab == item,
                        onClick = { selectedTab = item }
                    )
                }
            }
        }
    ) { paddingValues ->
        Box(modifier = Modifier.padding(paddingValues)) {
            when (selectedTab) {
                BottomNavItem.HOME -> DashboardTab()
                BottomNavItem.SAVINGS -> SavingsTab()
                BottomNavItem.SHOPPING -> ShoppingTab()
                BottomNavItem.HISTORY -> com.esimko.mobile.ui.history.HistoryTab()
                BottomNavItem.PROFILE -> ProfileTab(onLogout = onLogout)
            }
        }
    }
}
