package com.esimko.mobile.domain.model

data class PengajuanPinjaman(
    val id: Long,
    val status: Int,
    val jenisPinjaman: Int,
    val nominal: Long,
    val tenor: Int,
    val gajiPokok: Long,
    val dokumenTerlampir: List<String>,
    val alasanPenolakan: String?,
    val batasAjukanUlang: String?
)