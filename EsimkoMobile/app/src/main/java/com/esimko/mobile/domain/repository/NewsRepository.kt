package com.esimko.mobile.domain.repository

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.News
import com.esimko.mobile.domain.model.NewsDetail

interface NewsRepository {
    suspend fun getNews(page: Int? = null, perPage: Int? = null): Result<List<News>>
    suspend fun getNewsDetail(id: Long): Result<NewsDetail>
}
