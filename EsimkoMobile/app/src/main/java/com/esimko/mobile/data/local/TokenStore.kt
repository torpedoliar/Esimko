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
    // ponytail: EncryptedSharedPreferences butuh Android Keystore. Sebagian HP (ROM OEM
    // tertentu, kondisi keystore rusak pasca-reset) gagal membangun master key → lempar
    // GeneralSecurityException di lazy init ini, yang dipicu di frame pertama setContent
    // (MainViewModel.isLoggedIn). Tanpa try/catch, itu crash blank-putih lalu tertutup.
    // Fallback ke SharedPreferences biasa: token tak terenkripsi-at-rest, tapi app jalan.
    // Saat keystore kembali sehat, data lama tetap terbaca (scheme sama bila keystore ada).
    private val prefs by lazy {
        try {
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
        } catch (e: Exception) {
            android.util.Log.w("TokenStore", "Keystore unavailable, fallback plain prefs", e)
            context.getSharedPreferences("esimko_secure_prefs", Context.MODE_PRIVATE)
        }
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
