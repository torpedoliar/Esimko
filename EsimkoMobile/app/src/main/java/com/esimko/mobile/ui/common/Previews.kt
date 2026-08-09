package com.esimko.mobile.ui.common

import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Surface
import androidx.compose.runtime.Composable
import androidx.compose.ui.tooling.preview.Preview
import com.esimko.mobile.ui.theme.EsimkoTheme

/**
 * Satu anotasi, dua preview. Spec §3: tiap layar dan komponen wajib punya
 * preview light DAN dark — supaya bisa dinilai di Android Studio tanpa build APK.
 */
@Preview(name = "Light", showBackground = true)
@Preview(name = "Dark", showBackground = true, uiMode = android.content.res.Configuration.UI_MODE_NIGHT_YES)
annotation class LightDarkPreview

/** Pembungkus preview: tema + surface, supaya komponen tidak melayang tanpa latar. */
@Composable
fun EsimkoPreview(
    darkTheme: Boolean = false,
    content: @Composable () -> Unit
) {
    EsimkoTheme(darkTheme = darkTheme) {
        Surface(color = MaterialTheme.colorScheme.background) { content() }
    }
}
