package com.esimko.mobile

import android.app.NotificationChannel
import android.app.NotificationManager
import android.content.Context
import androidx.core.app.NotificationCompat
import androidx.core.app.NotificationManagerCompat
import com.esimko.mobile.data.local.FcmTokenStore
import com.esimko.mobile.data.local.TokenStore
import com.esimko.mobile.domain.repository.DeviceRepository
import com.google.firebase.messaging.FirebaseMessagingService
import com.google.firebase.messaging.RemoteMessage
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.SupervisorJob
import kotlinx.coroutines.cancel
import kotlinx.coroutines.launch
import javax.inject.Inject

@AndroidEntryPoint
class FcmMessagingService : FirebaseMessagingService() {

    @Inject
    lateinit var deviceRepository: DeviceRepository

    @Inject
    lateinit var tokenStore: TokenStore

    private val scope = CoroutineScope(SupervisorJob() + Dispatchers.IO)

    /**
     * PENTING: token yang dibuat/diperbarui FCM harus DIKIRIM ke server, bukan hanya
     * disimpan lokal. Sebelumnya hanya disimpan lokal, sehingga perangkat baru
     * (mis. tablet yang baru login) tidak pernah terdaftar dan tidak menerima notifikasi.
     */
    override fun onNewToken(token: String) {
        FcmTokenStore.save(this, token)
        val session = tokenStore.currentSession() ?: return
        scope.launch { deviceRepository.registerToken(token, session.revision) }
    }

    override fun onDestroy() {
        scope.cancel()
        super.onDestroy()
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