package com.esimko.mobile.ui.savings

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.repository.TransactionRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale
import javax.inject.Inject

enum class FormStep { NOMINAL, BUKTI, SELESAI }

data class SavingsFormState(
    val jenis: String = "",
    val step: FormStep = FormStep.NOMINAL,
    val nominal: String = "",
    val keterangan: String = "",
    val isSubmitting: Boolean = false,
    val error: String? = null,
    /** Id transaksi hasil `processTransaction` — dipakai unggah bukti. */
    val lastTransactionId: Long? = null,
    val buktiBytes: ByteArray? = null,
    val buktiMime: String? = null,
    val buktiName: String? = null,
    val uploadError: String? = null
) {
    val nominalValid: Boolean get() = (nominal.toLongOrNull() ?: 0L) > 0L
    /** Konfirmasi keluar hanya perlu kalau ada yang bisa hilang. */
    val punyaDraft: Boolean get() = step != FormStep.SELESAI && (nominalValid || buktiBytes != null)
}

@HiltViewModel
class SavingsFormViewModel @Inject constructor(
    private val repository: TransactionRepository
) : ViewModel() {

    private val _state = MutableStateFlow(SavingsFormState())
    val state: StateFlow<SavingsFormState> = _state.asStateFlow()

    fun start(jenis: String) {
        if (_state.value.jenis.isNotEmpty()) return
        _state.value = _state.value.copy(jenis = jenis)
    }

    fun onNominalChange(value: String) {
        _state.value = _state.value.copy(nominal = value.filter { it.isDigit() }, error = null)
    }

    fun onKeteranganChange(value: String) {
        _state.value = _state.value.copy(keterangan = value)
    }

    fun onBuktiPicked(bytes: ByteArray, mime: String, name: String) {
        _state.value = _state.value.copy(
            buktiBytes = bytes, buktiMime = mime, buktiName = name, uploadError = null
        )
    }

    fun clearBukti() {
        _state.value = _state.value.copy(buktiBytes = null, buktiMime = null, buktiName = null)
    }

    /** Langkah 1 → 2. Pengajuan sudah terkirim di sini; bukti menyusul. */
    fun submitNominal() {
        val s = _state.value
        val nominal = s.nominal.toLongOrNull() ?: 0L
        if (s.isSubmitting || nominal <= 0L) return
        viewModelScope.launch {
            _state.value = s.copy(isSubmitting = true, error = null)
            val ts = SimpleDateFormat("dd-MM-yyyy HH:mm", Locale.getDefault()).format(Date())
            val label = "Pengajuan Setoran dari Esimko Mobile App $ts"
            val keterangan = if (s.keterangan.isNotBlank()) "$label — ${s.keterangan}" else label
            when (val r = repository.processTransaction(
                jenis = s.jenis,
                nominal = nominal,
                keterangan = keterangan
            )) {
                is Result.Success -> _state.value = _state.value.copy(
                    isSubmitting = false,
                    // `lastTransactionId` dipertahankan sampai layar ditutup — kalau unggah gagal,
                    // user bisa coba lagi tanpa mengirim pengajuan kedua.
                    lastTransactionId = r.data.id,
                    step = FormStep.BUKTI
                )
                is Result.Error -> _state.value =
                    _state.value.copy(isSubmitting = false, error = r.message)
                is Result.Loading -> Unit
            }
        }
    }

    /** Langkah 2 → selesai. Bukti opsional: pengajuan sudah masuk tanpanya. */
    fun uploadBukti() {
        val s = _state.value
        val id = s.lastTransactionId ?: return
        val bytes = s.buktiBytes ?: return
        if (s.isSubmitting) return
        viewModelScope.launch {
            _state.value = s.copy(isSubmitting = true, uploadError = null)
            when (val r = repository.uploadTransactionProof(id, bytes, s.buktiMime ?: "image/jpeg")) {
                is Result.Success ->
                    _state.value = _state.value.copy(isSubmitting = false, step = FormStep.SELESAI)
                is Result.Error ->
                    _state.value = _state.value.copy(isSubmitting = false, uploadError = r.message)
                is Result.Loading -> Unit
            }
        }
    }

    /** "Nanti saja" — pengajuan tetap sah, bukti bisa diunggah lain waktu. */
    fun skipBukti() {
        _state.value = _state.value.copy(step = FormStep.SELESAI)
    }
}
