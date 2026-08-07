package com.esimko.mobile.ui.news

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.News
import com.esimko.mobile.domain.model.NewsDetail
import com.esimko.mobile.domain.model.Paged
import com.esimko.mobile.domain.model.emptyPaged
import com.esimko.mobile.domain.repository.NewsRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.Job
import kotlinx.coroutines.delay
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

private const val SEARCH_DEBOUNCE_MS = 400L

data class NewsState(
    val news: Paged<News> = emptyPaged(),
    val detail: NewsDetail? = null,
    val isLoading: Boolean = false,
    val detailLoading: Boolean = false,
    val error: String? = null,
    val detailError: String? = null,
    val search: String = ""
)

@HiltViewModel
class NewsViewModel @Inject constructor(
    private val newsRepository: NewsRepository
) : ViewModel() {

    private val _state = MutableStateFlow(NewsState())
    val state: StateFlow<NewsState> = _state.asStateFlow()

    private var searchJob: Job? = null

    init {
        loadNews()
    }

    fun onSearchChange(value: String) {
        _state.value = _state.value.copy(search = value)
        searchJob?.cancel()
        searchJob = viewModelScope.launch {
            delay(SEARCH_DEBOUNCE_MS)
            loadNews()
        }
    }

    fun loadNews() {
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoading = true, error = null)
            // ponytail: per_page=50 + sort di repo = dapat ≤50 berita terbaru-urut-klien.
            // > 50 berita, yang baru mulai hilang (spec §7 :292-297). Perbaikan benarnya satu
            // kata di backend: ->orderBy('created_at','DESC'). Pengguna memutuskan backend
            // tidak disentuh.
            when (val result = newsRepository.getNews(
                page = 1,
                perPage = 50,
                search = _state.value.search.takeIf { it.isNotBlank() }
            )) {
                is Result.Success -> _state.value = _state.value.copy(
                    news = result.data, isLoading = false
                )
                is Result.Error -> _state.value = _state.value.copy(
                    error = result.message, isLoading = false
                )
                is Result.Loading -> Unit
            }
        }
    }

    fun loadDetail(id: Long) {
        viewModelScope.launch {
            _state.value = _state.value.copy(detailLoading = true, detailError = null)
            when (val result = newsRepository.getNewsDetail(id)) {
                is Result.Success -> _state.value = _state.value.copy(
                    detail = result.data, detailLoading = false
                )
                is Result.Error -> _state.value = _state.value.copy(
                    detailError = result.message, detailLoading = false
                )
                is Result.Loading -> Unit
            }
        }
    }
}
