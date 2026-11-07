package com.esimko.mobile.util

import java.io.ByteArrayInputStream
import java.io.IOException
import org.junit.Assert.*
import org.junit.Test

class ImageLimitsTest {
    @Test fun `panoramic and portrait decode limited by longest side`() {
        for ((width, height) in listOf(12000 to 500, 500 to 12000, 8000 to 6000, 1280 to 1280)) {
            val sample = imageSampleSize(width, height, 1280)
            assertTrue((maxOf(width, height).toLong() + sample - 1) / sample <= 1280)
            assertEquals(0, sample and (sample - 1))
        }
    }
    @Test fun `small images not enlarged`() { assertEquals(1, imageSampleSize(500, 400, 1280)) }
    @Test fun `invalid dimensions safe`() { assertEquals(1, imageSampleSize(-1, 0, 1280)) }
    @Test fun `reads exactly allowed input`() {
        val data = byteArrayOf(1, 2, 3)
        assertArrayEquals(data, ByteArrayInputStream(data).readBounded(3))
    }
    @Test(expected = IOException::class) fun `oversized input rejected`() {
        ByteArrayInputStream(ByteArray(100)).readBounded(99)
    }
    @Test(expected = IOException::class) fun `empty camera output rejected`() {
        ByteArrayInputStream(ByteArray(0)).readBounded()
    }
}
