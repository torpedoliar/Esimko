package com.esimko.mobile.domain.model

/** Syarat pengajuan pinjaman dari endpoint master/syarat_pinjaman (server-side config). */
data class SyaratPinjaman(
    val ktp2Threshold: Long,
    val dokumenWajib: List<String>,
    val format: List<String>,
    val maksKb: Int,
    val persetujuanVersi: String,
    val persetujuanTeks: String,
)

/** Satu dokumen terpilih di lokal (belum diupload). */
data class DokumenFile(
    val bytes: ByteArray,
    val mime: String,
    val nama: String
) {
    val isGambar: Boolean get() = mime.startsWith("image/")
}
