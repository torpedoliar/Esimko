package com.esimko.mobile.ui.shopping

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.*
import com.esimko.mobile.domain.repository.ShoppingRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.Job
import kotlinx.coroutines.delay
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

private const val SEARCH_DEBOUNCE_MS = 400L

data class ShoppingState(
    // produk
    val products: List<Product> = emptyList(),
    val query: String = "",
    val kelompokFilter: String? = null,
    val page: Int = 1,
    val hasMore: Boolean = false,
    val isLoadingProducts: Boolean = false,
    val isLoadingMore: Boolean = false,
    val productsError: String? = null,
    // detail produk
    val selectedProduct: ProductDetail? = null,
    val isLoadingDetail: Boolean = false,
    val detailError: String? = null,
    // keranjang + checkout
    val cart: Cart = Cart(emptyList(), 0),
    val isLoadingCart: Boolean = false,
    val cartError: String? = null,
    val actionError: String? = null,
    val addSuccess: Boolean = false,
    val isAdding: Boolean = false,
    val isCheckingOut: Boolean = false,
    val checkoutFailedItems: List<FailedItemInfo> = emptyList(),
    val checkedOut: Boolean = false
) {
    val cartQty: Int get() = cart.items.sumOf { it.qty }

    val kelompokList: List<String>
        get() = products.map { it.kelompok }.filter { it.isNotBlank() }.distinct().sorted()

    val visibleProducts: List<Product>
        get() = kelompokFilter?.let { k -> products.filter { it.kelompok == k } } ?: products
}

@HiltViewModel
class ShoppingViewModel @Inject constructor(
    private val shoppingRepository: ShoppingRepository
) : ViewModel() {

    private val _state = MutableStateFlow(ShoppingState())
    val state: StateFlow<ShoppingState> = _state.asStateFlow()

    private var searchJob: Job? = null

    init {
        loadProducts()
    }

    fun onQueryChange(value: String) {
        _state.value = _state.value.copy(query = value)
        searchJob?.cancel()
        searchJob = viewModelScope.launch {
            delay(SEARCH_DEBOUNCE_MS)
            loadProducts()
        }
    }

    fun onKelompokChange(value: String?) {
        _state.value = _state.value.copy(
            kelompokFilter = if (_state.value.kelompokFilter == value) null else value
        )
    }

    fun retryProducts() = loadProducts()

    fun loadProducts() {
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoadingProducts = true, productsError = null)
            when (val result = shoppingRepository.getProducts(page = 1, search = _state.value.query)) {
                is Result.Success -> _state.value = _state.value.copy(
                    products = result.data.items,
                    page = result.data.page,
                    hasMore = result.data.hasMore,
                    isLoadingProducts = false
                )
                is Result.Error -> _state.value = _state.value.copy(
                    productsError = result.message,
                    isLoadingProducts = false
                )
                is Result.Loading -> Unit
            }
        }
    }

    fun loadMoreProducts() {
        val s = _state.value
        if (s.isLoadingProducts || s.isLoadingMore || !s.hasMore) return
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoadingMore = true)
            when (val result = shoppingRepository.getProducts(page = s.page + 1, search = s.query)) {
                is Result.Success -> _state.value = _state.value.copy(
                    products = _state.value.products + result.data.items,
                    page = result.data.page,
                    hasMore = result.data.hasMore,
                    isLoadingMore = false
                )
                // Gagal muat halaman berikutnya tidak menghapus produk yang sudah tampil.
                is Result.Error -> _state.value = _state.value.copy(
                    isLoadingMore = false,
                    actionError = result.message
                )
                is Result.Loading -> Unit
            }
        }
    }

    fun loadProductDetail(id: String) {
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoadingDetail = true, detailError = null)
            when (val result = shoppingRepository.getProductDetail(id)) {
                is Result.Success -> _state.value = _state.value.copy(
                    selectedProduct = result.data, isLoadingDetail = false
                )
                is Result.Error -> _state.value = _state.value.copy(
                    detailError = result.message, isLoadingDetail = false
                )
                is Result.Loading -> Unit
            }
        }
    }

    fun loadCart() {
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoadingCart = true, cartError = null)
            when (val result = shoppingRepository.getCart()) {
                is Result.Success -> _state.value = _state.value.copy(
                    cart = result.data, isLoadingCart = false
                )
                is Result.Error -> _state.value = _state.value.copy(
                    cartError = result.message, isLoadingCart = false
                )
                is Result.Loading -> Unit
            }
        }
    }

    // qty = tambahan, bukan total: proses_keranjang menjumlahkan dengan isi lama.
    fun addToCart(productId: Long, qty: Int = 1) {
        viewModelScope.launch {
            _state.value = _state.value.copy(isAdding = true, actionError = null, addSuccess = false)
            when (val result = shoppingRepository.updateCart(productId, qty)) {
                is Result.Success -> _state.value = _state.value.copy(
                    isAdding = false, cart = result.data, actionError = null, addSuccess = true
                )
                is Result.Error -> _state.value = _state.value.copy(
                    isAdding = false, actionError = result.message
                )
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
            _state.value = _state.value.copy(isCheckingOut = true, actionError = null)
            val cart = _state.value.cart
            // it.id = id baris keranjang_belanja (checkout_keranjang mencarinya), bukan id produk.
            val result = shoppingRepository.checkout(
                cart.items.map { it.id },
                cart.items.map { it.qty }
            )
            when (result) {
                is Result.Success -> _state.value = _state.value.copy(
                    checkoutFailedItems = result.data.failedItems,
                    checkedOut = true,
                    isCheckingOut = false
                )
                is Result.Error -> _state.value = _state.value.copy(
                    actionError = result.message, isCheckingOut = false
                )
                is Result.Loading -> Unit
            }
        }
    }

    fun resetAddSuccess() {
        _state.value = _state.value.copy(addSuccess = false)
    }

    fun clearActionError() {
        _state.value = _state.value.copy(actionError = null)
    }

    fun resetCheckout() {
        _state.value = _state.value.copy(checkedOut = false, checkoutFailedItems = emptyList())
        loadCart()
    }
}
