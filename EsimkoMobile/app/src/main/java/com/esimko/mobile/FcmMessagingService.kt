package com.esimko.mobile

import android.app.NotificationChannel
import android.app.NotificationManager
import android.content.Context
import androidx.core.app.NotificationCompat
import androidx.core.app.NotificationManagerCompat
import com.esimko.mobile.data.local.FcmTokenStore
import com.google.firebase.messaging.FirebaseMessagingService
import com.google.firebase.messaging.RemoteMessage

class FcmMessagingService : FirebaseMessagingService() {

    override fun onNewToken(token: String) {
        FcmTokenStore.save(this, token)
    }

    override fun onMessageReceived(message: RemoteMessage) {
        // Latar belakang: sistem menampilkan otomatis (notification payload).
        // Foreground: tampilkan sendiri.
        val title = message.notification?.title ?: message.data["title"] ?: "eSIMKO"
        val body = message.notification?.body ?: message.data["body"] ?: ""
        showNotification(title, body)
    }

    private fun showNotification(title: String, body: String) {
        val channelId = "default"
        ensureChannel(channelId)
        val notification = NotificationCompat.Builder(this, channelId)
            .setSmallIcon(R.drawable.ic_notification_esimko)
            .setContentTitle(title)
            .setContentText(body)
            .setAutoCancel(true)
            .build()
        try {
            NotificationManagerCompat.from(this).notify((System.currentTimeMillis() % Int.MAX_VALUE).toInt(), notification)
        } catch (_: SecurityException) {
            // Izin notifikasi belum diberikan (Android 13+).
        }
    }

    private fun ensureChannel(channelId: String) {
        val manager = getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
        if (manager.getNotificationChannel(channelId) == null) {
            manager.createNotificationChannel(
                NotificationChannel(channelId, "Notifikasi eSIMKO", NotificationManager.IMPORTANCE_DEFAULT)
            )
        }
    }
}