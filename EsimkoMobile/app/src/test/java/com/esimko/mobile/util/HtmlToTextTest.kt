package com.esimko.mobile.util

import com.google.common.truth.Truth.assertThat
import org.junit.Test

class HtmlToTextTest {

    @Test
    fun `tag dikupas`() {
        assertThat(HtmlToText.strip("<p>Rapat <strong>anggota</strong> besok</p>"))
            .isEqualTo("Rapat anggota besok")
    }

    @Test
    fun `entity didekode`() {
        assertThat(HtmlToText.strip("Simpanan &amp; Pinjaman &nbsp;naik &quot;5%&quot;"))
            .isEqualTo("Simpanan & Pinjaman naik \"5%\"")
    }

    @Test
    fun `blok jadi satu spasi bukan nempel`() {
        assertThat(HtmlToText.strip("<ul><li>Satu</li><li>Dua</li></ul>"))
            .isEqualTo("Satu Dua")
    }

    @Test
    fun `br jadi spasi`() {
        assertThat(HtmlToText.strip("Baris satu<br>Baris dua<br/>Baris tiga"))
            .isEqualTo("Baris satu Baris dua Baris tiga")
    }

    @Test
    fun `null dan kosong aman`() {
        assertThat(HtmlToText.strip(null)).isEmpty()
        assertThat(HtmlToText.strip("   ")).isEmpty()
    }

    @Test
    fun `teks polos lewat tanpa berubah`() {
        assertThat(HtmlToText.strip("Tidak ada tag di sini")).isEqualTo("Tidak ada tag di sini")
    }

    @Test
    fun `script dan style dibuang beserta isinya`() {
        assertThat(HtmlToText.strip("<p>Isi</p><script>alert(1)</script><style>p{color:red}</style>"))
            .isEqualTo("Isi")
    }
}
