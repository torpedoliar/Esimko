package com.esimko.mobile.ui.common

import android.app.Activity
import android.content.Context
import android.content.ContextWrapper
import androidx.compose.runtime.Composable
import androidx.compose.runtime.DisposableEffect
import androidx.compose.runtime.SideEffect
import androidx.compose.ui.platform.LocalView
import androidx.core.view.WindowCompat

/** Ikon gelap untuk permukaan terang; pulihkan warna hero saat layar ditutup. */
@Composable
internal fun StatusBarAppearance(darkIcons: Boolean) {
    val view = LocalView.current
    if (view.isInEditMode) return
    val activity = view.context.findActivity() ?: return
    val window = activity.window
    val controller = WindowCompat.getInsetsController(window, view)
    DisposableEffect(window, view) {
        val previous = controller.isAppearanceLightStatusBars
        onDispose { controller.isAppearanceLightStatusBars = previous }
    }
    SideEffect { controller.isAppearanceLightStatusBars = darkIcons }
}

private fun Context.findActivity(): Activity? = when (this) {
    is Activity -> this
    is ContextWrapper -> baseContext.findActivity()
    else -> null
}
