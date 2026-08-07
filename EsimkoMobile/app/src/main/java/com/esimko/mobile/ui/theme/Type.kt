package com.esimko.mobile.ui.theme

import androidx.compose.material3.Typography
import androidx.compose.ui.text.TextStyle
import androidx.compose.ui.text.font.Font
import androidx.compose.ui.text.font.FontFamily
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.sp
import com.esimko.mobile.R

val EsimkoFontFamily = FontFamily(
    Font(R.font.plus_jakarta_sans_regular, FontWeight.Normal),
    Font(R.font.plus_jakarta_sans_medium, FontWeight.Medium),
    Font(R.font.plus_jakarta_sans_semibold, FontWeight.SemiBold),
    Font(R.font.plus_jakarta_sans_bold, FontWeight.Bold)
)

// Nominal: tabular figures wajib. Tanpa tnum, saldo bergeser horizontal setiap
// angka berubah — paling kelihatan di hero yang ukurannya besar.
private const val TABULAR = "tnum"

val MoneyHero = TextStyle(
    fontFamily = EsimkoFontFamily,
    fontWeight = FontWeight.Bold,
    fontSize = 32.sp,
    lineHeight = 40.sp,
    letterSpacing = (-0.5).sp,
    fontFeatureSettings = TABULAR
)

val MoneyRow = TextStyle(
    fontFamily = EsimkoFontFamily,
    fontWeight = FontWeight.Bold,
    fontSize = 16.sp,
    lineHeight = 22.sp,
    letterSpacing = 0.sp,
    fontFeatureSettings = TABULAR
)

val MoneySmall = TextStyle(
    fontFamily = EsimkoFontFamily,
    fontWeight = FontWeight.SemiBold,
    fontSize = 14.sp,
    lineHeight = 20.sp,
    letterSpacing = 0.sp,
    fontFeatureSettings = TABULAR
)

val Typography = Typography(
    displayLarge = TextStyle(
        fontFamily = EsimkoFontFamily, fontWeight = FontWeight.Bold,
        fontSize = 40.sp, lineHeight = 48.sp, letterSpacing = (-0.5).sp
    ),
    displayMedium = TextStyle(
        fontFamily = EsimkoFontFamily, fontWeight = FontWeight.Bold,
        fontSize = 32.sp, lineHeight = 40.sp, letterSpacing = (-0.25).sp
    ),
    displaySmall = TextStyle(
        fontFamily = EsimkoFontFamily, fontWeight = FontWeight.Bold,
        fontSize = 28.sp, lineHeight = 36.sp, letterSpacing = 0.sp
    ),
    headlineLarge = TextStyle(
        fontFamily = EsimkoFontFamily, fontWeight = FontWeight.Bold,
        fontSize = 28.sp, lineHeight = 36.sp, letterSpacing = 0.sp
    ),
    headlineMedium = TextStyle(
        fontFamily = EsimkoFontFamily, fontWeight = FontWeight.Bold,
        fontSize = 24.sp, lineHeight = 32.sp, letterSpacing = 0.sp
    ),
    headlineSmall = TextStyle(
        fontFamily = EsimkoFontFamily, fontWeight = FontWeight.SemiBold,
        fontSize = 20.sp, lineHeight = 28.sp, letterSpacing = 0.sp
    ),
    // Judul layar — spec §2: 22sp Bold
    titleLarge = TextStyle(
        fontFamily = EsimkoFontFamily, fontWeight = FontWeight.Bold,
        fontSize = 22.sp, lineHeight = 28.sp, letterSpacing = 0.sp
    ),
    // Judul kartu — 16sp Medium
    titleMedium = TextStyle(
        fontFamily = EsimkoFontFamily, fontWeight = FontWeight.Medium,
        fontSize = 16.sp, lineHeight = 22.sp, letterSpacing = 0.sp
    ),
    titleSmall = TextStyle(
        fontFamily = EsimkoFontFamily, fontWeight = FontWeight.Medium,
        fontSize = 14.sp, lineHeight = 20.sp, letterSpacing = 0.1.sp
    ),
    bodyLarge = TextStyle(
        fontFamily = EsimkoFontFamily, fontWeight = FontWeight.Normal,
        fontSize = 16.sp, lineHeight = 26.sp, letterSpacing = 0.15.sp
    ),
    // Isi — 14sp Regular
    bodyMedium = TextStyle(
        fontFamily = EsimkoFontFamily, fontWeight = FontWeight.Normal,
        fontSize = 14.sp, lineHeight = 22.sp, letterSpacing = 0.15.sp
    ),
    bodySmall = TextStyle(
        fontFamily = EsimkoFontFamily, fontWeight = FontWeight.Normal,
        fontSize = 12.sp, lineHeight = 18.sp, letterSpacing = 0.2.sp
    ),
    labelLarge = TextStyle(
        fontFamily = EsimkoFontFamily, fontWeight = FontWeight.SemiBold,
        fontSize = 14.sp, lineHeight = 20.sp, letterSpacing = 0.1.sp
    ),
    // Label/caption — 12sp Medium
    labelMedium = TextStyle(
        fontFamily = EsimkoFontFamily, fontWeight = FontWeight.Medium,
        fontSize = 12.sp, lineHeight = 16.sp, letterSpacing = 0.4.sp
    ),
    // Spec: tidak ada teks di bawah 12sp — labelSmall dinaikkan dari 11sp.
    labelSmall = TextStyle(
        fontFamily = EsimkoFontFamily, fontWeight = FontWeight.Medium,
        fontSize = 12.sp, lineHeight = 16.sp, letterSpacing = 0.4.sp
    )
)
