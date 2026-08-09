package com.esimko.mobile.ui.common

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.widthIn
import androidx.compose.material3.LocalContentColor
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.semantics.contentDescription
import androidx.compose.ui.semantics.semantics
import androidx.compose.ui.text.TextStyle
import androidx.compose.ui.unit.dp
import com.esimko.mobile.ui.theme.GoldOnHero
import com.esimko.mobile.ui.theme.HeroGreen
import com.esimko.mobile.ui.theme.MoneyHero
import com.esimko.mobile.ui.theme.MoneyRow
import com.esimko.mobile.util.MoneyFormatter

/**
 * Satu-satunya jalan menampilkan rupiah di UI — menjamin tabular figures ikut
 * terpasang, bukan bergantung pemanggil ingat. Negatif: minus + warna TETAP
 * tanggung jawab pemanggil (spec §6: negatif pakai minus + warna plus label).
 */
@Composable
fun Money(
    amount: Long,
    modifier: Modifier = Modifier,
    style: TextStyle = MoneyRow,
    color: Color = Color.Unspecified,
    showSign: Boolean = false,
    contentDescription: String? = null
) {
    val text = if (showSign && amount > 0) "+" + MoneyFormatter.format(amount) else MoneyFormatter.format(amount)
    val resolvedColor = if (color == Color.Unspecified) LocalContentColor.current else color
    val semanticsModifier = if (contentDescription != null) {
        modifier.semantics { this.contentDescription = contentDescription }
    } else {
        modifier
    }
    Text(
        text = text,
        style = style,
        color = resolvedColor,
        modifier = semanticsModifier,
        maxLines = 1,
        softWrap = false
    )
}

@LightDarkPreview
@Composable
private fun MoneyHeroPreview() {
    EsimkoPreview {
        Box(Modifier.background(HeroGreen).padding(24.dp)) {
            Money(amount = 4_250_000L, style = MoneyHero, color = GoldOnHero)
        }
    }
}

@LightDarkPreview
@Composable
private fun MoneyRowPreview() {
    EsimkoPreview {
        Column {
            Money(amount = 1_250_000L)
            Money(amount = 0L)
        }
    }
}

@LightDarkPreview
@Composable
private fun MoneySignedPreview() {
    EsimkoPreview {
        Row(Modifier.widthIn(min = 200.dp)) {
            Money(amount = 0L)
            Money(amount = -50_000L, color = Color.Red, showSign = false)
            Money(amount = 75_000L, showSign = true)
        }
    }
}
