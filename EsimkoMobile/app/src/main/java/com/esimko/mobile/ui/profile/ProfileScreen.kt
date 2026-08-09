package com.esimko.mobile.ui.profile

import android.net.Uri
import androidx.activity.compose.rememberLauncherForActivityResult
import androidx.activity.result.contract.ActivityResultContracts
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.heightIn
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowForward
import androidx.compose.material.icons.automirrored.filled.Logout
import androidx.compose.material.icons.filled.AccountCircle
import androidx.compose.material.icons.filled.Badge
import androidx.compose.material.icons.filled.Business
import androidx.compose.material.icons.filled.Email
import androidx.compose.material.icons.filled.LocationOn
import androidx.compose.material.icons.filled.Lock
import androidx.compose.material.icons.filled.Phone
import androidx.compose.material.icons.filled.PhotoCamera
import androidx.compose.material.icons.filled.Settings
import androidx.compose.material3.AlertDialog
import androidx.compose.material3.AssistChip
import androidx.compose.material3.Button
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import coil.compose.AsyncImage
import com.esimko.mobile.domain.model.Profile
import com.esimko.mobile.ui.common.EsimkoPreview
import com.esimko.mobile.util.compressForUpload
import com.esimko.mobile.ui.common.ErrorView
import com.esimko.mobile.ui.common.LightDarkPreview
import com.esimko.mobile.ui.common.ListRow
import com.esimko.mobile.ui.common.Money
import com.esimko.mobile.ui.common.RowDivider
import com.esimko.mobile.ui.common.SectionHeader
import com.esimko.mobile.ui.common.SkeletonListRows
import com.esimko.mobile.ui.common.UiState

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ProfileScreen(
    onLogout: () -> Unit,
    onOpenSettings: () -> Unit = {},
    viewModel: ProfileViewModel = hiltViewModel()
) {
    val uiState by viewModel.uiState.collectAsState()
    val passwordChangeState by viewModel.passwordChangeState.collectAsState()
    val avatarUploadState by viewModel.avatarUploadState.collectAsState()

    var showPasswordDialog by remember { mutableStateOf(false) }
    var oldPassword by remember { mutableStateOf("") }
    var newPassword by remember { mutableStateOf("") }
    var confirmPassword by remember { mutableStateOf("") }
    var passwordMismatch by remember { mutableStateOf(false) }

    val context = LocalContext.current
    val imagePickerLauncher = rememberLauncherForActivityResult(
        contract = ActivityResultContracts.GetContent()
    ) { uri: Uri? ->
        uri?.let {
            context.contentResolver.openInputStream(it)?.use { inputStream ->
                // Kompres dulu: foto profil kamera HP 3-8MB → ~200KB. Tanpa ini server 413.
                val compressed = inputStream.readBytes().compressForUpload()
                if (compressed != null) {
                    viewModel.uploadAvatar(compressed, "image/jpeg")
                }
            }
        }
    }

    LaunchedEffect(passwordChangeState) {
        if (passwordChangeState is UiState.Success) {
            showPasswordDialog = false
            oldPassword = ""
            newPassword = ""
            confirmPassword = ""
            passwordMismatch = false
        }
    }

    Box(Modifier.fillMaxSize()) {
        when (val state = uiState) {
            is UiState.Loading -> SkeletonListRows(count = 6, modifier = Modifier.fillMaxSize())
            is UiState.Error -> ErrorView(
                message = state.message ?: "Terjadi kesalahan",
                onRetry = { viewModel.loadProfile() },
                modifier = Modifier.fillMaxSize()
            )
            is UiState.Success -> ProfileContent(
                profile = state.data,
                avatarUploadState = avatarUploadState,
                onPickAvatar = { imagePickerLauncher.launch("image/*") },
                onOpenSettings = onOpenSettings,
                onChangePassword = { showPasswordDialog = true },
                onLogout = {
                    viewModel.logout()
                    onLogout()
                }
            )
            is UiState.Idle -> LaunchedEffect(Unit) { viewModel.loadProfile() }
        }

        if (showPasswordDialog) {
            AlertDialog(
                onDismissRequest = {
                    if (passwordChangeState !is UiState.Loading) {
                        showPasswordDialog = false
                        passwordMismatch = false
                    }
                },
                title = { Text("Ubah Password") },
                text = {
                    Column(verticalArrangement = Arrangement.spacedBy(12.dp)) {
                        OutlinedTextField(
                            value = oldPassword,
                            onValueChange = { oldPassword = it; passwordMismatch = false },
                            label = { Text("Password Lama") },
                            visualTransformation = PasswordVisualTransformation(),
                            singleLine = true,
                            enabled = passwordChangeState !is UiState.Loading
                        )
                        OutlinedTextField(
                            value = newPassword,
                            onValueChange = { newPassword = it; passwordMismatch = false },
                            label = { Text("Password Baru") },
                            visualTransformation = PasswordVisualTransformation(),
                            singleLine = true,
                            enabled = passwordChangeState !is UiState.Loading
                        )
                        OutlinedTextField(
                            value = confirmPassword,
                            onValueChange = { confirmPassword = it; passwordMismatch = false },
                            label = { Text("Konfirmasi Password Baru") },
                            visualTransformation = PasswordVisualTransformation(),
                            singleLine = true,
                            enabled = passwordChangeState !is UiState.Loading
                        )
                        if (passwordMismatch) {
                            Text(
                                text = "Password baru dan konfirmasi tidak sama",
                                color = MaterialTheme.colorScheme.error,
                                style = MaterialTheme.typography.bodySmall
                            )
                        }
                        if (passwordChangeState is UiState.Error) {
                            Text(
                                text = (passwordChangeState as UiState.Error).message ?: "Error",
                                color = MaterialTheme.colorScheme.error,
                                style = MaterialTheme.typography.bodySmall
                            )
                        }
                    }
                },
                confirmButton = {
                    Button(
                        onClick = {
                            if (newPassword != confirmPassword) {
                                passwordMismatch = true
                            } else {
                                passwordMismatch = false
                                viewModel.changePassword(oldPassword, newPassword)
                            }
                        },
                        enabled = oldPassword.isNotBlank() &&
                                  newPassword.isNotBlank() &&
                                  confirmPassword.isNotBlank() &&
                                  passwordChangeState !is UiState.Loading
                    ) {
                        if (passwordChangeState is UiState.Loading) {
                            CircularProgressIndicator(Modifier.size(16.dp), strokeWidth = 2.dp)
                        } else {
                            Text("Simpan")
                        }
                    }
                },
                dismissButton = {
                    TextButton(
                        onClick = {
                            showPasswordDialog = false
                            passwordMismatch = false
                        },
                        enabled = passwordChangeState !is UiState.Loading
                    ) { Text("Batal") }
                }
            )
        }
    }
}

