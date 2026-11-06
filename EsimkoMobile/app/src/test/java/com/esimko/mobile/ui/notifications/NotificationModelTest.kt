package com.esimko.mobile.ui.notifications

import com.esimko.mobile.domain.model.NotificationType
import com.esimko.mobile.domain.model.notificationRoute
import com.google.common.truth.Truth.assertThat
import org.junit.Test

class NotificationModelTest {

    @Test
    fun `update route selalu ke pengaturan`() {
        assertThat(notificationRoute(NotificationType.UPDATE, null)).isEqualTo("settings")
        assertThat(notificationRoute(NotificationType.UPDATE, 9L)).isEqualTo("settings")
    }

    @Test
    fun `status route ke detail transaksi bila ada id`() {
        assertThat(notificationRoute(NotificationType.STATUS, 812L)).isEqualTo("history/transaksi/812")
        assertThat(notificationRoute(NotificationType.STATUS, null)).isNull()
    }

    @Test
    fun `info route ke berita (fallback tanpa id)`() {
        assertThat(notificationRoute(NotificationType.INFO, 5L)).isEqualTo("news/5")
        assertThat(notificationRoute(NotificationType.INFO, null)).isEqualTo("news")
    }
}