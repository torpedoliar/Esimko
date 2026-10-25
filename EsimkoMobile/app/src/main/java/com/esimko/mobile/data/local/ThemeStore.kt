package com.esimko.mobile.data.local

import android.content.Context

/** Mode tampilan pilihan anggota. */
enum class ThemeMode { SYSTEM, LIGHT, DARK }

/**
 * Preferensi tema — SharedPreferences biasa (bukan data sensitif, tak perlu enkripsi).
 * Disimpan per-pilihan, dipakai EsimkoApp sebagai sumber kebenaran global.
 */
class ThemeStore(context: Context) {
    private val prefs = context.getSharedPreferences("esimko_theme", Context.MODE_PRIVATE)

    var mode: ThemeMode
        get() = when (prefs.getString(KEY, null)) {
            "LIGHT" -> ThemeMode.LIGHT
            "DARK" -> ThemeMode.DARK
            else -> ThemeMode.SYSTEM
        }
        set(value) {
            prefs.edit().putString(KEY, value.name).apply()
        }

    companion object {
        private const val KEY = "mode"
    }
}
