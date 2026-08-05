package com.esimko.mobile.ui.news

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.News
import com.esimko.mobile.domain.repository.NewsRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

data class NewsState(
    val news: List<News> = emptyList(),
    val isLoading: Boolean = false,
    val error: String? = null
)

@HiltViewModel
class NewsViewModel @Inject constructor(
    private val newsRepository: NewsRepository
) : ViewModel() {

    private val _state = MutableStateFlow(NewsState())
    val state: StateFlow<NewsState> = _state

    init {
        loadNews()
    }

    fun loadNews() {
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoading = true, error = null)
            when (val result = newsRepository.getNews()) {
                is Result.Success -> {
                    _state.value = _state.value.copy(
                        news = result.data,
                        isLoading = false
                    )
                }
                is Result.Error -> {
                    _state.value = _state.value.copy(
                        error = result.message,
                        isLoading = false
                    )
                }
                is Result.Loading -> Unit
            }
        }
    }
}
