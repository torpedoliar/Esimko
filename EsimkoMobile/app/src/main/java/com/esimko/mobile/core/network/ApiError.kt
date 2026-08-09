package com.esimko.mobile.core.network

import com.squareup.moshi.Moshi
import com.squareup.moshi.kotlin.reflect.KotlinJsonAdapterFactory
import retrofit2.HttpException
import java.io.IOException
import java.net.SocketTimeoutException
import java.net.UnknownHostException

data class ApiErrorBody(
    val success: Boolean? = null,
    val message: String? = null
)

/**
 * Terjemahkan exception jaringan/HTTP jadi pesan yang dipahami user awam.
 * - HttpException: coba ambil `message` dari envelope backend (validasi spesifik,
 *   mis. "No anggota tidak ditemukan"); kalau kosong/bukan JSON (mis. halaman 413
 *   nginx), pakai teks per kode HTTP.
 * - IOException (timeout/host unreachable): "Koneksi bermasalah", bukan stacktrace.
 *
 * ponytail: satu fungsi, dipanggil semua repository. Tidak ada kelas exception baru.
 */
fun apiErrorMessage(e: Exception, fallback: String = "Terjadi kesalahan, coba lagi"): String {
    // Koneksi/network dulu — paling umum, paling kabur kalau dibiarkan mentah.
    when (e) {
        is SocketTimeoutException -> return "Server lambat merespons. Periksa koneksi internet lalu coba lagi."
        is UnknownHostException -> return "Tidak bisa terhubung ke server. Periksa koneksi internet Anda."
        is IOException -> return "Koneksi terputus. Periksa jaringan internet lalu coba lagi."
    }
    if (e is HttpException) {
        val code = e.code()
        // Coba pesan spesifik backend dulu (envelope {success,message}).
        val body = e.response()?.errorBody()?.string()
        if (!body.isNullOrEmpty()) {
            try {
                val parsed = Moshi.Builder()
                    .add(KotlinJsonAdapterFactory())
                    .build()
                    .adapter(ApiErrorBody::class.java)
                    .fromJson(body)
                if (!parsed?.message.isNullOrBlank()) return parsed!!.message!!
            } catch (_: Exception) {
                // body bukan JSON envelope (mis. HTML nginx) → pakai teks per kode
            }
        }
        // Body kosong/bukan JSON → teks per kode HTTP, dikenali user awam.
        return when (code) {
            400 -> "Data yang dikirim belum benar. Periksa kembali isian Anda."
            401 -> "No. Anggota atau password salah."
            403 -> "Akses ditolak. Akun Anda mungkin tidak punya izin untuk aksi ini."
            404 -> "Data tidak ditemukan. Periksa kembali input Anda."
            413 -> "File terlalu besar (maks 1MB). Pilih foto dengan ukuran lebih kecil."
            422 -> "Input belum lengkap atau tidak valid. Lengkapi semua isian lalu coba lagi."
            429 -> "Terlalu banyak percobaan. Tunggu sebentar lalu coba lagi."
            in 500..599 -> "Server sedang bermasalah. Coba lagi beberapa saat."
            else -> "Terjadi kesalahan ($code). Coba lagi."
        }
    }
    return e.message ?: fallback
}

