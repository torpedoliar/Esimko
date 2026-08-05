package com.esimko.mobile.data.remote.dto

import com.squareup.moshi.Json
import com.squareup.moshi.JsonClass

@JsonClass(generateAdapter = true)
data class LoginRequest(
    @Json(name = "username") val username: String,
    @Json(name = "password") val password: String
)

@JsonClass(generateAdapter = true)
data class LoginResponse(
    @Json(name = "token") val token: String? = null,
    @Json(name = "no_anggota") val no_anggota: String? = null,
    @Json(name = "nama") val nama: String? = null,
    @Json(name = "avatar") val avatar: String? = null,
    @Json(name = "msg") val msg: String? = null
)

@JsonClass(generateAdapter = true)
data class ChangePasswordRequest(
    @Json(name = "password_lama") val password_lama: String,
    @Json(name = "password_baru") val password_baru: String,
    @Json(name = "ulangi_password_baru") val ulangiPasswordBaru: String
)
