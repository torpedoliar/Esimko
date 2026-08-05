package com.esimko.mobile.data.remote.api

import com.esimko.mobile.data.remote.dto.ApiResponse
import com.esimko.mobile.data.remote.dto.NewsResponse
import com.esimko.mobile.data.remote.dto.NewsDetailResponse
import retrofit2.http.GET
import retrofit2.http.Query

interface NewsApi {
    @GET("mobile/berita")
    suspend fun getNews(
        @Query("page") page: Int? = null,
        @Query("per_page") perPage: Int? = null
    ): ApiResponse<List<NewsResponse>>

    @GET("mobile/berita/detail")
    suspend fun getNewsDetail(@Query("id") id: Long): ApiResponse<NewsDetailResponse>
}
