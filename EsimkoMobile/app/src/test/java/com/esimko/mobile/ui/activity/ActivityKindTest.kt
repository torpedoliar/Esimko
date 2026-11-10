package com.esimko.mobile.ui.activity

import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.AccountBalanceWallet
import com.google.common.truth.Truth.assertThat
import org.junit.Test

class ActivityKindTest {

    @Test
    fun `simpanan dan penarikan dibedakan`() {
        assertThat(ActivityKinds.of("Simpanan Sukarela", "transaksi")).isEqualTo(ActivityKind.SAVINGS)
        assertThat(ActivityKinds.of("Penarikan Simpanan Sukarela", "transaksi")).isEqualTo(ActivityKind.WITHDRAWAL)
    }

    @Test
    fun `angsuran didahulukan atas pinjaman`() {
        assertThat(ActivityKinds.of("Angsuran Pinjaman", "transaksi")).isEqualTo(ActivityKind.INSTALLMENT)
        assertThat(ActivityKinds.of("Pinjaman Jangka Panjang", "transaksi")).isEqualTo(ActivityKind.LOAN)
    }

    @Test
    fun `belanja retur dan angsuran belanja dari modul`() {
        assertThat(ActivityKinds.of("Belanja Toko", "toko")).isEqualTo(ActivityKind.SHOPPING)
        assertThat(ActivityKinds.of("Angsuran Belanja", "online")).isEqualTo(ActivityKind.INSTALLMENT)
        assertThat(ActivityKinds.of("Retur Barang", "retur")).isEqualTo(ActivityKind.RETURN)
    }

    @Test
    fun `simpanan memakai ikon dompet netral`() {
        assertThat(ActivityKind.SAVINGS.icon()).isEqualTo(Icons.Outlined.AccountBalanceWallet)
    }

    @Test
    fun `jenis tak dikenal jatuh ke lainnya`() {
        assertThat(ActivityKinds.of("Koreksi", "transaksi")).isEqualTo(ActivityKind.OTHER)
    }
}
