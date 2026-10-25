package com.esimko.mobile.data.local

import android.content.Context
import android.content.SharedPreferences
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
    // (MainViewModel.isLoggedIn). Fallback ke SharedPreferences biasa.
    //
    // ponytail #2 (v1.0.7): pembacaan nilai LAMA juga bisa melempar — bila master key
    // berubah (update via installer/package-installer di ROM OEM tertentu memutar kunci),
    // getString → AEADBadTagException → force close TIAP buka app; hanya "hapus data"
    // yang menyembuhkan. Karena itu SEMUA akses baca/tulis dijaga; bila gagal → sesi
    // direset (file prefs dihapus + fallback plain) → anggota tinggal login ulang.
    @Volatile
    private var gunaPlain = false

    private val encrypted: SharedPreferences by lazy {
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

    private val plain: SharedPreferences by lazy {
        context.getSharedPreferences("esimko_secure_prefs", Context.MODE_PRIVATE)
    }

    private val prefs: SharedPreferences
        get() = try {
            if (gunaPlain) plain else encrypted
        } catch (e: Exception) {
            android.util.Log.w("TokenStore", "Keystore unavailable, fallback plain prefs", e)
            gunaPlain = true
            plain
        }

    /** Nilai lama tak terdekripsi (kunci berubah) → buang file, lanjut plain. */
    private fun pulihkan(penyebab: Exception) {
        android.util.Log.w("TokenStore", "Prefs tak terbaca — reset sesi (login ulang diperlukan)", penyebab)
        gunaPlain = true
        try {
            context.deleteSharedPreferences("esimko_secure_prefs")
        } catch (_: Exception) {
        }
    }

    var token: String?
        get() = try {
            prefs.getString("auth_token", null)
        } catch (e: Exception) {
            pulihkan(e)
            null
        }
        set(value) {
            try {
                prefs.edit().putString("auth_token", value).apply()
            } catch (e: Exception) {
                pulihkan(e)
                plain.edit().putString("auth_token", value).apply()
            }
        }

    var noAnggota: String?
        get() = try {
            prefs.getString("no_anggota", null)
        } catch (e: Exception) {
            pulihkan(e)
            null
        }
        set(value) {
            try {
                prefs.edit().putString("no_anggota", value).apply()
            } catch (e: Exception) {
                pulihkan(e)
                plain.edit().putString("no_anggota", value).apply()
            }
        }

    fun clear() {
        try {
            prefs.edit().clear().apply()
        } catch (e: Exception) {
            pulihkan(e)
        }
    }

    fun isLoggedIn(): Boolean = token != null
}
