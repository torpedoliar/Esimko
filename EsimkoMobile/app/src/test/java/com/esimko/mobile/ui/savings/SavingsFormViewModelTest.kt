package com.esimko.mobile.ui.savings

import com.esimko.mobile.core.network.Result
import com.esimko.mobile.data.local.PendingSavings
import com.esimko.mobile.data.local.PendingSavingsStore
import com.esimko.mobile.data.local.TokenStore
import com.esimko.mobile.domain.model.Transaction
import com.esimko.mobile.domain.repository.TransactionRepository
import com.esimko.mobile.domain.repository.SavingsReconciliation
import com.google.common.truth.Truth.assertThat
import io.mockk.*
import kotlinx.coroutines.*
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.test.*
import org.junit.After
import org.junit.Before
import org.junit.Test
import java.util.UUID

@OptIn(ExperimentalCoroutinesApi::class)
class SavingsFormViewModelTest {
    private val dispatcher = StandardTestDispatcher()
    private val repository = mockk<TransactionRepository>()
    private val store = mockk<PendingSavingsStore>()
    private val tokens = mockk<TokenStore>()
    private val changes = MutableStateFlow(0L)
    private val originalSession = TokenStore.Session("test-token", "A001", 0)
    private var current: TokenStore.Session? = originalSession
    private var saved: PendingSavings? = null
    private val transaction = Transaction(123, "Simpanan", "transaksi", 1000, "2026-11-09", "Belum Verifikasi", "Belum Verifikasi", null)

    @Before fun setup() {
        Dispatchers.setMain(dispatcher)
        every { tokens.currentSession() } answers { current }
        every { tokens.sessionChanges } returns changes
        every { store.load(any(), any()) } answers { saved?.takeIf { it.member == firstArg<String>() && it.jenis == secondArg<String>() } }
        every { store.createOrLoad(any()) } answers { saved ?: firstArg<PendingSavings>().also { saved = it } }
        every { store.recordSuccess(any(), any()) } answers {
            firstArg<PendingSavings>().copy(transactionId = secondArg()).also { saved = it }
        }
        every { store.complete(any()) } answers { saved = null }
        every { store.abandon(any(), any()) } answers {
            val draft = firstArg<PendingSavings>()
            val proof = secondArg<SavingsReconciliation.Abandoned>()
            check(proof.key == draft.key && proof.jenis == draft.jenis && saved == draft)
            saved = null
        }
    }
    @After fun teardown() { Dispatchers.resetMain() }

    private fun TestScope.model(jenis: String = "setoran") = SavingsFormViewModel(repository, store, tokens, dispatcher).apply {
        start(jenis)
        runCurrent()
        onNominalChange("1000")
    }

    @Test fun `typing never saves or sends and repeated consent taps submit only once`() = runTest(dispatcher) {
        coEvery { repository.processTransaction(any(), any(), any(), any(), any()) } returns Result.Success(transaction)
        val model = model()
        model.onKeteranganChange("note")
        verify(exactly = 0) { store.createOrLoad(any()) }
        coVerify(exactly = 0) { repository.processTransaction(any(), any(), any(), any(), any()) }
        model.submitNominal()
        model.submitNominal()
        assertThat(model.state.value.isSubmitting).isTrue()
        runCurrent()
        model.submitNominal()
        runCurrent()
        coVerify(exactly = 1) { repository.processTransaction("setoran", 1000, any(), any(), originalSession) }
        coVerifyOrder {
            store.createOrLoad(any())
            repository.processTransaction(any(), any(), any(), any(), any())
            store.recordSuccess(any(), 123)
        }
        assertThat(UUID.fromString(saved!!.key).toString()).isEqualTo(saved!!.key)
        assertThat(model.state.value.lastTransactionId).isEqualTo(123)
        assertThat(model.state.value.step).isEqualTo(FormStep.BUKTI)
    }

    @Test fun `invalid or overflowing amount never saves or sends`() = runTest(dispatcher) {
        val model = model()
        listOf("0", "1000000000000", "999999999999999999999999999").forEach {
            model.onNominalChange(it)
            model.submitNominal()
        }
        runCurrent()
        verify(exactly = 0) { store.createOrLoad(any()) }
        coVerify(exactly = 0) { repository.processTransaction(any(), any(), any(), any(), any()) }
    }

    @Test fun `timeout restart and edit attempts preserve key and full fingerprint`() = runTest(dispatcher) {
        coEvery { repository.processTransaction(any(), any(), any(), any(), any()) } returnsMany listOf(
            Result.Error("Timeout"), Result.Error("Offline"), Result.Success(transaction)
        )
        val first = model()
        first.onKeteranganChange("frozen note")
        first.submitNominal()
        runCurrent()
        val draft = saved!!
        first.onNominalChange("9999")
        first.onKeteranganChange("changed")
        first.submitNominal()
        runCurrent()
        val reopened = model()
        assertThat(reopened.state.value.hasPendingSubmission).isTrue()
        assertThat(reopened.state.value.keterangan).isEqualTo("frozen note")
        reopened.onNominalChange("50000")
        reopened.submitNominal()
        runCurrent()
        coVerify(exactly = 3) {
            repository.processTransaction(draft.jenis, draft.nominal, draft.keterangan, draft.key, originalSession)
        }
        assertThat(saved!!.key).isEqualTo(draft.key)
        assertThat(reopened.state.value.lastTransactionId).isEqualTo(123)
    }

