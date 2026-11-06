package com.esimko.mobile.domain.model

import com.squareup.moshi.Json
import com.squareup.moshi.JsonClass

enum class NotificationType { UPDATE, STATUS, INFO }

@JsonClass(generateAdapter = true)
data class AppNotification(
    val id: String,
    val type: NotificationType,
    val title: String,
    val message: String,
    val createdAt: Long,
    val read: Boolean = false,
    val refId: Long? = null
)

/**
 * Pemetaan notifikasi → rute navigasi. Satu-satunya tempat string rute dibuat,
 * supaya layar riwayat tidak menulis route inline dan targetnya konsisten
 * dengan EsimkoNavHost (`history/{module}/{id}`, `settings`, `news/{id}`).
 */
fun notificationRoute(type: NotificationType, refId: Long?): String? = when (type) {
    NotificationType.UPDATE -> "settings"
    NotificationType.STATUS -> if (refId != null) "history/transaksi/$refId" else null
    NotificationType.INFO -> if (refId != null) "news/$refId" else "news"
}