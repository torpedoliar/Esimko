package com.esimko.mobile.util

import androidx.compose.ui.graphics.Color
import com.google.common.truth.Truth.assertThat
import org.junit.Test

class StatusMetaTest {

    @Test
    fun `hex enam digit dengan pagar diparse`() {
        assertThat(StatusMeta.parseColor("#e67e22")).isEqualTo(Color(0xFFE67E22))
    }

    @Test
    fun `hex tanpa pagar juga diparse`() {
        assertThat(StatusMeta.parseColor("27ae60")).isEqualTo(Color(0xFF27AE60))
    }

    @Test
    fun `hex huruf besar diparse sama`() {
        assertThat(StatusMeta.parseColor("#E74C3C")).isEqualTo(StatusMeta.parseColor("#e74c3c"))
    }

    @Test
    fun `hex delapan digit dibaca sebagai ARGB`() {
        assertThat(StatusMeta.parseColor("#8027AE60")).isEqualTo(Color(0x8027AE60))
    }

    @Test
    fun `masukan rusak balik null bukan crash`() {
        assertThat(StatusMeta.parseColor(null)).isNull()
        assertThat(StatusMeta.parseColor("")).isNull()
        assertThat(StatusMeta.parseColor("hijau")).isNull()
        assertThat(StatusMeta.parseColor("#12")).isNull()
        assertThat(StatusMeta.parseColor("#zzzzzz")).isNull()
    }

    @Test
    fun `ikon sesuai status nyata dari DB`() {
        assertThat(StatusMeta.iconFor("Belum Verifikasi")).isEqualTo(StatusIcon.PENDING)
        assertThat(StatusMeta.iconFor("Disetujui")).isEqualTo(StatusIcon.APPROVED)
        assertThat(StatusMeta.iconFor("Ditolak")).isEqualTo(StatusIcon.REJECTED)
        assertThat(StatusMeta.iconFor("Selesai")).isEqualTo(StatusIcon.DONE)
        assertThat(StatusMeta.iconFor("Pinjaman Lunas")).isEqualTo(StatusIcon.DONE)
        assertThat(StatusMeta.iconFor("Dibayar")).isEqualTo(StatusIcon.DONE)
        assertThat(StatusMeta.iconFor("Dibatalkan")).isEqualTo(StatusIcon.CANCELLED)
        assertThat(StatusMeta.iconFor("Simulasi")).isEqualTo(StatusIcon.PENDING)
        assertThat(StatusMeta.iconFor("Payroll")).isEqualTo(StatusIcon.APPROVED)
        assertThat(StatusMeta.iconFor("Belum Bayar")).isEqualTo(StatusIcon.PENDING)
        assertThat(StatusMeta.iconFor("Hold")).isEqualTo(StatusIcon.PENDING)
        assertThat(StatusMeta.iconFor("Kredit Lunas")).isEqualTo(StatusIcon.DONE)
    }

    @Test
    fun `status tak dikenal dapat ikon UNKNOWN bukan exception`() {
        assertThat(StatusMeta.iconFor("Entah Apa")).isEqualTo(StatusIcon.UNKNOWN)
        assertThat(StatusMeta.iconFor("")).isEqualTo(StatusIcon.UNKNOWN)
    }

    @Test
    fun `pencocokan status abai huruf besar kecil dan spasi berlebih`() {
        assertThat(StatusMeta.iconFor("  ditolak  ")).isEqualTo(StatusIcon.REJECTED)
        assertThat(StatusMeta.isFinal("  SELESAI ")).isTrue()
    }

    @Test
    fun `status final adalah yang tidak bisa berubah lagi`() {
        assertThat(StatusMeta.isFinal("Ditolak")).isTrue()
        assertThat(StatusMeta.isFinal("Selesai")).isTrue()
        assertThat(StatusMeta.isFinal("Dibatalkan")).isTrue()
        assertThat(StatusMeta.isFinal("Pinjaman Lunas")).isTrue()
        assertThat(StatusMeta.isFinal("Dibayar")).isTrue()
        assertThat(StatusMeta.isFinal("Kredit Lunas")).isTrue()
    }

    @Test
    fun `status berjalan tidak final`() {
        assertThat(StatusMeta.isFinal("Belum Verifikasi")).isFalse()
        assertThat(StatusMeta.isFinal("Disetujui")).isFalse()
        assertThat(StatusMeta.isFinal("Simulasi")).isFalse()
        assertThat(StatusMeta.isFinal("Payroll")).isFalse()
        assertThat(StatusMeta.isFinal("Hold")).isFalse()
        assertThat(StatusMeta.isFinal("Belum Bayar")).isFalse()
    }

    @Test
    fun `status baru yang tak dikenal dianggap berjalan`() {
        // Ceiling yang disengaja: status baru di DB masuk grup "Berjalan",
        // bukan hilang dari daftar.
        assertThat(StatusMeta.isFinal("Status Baru Entah Apa")).isFalse()
    }
}
