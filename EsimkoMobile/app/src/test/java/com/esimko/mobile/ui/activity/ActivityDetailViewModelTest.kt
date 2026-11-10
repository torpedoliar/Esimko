package com.esimko.mobile.ui.activity

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.data.repository.ReceiptRepository
import com.esimko.mobile.domain.model.TransactionDetail
import com.esimko.mobile.domain.repository.ShoppingRepository
import com.esimko.mobile.domain.repository.TransactionHistoryRepository
import com.esimko.mobile.domain.repository.TransactionRepository
import io.mockk.coEvery
import io.mockk.every
import io.mockk.mockk
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.ExperimentalCoroutinesApi
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.test.StandardTestDispatcher
import kotlinx.coroutines.test.resetMain
import kotlinx.coroutines.test.runTest
import kotlinx.coroutines.test.setMain
import org.junit.After
import org.junit.Assert.*
import org.junit.Before
import org.junit.Test

@OptIn(ExperimentalCoroutinesApi::class)
class ActivityDetailViewModelTest {
    private val dispatcher = StandardTestDispatcher()
    private val transactions = mockk<TransactionRepository>()
    private val shopping = mockk<ShoppingRepository>()
    private val history = mockk<TransactionHistoryRepository>()
    private val receipts = mockk<ReceiptRepository>()
    private val revision = MutableStateFlow(1L)

    @Before fun setup() {
        Dispatchers.setMain(dispatcher)
        every { receipts.sessionRevision } returns revision
        coEvery { history.getTransactionHistory(any(), any()) } returns Result.Success(emptyList())
    }
    @After fun cleanup() { Dispatchers.resetMain() }

    @Test fun `session change removes receipt availability and attachment metadata`() = runTest {
        val detail = mockk<TransactionDetail>()
        coEvery { transactions.getTransactionDetail("transaksi", 1) } returns Result.Success(detail)
        val vm = ActivityDetailViewModel(transactions, shopping, history, receipts)
        vm.start(1, "transaksi")
        dispatcher.scheduler.runCurrent()
        assertSame(detail, vm.state.value.transaksi)
        revision.value = 2
        dispatcher.scheduler.runCurrent()
        assertFalse(vm.state.value.hasContent)
        assertNull(vm.state.value.transaksi)
        assertTrue(vm.state.value.timeline.isEmpty())
    }
}