    @Test fun `storage failure prevents POST and retry keeps original generated key`() = runTest(dispatcher) {
        val attempts = mutableListOf<PendingSavings>()
        every { store.createOrLoad(capture(attempts)) } throws IllegalStateException("keystore unavailable")
        val model = model()
        model.submitNominal()
        runCurrent()
        model.onNominalChange("5000")
        model.submitNominal()
        runCurrent()
        assertThat(attempts).hasSize(2)
        assertThat(attempts[0]).isEqualTo(attempts[1])
        coVerify(exactly = 0) { repository.processTransaction(any(), any(), any(), any(), any()) }
    }

    @Test fun `unreadable encrypted draft blocks new keys and POST`() = runTest(dispatcher) {
        every { store.load(any(), any()) } throws IllegalStateException("authentication failed")
        val model = model()
        model.submitNominal()
        runCurrent()
        assertThat(model.state.value.blocked).isTrue()
        verify(exactly = 0) { store.createOrLoad(any()) }
        coVerify(exactly = 0) { repository.processTransaction(any(), any(), any(), any(), any()) }
    }

    @Test fun `proof retries and process restart reuse persisted successful transaction`() = runTest(dispatcher) {
        coEvery { repository.processTransaction(any(), any(), any(), any(), any()) } returns Result.Success(transaction)
        coEvery { repository.uploadTransactionProof(any(), any(), any(), any()) } returnsMany listOf(Result.Error("Offline"), Result.Success(Unit))
        val first = model()
        first.submitNominal()
        runCurrent()
        val reopened = model()
        assertThat(reopened.state.value.lastTransactionId).isEqualTo(123)
        reopened.submitNominal()
        reopened.onBuktiPicked(byteArrayOf(1), "image/jpeg", "test.jpg")
        reopened.uploadBukti()
        reopened.uploadBukti()
        runCurrent()
        assertThat(reopened.state.value.uploadError).isEqualTo("Offline")
        reopened.uploadBukti()
        runCurrent()
        coVerify(exactly = 1) { repository.processTransaction(any(), any(), any(), any(), any()) }
        coVerify(exactly = 2) { repository.uploadTransactionProof(123, any(), any(), originalSession) }
        assertThat(reopened.state.value.step).isEqualTo(FormStep.SELESAI)
    }

    @Test fun `only explicit completion allows new submission key`() = runTest(dispatcher) {
        coEvery { repository.processTransaction(any(), any(), any(), any(), any()) } returns Result.Success(transaction)
        val model = model()
        model.submitNominal()
        runCurrent()
        val oldKey = saved!!.key
        var completed = false
        model.completeSubmission { completed = true }
        runCurrent()
        assertThat(completed).isFalse()
        model.skipBukti()
        model.completeSubmission { completed = true }
        runCurrent()
        assertThat(completed).isTrue()
        assertThat(saved).isNull()
        model.onNominalChange("1000")
        model.submitNominal()
        runCurrent()
        assertThat(saved!!.key).isNotEqualTo(oldKey)
    }

    @Test fun `account switch clears display and prevents old draft under new bearer`() = runTest(dispatcher) {
        coEvery { repository.processTransaction(any(), any(), any(), any(), any()) } returns Result.Error("Timeout")
        val old = model()
        old.submitNominal()
        runCurrent()
        val originalDraft = saved
        current = TokenStore.Session("other-token", "A002", 1)
        changes.value = 1
        runCurrent()
        old.submitNominal()
        old.uploadBukti()
        runCurrent()
        assertThat(old.state.value.blocked).isTrue()
        assertThat(old.state.value.nominal).isEmpty()
        assertThat(saved).isEqualTo(originalDraft)
        val next = model()
        assertThat(next.state.value.hasPendingSubmission).isFalse()
        coVerify(exactly = 1) { repository.processTransaction(any(), any(), any(), any(), any()) }
    }

    @Test fun `existing delayed create is preserved by check and advances to original proof`() = runTest(dispatcher) {
        coEvery { repository.processTransaction(any(), any(), any(), any(), any()) } returns Result.Error("Timeout")
        val model = model()
        model.submitNominal()
        runCurrent()
        val original = saved!!
        coEvery { repository.reconcileSavings(original.jenis, original.nominal, original.keterangan, original.key, originalSession) } returns
            Result.Success(SavingsReconciliation.Existing(123))
        model.reconcilePending()
        model.reconcilePending()
        runCurrent()
        assertThat(saved!!.key).isEqualTo(original.key)
        assertThat(saved!!.transactionId).isEqualTo(123)
        assertThat(model.state.value.step).isEqualTo(FormStep.BUKTI)
        verify(exactly = 0) { store.abandon(any(), any()) }
        coVerify(exactly = 1) { repository.reconcileSavings(any(), any(), any(), any(), any()) }
    }

