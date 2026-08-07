package com.esimko.mobile.ui.navigation

import androidx.compose.runtime.Composable
import androidx.navigation.NavHostController
import androidx.navigation.NavType
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.navArgument
import com.esimko.mobile.ui.auth.login.LoginScreen
import com.esimko.mobile.ui.auth.register.RegisterScreen
import com.esimko.mobile.ui.home.HomeScreen
import com.esimko.mobile.ui.installment.InstallmentScreen
import com.esimko.mobile.ui.installment.LoanApplicationScreen
import com.esimko.mobile.ui.news.NewsListScreen
import com.esimko.mobile.ui.news.NewsDetailScreen
import com.esimko.mobile.ui.shopping.ShoppingDetailScreen
import com.esimko.mobile.ui.shopping.CartScreen
import com.esimko.mobile.ui.settings.SettingsScreen
import com.esimko.mobile.ui.activity.ActivityDetailScreen
import com.esimko.mobile.ui.home.SavingsTab
import com.esimko.mobile.ui.savings.SavingsFormScreen

@Composable
fun EsimkoNavHost(
    navController: NavHostController,
    startDestination: String = "login",
    onLogout: () -> Unit = {}
) {
    NavHost(
        navController = navController,
        startDestination = startDestination
    ) {
        composable("login") {
            LoginScreen(
                onLoginSuccess = {
                    navController.navigate("home") {
                        popUpTo("login") { inclusive = true }
                    }
                },
                onNavigateToRegister = {
                    navController.navigate("register")
                }
            )
        }

        composable("register") {
            RegisterScreen(
                onRegisterSuccess = {
                    navController.navigate("login") {
                        popUpTo("register") { inclusive = true }
                    }
                },
                onBackToLogin = {
                    navController.popBackStack()
                }
            )
        }

        composable("home") {
            HomeScreen(
                onLogout = {
                    onLogout()
                    navController.navigate("login") {
                        popUpTo("home") { inclusive = true }
                    }
                },
                onNavigateRoot = { route -> navController.navigate(route) }
            )
        }

        composable("savings") {
            SavingsTab(
                onOpenHistory = { id, modul -> navController.navigate("history/$modul/$id") },
                onOpenForm = { jenis -> navController.navigate("savings_form/$jenis") }
            )
        }
        composable(
            route = "savings_form/{jenis}",
            arguments = listOf(navArgument("jenis") { type = NavType.StringType })
        ) { entry ->
            SavingsFormScreen(
                jenis = entry.arguments?.getString("jenis") ?: "setoran",
                onBack = { navController.popBackStack() },
                onDone = { navController.popBackStack() }
            )
        }

        composable(
            route = "history/{module}/{id}",
            arguments = listOf(
                navArgument("module") { type = NavType.StringType },
                navArgument("id") { type = NavType.LongType }
            )
        ) { entry ->
            ActivityDetailScreen(
                transactionId = entry.arguments?.getLong("id") ?: 0L,
                modul = entry.arguments?.getString("module") ?: "transaksi",
                onBack = { navController.popBackStack() }
            )
        }

        composable("settings") {
            SettingsScreen(
                onBack = { navController.popBackStack() }
            )
        }

        composable("installment") {
            InstallmentScreen(
                onBack = { navController.popBackStack() },
                onApplyLoan = { navController.navigate("installment/apply") }
            )
        }

        composable("installment/apply") {
            LoanApplicationScreen(
                onBack = { navController.popBackStack() },
                onSubmitted = {
                    navController.popBackStack()
                }
            )
        }

        composable("news") {
            NewsListScreen(
                onBack = { navController.popBackStack() },
                onNewsClick = { newsId ->
                    navController.navigate("news/$newsId")
                }
            )
        }

        composable("shopping_detail/{kode}") { entry ->
            ShoppingDetailScreen(
                productKode = entry.arguments?.getString("kode").orEmpty(),
                onBack = { navController.popBackStack() },
                onGoToCart = { navController.navigate("cart") }
            )
        }

        composable("cart") {
            CartScreen(
                onBack = { navController.popBackStack() }
            )
        }

        composable(
            route = "news/{newsId}",
            arguments = listOf(navArgument("newsId") { type = NavType.LongType })
        ) { entry ->
            NewsDetailScreen(
                newsId = entry.arguments?.getLong("newsId") ?: 0,
                onBack = { navController.popBackStack() }
            )
        }
    }
}
