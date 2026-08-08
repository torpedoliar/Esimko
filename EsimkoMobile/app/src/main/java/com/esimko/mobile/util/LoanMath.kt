package com.esimko.mobile.util

/**
 * Hitung angsuran pinjaman. Dipindah dari `LoanApplicationScreen.kt:288` yang menghitungnya di dalam
 * komposabel — tidak bisa dites dan diam-diam berbeda dari server.
 *
 * Rumus mengikuti yang sudah dipakai UI lama: bunga **flat** atas nominal awal, bukan anuitas.
 *
 * // ponytail: bunga 1% flat itu tebakan client, sama seperti kode lama. Backend
 * // (`MobileController::validasi_transaksi`) tidak mengirim bunga per jenis pinjaman, jadi angka ini
 * // tetap **estimasi** dan labelnya di UI wajib berkata "Estimasi". Kalau nanti backend mengirim
 * // `bunga` per jenis, teruskan ke `rate` — tanda tangannya sudah menerimanya.
 */
object LoanMath {

    /** Bunga flat per bulan atas nominal awal. */
    const val RATE = 0.01

    fun angsuranPerBulan(nominal: Long, tenor: Int, rate: Double = RATE): Long {
        if (nominal <= 0L || tenor <= 0) return 0L
        val pokok = Math.round(nominal.toDouble() / tenor)
        val bunga = Math.round(nominal * rate)
        return pokok + bunga
    }

    fun totalBayar(nominal: Long, tenor: Int, rate: Double = RATE): Long =
        angsuranPerBulan(nominal, tenor, rate) * tenor
}

/**
 * Double? dari kolom DB double/decimal (nominal, saldo, angsuran, bunga) -> Long aman dibulatkan.
 * Moshi menolak pecahan sebagai Long; semua field uang yang dibaca dari backend pakai ini di mapper.
 */
fun Double?.roundL(): Long = this?.let { Math.round(it) } ?: 0L

