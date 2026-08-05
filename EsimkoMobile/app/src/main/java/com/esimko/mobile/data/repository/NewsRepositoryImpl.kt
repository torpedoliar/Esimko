package com.esimko.mobile.data.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.core.network.apiErrorMessage
import com.esimko.mobile.data.remote.api.NewsApi
import com.esimko.mobile.domain.model.News
import com.esimko.mobile.domain.model.NewsDetail
import com.esimko.mobile.domain.repository.NewsRepository
import javax.inject.Inject

class NewsRepositoryImpl @Inject constructor(
    private val api: NewsApi
) : NewsRepository {

    override suspend fun getNews(page: Int?, perPage: Int?): Result<List<News>> {
        return try {
            val response = api.getNews(page, perPage)
            if (response.success && response.data != null) {
                Result.Success(response.data.map { dto ->
                    News(
                        id = dto.id,
                        judul = dto.judul.orEmpty(),
                        ringkasan = dto.ringkasan,
                        gambar = dto.gambar,
                        tanggal = dto.tanggal.orEmpty()
                    )
                })
            } else {
                Result.Error(response.message ?: "Failed to load news")
            }
        } catch (e: Exception) {
            Result.Error(apiErrorMessage(e, "Network error"))
        }
    }

    override suspend fun getNewsDetail(id: Long): Result<NewsDetail> {
        return try {
            val response = api.getNewsDetail(id)
            if (response.success && response.data != null) {
                val dto = response.data
                Result.Success(NewsDetail(
                    id = dto.id,
                    judul = dto.judul.orEmpty(),
                    konten = dto.konten.orEmpty(),
                    gambar = dto.gambar,
                    tanggal = dto.tanggal.orEmpty()
                ))
            } else {
                Result.Error(response.message ?: "Failed to load news detail")
            }
        } catch (e: Exception) {
            Result.Error(apiErrorMessage(e, "Network error"))
        }
    }
}
