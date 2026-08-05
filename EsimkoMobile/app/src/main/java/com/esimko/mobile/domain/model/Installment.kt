package com.esimko.mobile.domain.model

data class Installment(
    val id: Long,
    val ke: Int,
    val nominal: Long,
    val bunga: Long,
    val pokok: Long,
    val tanggalJatuhTempo: String,
    val tanggalBayar: String?,
    val status: String
)

data class Salary(
    val gajiPokok: Long
)
