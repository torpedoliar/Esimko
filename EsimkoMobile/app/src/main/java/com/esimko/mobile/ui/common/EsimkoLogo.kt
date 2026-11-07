package com.esimko.mobile.ui.common

import androidx.compose.foundation.Image
import androidx.compose.material3.MaterialTheme
import androidx.compose.runtime.Composable
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.luminance
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.res.painterResource
import com.esimko.mobile.R

/** Logo resmi gabungan: lambang berwarna tetap utuh, tulisan mengikuti warna latar. */
@Composable
fun EsimkoLogo(
    modifier: Modifier = Modifier,
    background: Color = MaterialTheme.colorScheme.background
) {
    Image(
        painter = painterResource(
            if (background.luminance() < 0.5f) R.drawable.logo_esimko_wordmark_light
            else R.drawable.logo_esimko_wordmark
        ),
        contentDescription = "Logo eSIMKO — Koperasi Karyawan Satya Sejahtera",
        contentScale = ContentScale.Fit,
        modifier = modifier
    )
}