@Composable
private fun ProfileContent(
    profile: Profile,
    avatarUploadState: UiState<Unit>,
    onPickAvatar: () -> Unit,
    onOpenSettings: () -> Unit,
    onChangePassword: () -> Unit,
    onLogout: () -> Unit,
    modifier: Modifier = Modifier
) {
    Column(modifier.fillMaxSize().verticalScroll(rememberScrollState())) {
        // Kepala avatar — latar surfaceVariant, BUKAN hero (spec §3 aturan 1: hero hanya Beranda).
        Surface(
            color = MaterialTheme.colorScheme.surfaceVariant,
            modifier = Modifier.fillMaxWidth()
        ) {
            Column(
                Modifier.fillMaxWidth().padding(24.dp),
                horizontalAlignment = Alignment.CenterHorizontally,
                verticalArrangement = Arrangement.spacedBy(8.dp)
            ) {
                if (profile.avatar != null) {
                    AsyncImage(
                        model = profile.avatar,
                        contentDescription = "Avatar",
                        modifier = Modifier.size(96.dp).clip(CircleShape),
                        contentScale = ContentScale.Crop
                    )
                } else {
                    Icon(
                        imageVector = Icons.Default.AccountCircle,
                        contentDescription = "Avatar",
                        modifier = Modifier.size(96.dp),
                        tint = MaterialTheme.colorScheme.primary
                    )
                }
                Text(
                    text = profile.nama,
                    style = MaterialTheme.typography.headlineSmall,
                    fontWeight = FontWeight.Bold
                )
                Text(
                    text = "No. Anggota: ${profile.noAnggota}",
                    style = MaterialTheme.typography.bodyMedium,
                    color = MaterialTheme.colorScheme.onSurfaceVariant
                )
                if (profile.statusAnggota.isNotBlank()) {
                    AssistChip(
                        onClick = {},
                        label = { Text(profile.statusAnggota) },
                        modifier = Modifier.heightIn(min = 48.dp)
                    )
                }
                TextButton(
                    onClick = onPickAvatar,
                    enabled = avatarUploadState !is UiState.Loading
                ) {
                    if (avatarUploadState is UiState.Loading) {
                        CircularProgressIndicator(Modifier.size(18.dp), strokeWidth = 2.dp)
                    } else {
                        Icon(Icons.Default.PhotoCamera, contentDescription = null, modifier = Modifier.size(18.dp))
                        Spacer(Modifier.width(8.dp))
                        Text("Ubah Foto")
                    }
                }
            }
        }

        SectionHeader(title = "Informasi Profil", modifier = Modifier.padding(top = 16.dp))
        ProfileInfoList(profile)

        SectionHeader(title = "Ringkasan Keuangan", modifier = Modifier.padding(top = 16.dp))
        ProfileFinanceList(profile)

        SectionHeader(title = "Pengaturan", modifier = Modifier.padding(top = 16.dp))
        ProfileActionList(onOpenSettings, onChangePassword, onLogout)

        Spacer(Modifier.height(24.dp))
    }
}

