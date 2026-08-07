package com.esimko.mobile.util

/**
 * Fasad lama. Semua pemanggil dipindahkan ke komponen Money / MoneyFormatter
 * sepanjang rework; delegasi di sini supaya tidak ada layar rusak di tengah jalan.
 */
@Deprecated(
    message = "Pakai komponen Money() untuk UI, atau MoneyFormatter.format() untuk string.",
    replaceWith = ReplaceWith("MoneyFormatter")
)
object AmountFormatter {
    fun formatRupiah(amount: Long): String = MoneyFormatter.format(amount)
    fun formatRupiah(amount: Double): String = MoneyFormatter.format(amount.toLong())
    fun format(amount: Long): String = MoneyFormatter.format(amount)
    fun format(amount: Double): String = MoneyFormatter.format(amount.toLong())
}
