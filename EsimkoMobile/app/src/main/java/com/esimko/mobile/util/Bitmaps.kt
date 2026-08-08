package com.esimko.mobile.util

import android.graphics.Bitmap
import android.graphics.BitmapFactory

// ponytail: decode dengan inSampleSize (downsample ke max 1024px) supaya slip gaji/bukti
// resolusi tinggi gak makan 40-50MB heap per bitmap. OOM silent kill di HP low-RAM.
fun ByteArray.decodeSampled(maxPx: Int = 1024): Bitmap? {
    val opts = BitmapFactory.Options().apply { inJustDecodeBounds = true }
    BitmapFactory.decodeByteArray(this, 0, size, opts)
    if (opts.outWidth <= 0) return null
    var s = 1
    while (opts.outWidth / (s * 2) >= maxPx && opts.outHeight / (s * 2) >= maxPx) s *= 2
    opts.inSampleSize = s
    opts.inJustDecodeBounds = false
    return try { BitmapFactory.decodeByteArray(this, 0, size, opts) } catch (e: Exception) { null }
}
