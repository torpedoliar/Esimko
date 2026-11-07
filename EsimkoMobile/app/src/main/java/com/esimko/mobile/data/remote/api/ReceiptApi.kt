package com.esimko.mobile.data.remote.api

import okhttp3.ResponseBody
import retrofit2.Response
import retrofit2.http.GET
import retrofit2.http.Path
import retrofit2.http.Streaming

interface ReceiptApi {
    @Streaming
    @GET("mobile/bukti/{jenis}/{id}")
    suspend fun download(@Path("jenis") jenis: String, @Path("id") id: Long): Response<ResponseBody>
}
