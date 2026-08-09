package com.esimko.mobile.ui.shopping

import com.esimko.mobile.domain.model.Cart
import com.esimko.mobile.domain.model.CartItem
import com.esimko.mobile.domain.model.Product
import org.junit.Assert.assertEquals
import org.junit.Test

class ShoppingStateTest {

    private fun product(id: Long, kelompok: String) = Product(
        id = id, nama = "P$id", harga = 1000, stok = 5, gambar = null,
        satuan = "pcs", kode = "K$id", kategori = "", kelompok = kelompok
    )

    private fun cartItem(id: Long, qty: Int) = CartItem(
        id = id, produkId = id, nama = "P$id", harga = 1000, qty = qty, subtotal = 1000L * qty
    )

    @Test
    fun `cartQty menjumlahkan qty bukan baris`() {
        val state = ShoppingState(cart = Cart(listOf(cartItem(1, 3), cartItem(2, 3)), 6000))
        assertEquals(6, state.cartQty)
    }

    @Test
    fun `cartQty keranjang kosong nol`() {
        assertEquals(0, ShoppingState().cartQty)
    }

    @Test
    fun `kelompokList unik dan urut, kosong dibuang`() {
        val state = ShoppingState(
            products = listOf(
                product(1, "Sembako"), product(2, "Minuman"),
                product(3, "Sembako"), product(4, "")
            )
        )
        assertEquals(listOf("Minuman", "Sembako"), state.kelompokList)
    }

    @Test
    fun `visibleProducts tanpa filter mengembalikan semua`() {
        val state = ShoppingState(products = listOf(product(1, "Sembako"), product(2, "Minuman")))
        assertEquals(2, state.visibleProducts.size)
    }

    @Test
    fun `visibleProducts dengan filter hanya kelompok itu`() {
        val state = ShoppingState(
            products = listOf(product(1, "Sembako"), product(2, "Minuman"), product(3, "Sembako")),
            kelompokFilter = "Sembako"
        )
        assertEquals(listOf(1L, 3L), state.visibleProducts.map { it.id })
    }
}
