package com.esimko.mobile.data.remote.interceptor

import com.esimko.mobile.data.local.TokenStore
import com.google.common.truth.Truth.assertThat
import io.mockk.every
import io.mockk.mockk
import io.mockk.verify
import okhttp3.Interceptor
import okhttp3.Protocol
import okhttp3.Request
import okhttp3.Response
import org.junit.Before
import org.junit.Test
import java.io.IOException

class AuthInterceptorTest {
    private val store = mockk<TokenStore>(relaxed = true)
    private val chain = mockk<Interceptor.Chain>()
    private val a = TokenStore.Session("auth-a", "A", 1)
    private val b = TokenStore.Session("auth-b", "B", 2)
    private lateinit var interceptor: AuthInterceptor
    private var sent: Request? = null
    private var status = 200

    @Before
    fun setup() {
        sent = null
        every { store.currentSession() } returns a
        every { chain.proceed(any()) } answers {
            val request = firstArg<Request>()
            sent = request
            Response.Builder().request(request).protocol(Protocol.HTTP_1_1).code(status).message("test").build()
        }
        interceptor = AuthInterceptor(store)
    }

    private fun request(path: String, auth: String? = null, session: TokenStore.Session? = null) {
        val builder = Request.Builder().url("https://example.invalid/api/mobile/$path")
        if (auth != null) builder.header("Authorization", auth)
        if (session != null) builder.tag(TokenStore.Session::class.java, session)
        every { chain.request() } returns builder.build()
    }

    @Test
    fun `authenticated requests get single current bearer`() {
        request("profil", "Bearer untrusted-old")
        interceptor.intercept(chain)
        assertThat(sent!!.headers.values("Authorization")).containsExactly("Bearer auth-a")
    }

    @Test
    fun `public login and register neither send credentials nor invalidate session on 401`() {
        status = 401
        for (path in listOf("auth/login", "auth/register")) {
            request(path, "Bearer old")
            interceptor.intercept(chain)
            assertThat(sent!!.header("Authorization")).isNull()
        }
        verify(exactly = 0) { store.clearIfCurrent(any()) }
    }

    @Test
    fun `401 invalidates only revision captured for request`() {
        request("profil")
        status = 401
        interceptor.intercept(chain)
        verify(exactly = 1) { store.clearIfCurrent(a.revision) }
    }

    @Test
    fun `stale 401 never targets replacement revision`() {
        request("profil")
        every { chain.proceed(any()) } answers {
            every { store.currentSession() } returns b
            Response.Builder().request(firstArg()).protocol(Protocol.HTTP_1_1).code(401).message("expired").build()
        }
        every { store.clearIfCurrent(a.revision) } returns false
        interceptor.intercept(chain)
        verify(exactly = 1) { store.clearIfCurrent(a.revision) }
        verify(exactly = 0) { store.clearIfCurrent(b.revision) }
    }

    @Test
    fun `queued old registration aborts before network for replacement session`() {
        request("device/register", session = a)
        every { store.currentSession() } returns b
        var rejected = false
        try { interceptor.intercept(chain) } catch (_: IOException) { rejected = true }
        assertThat(rejected).isTrue()
        verify(exactly = 0) { chain.proceed(any()) }
    }

    @Test
    fun `logout keeps captured old bearer and its 401 cannot clear new login`() {
        request("auth/logout", "Bearer auth-a")
        every { store.currentSession() } returns b
        status = 401
        interceptor.intercept(chain)
        assertThat(sent!!.header("Authorization")).isEqualTo("Bearer auth-a")
        verify(exactly = 0) { store.clearIfCurrent(any()) }
    }

    @Test
    fun `logged out requests do not send leftover Authorization`() {
        request("profil", "Bearer old")
        every { store.currentSession() } returns null
        status = 401
        interceptor.intercept(chain)
        assertThat(sent!!.header("Authorization")).isNull()
        verify(exactly = 0) { store.clearIfCurrent(any()) }
    }
}
