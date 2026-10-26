package com.esimko.mobile.ui.installment

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.esimko.mobile.core.network.Result
import com.esimko.mobile.domain.model.DokumenFile
import com.esimko.mobile.domain.model.Salary
import com.esimko.mobile.domain.model.SyaratPinjaman
import com.esimko.mobile.domain.model.TransactionType
import com.esimko.mobile.domain.repository.InstallmentRepository
import com.esimko.mobile.domain.repository.MasterRepository
import com.esimko.mobile.util.LoanDocs
import com.esimko.mobile.util.LoanMath
import dagger.hilt.android.lifecycle.HiltViewModel
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

// Tenor maksimal per jenis pinjaman, sama dengan validasi backend
// (MobileController::validasi_transaksi: array(9 => 50, 10 => 18, 11 => 18))
private val MAX_TENOR = mapOf(9 to 50, 10 to 18, 11 to 18)

// Fallback kalau endpoint syarat_pinjaman gagal dimuat — sama dengan default server.
private const val KTP2_THRESHOLD_DEFAULT = 10_000_000L

data class LoanApplicationState(
    val loanTypes: List<TransactionType> = emptyList(),
    val salary: Salary? = null,
    val syarat: SyaratPinjaman? = null,
    val isLoading: Boolean = false,
    val isSubmitting: Boolean = false,
    val error: String? = null,
    val dokumenError: String? = null,
    val submitError: String? = null,
    val submitSuccess: Long? = null,
    val selectedJenis: Int? = null,
    val nominal: String = "",
    val tenor: String = "",
    val gajiPokok: String = "",
    val dokumen: Map<String, DokumenFile> = emptyMap(),
    val persetujuanSetuju: Boolean = false,
    /** Checkbox kedua: pernyataan pengajuan pinjaman (syarat & ketentuan). */
    val persetujuanPinjamanSetuju: Boolean = false
) {
    /** Batas tenor jenis terpilih; 0 = belum ada jenis dipilih. */
    val maxTenor: Int get() = MAX_TENOR[selectedJenis] ?: 0

    val nominalValue: Long get() = nominal.toLongOrNull() ?: 0L
    val tenorValue: Int get() = tenor.toIntOrNull() ?: 0
    val gajiValue: Long get() = gajiPokok.toLongOrNull() ?: 0L

    val tenorValid: Boolean get() = tenorValue in 1..maxTenor

    val ktp2Threshold: Long get() = syarat?.ktp2Threshold ?: KTP2_THRESHOLD_DEFAULT

    /** KTP pasangan muncul (wajib) hanya saat nominal di atas threshold. */
    val perluKtp2: Boolean get() = LoanDocs.perluKtp2(nominalValue, ktp2Threshold)

    val bungaRate: Double?
        get() = syarat?.bungaPinjamanRate?.takeIf { it.isFinite() && it >= 0.0 }

    /** Tidak menebak bunga ketika pengaturan server belum tersedia. */
    val estimasiAngsuran: Long
        get() = bungaRate?.let { LoanMath.angsuranPerBulan(nominalValue, tenorValue, it) } ?: 0L

    /** Kunci slot dokumen yang tampil di UI: 3 slip + 4 wajib + ktp2 kondisional + surat hutang (jenis 9). */
    val slotDokumen: List<String>
        get() {
            val daftar = (LoanDocs.SLIP_SLOTS + LoanDocs.WAJIB).toMutableList()
            if (perluKtp2) daftar += LoanDocs.KTP2
            if (selectedJenis == LoanDocs.JENIS_JANGKA_PANJANG) daftar += LoanDocs.SURAT_HUTANG
            return daftar
        }

    val dokumenKurang: List<String>
        get() = slotDokumen.filter { it !in dokumen.keys }

    val bisaKirim: Boolean
        get() = !isSubmitting && selectedJenis != null && nominalValue > 0L &&
            tenorValid && gajiValue > 0L && dokumenKurang.isEmpty() &&
            persetujuanSetuju && persetujuanPinjamanSetuju

    /** Konten form sudah bisa digambar (jenis pinjaman termuat). */
    val hasContent: Boolean get() = loanTypes.isNotEmpty()
}

