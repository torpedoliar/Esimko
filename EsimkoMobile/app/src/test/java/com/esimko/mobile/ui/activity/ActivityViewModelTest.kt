package com.esimko.mobile.ui.activity

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.Paged
import com.esimko.mobile.domain.model.Transaction
import com.esimko.mobile.domain.model.TransactionStatus
import com.esimko.mobile.domain.repository.MasterRepository
import com.esimko.mobile.domain.repository.ShoppingRepository
import com.esimko.mobile.domain.repository.TransactionRepository
import io.mockk.coEvery
import io.mockk.coVerify
import io.mockk.mockk
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.ExperimentalCoroutinesApi
import kotlinx.coroutines.test.StandardTestDispatcher
import kotlinx.coroutines.test.resetMain
import kotlinx.coroutines.test.runTest
import kotlinx.coroutines.test.setMain
import org.junit.After
import org.junit.Assert.*
import org.junit.Before
import org.junit.Test
import kotlin.coroutines.Continuation
import kotlin.coroutines.resume
import kotlin.coroutines.suspendCoroutine

@OptIn(ExperimentalCoroutinesApi::class)
class ActivityViewModelTest {
    private val dispatcher = StandardTestDispatcher()
    private val transactions = mockk<TransactionRepository>()
    private val shopping = mockk<ShoppingRepository>()
    private val master = mockk<MasterRepository>()

    @Before fun setup() {
        Dispatchers.setMain(dispatcher)
        coEvery { master.getTransactionStatuses(any()) } returns Result.Success(emptyList())
    }
    @After fun cleanup() { Dispatchers.resetMain() }

    private fun vm() = ActivityViewModel(transactions, shopping, master)
    private fun trx(id: Long) = Transaction(id, "Simpanan", "transaksi", 100L,
        "2026-08-01", "Selesai", "Selesai", null)
    private fun page(vararg ids: Long, number: Int = 1, last: Int = 1): Result<Paged<Transaction>> =
        Result.Success(Paged(ids.map(::trx), number, last))

    @Test fun `savings and loan tabs send their backend filter modules`() = runTest {
        coEvery { transactions.getTransactions(any(), any(), any(), any(), any(), any(), any(), any(), any()) } returns page(1)
        val vm = vm()
        vm.start(null)
        dispatcher.scheduler.runCurrent()
        vm.selectSegment(ActivitySegment.PINJAMAN)
        dispatcher.scheduler.runCurrent()
        coVerify { transactions.getTransactions("simpanan", null, null, null, null, null, "terbaru", 1, 20) }
        coVerify { transactions.getTransactions("pinjaman", null, null, null, null, null, "terbaru", 1, 20) }
        coVerify { master.getTransactionStatuses("simpanan") }
        coVerify { master.getTransactionStatuses("pinjaman") }
    }

    @Test fun `late uncancellable segment response cannot overwrite selected segment`() = runTest {
        lateinit var old: Continuation<Result<Paged<Transaction>>>
        coEvery { transactions.getTransactions("simpanan", any(), any(), any(), any(), any(), any(), any(), any()) } coAnswers {
            suspendCoroutine { old = it }
        }
        coEvery { transactions.getTransactions("pinjaman", any(), any(), any(), any(), any(), any(), any(), any()) } returns page(2)
        val vm = vm()
        vm.start(null)
        dispatcher.scheduler.runCurrent()
        vm.selectSegment(ActivitySegment.PINJAMAN)
        dispatcher.scheduler.runCurrent()
        old.resume(page(1))
        dispatcher.scheduler.runCurrent()
        assertEquals(ActivitySegment.PINJAMAN, vm.state.value.segment)
        assertEquals(listOf(2L), vm.state.value.items.map { it.id })
    }

    @Test fun `search invalidates old request before debounce and segment cancels pending search`() = runTest {
        lateinit var old: Continuation<Result<Paged<Transaction>>>
        coEvery { transactions.getTransactions("simpanan", any(), any(), any(), any(), any(), any(), any(), any()) } coAnswers {
            suspendCoroutine { old = it }
        }
        coEvery { transactions.getTransactions("pinjaman", any(), any(), any(), any(), any(), any(), any(), any()) } returns page(2)
        val vm = vm()
        vm.start(null)
        dispatcher.scheduler.runCurrent()
        vm.onSearchChange("new")
        old.resume(page(1))
        dispatcher.scheduler.runCurrent()
        assertTrue(vm.state.value.items.isEmpty())
        vm.selectSegment(ActivitySegment.PINJAMAN)
        dispatcher.scheduler.advanceUntilIdle()
        coVerify(exactly = 1) { transactions.getTransactions("pinjaman", any(), any(), any(), any(), any(), any(), any(), any()) }
        assertEquals("", vm.state.value.search)
    }

