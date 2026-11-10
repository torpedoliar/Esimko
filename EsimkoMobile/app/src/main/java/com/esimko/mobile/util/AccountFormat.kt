package com.esimko.mobile.util

/** Format tampilan tab Akun. Murni — diuji di JVM. */
object AccountFormat {

    /** "Yohanes Oktavianus Putra" → "YO". Kosong → "?". */
    fun initials(nama: String): String {
        val parts = nama.trim().split(Regex("\\s+")).filter { it.any(Char::isLetterOrDigit) }
        if (parts.isEmpty()) return "?"
        return parts.take(2).joinToString("") { it.first { c -> c.isLetterOrDigit() }.uppercase() }
    }

    /**
     * NIK tidak pernah tampil utuh di layar (bisa terlihat orang di sebelah / tangkapan layar):
     * "3201234567890001" → "3201 •••• •••• 0001". Terlalu pendek untuk disamarkan → tetap disamarkan semua.
     */
    fun maskKtp(ktp: String): String {
        val digits = ktp.filter { !it.isWhitespace() }
        if (digits.isEmpty()) return "-"
        if (digits.length < 9) return "•".repeat(digits.length)
        return "${digits.take(4)} •••• •••• ${digits.takeLast(4)}"
    }

    /**
     * Porsi tiap saldo terhadap total untuk bilah komposisi. Saldo negatif dihitung nol supaya
     * bilah tidak melebihi 100%. Total nol → semua nol (bilah kosong, bukan bagi nol).
     */
    fun shares(values: List<Long>): List<Float> {
        val clean = values.map { if (it > 0) it else 0L }
        val total = clean.sumOf { it.toDouble() }
        if (total <= 0.0) return clean.map { 0f }
        return clean.map { (it.toDouble() / total).toFloat() }
    }

    /** "Aktif" dianggap status baik; lainnya ditampilkan netral. */
    fun isActiveStatus(status: String): Boolean = status.trim().equals("aktif", ignoreCase = true)
}
