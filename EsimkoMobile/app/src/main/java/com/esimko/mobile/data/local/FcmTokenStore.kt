package com.esimko.mobile.data.local

import android.content.Context

/** Token perangkat FCM — disimpan lokal; di-register ke backend saat login berhasil. */
object FcmTokenStore {
    private const val PREFS = "esimko_fcm"
    private const val KEY_TOKEN = "token"

    fun save(context: Context, token: String) {
        context.getSharedPreferences(PREFS, Context.MODE_PRIVATE)
            .edit().putString(KEY_TOKEN, token).apply()
    }

    fun get(context: Context): String? =
        context.getSharedPreferences(PREFS, Context.MODE_PRIVATE).getString(KEY_TOKEN, null)
}