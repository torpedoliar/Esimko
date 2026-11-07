package com.esimko.mobile.ui.common

import com.esimko.mobile.data.repository.ReceiptRepository
import com.esimko.mobile.data.repository.SavedReceipt
import io.mockk.coEvery
import io.mockk.every
import io.mockk.mockk
import kotlinx.coroutines.CancellationException
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.ExperimentalCoroutinesApi
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.test.StandardTestDispatcher
import kotlinx.coroutines.test.resetMain
import kotlinx.coroutines.test.runTest
import kotlinx.coroutines.test.setMain
import org.junit.After
import org.junit.Assert.*
import org.junit.Before
import org.junit.Test

@OptIn(ExperimentalCoroutinesApi::class)
class ReceiptViewModelTest {
    private val dispatcher = StandardTestDispatcher()
    private val repository = mockk<ReceiptRepository>()
    private val revision = MutableStateFlow(1L)

    @Before fun setup() {
        Dispatchers.setMain(dispatcher)
        every { repository.sessionRevision } returns revision.asStateFlow()
    }
    @After fun cleanup() { Dispatchers.resetMain() }

    @Test fun `successful receipt offers saved URI and session change clears it`() = runTest {
        val saved = SavedReceipt("content://receipt/1", true, 1)
        coEvery { repository.download("transaksi", 1) } returns saved
        coEvery { repository.isCurrent(saved) } returns true
        val vm = ReceiptViewModel(repository)
        dispatcher.scheduler.runCurrent()
        vm.download("transaksi", 1)
        dispatcher.scheduler.advanceUntilIdle()
        assertEquals(saved, vm.states.value["transaksi/1"]?.saved)
        revision.value = 2
        dispatcher.scheduler.advanceUntilIdle()
        assertTrue(vm.states.value.isEmpty())
    }

    @Test fun `previous user URI is never passed to external action`() = runTest {
        val saved = SavedReceipt("content://receipt/old", true, 1)
        coEvery { repository.isCurrent(saved) } returns false
        val vm = ReceiptViewModel(repository)
        dispatcher.scheduler.runCurrent()
        var launched = false
        vm.useSaved(saved) { launched = true }
        dispatcher.scheduler.advanceUntilIdle()
        assertFalse(launched)
    }

    @Test fun `download errors do not expose raw sensitive server details`() = runTest {
        coEvery { repository.download("transaksi", 1) } throws IllegalStateException("secret-token-body")
        val vm = ReceiptViewModel(repository)
        dispatcher.scheduler.runCurrent()
        vm.download("transaksi", 1)
        dispatcher.scheduler.advanceUntilIdle()
        val state = vm.states.value["transaksi/1"]!!
        assertFalse(state.downloading)
        assertNull(state.saved)
        assertFalse(state.error.orEmpty().contains("secret-token-body"))
    }

    @Test fun `cancelled download is not reported as ordinary failure`() = runTest {
        coEvery { repository.download("angsuran", 1) } throws CancellationException("cancelled")
        val vm = ReceiptViewModel(repository)
        dispatcher.scheduler.runCurrent()
        vm.download("angsuran", 1)
        dispatcher.scheduler.advanceUntilIdle()
        assertNull(vm.states.value["angsuran/1"]?.error)
        assertNull(vm.states.value["angsuran/1"]?.saved)
    }
}
