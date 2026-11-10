package com.esimko.mobile.domain.model

/** Metadata only; profile/financial values remain in the session-bound memory snapshot. */
data class ProfileCacheStatus(
    val refreshing: Boolean = false,
    val error: String? = null,
    val updatedAtMillis: Long? = null,
    val sessionEpoch: Long = 0L
)