    @Test fun `search debounce sends only latest query and preserves snapshot across purchase calls`() = runTest {
        lateinit var old: Continuation<Result<Paged<com.esimko.mobile.domain.model.PurchaseHistory>>>
        coEvery { shopping.getPurchaseHistory("toko", any(), any(), null, any()) } coAnswers {
            suspendCoroutine { old = it }
        }
        coEvery { shopping.getPurchaseHistory(any(), any(), any(), "latest", any()) } returns
            Result.Success(Paged(emptyList(), 1, 1))
        coEvery { shopping.getPurchaseHistory("konsinyasi", any(), any(), null, any()) } returns
            Result.Success(Paged(emptyList(), 1, 1))
        coEvery { shopping.getPurchaseHistory("online", any(), any(), null, any()) } returns
            Result.Success(Paged(emptyList(), 1, 1))
        val vm = vm()
        vm.start("belanja")
        dispatcher.scheduler.runCurrent()
        vm.onSearchChange("first")
        dispatcher.scheduler.runCurrent()
        dispatcher.scheduler.advanceTimeBy(200)
        vm.onSearchChange("latest")
        old.resume(Result.Success(Paged(emptyList(), 1, 1)))
        dispatcher.scheduler.runCurrent()
        dispatcher.scheduler.advanceUntilIdle()
        // Even an uncancellable old multi-call request must not mix in the new search.
        coVerify { shopping.getPurchaseHistory("konsinyasi", 1, 20, null, "terbaru") }
        coVerify { shopping.getPurchaseHistory("online", 1, 20, null, "terbaru") }
        coVerify(exactly = 0) { shopping.getPurchaseHistory(any(), any(), any(), "first", any()) }
        coVerify(exactly = 3) { shopping.getPurchaseHistory(any(), 1, 20, "latest", "terbaru") }
        assertEquals("latest", vm.state.value.search)
        assertFalse(vm.state.value.isFirstLoad)
    }

    @Test fun `refresh rejects old pagination response and resets loading flags`() = runTest {
        lateinit var old: Continuation<Result<Paged<Transaction>>>
        coEvery { transactions.getTransactions(any(), any(), any(), any(), any(), any(), any(), 1, any()) } returns page(1, last = 2)
        coEvery { transactions.getTransactions(any(), any(), any(), any(), any(), any(), any(), 2, any()) } coAnswers {
            suspendCoroutine { old = it }
        }
        val vm = vm()
        vm.start(null)
        dispatcher.scheduler.runCurrent()
        vm.loadMore()
        dispatcher.scheduler.runCurrent()
        coEvery { transactions.getTransactions(any(), any(), any(), any(), any(), any(), any(), 1, any()) } returns page(3)
        vm.refresh()
        dispatcher.scheduler.runCurrent()
        old.resume(page(2, number = 2, last = 2))
        dispatcher.scheduler.runCurrent()
        assertEquals(listOf(3L), vm.state.value.items.map { it.id })
        assertEquals(1, vm.state.value.page)
        assertFalse(vm.state.value.isRefreshing)
        assertFalse(vm.state.value.isLoadingMore)
    }

    @Test fun `late status response cannot replace new segment statuses`() = runTest {
        lateinit var old: Continuation<Result<List<TransactionStatus>>>
        coEvery { master.getTransactionStatuses("simpanan") } coAnswers { suspendCoroutine { old = it } }
        coEvery { master.getTransactionStatuses("pinjaman") } returns Result.Error("new error")
        coEvery { transactions.getTransactions(any(), any(), any(), any(), any(), any(), any(), any(), any()) } returns page(1)
        val vm = vm()
        vm.start(null)
        dispatcher.scheduler.runCurrent()
        vm.selectSegment(ActivitySegment.PINJAMAN)
        dispatcher.scheduler.runCurrent()
        old.resume(Result.Success(emptyList()))
        dispatcher.scheduler.runCurrent()
        assertEquals("new error", vm.state.value.statusError)
    }

    @Test fun `overlapping pagination is deduplicated and concurrent load more is ignored`() = runTest {
        coEvery { transactions.getTransactions(any(), any(), any(), any(), any(), any(), any(), 1, any()) } returns page(1, 2, last = 2)
        coEvery { transactions.getTransactions(any(), any(), any(), any(), any(), any(), any(), 2, any()) } returns page(2, 3, number = 2, last = 2)
        val vm = vm()
        vm.start(null)
        dispatcher.scheduler.runCurrent()
        vm.loadMore()
        vm.loadMore()
        dispatcher.scheduler.runCurrent()
        assertEquals(listOf(1L, 2L, 3L), vm.state.value.items.map { it.id })
        assertEquals(vm.state.value.rows.size, vm.state.value.rows.map { it.key }.distinct().size)
        coVerify(exactly = 1) { transactions.getTransactions(any(), any(), any(), any(), any(), any(), any(), 2, any()) }
    }

    @Test fun `status and sort changes reject late errors from older query`() = runTest {
        lateinit var old: Continuation<Result<Paged<Transaction>>>
        coEvery { transactions.getTransactions(any(), any(), null, any(), any(), any(), any(), any(), any()) } coAnswers {
            suspendCoroutine { old = it }
        }
        coEvery { transactions.getTransactions(any(), any(), 3, any(), any(), any(), any(), any(), any()) } returns page(3)
        val vm = vm()
        vm.start(null)
        dispatcher.scheduler.runCurrent()
        vm.selectStatus(3)
        vm.toggleSort()
        dispatcher.scheduler.runCurrent()
        old.resume(Result.Error("stale failure"))
        dispatcher.scheduler.runCurrent()
        assertEquals(3, vm.state.value.selectedStatusId)
        assertFalse(vm.state.value.sortTerbaru)
        assertNull(vm.state.value.error)
        assertEquals(listOf(3L), vm.state.value.items.map { it.id })
        coVerify { transactions.getTransactions("simpanan", null, 3, null, null, null, "terlama", 1, 20) }
    }
}
