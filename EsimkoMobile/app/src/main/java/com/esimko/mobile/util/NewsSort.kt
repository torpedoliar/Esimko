package com.esimko.mobile.util

import com.esimko.mobile.domain.model.News

object NewsSort {

    fun descByCreated(list: List<News>): List<News> =
        list.sortedWith(compareByDescending<News> { it.tanggal }.thenByDescending { it.id })
}
