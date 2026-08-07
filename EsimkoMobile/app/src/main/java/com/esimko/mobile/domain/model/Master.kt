package com.esimko.mobile.domain.model

data class TransactionType(
    val id: Int,
    val nama: String,
    val kode: String
)

data class TransactionStatus(
    val id: Int,
    val nama: String,
    val kode: String,
    val color: String? = null
)
