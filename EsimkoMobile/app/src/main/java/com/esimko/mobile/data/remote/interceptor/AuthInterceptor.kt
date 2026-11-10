package com.esimko.mobile.data.remote.interceptor

import com.esimko.mobile.data.local.TokenStore
import okhttp3.Interceptor
import okhttp3.Response
import java.io.IOException
import javax.inject.Inject

class AuthInterceptor @Inject constructor(
    private val tokenStore: TokenStore
) : Interceptor {
    override fun intercept(chain: Interceptor.Chain): Response {
        val request = chain.request()
        val path = request.url.encodedPath.trimEnd('/')
        val publicAuth = path.endsWith("/mobile/auth/login") || path.endsWith("/mobile/auth/register")
        val logout = path.endsWith("/mobile/auth/logout")
        val boundSession = request.tag(TokenStore.Session::class.java)
        val session = tokenStore.currentSession()
        // A queued registration must never be sent using a replacement account's credentials.
        if (boundSession != null && boundSession != session) throw IOException("Session changed")
        val builder = request.newBuilder()
        val authorization = when {
            publicAuth -> null
            logout -> request.header("Authorization") // logout is explicitly bound before local clear
            else -> session?.let { "Bearer ${it.token}" }
        }
        builder.removeHeader("Authorization")
        if (authorization != null) builder.header("Authorization", authorization)
        val response = chain.proceed(builder.build())
        // Public auth failures and late logout responses cannot invalidate another session.
        if (response.code == 401 && !publicAuth && !logout && session != null) {
            tokenStore.clearIfCurrent(session.revision)
        }
        return response
    }
}
