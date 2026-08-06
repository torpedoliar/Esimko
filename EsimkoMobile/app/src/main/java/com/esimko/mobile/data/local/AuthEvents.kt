package com.esimko.mobile.data.local

import kotlinx.coroutines.flow.MutableSharedFlow
import kotlinx.coroutines.flow.asSharedFlow

// Emit saat sesi invalid (401) — app harus balik ke login.
object AuthEvents {
    private val _loggedOut = MutableSharedFlow<Unit>(extraBufferCapacity = 1)
    val loggedOut = _loggedOut.asSharedFlow()

    fun emitLoggedOut() {
        _loggedOut.tryEmit(Unit)
    }
}