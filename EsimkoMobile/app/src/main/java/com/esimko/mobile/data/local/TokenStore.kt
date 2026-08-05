package com.esimko.mobile.data.local

import android.content.Context
import androidx.security.crypto.EncryptedSharedPreferences
import androidx.security.crypto.MasterKey
import dagger.hilt.android.qualifiers.ApplicationContext
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class TokenStore @Inject constructor(
    @ApplicationContext private val context: Context
) {
    private val prefs by lazy {
        val masterKey = MasterKey.Builder(context)
            .setKeyScheme(MasterKey.KeyScheme.AES256_GCM)
            .build()
        EncryptedSharedPreferences.create(
            context,
            "esimko_secure_prefs",
            masterKey,
            EncryptedSharedPreferences.PrefKeyEncryptionScheme.AES256_SIV,
            EncryptedSharedPreferences.PrefValueEncryptionScheme.AES256_GCM
        )
    }

    var token: String?
        get() = prefs.getString("auth_token", null)
        set(value) { prefs.edit().putString("auth_token", value).apply() }

    var noAnggota: String?
        get() = prefs.getString("no_anggota", null)
        set(value) { prefs.edit().putString("no_anggota", value).apply() }

    fun clear() {
        prefs.edit().clear().apply()
    }

    fun isLoggedIn(): Boolean = token != null
}
