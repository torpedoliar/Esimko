package com.esimko.mobile.util

/**
 * Satu-satunya sumber format rupiah di app. Sengaja tidak memakai NumberFormat:
 * hasilnya bergantung data locale JVM/Android dan bisa berbeda antara unit test
 * dan device. Backend memakai number_format($n, 0, ',', '.') — ini menyamakannya.
 */
object MoneyFormatter {

    /** Nominal terbesar yang masuk akal untuk diinput manusia: 999 miliar. */
    const val MAX_INPUT = 999_999_999_999L

    fun format(amount: Long): String =
        if (amount < 0) "-Rp " + plain(absSafe(amount)) else "Rp " + plain(amount)

    /** Angka saja, tanpa "Rp" — untuk field input dan label yang sudah punya konteks. */
    fun plain(amount: Long): String {
        val digits = absSafe(amount).toString()
        val sb = StringBuilder(digits.length + digits.length / 3)
        val lead = digits.length % 3
        if (lead > 0) sb.append(digits, 0, lead)
        var i = lead
        while (i < digits.length) {
            if (sb.isNotEmpty()) sb.append('.')
            sb.append(digits, i, i + 3)
            i += 3
        }
        val body = sb.toString()
        return if (amount < 0) "-$body" else body
    }

    /**
     * Buang semua non-digit lalu batasi ke MAX_INPUT. Dipakai di onValueChange
     * field nominal, jadi harus tahan input aneh: paste, tahan tombol, teks.
     */
    fun digitsToLong(input: String): Long {
        val digits = input.filter { it.isDigit() }.trimStart('0')
        if (digits.isEmpty()) return 0L
        if (digits.length > 12) return MAX_INPUT
        return digits.toLong().coerceAtMost(MAX_INPUT)
    }

    // -Long.MIN_VALUE overflow ke dirinya sendiri; nilai itu tidak pernah nominal
    // nyata, tapi crash karenanya tetap crash.
    private fun absSafe(v: Long): Long = if (v == Long.MIN_VALUE) Long.MAX_VALUE else kotlin.math.abs(v)
}
