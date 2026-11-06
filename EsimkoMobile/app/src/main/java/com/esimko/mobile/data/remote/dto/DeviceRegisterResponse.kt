package com.esimko.mobile.data.remote.dto

import com.squareup.moshi.JsonClass

@JsonClass(generateAdapter = true)
data class DeviceRegisterResponse(
    val ok: Boolean? = null
)