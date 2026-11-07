package com.esimko.mobile.util

import java.io.ByteArrayOutputStream
import java.io.IOException
import java.io.InputStream

internal const val MAX_IMAGE_INPUT_BYTES = 20 * 1024 * 1024

/** Bound the longest side, including panoramic images, before allocating a bitmap. */
internal fun imageSampleSize(width: Int, height: Int, maxPx: Int): Int {
    require(maxPx > 0)
    if (width <= 0 || height <= 0) return 1
    var sample = 1
    val longest = maxOf(width, height)
    while ((longest.toLong() + sample - 1) / sample > maxPx) sample *= 2
    return sample
}

internal fun InputStream.readBounded(limit: Int = MAX_IMAGE_INPUT_BYTES): ByteArray {
    require(limit > 0)
    val output = ByteArrayOutputStream()
    val buffer = ByteArray(8192)
    while (true) {
        val read = read(buffer)
        if (read < 0) break
        if (output.size().toLong() + read > limit) throw IOException("Ukuran berkas terlalu besar (maksimal ${limit / 1024 / 1024} MB).")
        output.write(buffer, 0, read)
    }
    if (output.size() == 0) throw IOException("Berkas kosong. Silakan ambil foto atau pilih berkas lagi.")
    return output.toByteArray()
}
