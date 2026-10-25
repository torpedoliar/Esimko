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
        pasangPenangkapCrash()
    }

    /**
     * Semua crash tak tertangani dicatat ke files/crash_terakhir.txt (bisa dibaca
     * via adb/logs berikutnya untuk diagnosis) lalu diteruskan ke handler bawaan.
     */
    private fun pasangPenangkapCrash() {
        val bawaan = Thread.getDefaultUncaughtExceptionHandler()
        Thread.setDefaultUncaughtExceptionHandler { thread, throwable ->
            try {
                java.io.File(filesDir, "crash_terakhir.txt").writeText(
                    buildString {
                        append(java.text.SimpleDateFormat("yyyy-MM-dd HH:mm:ss", java.util.Locale.US)
                            .format(java.util.Date()))
                        append("\nversi: ").append(android.util.Log.getStackTraceString(throwable))
                    }
                )
            } catch (_: Exception) {
            }
            bawaan?.uncaughtException(thread, throwable)
        }
    }

    fun setThemeMode(mode: ThemeMode) {
        themeStore.mode = mode
        themeMode.value = mode
    }
}
