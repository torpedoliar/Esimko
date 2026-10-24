package com.esimko.mobile.util

/**
 * Aturan dokumen pengajuan pinjaman. Key = nama part multipart di backend
 * (MobileController: attachment utk slip, dokumen_<key> utk sisanya).
 */
object LoanDocs {
    const val SLIP = "slip"
    const val KTP2 = "ktp2"

    /** Urutan tampil di UI; slip ditangani slot sendiri tapi ikut dihitung wajib. */
    val WAJIB = listOf("kk", "ktp1", "kartu_anggota", "id_card")

    fun label(key: String): String = when (key) {
        SLIP -> "Slip Gaji"
        "kk" -> "Kartu Keluarga"
        "ktp1" -> "KTP Peminjam"
        KTP2 -> "KTP Suami/Istri"
        "kartu_anggota" -> "Kartu Anggota"
        "id_card" -> "ID Card Pegawai"
        else -> key
    }

    /** KTP pasangan wajib bila nominal di atas threshold (server: 10 juta). */
    fun perluKtp2(nominal: Long, ktp2Threshold: Long): Boolean = nominal > ktp2Threshold

    /** Dokumen wajib yang belum dilampirkan (kunci, urut sesuai WAJIB). */
    fun kurang(terlampir: Set<String>, nominal: Long, ktp2Threshold: Long): List<String> {
        val perlu = WAJIB + if (perluKtp2(nominal, ktp2Threshold)) listOf(KTP2) else emptyList()
        return perlu.filter { it !in terlampir }
    }

    /** Mime yang diterima server (MobileController::validasi_dokumen). */
    fun mimeDiterima(mime: String?): Boolean =
        mime == "image/jpeg" || mime == "image/png" || mime == "application/pdf"

    const val MAKS_BYTES: Long = 5120L * 1024L
}
