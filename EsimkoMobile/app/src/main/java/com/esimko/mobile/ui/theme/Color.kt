package com.esimko.mobile.ui.theme

import androidx.compose.ui.graphics.Color

// eSIMKO brand — koperasi SIMKO. Primary: hijau brand (#118334 dari logo),
// secondary: hijau desaturated (turunan hue yang sama), bukan biru default.
// Palette tonal M3 dari seed hijau brand.

// ---- Light ----
val Primary = Color(0xFF118334)            // brand green
val OnPrimary = Color(0xFFFFFFFF)
val PrimaryContainer = Color(0xFFB0EAC2)
val OnPrimaryContainer = Color(0xFF0A3B1E)

val Secondary = Color(0xFF3D5E48)          // desaturated green
val OnSecondary = Color(0xFFFFFFFF)
val SecondaryContainer = Color(0xFFD8EADB)
val OnSecondaryContainer = Color(0xFF123521)

val Tertiary = Color(0xFF7A5E1F)           // warm gold accent (logo kuning)
val OnTertiary = Color(0xFFFFFFFF)
val TertiaryContainer = Color(0xFFFFE4A5)
val OnTertiaryContainer = Color(0xFF3F2E00)

val Error = Color(0xFFB3261E)
val OnError = Color(0xFFFFFFFF)
val ErrorContainer = Color(0xFFF9DEDC)
val OnErrorContainer = Color(0xFF410E0B)

val Background = Color(0xFFF7FAF7)         // spec §2 — surface latar light
val OnBackground = Color(0xFF17231A)
val Surface = Color(0xFFF7FAF7)
val OnSurface = Color(0xFF17231A)
val SurfaceVariant = Color(0xFFDCE7DE)
val OnSurfaceVariant = Color(0xFF3F4A42)
val Outline = Color(0xFF6F7A71)
val OutlineVariant = Color(0xFFC2CEC4)

// ---- Dark ----
val DarkPrimary = Color(0xFF7BD494)       // spec §2 — 10.28:1 di atas #101510
val DarkOnPrimary = Color(0xFF003915)
val DarkPrimaryContainer = Color(0xFF00652A)
val DarkOnPrimaryContainer = Color(0xFFB0EAC2)

val DarkSecondary = Color(0xFFA9C9B0)
val DarkOnSecondary = Color(0xFF163421)
val DarkSecondaryContainer = Color(0xFF2C4B36)
val DarkOnSecondaryContainer = Color(0xFFC5E5CB)

val DarkTertiary = Color(0xFFF0C94F)
val DarkOnTertiary = Color(0xFF3F2E00)
val DarkTertiaryContainer = Color(0xFF5A4400)
val DarkOnTertiaryContainer = Color(0xFFFFE4A5)

val DarkError = Color(0xFFFFB4AB)
val DarkOnError = Color(0xFF690005)
val DarkErrorContainer = Color(0xFF93000A)
val DarkOnErrorContainer = Color(0xFFFFDAD6)

val DarkBackground = Color(0xFF101510)
val DarkOnBackground = Color(0xFFDCE9DC)
val DarkSurface = Color(0xFF101510)
val DarkOnSurface = Color(0xFFDCE9DC)
val DarkSurfaceVariant = Color(0xFF3F4A42)
val DarkOnSurfaceVariant = Color(0xFFBFCAC0)
val DarkOutline = Color(0xFF89958B)
val DarkOutlineVariant = Color(0xFF3F4A42)

// ---- Hero (hijau pekat) ----
// Bukan slot M3. Dipakai manual di HeroSurface: kartu saldo Beranda dan header
// layar tumpukan. Tidak masuk colorScheme supaya tidak ada komponen M3 yang
// memakainya tanpa sengaja.
val HeroGreen = Color(0xFF0B3D22)
val DarkHeroGreen = Color(0xFF0E2A19)
val OnHero = Color(0xFFFFFFFF)
val HeroDivider = Color(0x33FFFFFF)       // pemisah di dalam hero, 20% putih

// ---- Emas segel logo ----
// ATURAN: GoldOnHero HANYA di atas hijau hero. Di atas putih kontrasnya 1.68:1.
// Di atas latar terang pakai GoldOnLight; di atas latar gelap pakai GoldOnDark.
val GoldOnHero = Color(0xFFF2C230)
val GoldOnLight = Color(0xFF7A5E1F)
val GoldOnDark = Color(0xFFF0C94F)
