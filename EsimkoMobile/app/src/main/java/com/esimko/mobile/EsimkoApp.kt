package com.esimko.mobile

import android.app.Application
import com.esimko.mobile.data.local.ThemeMode
import com.esimko.mobile.data.local.ThemeStore
import dagger.hilt.android.HiltAndroidApp
import kotlinx.coroutines.flow.MutableStateFlow

@HiltAndroidApp
class EsimkoApp : Application() {

    private lateinit var themeStore: ThemeStore

    /** Mode tampilan aktif — dibaca MainActivity saat menyusun EsimkoTheme. */
    val themeMode = MutableStateFlow(ThemeMode.SYSTEM)

    override fun onCreate() {
        super.onCreate()
        themeStore = ThemeStore(this)
        themeMode.value = themeStore.mode
    }

    fun setThemeMode(mode: ThemeMode) {
        themeStore.mode = mode
        themeMode.value = mode
    }
}
