package com.esimko.mobile.util

import android.graphics.Bitmap
import android.graphics.BitmapFactory
import java.io.ByteArrayOutputStream

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

/**
 * Kompres gambar sebelum unggah: downscale ke maxPx lalu re-encode JPEG quality.
 * Foto kamera HP 3-8MB → ~100-300KB. Server dev default nginx 1MB → tanpa ini HTTP 413.
 * PNG/WEBP juga di-encode ulang ke JPEG (ukuran jauh lebih kecil, format upload tetap gambar).
 * Gagal decode (file rusak/bukan gambar) → null, pemanggil tampilkan error humanize.
 */
fun ByteArray.compressForUpload(maxPx: Int = 1280, quality: Int = 80): ByteArray? {
    val bmp = decodeSampled(maxPx) ?: return null
    return try {
        ByteArrayOutputStream().use { out ->
            bmp.compress(Bitmap.CompressFormat.JPEG, quality, out)
            out.toByteArray()
        }
    } finally {
        if (!bmp.isRecycled) bmp.recycle()
    }
}

