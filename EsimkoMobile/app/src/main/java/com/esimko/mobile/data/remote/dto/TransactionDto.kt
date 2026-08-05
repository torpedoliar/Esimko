package com.esimko.mobile.data.remote.dto

import com.squareup.moshi.Json
import com.squareup.moshi.JsonClass

@JsonClass(generateAdapter = true)
data class TransactionResponse(
    @Json(name = "id") val id: Long,
    @Json(name = "jenis_transaksi") val jenisTransaksi: String? = null,
    @Json(name = "nominal") val nominal: Long? = null,
    @Json(name = "tanggal") val tanggal: String? = null,
    @Json(name = "status") val status: String? = null,
    @Json(name = "color") val color: String? = null,
    @Json(name = "metode_pembayaran") val metodePembayaran: String? = null,
    @Json(name = "keterangan") val keterangan: String? = null
)

@JsonClass(generateAdapter = true)
data class TransactionDetailResponse(
    @Json(name = "id") val id: Long,
    @Json(name = "jenis_transaksi") val jenisTransaksi: String? = null,
    @Json(name = "nominal") val nominal: Long? = null,
    @Json(name = "tanggal") val tanggal: String? = null,
    @Json(name = "status") val status: String? = null,
    @Json(name = "color") val color: String? = null,
    @Json(name = "metode_pembayaran") val metodePembayaran: String? = null,
    @Json(name = "keterangan") val keterangan: String? = null,
    @Json(name = "bukti_transaksi") val buktiTransaksi: String? = null,
    @Json(name = "nama_lengkap") val namaLengkap: String? = null,
    @Json(name = "no_anggota") val noAnggota: String? = null,
    @Json(name = "avatar") val avatar: String? = null
)

@JsonClass(generateAdapter = true)
data class TransactionRequest(
    @Json(name = "jenis") val jenis: String,
    @Json(name = "nominal") val nominal: Long,
    @Json(name = "keterangan") val keterangan: String? = null
)

@JsonClass(generateAdapter = true)
data class CancelRequest(
    @Json(name = "id") val id: Long,
    @Json(name = "alasan") val alasan: String? = null
)
