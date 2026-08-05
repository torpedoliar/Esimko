package com.esimko.mobile.ui.shopping

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.*
import com.esimko.mobile.domain.repository.ShoppingRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

data class ShoppingState(
    val products: List<Product> = emptyList(),
    val selectedProduct: ProductDetail? = null,
    val cart: Cart = Cart(emptyList(), 0),
    val history: List<PurchaseHistory> = emptyList(),
    val historyDetail: PurchaseDetail? = null,
    val installments: List<ShoppingInstallment> = emptyList(),
    val returns: List<Return> = emptyList(),
    val isLoading: Boolean = false,
    val error: String? = null,
    val actionError: String? = null,
    val checkoutFailedItems: List<FailedItemInfo> = emptyList(),
    val checkedOut: Boolean = false
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
                is Result.Success -> _state.value = _state.value.copy(products = result.data, isLoading = false)
                is Result.Error -> _state.value = _state.value.copy(error = result.message, isLoading = false)
                is Result.Loading -> Unit
            }
        }
    }

    fun loadProductDetail(id: Long) {
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoading = true, error = null)
            when (val result = shoppingRepository.getProductDetail(id)) {
                is Result.Success -> _state.value = _state.value.copy(selectedProduct = result.data, isLoading = false)
                is Result.Error -> _state.value = _state.value.copy(error = result.message, isLoading = false)
                is Result.Loading -> Unit
            }
        }
    }

    fun loadCart() {
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoading = true, error = null)
            when (val result = shoppingRepository.getCart()) {
                is Result.Success -> _state.value = _state.value.copy(cart = result.data, isLoading = false)
                is Result.Error -> _state.value = _state.value.copy(error = result.message, isLoading = false)
                is Result.Loading -> Unit
            }
        }
    }

    fun addToCart(productId: Long, qty: Int = 1) {
        viewModelScope.launch {
            when (val result = shoppingRepository.updateCart(productId, qty)) {
                is Result.Success -> _state.value = _state.value.copy(cart = result.data, actionError = null)
                is Result.Error -> _state.value = _state.value.copy(actionError = result.message)
                is Result.Loading -> Unit
            }
        }
    }

    fun removeFromCart(productId: Long) {
        viewModelScope.launch {
            when (val result = shoppingRepository.deleteFromCart(productId)) {
                is Result.Success -> _state.value = _state.value.copy(cart = result.data)
                is Result.Error -> _state.value = _state.value.copy(actionError = result.message)
                is Result.Loading -> Unit
            }
        }
    }

    fun checkout() {
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoading = true, actionError = null)
            val cart = _state.value.cart
            val result = shoppingRepository.checkout(
                cart.items.map { it.id },
                cart.items.map { it.qty }
            )
            when (result) {
                is Result.Success -> _state.value = _state.value.copy(
                    checkoutFailedItems = result.data.failedItems,
                    checkedOut = true,
                    isLoading = false
                )
                is Result.Error -> _state.value = _state.value.copy(actionError = result.message, isLoading = false)
                is Result.Loading -> Unit
            }
        }
    }

    fun resetCheckout() {
        _state.value = _state.value.copy(checkedOut = false, checkoutFailedItems = emptyList())
        loadCart()
    }

    fun loadHistory(jenis: String) {
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoading = true, error = null)
            when (val result = shoppingRepository.getPurchaseHistory(jenis)) {
                is Result.Success -> _state.value = _state.value.copy(history = result.data, isLoading = false)
                is Result.Error -> _state.value = _state.value.copy(error = result.message, isLoading = false)
                is Result.Loading -> Unit
            }
        }
    }

    fun loadHistoryDetail(jenis: String, id: Long) {
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoading = true, error = null)
            when (val result = shoppingRepository.getPurchaseDetail(jenis, id)) {
                is Result.Success -> _state.value = _state.value.copy(historyDetail = result.data, isLoading = false)
                is Result.Error -> _state.value = _state.value.copy(error = result.message, isLoading = false)
                is Result.Loading -> Unit
            }
        }
    }

    fun loadInstallments() {
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoading = true, error = null)
            when (val result = shoppingRepository.getShoppingInstallments()) {
                is Result.Success -> _state.value = _state.value.copy(installments = result.data, isLoading = false)
                is Result.Error -> _state.value = _state.value.copy(error = result.message, isLoading = false)
                is Result.Loading -> Unit
            }
        }
    }

    fun loadReturns() {
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoading = true, error = null)
            when (val result = shoppingRepository.getReturns()) {
                is Result.Success -> _state.value = _state.value.copy(returns = result.data, isLoading = false)
                is Result.Error -> _state.value = _state.value.copy(error = result.message, isLoading = false)
                is Result.Loading -> Unit
            }
        }
    }
}