@HiltViewModel
class LoanApplicationViewModel @Inject constructor(
    private val installmentRepository: InstallmentRepository,
    private val masterRepository: MasterRepository
) : ViewModel() {

    private val _state = MutableStateFlow(LoanApplicationState())
    val state: StateFlow<LoanApplicationState> = _state.asStateFlow()

    init {
        load()
    }

    fun load() {
        viewModelScope.launch {
            _state.value = _state.value.copy(isLoading = true, error = null)
            loadSalary()
            loadSyarat()
            when (val result = masterRepository.getTransactionTypes("pinjaman")) {
                is Result.Success -> _state.value = _state.value.copy(
                    loanTypes = result.data,
                    isLoading = false
                )
                is Result.Error -> _state.value = _state.value.copy(
                    error = result.message,
                    isLoading = false
                )
                is Result.Loading -> Unit
            }
        }
    }

    private suspend fun loadSalary() {
        when (val result = installmentRepository.getBaseSalary()) {
            is Result.Success -> _state.value = _state.value.copy(salary = result.data)
            else -> Unit
        }
    }

    private suspend fun loadSyarat() {
        // Gagal memuat syarat tidak memblokir form — threshold fallback default 10jt.
        when (val result = installmentRepository.getSyaratPinjaman()) {
            is Result.Success -> _state.value = _state.value.copy(syarat = result.data)
            else -> Unit
        }
    }

    fun selectJenis(id: Int) {
        _state.value = _state.value.copy(selectedJenis = id, tenor = "")
    }

    fun onNominalChange(value: String) {
        _state.value = _state.value.copy(nominal = value.filter { it.isDigit() })
    }

    fun onTenorChange(value: String) {
        _state.value = _state.value.copy(tenor = value.filter { it.isDigit() }.take(2))
    }

    fun onGajiChange(value: String) {
        _state.value = _state.value.copy(gajiPokok = value.filter { it.isDigit() })
    }

    /** Slot dokumen terpilih dari picker. Validasi mime/ukungan client-side dulu
     *  (server memvalidasi ulang — client hanya untuk UX cepat). */
    fun onDokumenPicked(key: String, file: DokumenFile?) {
        if (file == null) return
        if (!LoanDocs.mimeDiterima(file.mime)) {
            _state.value = _state.value.copy(
                dokumenError = "${LoanDocs.label(key)}: format harus JPG, PNG, atau PDF"
            )
            return
        }
        if (file.bytes.size > LoanDocs.MAKS_BYTES) {
            _state.value = _state.value.copy(
                dokumenError = "${LoanDocs.label(key)}: ukuran melebihi 5 MB"
            )
            return
        }
        _state.value = _state.value.copy(
            dokumen = _state.value.dokumen + (key to file),
            dokumenError = null
        )
    }

    fun clearDokumen(key: String) {
        _state.value = _state.value.copy(dokumen = _state.value.dokumen - key)
    }

    fun onPersetujuanChange(setuju: Boolean) {
        _state.value = _state.value.copy(persetujuanSetuju = setuju)
    }

    fun onPersetujuanPinjamanChange(setuju: Boolean) {
        _state.value = _state.value.copy(persetujuanPinjamanSetuju = setuju)
    }

    fun submit() {
        val s = _state.value
        val jenis = s.selectedJenis ?: return
        if (!s.bisaKirim) return

        viewModelScope.launch {
            _state.value = _state.value.copy(isSubmitting = true, submitError = null)
            val jenisLabel = when (jenis) {
                9 -> "Pinjaman Jangka Panjang"
                10 -> "Pinjaman Jangka Pendek"
                11 -> "Pinjaman Barang"
                else -> "Pinjaman"
            }
            val ts = SimpleDateFormat("dd-MM-yyyy HH:mm", Locale.getDefault()).format(Date())
            val keterangan = "Pengajuan $jenisLabel dari Esimko Mobile App $ts"
            when (val result = installmentRepository.submitLoan(
                jenis, s.nominalValue, s.tenorValue, s.gajiValue, keterangan,
                s.syarat?.persetujuanVersi, s.syarat?.persetujuanPinjamanVersi, s.dokumen
            )) {
                is Result.Success -> _state.value = _state.value.copy(
                    isSubmitting = false,
                    submitSuccess = result.data
                )
                is Result.Error -> _state.value = _state.value.copy(
                    isSubmitting = false,
                    submitError = result.message
                )
                is Result.Loading -> Unit
            }
        }
    }

    fun clearSubmit() {
        _state.value = _state.value.copy(submitError = null, submitSuccess = null)
    }
}
