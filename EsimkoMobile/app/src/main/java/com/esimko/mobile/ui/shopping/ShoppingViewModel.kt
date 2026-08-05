package com.esimko.mobile.ui.shopping

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.Product
import com.esimko.mobile.domain.repository.ShoppingRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

data class ShoppingState(
    val products: List<Product> = emptyList(),
    val isLoading: Boolean = false,
    val error: String? = null
)

@HiltViewModel
class ShoppingViewModel @Inject constructor(
    private val shoppingRepository: ShoppingRepository
) : ViewModel() {

    private val _state = MutableStateFlow(ShoppingState())
    val state: StateFlow<ShoppingState> = _state

    init {
        loadProducts()
    }

    fun loadProducts() {
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoading = true, error = null)
            when (val result = shoppingRepository.getProducts(1)) {
                is Result.Success -> {
                    _state.value = _state.value.copy(
                        products = result.data,
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
