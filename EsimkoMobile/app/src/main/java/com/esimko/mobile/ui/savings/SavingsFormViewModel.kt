package com.esimko.mobile.ui.savings

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.esimko.mobile.core.network.Result
import com.esimko.mobile.data.local.PendingSavings
import com.esimko.mobile.data.local.PendingSavingsStore
import com.esimko.mobile.data.local.TokenStore
import com.esimko.mobile.domain.repository.TransactionRepository
import com.esimko.mobile.domain.repository.SavingsReconciliation
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.CancellationException
import kotlinx.coroutines.CoroutineDispatcher
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale
import java.util.UUID
import javax.inject.Inject

enum class FormStep { NOMINAL, BUKTI, SELESAI }

data class SavingsFormState(
    val jenis: String = "",
    val step: FormStep = FormStep.NOMINAL,
    val nominal: String = "",
    val keterangan: String = "",
    val isSubmitting: Boolean = false,
    val isLoading: Boolean = false,
    val blocked: Boolean = false,
    val hasPendingSubmission: Boolean = false,
    val error: String? = null,
    val lastTransactionId: Long? = null,
    val buktiBytes: ByteArray? = null,
    val buktiMime: String? = null,
    val buktiName: String? = null,
    val uploadError: String? = null
) {
    val nominalValid: Boolean get() = (nominal.toLongOrNull() ?: 0L) in 1L..999_999_999_999L
    val canEdit: Boolean get() = !hasPendingSubmission && !isLoading && !isSubmitting && !blocked
    val punyaDraft: Boolean get() = step != FormStep.SELESAI && (nominalValid || buktiBytes != null || hasPendingSubmission)
}

