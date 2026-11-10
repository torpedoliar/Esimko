package com.esimko.mobile.data.local

import com.google.common.truth.Truth.assertThat
import org.junit.Rule
import org.junit.Test
import org.junit.rules.TemporaryFolder
import java.io.File
import java.util.UUID
import javax.crypto.Cipher
import javax.crypto.KeyGenerator
import javax.crypto.spec.GCMParameterSpec

class PendingSavingsStoreTest {
    @get:Rule val temporary = TemporaryFolder()
    private val secret = KeyGenerator.getInstance("AES").apply { init(256) }.generateKey()
    private val draft = PendingSavings("A001", "setoran", UUID.randomUUID().toString(), 1000,
        "Pengajuan 09-11-2026 10:00 — private note", "private note")

    private fun store(directory: File) = PendingSavingsStore(directory,
        encrypt = { bytes, binding ->
            Cipher.getInstance("AES/GCM/NoPadding").run {
                init(Cipher.ENCRYPT_MODE, secret)
                updateAAD(binding)
                iv + doFinal(bytes)
            }
        },
        decrypt = { bytes, binding ->
            Cipher.getInstance("AES/GCM/NoPadding").run {
                init(Cipher.DECRYPT_MODE, secret, GCMParameterSpec(128, bytes.copyOfRange(0, 12)))
                updateAAD(binding)
                doFinal(bytes.copyOfRange(12, bytes.size))
            }
        })

    @Test fun `ciphertext reopens unchanged across instances and never stores proof or plaintext`() {
        val directory = temporary.newFolder()
        val first = store(directory)
        assertThat(first.createOrLoad(draft)).isEqualTo(draft)
        val reopened = store(directory)
        assertThat(reopened.load("A001", "setoran")).isEqualTo(draft)
        assertThat(directory.listFiles()!!.toList()).hasSize(1)
        val file = directory.listFiles()!!.single()
        assertThat(file.length()).isLessThan(16L * 1024)
        val raw = file.readBytes().toString(Charsets.ISO_8859_1)
        assertThat(raw).doesNotContain(draft.member)
        assertThat(raw).doesNotContain(draft.key)
        assertThat(raw).doesNotContain(draft.keterangan)
        assertThat(raw).doesNotContain(draft.note)
        assertThat(file.name).doesNotContain(draft.member)
        assertThat(reopened.load("A002", "setoran")).isNull()
        assertThat(reopened.load("A001", "penarikan")).isNull()
    }

    @Test fun `unresolved create cannot overwrite original fingerprint or key`() {
        val store = store(temporary.newFolder())
        store.createOrLoad(draft)
        val replacement = draft.copy(key = UUID.randomUUID().toString(), nominal = 9000, keterangan = "new timestamp")
        assertThat(store.createOrLoad(replacement)).isEqualTo(draft)
        assertThat(runCatching { store.complete(draft) }.isFailure).isTrue()
        assertThat(store.load(draft.member, draft.jenis)).isEqualTo(draft)
    }

    @Test fun `successful id survives restart until explicit completion enables new key`() {
        val directory = temporary.newFolder()
        val first = store(directory)
        first.createOrLoad(draft)
        first.recordSuccess(draft, 123)
        val reopened = store(directory)
        val resolved = reopened.load(draft.member, draft.jenis)!!
        assertThat(resolved.transactionId).isEqualTo(123)
        assertThat(resolved.key).isEqualTo(draft.key)
        reopened.complete(resolved)
        assertThat(reopened.load(draft.member, draft.jenis)).isNull()
        val next = draft.copy(key = UUID.randomUUID().toString())
        assertThat(reopened.createOrLoad(next)).isEqualTo(next)
    }

    @Test fun `key or id mismatch cannot corrupt or complete another submission`() {
        val store = store(temporary.newFolder())
        store.createOrLoad(draft)
        store.recordSuccess(draft, 123)
        val other = draft.copy(key = UUID.randomUUID().toString())
        assertThat(runCatching { store.recordSuccess(other, 456) }.isFailure).isTrue()
        assertThat(runCatching { store.complete(other) }.isFailure).isTrue()
        assertThat(runCatching { store.recordSuccess(draft, 456) }.isFailure).isTrue()
        assertThat(store.load(draft.member, draft.jenis)!!.transactionId).isEqualTo(123)
    }

