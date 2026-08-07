package com.esimko.mobile.data.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.core.network.apiErrorMessage
import com.esimko.mobile.data.remote.api.NewsApi
import com.esimko.mobile.domain.model.News
import com.esimko.mobile.domain.model.NewsAttachment
import com.esimko.mobile.domain.model.NewsDetail
import com.esimko.mobile.domain.model.Paged
import com.esimko.mobile.domain.repository.NewsRepository
import com.esimko.mobile.util.NewsSort
import javax.inject.Inject

class NewsRepositoryImpl @Inject constructor(
    private val api: NewsApi
) : NewsRepository {

    override suspend fun getNews(page: Int?, perPage: Int?, search: String?): Result<Paged<News>> {
        return try {
            val response = api.getNews(page, perPage, search)
            if (response.success && response.data != null) {
                val items = response.data.map { dto ->
                    News(
                        id = dto.id,
                        judul = dto.judul.orEmpty(),
                        ringkasan = dto.ringkasan,
                        gambar = dto.gambar,
                        tanggal = dto.tanggal.orEmpty(),
                        jumlahAttachment = dto.jumlahAttachment ?: 0
                    )
                }
                val sorted = NewsSort.descByCreated(items)
                val currentPage = response.meta?.page ?: page ?: 1
                val lastPage = response.meta?.last_page ?: currentPage
                Result.Success(Paged(items = sorted, page = currentPage, lastPage = lastPage))
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
                    tanggal = dto.tanggal.orEmpty(),
                    attachments = dto.attachment.orEmpty().map { att ->
                        NewsAttachment(
                            id = att.id,
                            judul = att.judul ?: "Lampiran tanpa judul",
                            url = att.attachment ?: ""
                        )
                    }.filter { it.url.isNotBlank() }
                ))
            } else {
                Result.Error(response.message ?: "Failed to load news detail")
            }
        } catch (e: Exception) {
            Result.Error(apiErrorMessage(e, "Network error"))
        }
    }
}
