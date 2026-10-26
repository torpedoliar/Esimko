package com.esimko.mobile.util

/**
 * Bunga flat atas nominal awal, bukan anuitas. Form pengajuan harus meneruskan rate
 * dari pengaturan server; nilai default historis tidak boleh dipakai sebagai fallback jaringan.
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

