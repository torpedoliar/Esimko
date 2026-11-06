package com.esimko.mobile.data.repository

import android.content.Context
import com.esimko.mobile.data.local.NotificationStore
import com.esimko.mobile.domain.model.AppNotification
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class NotificationRepository @Inject constructor(
    @ApplicationContext context: Context
) {
    private val store = NotificationStore(context)
    private val _data = MutableStateFlow(store.load().sortedByDescending { it.createdAt })
    val data: StateFlow<List<AppNotification>> = _data.asStateFlow()

    fun capture(notification: AppNotification) {
        if (_data.value.any { it.id == notification.id }) return
        val next = (listOf(notification) + _data.value).sortedByDescending { it.createdAt }
        _data.value = next
        store.save(next)
    }

    fun markRead(id: String) {
        mutate { it -> if (it.id == id && !it.read) it.copy(read = true) else it }
    }

    fun markAllRead() {
        mutate { it -> if (it.read) it else it.copy(read = true) }
    }

    private fun mutate(transform: (AppNotification) -> AppNotification) {
        val next = _data.value.map(transform)
        if (next != _data.value) {
            _data.value = next
            store.save(next)
        }
    }
}