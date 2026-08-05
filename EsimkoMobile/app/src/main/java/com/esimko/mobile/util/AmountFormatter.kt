package com.esimko.mobile.util

import java.text.NumberFormat
import java.util.Locale

object AmountFormatter {
    private val formatter = NumberFormat.getNumberInstance(Locale("id", "ID"))

    fun formatRupiah(amount: Long): String {
        return "Rp ${formatter.format(amount)}"
    }

    fun formatRupiah(amount: Double): String {
        return "Rp ${formatter.format(amount)}"
    }

    fun format(amount: Long): String = formatRupiah(amount)
    fun format(amount: Double): String = formatRupiah(amount)
}
