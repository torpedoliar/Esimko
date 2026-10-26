package com.esimko.mobile.util

/**
 * Aturan dokumen pengajuan pinjaman. Key = nama part multipart di backend
 * (MobileController: attachment utk slip, dokumen_<key> utk sisanya).
 */
object LoanDocs {
    /** Slot slip gaji — syarat Okt 2026: 3 bulan terakhir (key = jenis_dok di server). */
    val SLIP_SLOTS = listOf("slip_bulan_ini", "slip_bulan_lalu", "slip_dua_bulan_lalu")
    const val SLIP = "slip_bulan_ini" // slot pertama (part "attachment" di submit)
    const val KTP2 = "ktp2"
    /** Surat Hutang bermaterai — wajib hanya utk Pinjaman Jangka Panjang (jenis 9). */
    const val SURAT_HUTANG = "surat_hutang"
    const val JENIS_JANGKA_PANJANG = 9

    /** Urutan tampil di UI; slip ditangani slot sendiri tapi ikut dihitung wajib. */
    val WAJIB = listOf("kk", "ktp1", "kartu_anggota", "id_card")

    fun label(key: String): String = when (key) {
        "slip_bulan_ini" -> "Slip Gaji — Bulan Ini"
        "slip_bulan_lalu" -> "Slip Gaji — 1 Bulan Lalu"
        "slip_dua_bulan_lalu" -> "Slip Gaji — 2 Bulan Lalu"
        "kk" -> "Kartu Keluarga"
        "ktp1" -> "KTP Peminjam"
        KTP2 -> "KTP Suami/Istri"
        "kartu_anggota" -> "Kartu Anggota"
        "id_card" -> "ID Card Pegawai"
        SURAT_HUTANG -> "Surat Hutang (Materai)"
        else -> key
    }

    /** KTP pasangan wajib bila nominal di atas threshold (server: 10 juta). */
    fun perluKtp2(nominal: Long, ktp2Threshold: Long): Boolean = nominal > ktp2Threshold

    /** Dokumen wajib yang belum dilampirkan (kunci, urut sesuai tampilan). */
    fun kurang(terlampir: Set<String>, nominal: Long, ktp2Threshold: Long): List<String> {
        val perlu = SLIP_SLOTS + WAJIB + if (perluKtp2(nominal, ktp2Threshold)) listOf(KTP2) else emptyList()
        return perlu.filter { it !in terlampir }
    }

    /** Mime yang diterima server (MobileController::validasi_dokumen). */
    fun mimeDiterima(mime: String?): Boolean =
        mime == "image/jpeg" || mime == "image/png" || mime == "application/pdf"

    /**
     * Teks bantuan tiap slot — WAJIB menyebut dokumen apa yang diupload, bukan hanya
     * aturannya (anggota bingung bila cuma "wajib di atas Rp 10 juta").
     */
    fun infoSlot(key: String, ktp2Threshold: Long): String = when (key) {
        "slip_bulan_ini" -> "Unggah slip gaji bulan INI — foto atau PDF (wajib)"
        "slip_bulan_lalu" -> "Unggah slip gaji 1 bulan lalu — foto atau PDF (wajib)"
        "slip_dua_bulan_lalu" -> "Unggah slip gaji 2 bulan lalu — foto atau PDF (wajib)"
        "kk" -> "Unggah foto/scan Kartu Keluarga — foto atau PDF (wajib)"
        "ktp1" -> "Unggah foto KTP Anda sendiri selaku peminjam — foto atau PDF (wajib)"
        KTP2 -> "Unggah foto KTP suami/istri Anda — dokumen ini muncul karena nominal pinjaman di atas Rp ${formatRingkas(ktp2Threshold)}. Boleh foto atau PDF."
        "kartu_anggota" -> "Unggah foto kartu anggota koperasi Anda — foto atau PDF (wajib)"
        "id_card" -> "Unggah foto ID Card pegawai Anda — foto atau PDF (wajib)"
        SURAT_HUTANG -> "Unggah Surat Hutang yang sudah DITANDATANGANI dan BERMATERAI — foto atau PDF (wajib untuk Pinjaman Jangka Panjang)"
        else -> "Unggah ${label(key)} — foto atau PDF"
    }

    /** 10000000 → "10.000.000" (pemisah ribuan gaya Indonesia). */
    private fun formatRingkas(n: Long): String =
        n.toString().reversed().chunked(3).joinToString(".").reversed()

    const val MAKS_BYTES: Long = 5120L * 1024L
}
