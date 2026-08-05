package com.esimko.mobile.domain.model

data class Transaction(
    val id: Long,
    val jenis: String,
    val modul: String,
    val nominal: Long,
    val tanggal: String,
    val status: String,
    val statusLabel: String,
    val keterangan: String?
)

data class TransactionDetail(
    val id: Long,
    val jenis: String,
    val nominal: Long,
    val tanggal: String,
    val status: String,
    val statusLabel: String,
    val keterangan: String?,
    val buktiTransaksi: String?,
    val items: List<TransactionItem>?
)

data class TransactionItem(
    val id: Long,
    val nama: String,
    val nominal: Long,
    val qty: Int
)
