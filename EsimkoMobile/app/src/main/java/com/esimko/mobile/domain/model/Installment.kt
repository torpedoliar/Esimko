package com.esimko.mobile.domain.model

data class Installment(
    val id: Long,
    val ke: Int,
    val pokok: Long,
    val bunga: Long,
    val status: String,
    val jenisTransaksi: String? = null,
    val namaBulan: String? = null
)

data class Salary(
    val gajiPokok: Long
)