@HiltViewModel
class SavingsFormViewModel internal constructor(
    private val repository: TransactionRepository,
    private val pendingStore: PendingSavingsStore,
    private val tokenStore: TokenStore,
    private val ioDispatcher: CoroutineDispatcher
) : ViewModel() {
    @Inject constructor(repository: TransactionRepository, pendingStore: PendingSavingsStore, tokenStore: TokenStore) :
        this(repository, pendingStore, tokenStore, Dispatchers.IO)
    private val _state = MutableStateFlow(SavingsFormState())
    val state: StateFlow<SavingsFormState> = _state.asStateFlow()
    private var session: TokenStore.Session? = null
    private var pending: PendingSavings? = null

    init {
        viewModelScope.launch {
            tokenStore.sessionChanges.collect {
                val bound = session
                if (bound != null && tokenStore.currentSession() != bound) invalidateSession()
            }
        }
    }

    fun start(jenis: String) {
        if (_state.value.jenis.isNotEmpty()) return
        val bound = tokenStore.currentSession()
        session = bound
        _state.value = SavingsFormState(jenis = jenis, isLoading = true)
        if (bound == null || jenis !in setOf("setoran", "penarikan")) {
            invalidateSession()
            return
        }
        viewModelScope.launch {
            try {
                val draft = withContext(ioDispatcher) { pendingStore.load(bound.noAnggota, jenis) }
                if (!sessionCurrent(bound)) return@launch
                pending = draft
                _state.value = if (draft == null) _state.value.copy(isLoading = false) else restored(draft)
            } catch (e: CancellationException) {
                throw e
            } catch (_: Exception) {
                if (sessionCurrent(bound)) _state.value = _state.value.copy(
                    isLoading = false, blocked = true, error = STORAGE_ERROR
                )
            }
        }
    }

    fun onNominalChange(value: String) {
        if (!_state.value.canEdit) return
        _state.value = _state.value.copy(nominal = value.filter { it.isDigit() }, error = null)
    }

    fun onKeteranganChange(value: String) {
        if (!_state.value.canEdit) return
        _state.value = _state.value.copy(keterangan = value.take(2000))
    }

    fun onBuktiPicked(bytes: ByteArray, mime: String, name: String) {
        if (_state.value.blocked || _state.value.isSubmitting) return
        _state.value = _state.value.copy(buktiBytes = bytes, buktiMime = mime, buktiName = name, uploadError = null)
    }

    fun clearBukti() {
        if (_state.value.blocked || _state.value.isSubmitting) return
        _state.value = _state.value.copy(buktiBytes = null, buktiMime = null, buktiName = null)
    }

    /** Called only by the explicit confirmation. A retry never changes the consented payload/key. */
    fun submitNominal() {
        val s = _state.value
        val bound = session ?: return
        val nominal = s.nominal.toLongOrNull() ?: 0L
        if (s.isSubmitting || s.isLoading || s.blocked || s.lastTransactionId != null ||
            s.step != FormStep.NOMINAL || s.jenis !in setOf("setoran", "penarikan") || !s.nominalValid) return
        if (!sessionCurrent(bound)) return
        _state.value = s.copy(isSubmitting = true, error = null)
        viewModelScope.launch {
            try {
                val draft = pending ?: run {
                    val ts = SimpleDateFormat("dd-MM-yyyy HH:mm", Locale.getDefault()).format(Date())
                    val label = "Pengajuan ${if (s.jenis == "penarikan") "Penarikan" else "Setoran"} dari Esimko Mobile App $ts"
                    val note = if (s.keterangan.isNotBlank()) "$label — ${s.keterangan}" else label
                    // Hold in memory even if storage fails; never mint a replacement key on error.
                    PendingSavings(bound.noAnggota, s.jenis, UUID.randomUUID().toString(), nominal, note, s.keterangan)
                        .also { pending = it }
                }
                val durable = withContext(ioDispatcher) { pendingStore.createOrLoad(draft) }
                if (!sessionCurrent(bound)) return@launch
                pending = durable
                _state.value = restored(durable).copy(isSubmitting = durable.transactionId == null)
                if (durable.transactionId != null) return@launch
                when (val result = repository.processTransaction(
                    durable.jenis, durable.nominal, durable.keterangan, durable.key, bound
                )) {
                    is Result.Success -> {
                        // Persist original-account result even if the visible session changed in flight.
                        val saved = withContext(ioDispatcher) { pendingStore.recordSuccess(durable, result.data.id) }
                        if (sessionCurrent(bound)) {
                            pending = saved
                            _state.value = restored(saved)
                        }
                    }
                    is Result.Error -> if (sessionCurrent(bound)) _state.value = _state.value.copy(
                        isSubmitting = false, error = result.message
                    )
                    is Result.Loading -> if (sessionCurrent(bound)) _state.value = _state.value.copy(isSubmitting = false)
                }
            } catch (e: CancellationException) {
                throw e
            } catch (_: Exception) {
                if (sessionCurrent(bound)) _state.value = _state.value.copy(
                    isSubmitting = false, hasPendingSubmission = pending != null, error = STORAGE_ERROR
                )
            }
        }
    }

    /** Online atomic check/abandon, never a local reset. Repeating after response loss is safe. */
    fun reconcilePending() {
        val s = _state.value
        val bound = session ?: return
        val draft = pending ?: return
        if (!s.hasPendingSubmission || s.lastTransactionId != null || s.step != FormStep.NOMINAL ||
            s.isSubmitting || s.isLoading || s.blocked || !sessionCurrent(bound)) return
        _state.value = s.copy(isSubmitting = true, error = null)
        viewModelScope.launch {
            try {
                // Even a prior failed storage write must become durable before contacting the server.
                val durable = withContext(ioDispatcher) { pendingStore.createOrLoad(draft) }
                if (!sessionCurrent(bound)) return@launch
                pending = durable
                if (durable.transactionId != null) {
                    _state.value = restored(durable)
                    return@launch
                }
                when (val result = repository.reconcileSavings(
                    durable.jenis, durable.nominal, durable.keterangan, durable.key, bound
                )) {
                    is Result.Success -> {
                        if (!sessionCurrent(bound)) return@launch
                        when (val resolution = result.data) {
                            is SavingsReconciliation.Existing -> {
                                val saved = withContext(ioDispatcher) {
                                    pendingStore.recordSuccess(durable, resolution.transactionId)
                                }
                                if (sessionCurrent(bound)) {
                                    pending = saved
                                    _state.value = restored(saved)
                                }
                            }
                            is SavingsReconciliation.Abandoned -> {
                                withContext(ioDispatcher) { pendingStore.abandon(durable, resolution) }
                                if (sessionCurrent(bound)) {
                                    pending = null
                                    // Keep the visible inputs; the next POST still requires fresh consent/new UUID.
                                    _state.value = SavingsFormState(
                                        jenis = durable.jenis, nominal = durable.nominal.toString(), keterangan = durable.note
                                    )
                                }
                            }
                        }
                    }
                    is Result.Error -> if (sessionCurrent(bound)) _state.value = _state.value.copy(
                        isSubmitting = false, error = result.message
                    )
                    is Result.Loading -> if (sessionCurrent(bound)) _state.value = _state.value.copy(isSubmitting = false)
                }
            } catch (e: CancellationException) {
                throw e
            } catch (_: Exception) {
                if (sessionCurrent(bound)) _state.value = _state.value.copy(isSubmitting = false, error = STORAGE_ERROR)
            }
        }
    }

    fun uploadBukti() {
        val s = _state.value
        val bound = session ?: return
        val id = s.lastTransactionId ?: return
        val bytes = s.buktiBytes ?: return
        if (s.isSubmitting || s.blocked || s.step != FormStep.BUKTI || !sessionCurrent(bound)) return
        _state.value = s.copy(isSubmitting = true, uploadError = null)
        viewModelScope.launch {
            when (val result = repository.uploadTransactionProof(id, bytes, s.buktiMime ?: "image/jpeg", bound)) {
                is Result.Success -> if (sessionCurrent(bound)) _state.value = _state.value.copy(isSubmitting = false, step = FormStep.SELESAI)
                is Result.Error -> if (sessionCurrent(bound)) _state.value = _state.value.copy(isSubmitting = false, uploadError = result.message)
                is Result.Loading -> if (sessionCurrent(bound)) _state.value = _state.value.copy(isSubmitting = false)
            }
        }
    }

    fun skipBukti() {
        val bound = session ?: return
        if (_state.value.isSubmitting || _state.value.blocked || _state.value.lastTransactionId == null || !sessionCurrent(bound)) return
        _state.value = _state.value.copy(step = FormStep.SELESAI)
    }

    /** Explicit completion allows the next submission to get a new consent/key. Never discard unresolved work. */
    fun completeSubmission(onDone: () -> Unit) {
        val bound = session ?: return
        val draft = pending ?: return
        val s = _state.value
        if (s.step != FormStep.SELESAI || s.isSubmitting || s.blocked || !sessionCurrent(bound)) return
        _state.value = s.copy(isSubmitting = true)
        viewModelScope.launch {
            try {
                withContext(ioDispatcher) { pendingStore.complete(draft) }
                if (!sessionCurrent(bound)) return@launch
                pending = null
                _state.value = SavingsFormState(jenis = s.jenis)
                onDone()
            } catch (e: CancellationException) {
                throw e
            } catch (_: Exception) {
                if (sessionCurrent(bound)) _state.value = _state.value.copy(isSubmitting = false, error = STORAGE_ERROR)
            }
        }
    }

    private fun restored(draft: PendingSavings) = SavingsFormState(
        jenis = draft.jenis, nominal = draft.nominal.toString(), keterangan = draft.note,
        hasPendingSubmission = true, lastTransactionId = draft.transactionId,
        step = if (draft.transactionId == null) FormStep.NOMINAL else FormStep.BUKTI
    )

    private fun sessionCurrent(bound: TokenStore.Session): Boolean {
        if (session == bound && tokenStore.currentSession() == bound && !_state.value.blocked) return true
        invalidateSession()
        return false
    }

    private fun invalidateSession() {
        pending = null
        session = null
        _state.value = SavingsFormState(jenis = _state.value.jenis, blocked = true,
            error = "Sesi berubah. Buka kembali pengajuan pada akun asal.")
    }

    private companion object {
        const val STORAGE_ERROR = "Pengajuan belum dapat dipastikan. Penyimpanan aman tidak tersedia; buka kembali dan coba pengajuan yang sama."
    }
}
