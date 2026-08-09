package com.esimko.mobile.domain.model

data class Paged<T>(
    val items: List<T>,
    val page: Int,
    val lastPage: Int
) {
    val hasMore: Boolean get() = page < lastPage
}

fun <T> emptyPaged(): Paged<T> = Paged(emptyList(), page = 1, lastPage = 1)
