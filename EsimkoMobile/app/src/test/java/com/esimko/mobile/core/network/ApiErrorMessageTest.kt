package com.esimko.mobile.core.network

import com.google.common.truth.Truth.assertThat
import okhttp3.MediaType.Companion.toMediaType
import okhttp3.ResponseBody.Companion.toResponseBody
import org.junit.Test
import retrofit2.HttpException
import retrofit2.Response
import java.io.IOException
import java.net.SocketTimeoutException
import java.net.UnknownHostException

/**
 * Self-check untuk `apiErrorMessage` — logika non-trivial (peta kode HTTP + IO exception
 * ke teks humanize). Jalankan: `./gradlew :app:testDebugUnitTest --tests ApiErrorMessageTest`.
 */
class ApiErrorMessageTest {

    private fun httpError(code: Int, body: String = "", mediaType: String = "text/plain"): HttpException =
        HttpException(
            Response.error<Any>(
                code,
                body.toResponseBody(mediaType.toMediaType())
            )
        )

    @Test fun http413_isFileTooLarge() {
        // nginx 413 body = HTML, bukan JSON envelope → harus pakai teks per kode.
        assertThat(apiErrorMessage(httpError(413, "<html>413</html>")))
            .contains("File terlalu besar")
    }

    @Test fun http422_keepsBackendMessage_whenPresent() {
        // Backend Laravel envelope {success,message} → pesan spesifik dipakai apa adanya.
        val body = """{"success":false,"message":"Nominal harus lebih dari 0"}"""
        assertThat(apiErrorMessage(httpError(422, body, "application/json")))
            .isEqualTo("Nominal harus lebih dari 0")
    }

    @Test fun http401_isBadCredentials() {
        assertThat(apiErrorMessage(httpError(401, "")))
            .contains("password salah")
    }

    @Test fun http500_isServerError() {
        assertThat(apiErrorMessage(httpError(500, "")))
            .contains("Server")
    }

    @Test fun socketTimeout_isConnectionSlow() {
        // Pesan mengandung "koneksi" (huruf kecil) — assertThat.contains bersifat case-sensitive.
        assertThat(apiErrorMessage(SocketTimeoutException("timeout")))
            .contains("oneksi")
    }

    @Test fun unknownHost_isNoConnection() {
        assertThat(apiErrorMessage(UnknownHostException("x")))
            .contains("terhubung")
    }

    @Test fun genericIo_isConnectionError() {
        assertThat(apiErrorMessage(IOException("reset")))
            .contains("oneksi")
    }
}
