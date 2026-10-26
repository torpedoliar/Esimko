package com.esimko.mobile.data.remote.dto

import com.esimko.mobile.domain.model.SyaratPinjaman
import com.squareup.moshi.Json
import com.squareup.moshi.JsonClass

@JsonClass(generateAdapter = true)
data class SyaratPinjamanResponse(
    @Json(name = "ktp2_threshold") val ktp2Threshold: Long = 10_000_000L,
    @Json(name = "dokumen_wajib") val dokumenWajib: List<String> = emptyList(),
    @Json(name = "dokumen_kondisional") val dokumenKondisional: List<String> = emptyList(),
    @Json(name = "format") val format: List<String> = emptyList(),
    @Json(name = "maks_kb") val maksKb: Int = 5120,
    @Json(name = "persetujuan_versi") val persetujuanVersi: String = "",
    @Json(name = "persetujuan_teks") val persetujuanTeks: String = "",
    @Json(name = "persetujuan_pinjaman_versi") val persetujuanPinjamanVersi: String = "",
    @Json(name = "persetujuan_pinjaman_teks") val persetujuanPinjamanTeks: String = "",
    @Json(name = "bunga_pinjaman_rate") val bungaPinjamanRate: Double? = null
) {
    fun toDomain(): SyaratPinjaman = SyaratPinjaman(
        ktp2Threshold = ktp2Threshold,
        dokumenWajib = dokumenWajib,
        format = format,
        maksKb = maksKb,
        persetujuanVersi = persetujuanVersi,
        persetujuanTeks = persetujuanTeks,
        persetujuanPinjamanVersi = persetujuanPinjamanVersi,
        persetujuanPinjamanTeks = persetujuanPinjamanTeks,
        bungaPinjamanRate = bungaPinjamanRate
    )
}
