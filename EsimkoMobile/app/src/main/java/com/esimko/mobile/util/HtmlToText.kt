package com.esimko.mobile.util

/**
 * HTML → teks polos satu baris, untuk ringkasan berita.
 *
 * Sengaja tidak memakai `HtmlCompat`: itu kelas Android, jadi util ini tidak akan bisa
 * diuji di JVM. Rendering HTML yang sesungguhnya (tebal, daftar, tautan) ada di
 * `HtmlText` (Task 24) yang memang butuh Android.
 */
object HtmlToText {

    private val SCRIPT_STYLE = Regex("(?is)<(script|style)\\b[^>]*>.*?</\\1\\s*>")
    private val TAG = Regex("<[^>]*>")
    private val WHITESPACE = Regex("\\s+")

    // ponytail: entity yang dipakai TinyMCE. Entity numerik (&#8220;) dan nama langka
    // lolos apa adanya — tambah kalau muncul di berita nyata.
    private val ENTITIES = listOf(
        "&nbsp;" to " ",
        "&lt;" to "<",
        "&gt;" to ">",
        "&quot;" to "\"",
        "&#39;" to "'",
        "&apos;" to "'",
        "&hellip;" to "…",
        "&ndash;" to "–",
        "&mdash;" to "—",
        "&amp;" to "&" // terakhir, supaya "&amp;lt;" tetap jadi "&lt;" bukan "<"
    )

    fun strip(html: String?): String {
        if (html.isNullOrBlank()) return ""
        var s = SCRIPT_STYLE.replace(html, " ")
        s = TAG.replace(s, " ")
        for ((entity, replacement) in ENTITIES) {
            s = s.replace(entity, replacement, ignoreCase = true)
        }
        return WHITESPACE.replace(s, " ").trim()
    }
}