    @Test fun `ciphertext account swap fails authentication and keeps original file`() {
        val directory = temporary.newFolder()
        val store = store(directory)
        store.createOrLoad(draft)
        store.createOrLoad(draft.copy(member = "A002"))
        val files = directory.listFiles()!!
        files[0].copyTo(files[1], overwrite = true)
        assertThat(runCatching { store.load("A001", "setoran") }.isFailure ||
            runCatching { store.load("A002", "setoran") }.isFailure).isTrue()
        assertThat(directory.listFiles()!!.size).isEqualTo(2)
    }

    @Test fun `keystore encryption failure creates no plaintext fallback`() {
        val directory = temporary.newFolder()
        val store = PendingSavingsStore(directory, { _, _ -> error("keystore unavailable") }, { _, _ -> error("unavailable") })
        assertThat(runCatching { store.createOrLoad(draft) }.isFailure).isTrue()
        assertThat(directory.listFiles()!!.toList()).isEmpty()
    }

    @Test fun `corrupt ciphertext is retained and never replaced with new key`() {
        val directory = temporary.newFolder()
        val store = store(directory)
        store.createOrLoad(draft)
        val file = directory.listFiles()!!.single()
        val corrupted = file.readBytes().also { it[it.lastIndex] = (it.last().toInt() xor 1).toByte() }
        file.writeBytes(corrupted)
        assertThat(runCatching { store.load(draft.member, draft.jenis) }.isFailure).isTrue()
        assertThat(runCatching { store.createOrLoad(draft.copy(key = UUID.randomUUID().toString())) }.isFailure).isTrue()
        assertThat(file.readBytes().toList()).isEqualTo(corrupted.toList())
    }

    @Test fun `only matching abandoned proof can clear unresolved draft and permit new key`() {
        val store = store(temporary.newFolder())
        store.createOrLoad(draft)
        val proof = com.esimko.mobile.domain.repository.SavingsReconciliation.Abandoned(draft.key, draft.jenis)
        assertThat(runCatching { store.abandon(draft, proof.copy(key = UUID.randomUUID().toString())) }.isFailure).isTrue()
        assertThat(runCatching { store.abandon(draft, proof.copy(jenis = "penarikan")) }.isFailure).isTrue()
        assertThat(runCatching { store.abandon(draft.copy(nominal = 2000), proof) }.isFailure).isTrue()
        assertThat(store.load(draft.member, draft.jenis)).isEqualTo(draft)
        store.abandon(draft, proof)
        assertThat(store.load(draft.member, draft.jenis)).isNull()
        assertThat(runCatching { store.abandon(draft, proof) }.isFailure).isTrue()
        val next = draft.copy(key = UUID.randomUUID().toString())
        assertThat(store.createOrLoad(next)).isEqualTo(next)
        assertThat(runCatching { store.abandon(draft, proof) }.isFailure).isTrue()
        assertThat(store.load(next.member, next.jenis)).isEqualTo(next)
    }

    @Test fun `abandoned proof cannot discard a locally known transaction`() {
        val store = store(temporary.newFolder())
        store.createOrLoad(draft)
        val resolved = store.recordSuccess(draft, 123)
        val proof = com.esimko.mobile.domain.repository.SavingsReconciliation.Abandoned(draft.key, draft.jenis)
        assertThat(runCatching { store.abandon(draft, proof) }.isFailure).isTrue()
        assertThat(runCatching { store.abandon(resolved, proof) }.isFailure).isTrue()
        assertThat(store.load(draft.member, draft.jenis)).isEqualTo(resolved)
    }

    @Test fun `oversized metadata and ciphertext fail closed`() {
        val directory = temporary.newFolder()
        val store = store(directory)
        assertThat(runCatching { store.createOrLoad(draft.copy(note = "x".repeat(2001))) }.isFailure).isTrue()
        store.createOrLoad(draft)
        val file = directory.listFiles()!!.single()
        file.writeBytes(ByteArray(16 * 1024 + 1))
        assertThat(runCatching { store.load(draft.member, draft.jenis) }.isFailure).isTrue()
    }
}
