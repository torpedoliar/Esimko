package com.esimko.mobile.data.local

import android.content.Context
import com.esimko.mobile.domain.model.AppNotification
import com.squareup.moshi.Moshi
import com.squareup.moshi.Types

/** Penyimpanan riwayat notifikasi (SharedPreferences + JSON via Moshi). */
class NotificationStore(context: Context) {
    private val prefs = context.getSharedPreferences("esimko_notifications", Context.MODE_PRIVATE)
    private val moshi = Moshi.Builder().build()
    private val listType = Types.newParameterizedType(List::class.java, AppNotification::class.java)
    private val adapter = moshi.adapter<List<AppNotification>>(listType)

    fun load(): List<AppNotification> = try {
        adapter.fromJson(prefs.getString(KEY, "[]") ?: "[]") ?: emptyList()
    } catch (e: Exception) {
        emptyList()
    }

    fun save(list: List<AppNotification>) {
        try {
            prefs.edit().putString(KEY, adapter.toJson(list)).apply()
        } catch (_: Exception) {
        }
    }

    companion object {
        private const val KEY = "items"
    }
}