    @Test fun `only durable abandoned proof unlocks editing and next explicit consent mints new UUID`() = runTest(dispatcher) {
        coEvery { repository.processTransaction(any(), any(), any(), any(), any()) } returns Result.Error("Stored 422 failure")
        val model = model()
        model.submitNominal()
        runCurrent()
        val old = saved!!
        assertThat(model.state.value.canEdit).isFalse()
        coEvery { repository.reconcileSavings(any(), any(), any(), any(), any()) } returns
            Result.Success(SavingsReconciliation.Abandoned(old.key, old.jenis))
        model.reconcilePending()
        runCurrent()
        assertThat(saved).isNull()
        assertThat(model.state.value.canEdit).isTrue()
        coVerify(exactly = 1) { repository.processTransaction(any(), any(), any(), any(), any()) }
        model.onNominalChange("2000")
        model.submitNominal()
        runCurrent()
        assertThat(saved!!.key).isNotEqualTo(old.key)
        assertThat(saved!!.nominal).isEqualTo(2000)
    }

    @Test fun `lost abandonment response repeated after process restart retains key until proof`() = runTest(dispatcher) {
        coEvery { repository.processTransaction(any(), any(), any(), any(), any()) } returns Result.Error("timeout")
        val first = model()
        first.submitNominal()
        runCurrent()
        val old = saved!!
        coEvery { repository.reconcileSavings(any(), any(), any(), any(), any()) } returnsMany listOf(
            Result.Error("Response lost"), Result.Success(SavingsReconciliation.Abandoned(old.key, old.jenis))
        )
        first.reconcilePending()
        runCurrent()
        assertThat(saved).isEqualTo(old)
        val reopened = model()
        reopened.reconcilePending()
        runCurrent()
        coVerify(exactly = 2) { repository.reconcileSavings(old.jenis, old.nominal, old.keterangan, old.key, originalSession) }
        assertThat(reopened.state.value.canEdit).isTrue()
    }

    @Test fun `422 404 network and missing local draft never authorize clearing`() = runTest(dispatcher) {
        val model = model()
        model.reconcilePending()
        runCurrent()
        coVerify(exactly = 0) { repository.reconcileSavings(any(), any(), any(), any(), any()) }
        coEvery { repository.processTransaction(any(), any(), any(), any(), any()) } returns Result.Error("422")
        model.submitNominal()
        runCurrent()
        val old = saved!!
        for (message in listOf("422", "404", "network")) {
            coEvery { repository.reconcileSavings(any(), any(), any(), any(), any()) } returns Result.Error(message)
            model.reconcilePending()
            runCurrent()
            assertThat(saved).isEqualTo(old)
            assertThat(model.state.value.canEdit).isFalse()
        }
        verify(exactly = 0) { store.abandon(any(), any()) }
    }

    @Test fun `abandoned proof storage failure keeps unresolved draft and can retry`() = runTest(dispatcher) {
        coEvery { repository.processTransaction(any(), any(), any(), any(), any()) } returns Result.Error("timeout")
        val model = model()
        model.submitNominal()
        runCurrent()
        val old = saved!!
        coEvery { repository.reconcileSavings(any(), any(), any(), any(), any()) } returns
            Result.Success(SavingsReconciliation.Abandoned(old.key, old.jenis))
        every { store.abandon(any(), any()) } throws IllegalStateException("storage failure")
        model.reconcilePending()
        runCurrent()
        assertThat(saved).isEqualTo(old)
        assertThat(model.state.value.canEdit).isFalse()
        assertThat(model.state.value.isSubmitting).isFalse()
    }

    @Test fun `late abandoned proof after account change cannot clear old pending`() = runTest(dispatcher) {
        coEvery { repository.processTransaction(any(), any(), any(), any(), any()) } returns Result.Error("timeout")
        val model = model()
        model.submitNominal()
        runCurrent()
        val old = saved!!
        val response = CompletableDeferred<Result<SavingsReconciliation>>()
        coEvery { repository.reconcileSavings(any(), any(), any(), any(), any()) } coAnswers { response.await() }
        model.reconcilePending()
        runCurrent()
        current = TokenStore.Session("other", "A002", 1)
        changes.value = 1
        response.complete(Result.Success(SavingsReconciliation.Abandoned(old.key, old.jenis)))
        runCurrent()
        assertThat(saved).isEqualTo(old)
        assertThat(model.state.value.blocked).isTrue()
        verify(exactly = 0) { store.abandon(any(), any()) }
    }

    @Test fun `queued consent cannot POST after session replacement`() = runTest(dispatcher) {
        val model = model()
        model.submitNominal()
        current = TokenStore.Session("other-token", "A002", 1)
        changes.value = 1
        runCurrent()
        coVerify(exactly = 0) { repository.processTransaction(any(), any(), any(), any(), any()) }
        assertThat(model.state.value.blocked).isTrue()
    }
}
