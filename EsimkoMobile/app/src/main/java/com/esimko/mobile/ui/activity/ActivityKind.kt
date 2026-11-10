package com.esimko.mobile.ui.activity

import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.AccountBalanceWallet
import androidx.compose.material.icons.outlined.AssignmentReturn
import androidx.compose.material.icons.outlined.EventRepeat
import androidx.compose.material.icons.outlined.MoveDown
import androidx.compose.material.icons.outlined.ReceiptLong
import androidx.compose.material.icons.outlined.RequestQuote
import androidx.compose.material.icons.outlined.ShoppingBag
import androidx.compose.runtime.Composable
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.luminance
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.material3.MaterialTheme

/**
 * Kelompok visual baris aktivitas: ikon + warna aksen. Diturunkan dari `jenis` (nama dari
 * backend) dan `modul` (kunci dispatch detail) — bukan dari id jenis, karena daftar belanja dan
 * retur tidak membawa id jenis transaksi.
 */
enum class ActivityKind { SAVINGS, WITHDRAWAL, LOAN, INSTALLMENT, SHOPPING, RETURN, OTHER }

object ActivityKinds {

    private val BELANJA = setOf("toko", "konsinyasi", "online")

    fun of(jenis: String, modul: String): ActivityKind {
        val j = jenis.trim().lowercase()
        val m = modul.trim().lowercase()
        return when {
            m == "retur" || "retur" in j -> ActivityKind.RETURN
            m in BELANJA -> if ("angsuran" in j) ActivityKind.INSTALLMENT else ActivityKind.SHOPPING
            "penarikan" in j || "tarik" in j -> ActivityKind.WITHDRAWAL
            "angsuran" in j || "pelunasan" in j -> ActivityKind.INSTALLMENT
            "pinjaman" in j -> ActivityKind.LOAN
            "simpanan" in j || "setor" in j || "bunga" in j -> ActivityKind.SAVINGS
            "belanja" in j -> ActivityKind.SHOPPING
            else -> ActivityKind.OTHER
        }
    }
}

fun ActivityKind.icon(): ImageVector = when (this) {
    ActivityKind.SAVINGS -> Icons.Outlined.AccountBalanceWallet
    ActivityKind.WITHDRAWAL -> Icons.Outlined.MoveDown
    ActivityKind.LOAN -> Icons.Outlined.RequestQuote
    ActivityKind.INSTALLMENT -> Icons.Outlined.EventRepeat
    ActivityKind.SHOPPING -> Icons.Outlined.ShoppingBag
    ActivityKind.RETURN -> Icons.Outlined.AssignmentReturn
    ActivityKind.OTHER -> Icons.Outlined.ReceiptLong
}

/** Ikon segmen di baris chip — memakai kelompok yang sama supaya warna/ikon konsisten. */
fun ActivitySegment.icon(): ImageVector = when (this) {
    ActivitySegment.SIMPANAN -> Icons.Outlined.AccountBalanceWallet
    ActivitySegment.PINJAMAN -> Icons.Outlined.RequestQuote
    ActivitySegment.BELANJA -> Icons.Outlined.ShoppingBag
    ActivitySegment.ANGSURAN_BELANJA -> Icons.Outlined.ReceiptLong
    ActivitySegment.RETUR -> Icons.Outlined.AssignmentReturn
}

/** Aksen per kelompok; versi gelap lebih terang supaya kontras di atas latar gelap. */
@Composable
fun ActivityKind.accent(): Color {
    val dark = MaterialTheme.colorScheme.background.luminance() < 0.5f
    return when (this) {
        ActivityKind.SAVINGS -> MaterialTheme.colorScheme.primary
        ActivityKind.WITHDRAWAL -> if (dark) Color(0xFFFFB870) else Color(0xFFB25E00)
        ActivityKind.LOAN -> if (dark) Color(0xFF9EC5FF) else Color(0xFF1F5FAF)
        ActivityKind.INSTALLMENT -> if (dark) Color(0xFFD3B8FF) else Color(0xFF6B3FA8)
        ActivityKind.SHOPPING -> if (dark) Color(0xFF7FD6C8) else Color(0xFF00796B)
        ActivityKind.RETURN -> if (dark) Color(0xFFF2B8A0) else Color(0xFF9A4A2A)
        ActivityKind.OTHER -> MaterialTheme.colorScheme.onSurfaceVariant
    }
}
