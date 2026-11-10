package com.esimko.mobile.data.local

import android.content.Context
import android.content.SharedPreferences
import com.google.common.truth.Truth.assertThat
import io.mockk.every
import io.mockk.mockk
import org.junit.Before
import org.junit.Test

class TokenStoreTest {
    private lateinit var store: TokenStore
    private val values = mutableMapOf<String, String?>()
    private val prefs = mockk<SharedPreferences>()
    private val editor = mockk<SharedPreferences.Editor>(relaxed = true)

    @Before
    fun setup() {
        values.clear()
        val context = mockk<Context>()
        every { context.getSharedPreferences("esimko_secure_prefs", Context.MODE_PRIVATE) } returns prefs
        every { prefs.getString(any(), any()) } answers { values[firstArg()] ?: secondArg() }
        every { prefs.edit() } returns editor
        val pending = mutableMapOf<String, String?>()
        var clear = false
        every { editor.putString(any(), any()) } answers {
            pending[firstArg()] = secondArg()
            editor
        }
        every { editor.clear() } answers { clear = true; editor }
        every { editor.apply() } answers {
            if (clear) values.clear()
            values.putAll(pending)
            pending.clear()
            clear = false
        }
        store = TokenStore(context)
        // Test session coordination independently of Android Keystore (covered on device).
        TokenStore::class.java.getDeclaredField("gunaPlain").apply { isAccessible = true }.set(store, true)
    }

    @Test
    fun `atomic login publishes complete credentials and invalidates old caches once`() {
        val changes = mutableListOf<Long>()
        store.addSessionListener { changes += store.sessionRevision }
        assertThat(store.saveSessionIfCurrent(0, "a", "A")).isTrue()
        assertThat(store.currentSession()).isEqualTo(TokenStore.Session("a", "A", 1))
        assertThat(changes).containsExactly(1L)
        assertThat(store.sessionChanges.value).isEqualTo(1L)
        assertThat(store.isLoggedIn()).isTrue()
    }

    @Test
    fun `old 401 cannot clear replacement session even when bearer reused`() {
        store.saveSessionIfCurrent(0, "same-token", "A")
        val old = store.currentSession()!!
        store.saveSessionIfCurrent(old.revision, "same-token", "B")
        assertThat(store.clearIfCurrent(old.revision)).isFalse()
        assertThat(store.currentSession()).isEqualTo(TokenStore.Session("same-token", "B", 2))
    }

    @Test
    fun `current 401 clears both credentials and advances observable revision`() {
        store.saveSessionIfCurrent(0, "a", "A")
        assertThat(store.clearIfCurrent(1)).isTrue()
        assertThat(store.token).isNull()
        assertThat(store.noAnggota).isNull()
        assertThat(store.currentSession()).isNull()
        assertThat(store.isLoggedIn()).isFalse()
        assertThat(store.sessionChanges.value).isEqualTo(2L)
    }

    @Test
    fun `logout invalidates pending login even without old credentials`() {
        store.clear()
        assertThat(store.saveSessionIfCurrent(0, "late-login", "A")).isFalse()
        assertThat(store.currentSession()).isNull()
    }

    @Test
    fun `competing late login cannot overwrite successful replacement`() {
        store.saveSessionIfCurrent(0, "b", "B")
        assertThat(store.saveSessionIfCurrent(0, "a", "A")).isFalse()
        assertThat(store.currentSession()).isEqualTo(TokenStore.Session("b", "B", 1))
    }

    @Test
    fun `session diagnostic string never exposes bearer or member`() {
        val text = TokenStore.Session("secret-bearer", "private-member", 1).toString()
        assertThat(text).doesNotContain("secret-bearer")
        assertThat(text).doesNotContain("private-member")
    }

    @Test
    fun `partial and blank stored credentials are not logged in`() {
        values["auth_token"] = "a"
        assertThat(store.isLoggedIn()).isFalse()
        values["no_anggota"] = " "
        assertThat(store.isLoggedIn()).isFalse()
        values["no_anggota"] = "A"
        values["auth_token"] = " "
        assertThat(store.isLoggedIn()).isFalse()
    }
}
