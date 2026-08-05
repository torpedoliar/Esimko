package com.esimko.mobile.core.network

import com.squareup.moshi.Moshi
import com.squareup.moshi.Types
import retrofit2.HttpException

private val errorType = Types.newParameterizedType(ApiErrorBody::class.java)

data class ApiErrorBody(
    val success: Boolean? = null,
    val message: String? = null
)

/**
 * Ambil pesan error dari response body backend (envelope {success,message}).
 * HttpException default e.message cuma "HTTP 400 Bad Request" — tak membantu user.
 */
fun apiErrorMessage(e: Exception, fallback: String = "Terjadi kesalahan, coba lagi"): String {
    if (e is HttpException) {
        val body = e.response()?.errorBody()?.string()
        if (!body.isNullOrEmpty()) {
            try {
                val parsed: ApiErrorBody? = Moshi.Builder().build()
                    .adapter<ApiErrorBody>(errorType)
                    .fromJson(body)
                if (!parsed?.message.isNullOrBlank()) {
                    return parsed!!.message!!
                }
            } catch (_: Exception) {
                // body bukan JSON envelope — fallback
            }
        }
        return "Terjadi kesalahan (${e.code()}), coba lagi"
    }
    return e.message ?: fallback
}
