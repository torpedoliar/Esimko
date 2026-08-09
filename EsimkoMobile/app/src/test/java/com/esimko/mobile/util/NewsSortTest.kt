package com.esimko.mobile.util

import com.esimko.mobile.domain.model.News
import com.google.common.truth.Truth.assertThat
import org.junit.Test

class NewsSortTest {

    private fun news(id: Long, tanggal: String) = News(
        id = id, judul = "B$id", ringkasan = null, gambar = null,
        tanggal = tanggal, jumlahAttachment = 0
    )

    @Test
    fun `tanggal ISO urut leksikografis terbaru dulu`() {
        val list = listOf(
            news(1, "2026-01-15 10:00:00"),
            news(2, "2026-08-06 09:00:00"),
            news(3, "2026-03-20 12:00:00")
        )
        val sorted = NewsSort.descByCreated(list)
        assertThat(sorted.map { it.id }).containsExactly(2L, 3L, 1L).inOrder()
    }

    @Test
    fun `tanggal sama urut id menurun sebagai tiebreak`() {
        val list = listOf(
            news(1, "2026-08-06 09:00:00"),
            news(2, "2026-08-06 09:00:00")
        )
        val sorted = NewsSort.descByCreated(list)
        assertThat(sorted.first().id).isEqualTo(2L)
    }

    @Test
    fun `stabil pada input kosong`() {
        assertThat(NewsSort.descByCreated(emptyList())).isEmpty()
    }
}
