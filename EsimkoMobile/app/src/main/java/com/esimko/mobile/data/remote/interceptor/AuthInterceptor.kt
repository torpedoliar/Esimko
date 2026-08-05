package com.esimko.mobile.data.remote.interceptor

import android.util.Log
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

        val token = tokenStore.token
        Log.d("AuthInterceptor", "Token from store: ${if (token.isNullOrEmpty()) "NULL/EMPTY" else "present (${token.length} chars)"}")

        token?.let {
            if (it.isNotEmpty()) {
                builder.addHeader("Authorization", "Bearer $it")
                Log.d("AuthInterceptor", "Added Authorization header for: ${request.url}")
            } else {
                Log.w("AuthInterceptor", "Token is empty string, not adding header")
            }
        } ?: Log.w("AuthInterceptor", "Token is null, not adding header for: ${request.url}")

        return chain.proceed(builder.build())
    }
}
