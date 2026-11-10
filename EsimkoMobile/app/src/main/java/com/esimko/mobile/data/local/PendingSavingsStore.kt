package com.esimko.mobile.data.local

import android.content.Context
import android.security.keystore.KeyGenParameterSpec
import android.security.keystore.KeyProperties
import java.io.FileOutputStream
import java.nio.file.Files
import java.nio.file.StandardCopyOption
import dagger.hilt.android.qualifiers.ApplicationContext
import java.io.ByteArrayInputStream
import java.io.ByteArrayOutputStream
import java.io.DataInputStream
import java.io.DataOutputStream
import java.io.File
import java.security.KeyStore
import java.security.MessageDigest
import java.util.UUID
import javax.crypto.Cipher
import javax.crypto.KeyGenerator
import javax.crypto.SecretKey
import javax.crypto.spec.GCMParameterSpec
import javax.inject.Inject
import javax.inject.Singleton

/** Small metadata only. Never persist proof images, bearer credentials, or plaintext fallbacks. */
data class PendingSavings(
    val member: String,
    val jenis: String,
    val key: String,
    val nominal: Long,
    val keterangan: String,
    val note: String,
    val transactionId: Long? = null
) {
    override fun toString(): String = "PendingSavings(redacted)"
}

@Singleton
class PendingSavingsStore internal constructor(
    private val directory: File,
    private val encrypt: (ByteArray, ByteArray) -> ByteArray,
    private val decrypt: (ByteArray, ByteArray) -> ByteArray
) {
    @Inject constructor(@ApplicationContext context: Context) : this(
        File(context.noBackupFilesDir, "pending_savings"), ::encryptWithKeystore, ::decryptWithKeystore
    )

    @Synchronized
    fun load(member: String, jenis: String): PendingSavings? {
        val file = file(member, jenis)
        if (!file.exists()) return null
        val encrypted = file.inputStream().use { input ->
            val bytes = input.readBytesBounded()
            check(bytes.isNotEmpty())
            bytes
        }
        val plain = decrypt(encrypted, binding(member, jenis))
        check(plain.size <= MAX_BYTES)
        return DataInputStream(ByteArrayInputStream(plain)).use { input ->
            check(input.readInt() == 1)
            PendingSavings(
                input.readUTF(), input.readUTF(), input.readUTF(), input.readLong(),
                input.readUTF(), input.readUTF(), input.readLong().takeIf { it > 0 }
            ).also {
                check(input.available() == 0)
                validate(it)
                check(it.member == member && it.jenis == jenis)
            }
        }
    }

    /** Commit before POST. Concurrent screens must reopen the existing consented submission. */
    @Synchronized
    fun createOrLoad(draft: PendingSavings): PendingSavings {
        load(draft.member, draft.jenis)?.let { return it }
        check(draft.transactionId == null)
        save(draft)
        return draft
    }

    @Synchronized
    fun recordSuccess(draft: PendingSavings, id: Long): PendingSavings {
        require(id > 0)
        val current = checkNotNull(load(draft.member, draft.jenis))
        check(current.key == draft.key)
        check(current.transactionId == null || current.transactionId == id)
        return current.copy(transactionId = id).also(::save)
    }

    /** Only a resolved submission can be explicitly completed; errors never rotate its key. */
    @Synchronized
    fun complete(draft: PendingSavings) {
        val current = checkNotNull(load(draft.member, draft.jenis))
        check(current.key == draft.key && current.transactionId != null)
        val file = file(draft.member, draft.jenis)
        check(file.delete())
    }

    /** Server has atomically fenced the original UUID against all delayed create requests. */
    @Synchronized
    fun abandon(draft: PendingSavings, proof: com.esimko.mobile.domain.repository.SavingsReconciliation.Abandoned) {
        check(proof.key == draft.key && proof.jenis == draft.jenis)
        val current = checkNotNull(load(draft.member, draft.jenis))
        check(current == draft && current.transactionId == null)
        check(file(draft.member, draft.jenis).delete())
    }

    private fun save(draft: PendingSavings) {
        validate(draft)
        val plain = ByteArrayOutputStream().also { bytes ->
            DataOutputStream(bytes).use {
                it.writeInt(1)
                it.writeUTF(draft.member)
                it.writeUTF(draft.jenis)
                it.writeUTF(draft.key)
                it.writeLong(draft.nominal)
                it.writeUTF(draft.keterangan)
                it.writeUTF(draft.note)
                it.writeLong(draft.transactionId ?: 0)
            }
        }.toByteArray()
        check(plain.size <= MAX_BYTES)
        val encrypted = encrypt(plain, binding(draft.member, draft.jenis))
        check(encrypted.size <= MAX_BYTES)
        check(directory.isDirectory || directory.mkdirs())
        val file = file(draft.member, draft.jenis)
        val temporary = File(directory, file.name + ".tmp")
        try {
            FileOutputStream(temporary).use { output ->
                output.write(encrypted)
                output.fd.sync()
            }
            Files.move(temporary.toPath(), file.toPath(), StandardCopyOption.ATOMIC_MOVE, StandardCopyOption.REPLACE_EXISTING)
        } finally {
            temporary.delete()
        }
        // Do not send unless durable storage can actually be read back.
        check(load(draft.member, draft.jenis) == draft)
    }

    private fun file(member: String, jenis: String): File {
        require(member.isNotBlank() && member.length <= 128)
        require(jenis == "setoran" || jenis == "penarikan")
        val name = MessageDigest.getInstance("SHA-256").digest(binding(member, jenis))
            .joinToString("") { "%02x".format(it) }
        return File(directory, "$name.enc")
    }

    private fun binding(member: String, jenis: String) = "$member\u0000$jenis".toByteArray(Charsets.UTF_8)

    private fun validate(draft: PendingSavings) {
        require(draft.nominal > 0 && draft.note.length <= 2000 && draft.keterangan.length <= 2400)
        require(UUID.fromString(draft.key).toString() == draft.key)
        require(draft.transactionId == null || draft.transactionId > 0)
    }

    private fun java.io.InputStream.readBytesBounded(): ByteArray {
        val output = ByteArrayOutputStream()
        val buffer = ByteArray(1024)
        while (true) {
            val count = read(buffer)
            if (count < 0) return output.toByteArray()
            check(output.size() + count <= MAX_BYTES)
            output.write(buffer, 0, count)
        }
    }

    companion object {
        private const val MAX_BYTES = 16 * 1024
        private const val ALIAS = "esimko_pending_savings_v1"

        @Synchronized
        private fun key(): SecretKey {
            val store = KeyStore.getInstance("AndroidKeyStore").apply { load(null) }
            (store.getKey(ALIAS, null) as? SecretKey)?.let { return it }
            return KeyGenerator.getInstance(KeyProperties.KEY_ALGORITHM_AES, "AndroidKeyStore").apply {
                init(KeyGenParameterSpec.Builder(ALIAS, KeyProperties.PURPOSE_ENCRYPT or KeyProperties.PURPOSE_DECRYPT)
                    .setBlockModes(KeyProperties.BLOCK_MODE_GCM)
                    .setEncryptionPaddings(KeyProperties.ENCRYPTION_PADDING_NONE).build())
            }.generateKey()
        }

        private fun encryptWithKeystore(plain: ByteArray, binding: ByteArray): ByteArray {
            val cipher = Cipher.getInstance("AES/GCM/NoPadding")
            cipher.init(Cipher.ENCRYPT_MODE, key())
            cipher.updateAAD(binding)
            check(cipher.iv.size == 12)
            return cipher.iv + cipher.doFinal(plain)
        }

        private fun decryptWithKeystore(encrypted: ByteArray, binding: ByteArray): ByteArray {
            require(encrypted.size >= 28)
            val cipher = Cipher.getInstance("AES/GCM/NoPadding")
            cipher.init(Cipher.DECRYPT_MODE, key(), GCMParameterSpec(128, encrypted.copyOfRange(0, 12)))
            cipher.updateAAD(binding)
            return cipher.doFinal(encrypted.copyOfRange(12, encrypted.size))
        }
    }
}
