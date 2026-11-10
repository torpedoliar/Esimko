package com.esimko.mobile.ui.common

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.esimko.mobile.data.repository.ReceiptRepository
import com.esimko.mobile.data.repository.SavedReceipt
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.CancellationException
import kotlinx.coroutines.Job
import kotlinx.coroutines.ensureActive
import kotlin.coroutines.coroutineContext
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch
import javax.inject.Inject

data class ReceiptState(val downloading: Boolean = false, val saved: SavedReceipt? = null, val error: String? = null)

@HiltViewModel
class ReceiptViewModel @Inject constructor(private val repository: ReceiptRepository) : ViewModel() {
    private val mutableStates = MutableStateFlow<Map<String, ReceiptState>>(emptyMap())
    val states = mutableStates.asStateFlow()
    private val jobs = mutableMapOf<String, Job>()

    init {
        val initialRevision = repository.sessionRevision.value
        viewModelScope.launch {
            var observedRevision = initialRevision
            repository.sessionRevision.collect { revision ->
                if (revision != observedRevision) {
                    observedRevision = revision
                    jobs.values.forEach { it.cancel() }
                    jobs.clear()
                    mutableStates.value = emptyMap()
                }
            }
        }
    }

    fun download(jenis: String, id: Long) {
        val key = "$jenis/$id"
        if (mutableStates.value[key]?.downloading == true) return
        val owner = repository.sessionRevision.value
        mutableStates.update { it + (key to ReceiptState(downloading = true)) }
        jobs[key] = viewModelScope.launch {
            try {
                val saved = repository.download(jenis, id)
                if (repository.isCurrent(saved) && repository.sessionRevision.value == owner) {
                    coroutineContext.ensureActive()
                    mutableStates.update { it + (key to ReceiptState(saved = saved)) }
                }
            } catch (e: CancellationException) {
                throw e
            } catch (_: Exception) {
                coroutineContext.ensureActive()
                // Never expose raw Retrofit/storage exceptions or response bodies to UI/logs.
                if (repository.sessionRevision.value == owner) {
                    mutableStates.update { it + (key to ReceiptState(error = "Bukti gagal diunduh. Periksa sesi, koneksi, dan ruang penyimpanan, lalu coba lagi. Jika belum tersedia, muat ulang rincian.")) }
                }
            } finally {
                if (repository.sessionRevision.value == owner) {
                    mutableStates.update { states ->
                        states[key]?.let { states + (key to it.copy(downloading = false)) } ?: states
                    }
                }
            }
        }
    }

    fun useSaved(receipt: SavedReceipt, action: () -> Unit) {
        viewModelScope.launch {
            if (repository.isCurrent(receipt) && repository.sessionRevision.value == receipt.sessionRevision) action()
            else mutableStates.value = emptyMap()
        }
    }
}