@Composable
private fun ProfileInfoList(profile: Profile) {
    Column {
        if (profile.divisi.isNotBlank() || profile.bagian.isNotBlank()) {
            ListRow(
                title = profile.divisi.ifBlank { "-" },
                subtitle = "Divisi${if (profile.bagian.isNotBlank()) " · ${profile.bagian}" else ""}",
                leading = { Icon(Icons.Default.Business, contentDescription = null) }
            )
            RowDivider()
        }
        ListRow(
            title = profile.ktp.ifBlank { "-" },
            subtitle = "No. KTP",
            leading = { Icon(Icons.Default.Badge, contentDescription = null) }
        )
        RowDivider()
        ListRow(
            title = profile.telepon.ifBlank { "-" },
            subtitle = "Telepon",
            leading = { Icon(Icons.Default.Phone, contentDescription = null) }
        )
        RowDivider()
        ListRow(
            title = profile.email ?: "-",
            subtitle = "Email",
            leading = { Icon(Icons.Default.Email, contentDescription = null) }
        )
        RowDivider()
        ListRow(
            title = profile.alamat.ifBlank { "-" },
            subtitle = "Alamat",
            leading = { Icon(Icons.Default.LocationOn, contentDescription = null) }
        )
    }
}

@Composable
private fun ProfileFinanceList(profile: Profile) {
    Column {
        ListRow(title = "Simpanan Pokok", trailing = { Money(profile.saldoSimpananPokok) })
        RowDivider()
        ListRow(title = "Simpanan Wajib", trailing = { Money(profile.saldoSimpananWajib) })
        RowDivider()
        ListRow(title = "Simpanan Sukarela", trailing = { Money(profile.saldoSimpananSukarela) })
        RowDivider()
        ListRow(title = "Simpanan Hari Raya", trailing = { Money(profile.saldoSimpananHariRaya) })
        RowDivider()
        ListRow(
            title = "Sisa Pinjaman",
            trailing = { Money(profile.saldoPinjaman, color = MaterialTheme.colorScheme.error) }
        )
        RowDivider()
        ListRow(
            title = "Angsuran Bulan Ini",
            trailing = { Money(profile.angsuranBulan, color = MaterialTheme.colorScheme.error) }
        )
    }
}

@Composable
private fun ProfileActionList(
    onOpenSettings: () -> Unit,
    onChangePassword: () -> Unit,
    onLogout: () -> Unit
) {
    Column {
        ListRow(
            title = "Pengaturan",
            leading = { Icon(Icons.Default.Settings, contentDescription = null) },
            trailing = {
                Icon(
                    Icons.AutoMirrored.Filled.ArrowForward,
                    contentDescription = null,
                    tint = MaterialTheme.colorScheme.onSurfaceVariant
                )
            },
            onClick = onOpenSettings
        )
        RowDivider()
        ListRow(
            title = "Ubah Password",
            leading = { Icon(Icons.Default.Lock, contentDescription = null) },
            trailing = {
                Icon(
                    Icons.AutoMirrored.Filled.ArrowForward,
                    contentDescription = null,
                    tint = MaterialTheme.colorScheme.onSurfaceVariant
                )
            },
            onClick = onChangePassword
        )
        RowDivider()
        ListRow(
            title = "Logout",
            leading = {
                Icon(
                    Icons.AutoMirrored.Filled.Logout,
                    contentDescription = null,
                    tint = MaterialTheme.colorScheme.error
                )
            },
            trailing = {
                Icon(
                    Icons.AutoMirrored.Filled.ArrowForward,
                    contentDescription = null,
                    tint = MaterialTheme.colorScheme.onSurfaceVariant
                )
            },
            onClick = onLogout
        )
    }
}

@LightDarkPreview
@Composable
private fun ProfileContentPreview() {
    EsimkoPreview {
        ProfileContent(
            profile = Profile(
                noAnggota = "12345",
                nama = "Budi Santoso",
                ktp = "3201234567890001",
                alamat = "Jl. Merdeka No. 10, Jakarta",
                telepon = "081234567890",
                email = "budi@example.com",
                avatar = null,
                saldoSimpanan = 1_500_000,
                saldoPinjaman = 3_000_000,
                angsuranBulan = 500_000,
                saldoSimpananPokok = 500_000,
                saldoSimpananWajib = 200_000,
                saldoSimpananSukarela = 800_000,
                saldoSimpananHariRaya = 300_000,
                statusAnggota = "Aktif",
                divisi = "Keuangan",
                bagian = "Kasir"
            ),
            avatarUploadState = UiState.Idle,
            onPickAvatar = {},
            onOpenSettings = {},
            onChangePassword = {},
            onLogout = {}
        )
    }
}
