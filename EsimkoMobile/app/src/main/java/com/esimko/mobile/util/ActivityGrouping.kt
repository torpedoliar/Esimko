package com.esimko.mobile.util

import com.esimko.mobile.domain.model.Transaction

sealed interface ActivityRow {
    /** Id stabil untuk `LazyColumn(key = …)`. Prefiks mencegah tabrakan antar jenis baris. */
    val key: String

    data class SectionHeaderRow(val title: String) : ActivityRow {
        override val key get() = "section:$title"
    }

    data class MonthHeaderRow(val title: String) : ActivityRow {
        override val key get() = "month:$title"
    }

    data class ItemRow(val transaction: Transaction) : ActivityRow {
        override val key get() = "trx:${ActivityGrouping.identity(transaction)}"
    }
}

object ActivityGrouping {

    /** Installments share the sale id, but different periods/installment numbers are real rows. */
    fun identity(transaction: Transaction): String = buildString {
        append(transaction.modul).append(':').append(transaction.id)
        if (transaction.jenis == "Angsuran Belanja") {
            append(':').append(transaction.tanggal).append(':').append(transaction.subtitleOverride)
        }
    }

    fun distinct(transactions: List<Transaction>): List<Transaction> =
        transactions.distinctBy(::identity)

    private val MONTHS = listOf(
        "Januari", "Februari", "Maret", "April", "Mei", "Juni",
        "Juli", "Agustus", "September", "Oktober", "November", "Desember"
    )
    private val MONTHS_SHORT = listOf(
        "Jan", "Feb", "Mar", "Apr", "Mei", "Jun",
        "Jul", "Agu", "Sep", "Okt", "Nov", "Des"
    )

    private const val NO_DATE = "Tanpa tanggal"

    /**
     * Susun jadi daftar rata siap `LazyColumn`: bagian "Berjalan" (tanpa header bulan) lalu
     * bagian "Selesai" (dipecah per bulan). Keduanya terbaru dulu.
     *
     * ponytail: pengelompokan final/berjalan menebak dari nama status (lihat StatusMeta.FINAL) —
     * API mengirim daftar status tapi tidak menandai mana yang final. Status baru dengan nama
     * lain akan masuk "Berjalan". Kalau backend kelak mengirim flag final, ganti StatusMeta.isFinal.
     */
    fun build(transactions: List<Transaction>, terbaru: Boolean = true): List<ActivityRow> {
        if (transactions.isEmpty()) return emptyList()

        val comparator = if (terbaru) {
            compareByDescending<Transaction> { it.tanggal }.thenByDescending { it.id }
        } else {
            compareBy<Transaction> { it.tanggal }.thenBy { it.id }
        }
        val sorted = distinct(transactions).sortedWith(comparator)
        val (running, finished) = sorted.partition { !StatusMeta.isFinal(it.status) }

        val rows = mutableListOf<ActivityRow>()

        if (running.isNotEmpty()) {
            rows += ActivityRow.SectionHeaderRow("Berjalan")
            running.forEach { rows += ActivityRow.ItemRow(it) }
        }

        if (finished.isNotEmpty()) {
            rows += ActivityRow.SectionHeaderRow("Selesai")
            // Raw dates can be malformed/non-canonical and interleave the same month label.
            // Group once per label, preserving the sorted order of the first occurrence.
            finished.groupBy { monthLabel(it.tanggal) }.forEach { (month, items) ->
                rows += ActivityRow.MonthHeaderRow(month)
                items.forEach { rows += ActivityRow.ItemRow(it) }
            }
        }

        return rows
    }

    /** "2026-08-01" → "Agustus 2026". Tidak terbaca → "Tanpa tanggal". */
    fun monthLabel(tanggal: String): String {
        val p = parse(tanggal) ?: return NO_DATE
        return "${MONTHS[p.month - 1]} ${p.year}"
    }

    /** "2026-08-01" → "1 Agu 2026". Tidak terbaca → "-". */
    fun dayLabel(tanggal: String): String {
        val p = parse(tanggal) ?: return "-"
        return "${p.day} ${MONTHS_SHORT[p.month - 1]} ${p.year}"
    }

    private data class Parsed(val year: Int, val month: Int, val day: Int)

    /**
     * Terima "yyyy-MM-dd" dengan atau tanpa suffix waktu. Sengaja tidak memakai
     * SimpleDateFormat/LocalDate: yang pertama bergantung locale device, yang kedua butuh
     * desugaring di minSdk 28. Bentuk masukannya cuma satu, jadi substring cukup.
     */
    private fun parse(tanggal: String): Parsed? {
        val date = tanggal.trim().take(10)
        if (date.length != 10 || date[4] != '-' || date[7] != '-') return null
        val year = date.substring(0, 4).toIntOrNull() ?: return null
        val month = date.substring(5, 7).toIntOrNull() ?: return null
        val day = date.substring(8, 10).toIntOrNull() ?: return null
        if (month !in 1..12 || day !in 1..31) return null
        return Parsed(year, month, day)
    }
}
