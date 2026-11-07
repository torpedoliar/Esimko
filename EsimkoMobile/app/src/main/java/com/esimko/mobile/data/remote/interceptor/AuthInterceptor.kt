package com.esimko.mobile.data.remote.interceptor

import com.esimko.mobile.data.local.AuthEvents
import com.esimko.mobile.data.local.TokenStore
import okhttp3.Interceptor
import okhttp3.Response
import javax.inject.Inject

class AuthInterceptor @Inject constructor(
    private val tokenStore: TokenStore
) : Interceptor {
    override fun intercept(chain: Interceptor.Chain): Response {
        val request = chain.request()
        val builder = request.newBuilder()

        val (token, revision) = synchronized(tokenStore) {
            tokenStore.token to tokenStore.sessionRevision
        }
        // Credentials, token metadata, and authenticated resource URLs must not be logged.
        token?.takeIf { it.isNotEmpty() }?.let {
            builder.addHeader("Authorization", "Bearer $it")
        }

        val response = chain.proceed(builder.build())

        // 401 dengan token yang dipakai = sesi invalid (expired / direset server).
        // Bersihkan token + notify agar UI balik ke login. Jangan untuk login/logout request sendiri.
        if (response.code == 401 && token?.isNotEmpty() == true) {
            if (tokenStore.clearIfCurrent(revision)) AuthEvents.emitLoggedOut()
        }

        return response
    }
}
