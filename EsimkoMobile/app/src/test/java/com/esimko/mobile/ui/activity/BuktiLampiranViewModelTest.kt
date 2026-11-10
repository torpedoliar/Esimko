package com.esimko.mobile.ui.activity

import com.esimko.mobile.data.repository.ReceiptRepository
import io.mockk.coEvery
import io.mockk.every
import io.mockk.mockk
import kotlinx.coroutines.CompletableDeferred
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
class BuktiLampiranViewModelTest {
    private val dispatcher = StandardTestDispatcher()
    private val repository = mockk<ReceiptRepository>()
    private val revision = MutableStateFlow(1L)

    @Before fun setup() {
        Dispatchers.setMain(dispatcher)
        every { repository.sessionRevision } returns revision
    }
    @After fun cleanup() { Dispatchers.resetMain() }

    @Test fun `session change clears private image`() = runTest {
        coEvery { repository.loadLampiran(1) } returns byteArrayOf(1)
        val vm = BuktiLampiranViewModel(repository)
        vm.load(1)
        dispatcher.scheduler.runCurrent()
        assertTrue(vm.state.value is LampiranState.Success)
        revision.value = 2
        dispatcher.scheduler.runCurrent()
        assertTrue(vm.state.value is LampiranState.Error)
    }

    @Test fun `new id cancels previous response and displays only latest`() = runTest {
        val pending = CompletableDeferred<ByteArray>()
        coEvery { repository.loadLampiran(1) } coAnswers { pending.await() }
        coEvery { repository.loadLampiran(2) } returns byteArrayOf(2)
        val vm = BuktiLampiranViewModel(repository)
        vm.load(1)
        dispatcher.scheduler.runCurrent()
        vm.load(2)
        dispatcher.scheduler.runCurrent()
        pending.complete(byteArrayOf(1))
        dispatcher.scheduler.runCurrent()
        assertArrayEquals(byteArrayOf(2), (vm.state.value as LampiranState.Success).bytes)
    }

    @Test fun `failed same id can retry without exposing exception details`() = runTest {
        coEvery { repository.loadLampiran(1) } throws IllegalStateException("private-server-body")
        val vm = BuktiLampiranViewModel(repository)
        vm.load(1)
        dispatcher.scheduler.runCurrent()
        assertFalse((vm.state.value as LampiranState.Error).message.contains("private-server-body"))
        coEvery { repository.loadLampiran(1) } returns byteArrayOf(1)
        vm.load(1)
        dispatcher.scheduler.runCurrent()
        assertTrue(vm.state.value is LampiranState.Success)
    }
}
