package com.esimko.mobile.data.remote.dto

import com.squareup.moshi.Json
import com.squareup.moshi.JsonClass
import com.esimko.mobile.domain.model.PengajuanPinjaman

@JsonClass(generateAdapter = true)
data class PengajuanPinjamanResponse(
    @Json(name = "id") val id: Long? = null,
    @Json(name = "status") val status: Int? = null,
    @Json(name = "jenis_pinjaman") val jenisPinjaman: Int? = null,
    @Json(name = "nominal") val nominal: Double? = null,
    @Json(name = "tenor") val tenor: Int? = null,
    @Json(name = "gaji_pokok") val gajiPokok: Double? = null,
    @Json(name = "dokumen_terlampir") val dokumenTerlampir: List<String> = emptyList(),
    @Json(name = "alasan_penolakan") val alasanPenolakan: String? = null,
    @Json(name = "batas_ajukan_ulang") val batasAjukanUlang: String? = null
) {
    fun toDomain(): PengajuanPinjaman = PengajuanPinjaman(
        id = id ?: 0L,
        status = status ?: 0,
        jenisPinjaman = jenisPinjaman ?: 9,
        nominal = Math.round(nominal ?: 0.0),
        tenor = tenor ?: 0,
        gajiPokok = Math.round(gajiPokok ?: 0.0),
        dokumenTerlampir = dokumenTerlampir,
        alasanPenolakan = alasanPenolakan?.takeIf { it.isNotBlank() },
        batasAjukanUlang = batasAjukanUlang
    )
}