package com.esimko.mobile.ui.profile

import android.net.Uri
import androidx.activity.compose.rememberLauncherForActivityResult
import androidx.activity.result.contract.ActivityResultContracts
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.BoxWithConstraints
import androidx.compose.ui.platform.LocalDensity
import com.esimko.mobile.util.MoneyFormatter
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.heightIn
import androidx.compose.foundation.layout.offset
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.outlined.KeyboardArrowRight
import androidx.compose.material.icons.automirrored.outlined.Logout
import androidx.compose.material.icons.outlined.Badge
import androidx.compose.material.icons.outlined.Business
import androidx.compose.material.icons.outlined.CalendarMonth
import androidx.compose.material.icons.outlined.Email
import androidx.compose.material.icons.outlined.Info
import androidx.compose.material.icons.outlined.Lock
import androidx.compose.material.icons.outlined.Phone
import androidx.compose.material.icons.outlined.PhotoCamera
import androidx.compose.material.icons.outlined.Place
import androidx.compose.material.icons.outlined.RequestQuote
import androidx.compose.material.icons.outlined.Settings
import androidx.compose.material.icons.outlined.Verified
import androidx.compose.material.icons.outlined.Visibility
import androidx.compose.material.icons.outlined.VisibilityOff
import androidx.compose.material3.AlertDialog
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.HorizontalDivider
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.saveable.rememberSaveable
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.luminance
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import coil.compose.AsyncImage
import com.esimko.mobile.BuildConfig
import com.esimko.mobile.domain.model.Profile
import com.esimko.mobile.ui.common.CooperativeLoading
import com.esimko.mobile.ui.common.ErrorView
import com.esimko.mobile.ui.common.EsimkoLogo
import com.esimko.mobile.ui.common.EsimkoPreview
import com.esimko.mobile.ui.common.IconBadge
import com.esimko.mobile.ui.common.LightDarkPreview
import com.esimko.mobile.ui.common.PremiumCard
import com.esimko.mobile.ui.common.StatusBarAppearance
import com.esimko.mobile.ui.common.UiState
import com.esimko.mobile.ui.common.heroBackground
import com.esimko.mobile.ui.theme.GoldOnHero
import com.esimko.mobile.ui.theme.MoneyHero
import com.esimko.mobile.ui.theme.MoneyRow
import com.esimko.mobile.ui.theme.MoneySmall
import com.esimko.mobile.ui.theme.OnHero
import com.esimko.mobile.util.AccountFormat
import com.esimko.mobile.util.compressForUpload
import com.esimko.mobile.util.readBounded

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ProfileScreen(
    onLogout: () -> Unit,
    onOpenSettings: () -> Unit = {},
    onOpenInstallments: () -> Unit = {},
    viewModel: ProfileViewModel = hiltViewModel()
) {
    val uiState by viewModel.uiState.collectAsState()
    val passwordChangeState by viewModel.passwordChangeState.collectAsState()
    val avatarUploadState by viewModel.avatarUploadState.collectAsState()
    val cacheStatus by viewModel.cacheStatus.collectAsState()

    var hideBalance by rememberSaveable { mutableStateOf(false) }
    var showPasswordDialog by remember { mutableStateOf(false) }
    var showLogoutDialog by remember { mutableStateOf(false) }
    val currentProfile = (uiState as? UiState.Success)?.data
    var photoOptions by remember(currentProfile?.noAnggota, cacheStatus.sessionEpoch) { mutableStateOf(false) }
    var photoPreview by remember(currentProfile?.noAnggota, cacheStatus.sessionEpoch) { mutableStateOf(false) }
    var oldPassword by remember { mutableStateOf("") }
    var newPassword by remember { mutableStateOf("") }
    var confirmPassword by remember { mutableStateOf("") }
    var passwordMismatch by remember { mutableStateOf(false) }

    val context = LocalContext.current
    val imagePickerLauncher = rememberLauncherForActivityResult(
        contract = ActivityResultContracts.GetContent()
    ) { uri: Uri? ->
        uri?.let { selected ->
            viewModel.uploadAvatar {
                context.contentResolver.openInputStream(selected)?.use { input ->
                    input.readBounded().compressForUpload()
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

    // Inset atas dipegang HomeScreen: status bar berada di atas latar biasa, bukan di atas hero.
    StatusBarAppearance(darkIcons = MaterialTheme.colorScheme.background.luminance() > 0.5f)
    Box(
        Modifier
            .fillMaxSize()
            .background(MaterialTheme.colorScheme.background)
    ) {
        when (val state = uiState) {
            is UiState.Loading, is UiState.Idle -> CooperativeLoading(message = "Memuat akun Anda…")
            is UiState.Error -> ErrorView(
                message = state.message ?: "Terjadi kesalahan",
                onRetry = { viewModel.loadProfile() },
                modifier = Modifier.fillMaxSize()
            )
            is UiState.Success -> ProfileContent(
                profile = state.data,
                avatarUploadState = avatarUploadState,
                onPickAvatar = { photoOptions = true },
                onOpenSettings = onOpenSettings,
                onOpenInstallments = onOpenInstallments,
                onChangePassword = { showPasswordDialog = true },
                onLogout = { showLogoutDialog = true },
                hideBalance = hideBalance,
                onToggleBalance = { hideBalance = !hideBalance }
            )
        }

        if (currentProfile != null) {
            ProfileCacheNotice(cacheStatus, onRefresh = { viewModel.loadProfile(forceRefresh = true) },
                modifier = Modifier.align(Alignment.BottomCenter))
        }

        if (avatarUploadState is UiState.Error) {
            Text(
                (avatarUploadState as UiState.Error).message,
                color = MaterialTheme.colorScheme.onErrorContainer,
                style = MaterialTheme.typography.bodyMedium,
                modifier = Modifier.align(Alignment.BottomCenter)
                    .fillMaxWidth().background(MaterialTheme.colorScheme.errorContainer).padding(16.dp)
            )
        }

        if (photoOptions && currentProfile != null) {
            ProfilePhotoOptions(
                hasPhoto = !currentProfile.avatar.isNullOrBlank(),
                onOpen = { photoOptions = false; photoPreview = true },
                onChange = { photoOptions = false; imagePickerLauncher.launch("image/*") },
                onDismiss = { photoOptions = false }
            )
        }
        if (photoPreview && currentProfile?.avatar?.isNotBlank() == true) {
            ProfilePhotoDialog(currentProfile.avatar, onDismiss = { photoPreview = false })
        }

        if (showLogoutDialog) {
            AlertDialog(
                onDismissRequest = { showLogoutDialog = false },
                icon = { Icon(Icons.AutoMirrored.Outlined.Logout, contentDescription = null) },
                title = { Text("Keluar dari akun?") },
                text = { Text("Sesi akun ini digunakan bersama. Jika permintaan logout berhasil diterima server, sesi di perangkat lain juga berakhir. Saat offline, hanya sesi di perangkat ini yang dapat langsung dihapus. Anda perlu masuk kembali untuk memakai aplikasi.") },
                confirmButton = {
                    Button(
                        onClick = {
                            showLogoutDialog = false
                            viewModel.logout()
                            // MainActivity navigates after the local session is cleared.
                        },
                        colors = ButtonDefaults.buttonColors(
                            containerColor = MaterialTheme.colorScheme.error,
                            contentColor = MaterialTheme.colorScheme.onError
                        )
                    ) { Text("Keluar") }
                },
                dismissButton = {
                    TextButton(onClick = { showLogoutDialog = false }) { Text("Batal") }
                }
            )
        }

        if (showPasswordDialog) {
            AlertDialog(
                onDismissRequest = {
                    if (passwordChangeState !is UiState.Loading) {
                        showPasswordDialog = false
                        passwordMismatch = false
                    }
                },
                icon = { Icon(Icons.Outlined.Lock, contentDescription = null) },
                title = { Text("Ubah Password") },
                text = {
                    Column(
                        modifier = Modifier.verticalScroll(rememberScrollState()),
                        verticalArrangement = Arrangement.spacedBy(12.dp)
                    ) {
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
internal fun ProfileContent(
    profile: Profile,
    avatarUploadState: UiState<Unit>,
    onPickAvatar: () -> Unit,
    onOpenSettings: () -> Unit,
    onOpenInstallments: () -> Unit,
    onChangePassword: () -> Unit,
    onLogout: () -> Unit,
    modifier: Modifier = Modifier,
    hideBalance: Boolean = false,
    onToggleBalance: () -> Unit = {}
) {
    Column(modifier.fillMaxSize().verticalScroll(rememberScrollState())) {
        MemberHeader(
            profile = profile,
            avatarUploadState = avatarUploadState,
            hideBalance = hideBalance,
            onToggleBalance = onToggleBalance,
            onPickAvatar = onPickAvatar
        )

        Column(
            Modifier
                .fillMaxWidth()
                .offset(y = (-28).dp)
                .padding(horizontal = 16.dp),
            verticalArrangement = Arrangement.spacedBy(16.dp)
        ) {
            SavingsCard(profile, hideBalance)
            LoanCard(profile, hideBalance, onOpenInstallments)
            SectionLabel("Data Anggota")
            InfoCard(profile)
            SectionLabel("Akun & Keamanan")
            MenuCard(onOpenSettings, onChangePassword, onLogout)
            AppFooter()
        }
    }
}

@Composable
private fun MemberHeader(
    profile: Profile,
    avatarUploadState: UiState<Unit>,
    hideBalance: Boolean,
    onToggleBalance: () -> Unit,
    onPickAvatar: () -> Unit
) {
    val hero = heroBackground()
    Column(
        Modifier
            .fillMaxWidth()
            .clip(RoundedCornerShape(bottomStart = 28.dp, bottomEnd = 28.dp))
            .background(
                Brush.verticalGradient(listOf(hero, Color(0xFF0F5530).copy(alpha = 0.95f)))
            )
            .padding(start = 20.dp, end = 20.dp, top = 16.dp, bottom = 48.dp)
    ) {
        Row(Modifier.fillMaxWidth(), verticalAlignment = Alignment.CenterVertically) {
            Text(
                "Akun Saya",
                style = MaterialTheme.typography.titleLarge,
                fontWeight = FontWeight.Bold,
                color = OnHero,
                modifier = Modifier.weight(1f)
            )
            EsimkoLogo(background = hero, modifier = Modifier.width(104.dp).height(28.dp))
        }
        Spacer(Modifier.height(20.dp))
        Row(verticalAlignment = Alignment.CenterVertically) {
            AvatarWithEdit(profile, avatarUploadState, onPickAvatar)
            Spacer(Modifier.width(16.dp))
            Column(Modifier.weight(1f)) {
                Text(
                    profile.nama,
                    style = MaterialTheme.typography.titleLarge,
                    fontWeight = FontWeight.Bold,
                    color = OnHero,
                    maxLines = 2,
                    overflow = TextOverflow.Ellipsis
                )
                Spacer(Modifier.height(2.dp))
                Text(
                    "No. Anggota ${profile.noAnggota}",
                    style = MaterialTheme.typography.bodyMedium,
                    color = OnHero.copy(alpha = 0.85f)
                )
                if (profile.statusAnggota.isNotBlank()) {
                    Spacer(Modifier.height(8.dp))
                    MemberStatusPill(profile.statusAnggota)
                }
            }
        }
        Spacer(Modifier.height(20.dp))
        HorizontalDivider(color = OnHero.copy(alpha = 0.18f))
        Spacer(Modifier.height(14.dp))
        Row(verticalAlignment = Alignment.CenterVertically) {
            Column(Modifier.weight(1f)) {
                Text(
                    "Total Simpanan",
                    style = MaterialTheme.typography.labelMedium,
                    color = OnHero.copy(alpha = 0.8f)
                )
                if (hideBalance) {
                    Text("Rp ••••••", style = MoneyHero, color = GoldOnHero)
                } else {
                    Text(MoneyFormatter.format(profile.saldoSimpanan), style = MoneyHero, color = GoldOnHero)
                }
            }
            IconButton(onClick = onToggleBalance) {
                Icon(
                    if (hideBalance) Icons.Outlined.Visibility else Icons.Outlined.VisibilityOff,
                    contentDescription = if (hideBalance) "Tampilkan saldo" else "Sembunyikan saldo",
                    tint = OnHero
                )
            }
        }
    }
}

@Composable
private fun AvatarWithEdit(
    profile: Profile,
    avatarUploadState: UiState<Unit>,
    onPickAvatar: () -> Unit
) {
    val uploading = avatarUploadState is UiState.Loading
    Box(Modifier.size(80.dp)) {
        Box(
            Modifier
                .size(80.dp)
                .clip(CircleShape)
                .border(2.dp, GoldOnHero.copy(alpha = 0.9f), CircleShape)
                .padding(3.dp)
                .clip(CircleShape)
                .background(OnHero.copy(alpha = 0.15f))
                .clickable(enabled = !uploading, onClick = onPickAvatar),
            contentAlignment = Alignment.Center
        ) {
            if (!profile.avatar.isNullOrBlank()) {
                AsyncImage(
                    model = profile.avatar,
                    contentDescription = "Foto profil",
                    modifier = Modifier.fillMaxSize().clip(CircleShape),
                    contentScale = ContentScale.Crop
                )
            } else {
                Text(
                    AccountFormat.initials(profile.nama),
                    style = MaterialTheme.typography.headlineSmall,
                    fontWeight = FontWeight.Bold,
                    color = OnHero
                )
            }
            if (uploading) {
                Box(
                    Modifier.fillMaxSize().background(Color.Black.copy(alpha = 0.45f)),
                    contentAlignment = Alignment.Center
                ) {
                    CircularProgressIndicator(Modifier.size(24.dp), strokeWidth = 2.dp, color = OnHero)
                }
            }
        }
        Box(
            Modifier
                .align(Alignment.BottomEnd)
                .size(28.dp)
                .background(GoldOnHero, CircleShape)
                .border(2.dp, heroBackground(), CircleShape)
                .clickable(enabled = !uploading, onClick = onPickAvatar),
            contentAlignment = Alignment.Center
        ) {
            Icon(
                Icons.Outlined.PhotoCamera,
                contentDescription = "Ubah foto profil",
                tint = Color(0xFF3F2E00),
                modifier = Modifier.size(15.dp)
            )
        }
    }
}

@Composable
private fun MemberStatusPill(status: String) {
    val active = AccountFormat.isActiveStatus(status)
    Row(
        Modifier
            .background(
                if (active) GoldOnHero.copy(alpha = 0.18f) else OnHero.copy(alpha = 0.14f),
                RoundedCornerShape(50)
            )
            .padding(horizontal = 10.dp, vertical = 4.dp),
        verticalAlignment = Alignment.CenterVertically,
        horizontalArrangement = Arrangement.spacedBy(4.dp)
    ) {
        Icon(
            if (active) Icons.Outlined.Verified else Icons.Outlined.Info,
            contentDescription = null,
            tint = if (active) GoldOnHero else OnHero,
            modifier = Modifier.size(14.dp)
        )
        Text(
            "Anggota $status",
            style = MaterialTheme.typography.labelMedium,
            color = if (active) GoldOnHero else OnHero
        )
    }
}

private data class SavingsPart(val label: String, val amount: Long, val color: Color)

@Composable
private fun SavingsCard(profile: Profile, hideBalance: Boolean) {
    val dark = MaterialTheme.colorScheme.background.luminance() < 0.5f
    val parts = listOf(
        SavingsPart("Pokok", profile.saldoSimpananPokok, MaterialTheme.colorScheme.primary),
        SavingsPart("Wajib", profile.saldoSimpananWajib, if (dark) Color(0xFF9EC5FF) else Color(0xFF1F5FAF)),
        SavingsPart("Sukarela", profile.saldoSimpananSukarela, if (dark) Color(0xFFF0C94F) else Color(0xFFB8860B)),
        SavingsPart("Hari Raya", profile.saldoSimpananHariRaya, if (dark) Color(0xFFD3B8FF) else Color(0xFF6B3FA8))
    )
    val shares = if (hideBalance) List(parts.size) { 0f } else AccountFormat.shares(parts.map { it.amount })

    PremiumCard {
        BoxWithConstraints {
            val columns = if (maxWidth < 380.dp || LocalDensity.current.fontScale > 1.15f) 1 else 2
            Column(Modifier.padding(18.dp)) {
                Text("Rincian Simpanan", style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.Bold)
                Text(
                    "Komposisi saldo simpanan Anda",
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.onSurfaceVariant
                )
                Spacer(Modifier.height(14.dp))
                // Bilah komposisi — porsi tiap jenis simpanan terhadap total.
                Row(
                    Modifier
                        .fillMaxWidth()
                        .height(10.dp)
                        .clip(RoundedCornerShape(50))
                        .background(MaterialTheme.colorScheme.outlineVariant.copy(alpha = 0.4f))
                ) {
                    parts.forEachIndexed { i, part ->
                        if (shares[i] > 0f) {
                            Box(Modifier.weight(shares[i]).fillMaxSize().background(part.color))
                        }
                    }
                }
                Spacer(Modifier.height(14.dp))
                parts.chunked(columns).forEach { pair ->
                    Row(Modifier.fillMaxWidth().padding(vertical = 6.dp)) {
                        pair.forEach { part ->
                            Row(Modifier.weight(1f), verticalAlignment = Alignment.Top) {
                                Box(
                                    Modifier
                                        .padding(top = 5.dp)
                                        .size(8.dp)
                                        .background(part.color, CircleShape)
                                )
                                Spacer(Modifier.width(8.dp))
                                Column {
                                    Text(
                                        "Simpanan ${part.label}",
                                        style = MaterialTheme.typography.labelMedium,
                                        color = MaterialTheme.colorScheme.onSurfaceVariant
                                    )
                                    if (hideBalance) {
                                        Text("Rp ••••", style = MoneySmall)
                                    } else {
                                        Text(MoneyFormatter.format(part.amount), style = MoneySmall)
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

@Composable
private fun LoanCard(profile: Profile, hideBalance: Boolean, onOpenInstallments: () -> Unit) {
    val dark = MaterialTheme.colorScheme.background.luminance() < 0.5f
    val loanAccent = if (dark) Color(0xFF9EC5FF) else Color(0xFF1F5FAF)
    val noLoan = profile.saldoPinjaman <= 0L && profile.angsuranBulan <= 0L
    PremiumCard {
        Column(
            Modifier
                .fillMaxWidth()
                .clickable(onClick = onOpenInstallments)
                .padding(18.dp)
        ) {
            Row(verticalAlignment = Alignment.CenterVertically) {
                IconBadge(Icons.Outlined.RequestQuote, tint = loanAccent, size = 40.dp, iconSize = 20.dp)
                Spacer(Modifier.width(12.dp))
                Column(Modifier.weight(1f)) {
                    Text("Pinjaman", style = MaterialTheme.typography.titleMedium, fontWeight = FontWeight.Bold)
                    Text(
                        if (noLoan) "Tidak ada pinjaman berjalan" else "Lihat jadwal angsuran",
                        style = MaterialTheme.typography.bodySmall,
                        color = MaterialTheme.colorScheme.onSurfaceVariant
                    )
                }
                Icon(
                    Icons.AutoMirrored.Outlined.KeyboardArrowRight,
                    contentDescription = null,
                    tint = MaterialTheme.colorScheme.onSurfaceVariant
                )
            }
            if (!noLoan) {
                Spacer(Modifier.height(14.dp))
                BoxWithConstraints {
                    if (maxWidth < 350.dp || LocalDensity.current.fontScale > 1.15f) {
                        Column(verticalArrangement = Arrangement.spacedBy(10.dp)) {
                            LoanFigure("Sisa Pinjaman", profile.saldoPinjaman, hideBalance, Modifier.fillMaxWidth())
                            LoanFigure("Angsuran Bulan Ini", profile.angsuranBulan, hideBalance, Modifier.fillMaxWidth())
                        }
                    } else {
                        Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                            LoanFigure("Sisa Pinjaman", profile.saldoPinjaman, hideBalance, Modifier.weight(1f))
                            LoanFigure("Angsuran Bulan Ini", profile.angsuranBulan, hideBalance, Modifier.weight(1f))
                        }
                    }
                }
            }
        }
    }
}

@Composable
private fun LoanFigure(label: String, amount: Long, hide: Boolean, modifier: Modifier) {
    Column(
        modifier
            .background(MaterialTheme.colorScheme.surfaceVariant.copy(alpha = 0.45f), RoundedCornerShape(14.dp))
            .padding(horizontal = 12.dp, vertical = 10.dp)
    ) {
        Text(label, style = MaterialTheme.typography.labelMedium, color = MaterialTheme.colorScheme.onSurfaceVariant)
        Spacer(Modifier.height(2.dp))
        Text(if (hide) "Rp ••••" else MoneyFormatter.format(amount), style = MoneyRow)
    }
}

@Composable
private fun SectionLabel(text: String) {
    Text(
        text,
        style = MaterialTheme.typography.titleSmall,
        fontWeight = FontWeight.Bold,
        color = MaterialTheme.colorScheme.onSurfaceVariant,
        modifier = Modifier.padding(start = 4.dp, top = 4.dp)
    )
}

@Composable
private fun InfoCard(profile: Profile) {
    val accent = MaterialTheme.colorScheme.primary
    val rows = buildList {
        if (profile.divisi.isNotBlank() || profile.bagian.isNotBlank()) {
            add(Triple(Icons.Outlined.Business, "Divisi / Bagian",
                listOf(profile.divisi, profile.bagian).filter { it.isNotBlank() }.joinToString(" · ")))
        }
        add(Triple(Icons.Outlined.Badge, "No. KTP", AccountFormat.maskKtp(profile.ktp)))
        add(Triple(Icons.Outlined.Phone, "Telepon", profile.telepon.ifBlank { "-" }))
        add(Triple(Icons.Outlined.Email, "Email", profile.email?.takeIf { it.isNotBlank() } ?: "-"))
        add(Triple(Icons.Outlined.Place, "Alamat", profile.alamat.ifBlank { "-" }))
    }
    PremiumCard {
        rows.forEachIndexed { i, (icon, label, value) ->
            Row(
                Modifier.fillMaxWidth().padding(horizontal = 16.dp, vertical = 12.dp),
                verticalAlignment = Alignment.CenterVertically
            ) {
                IconBadge(icon, tint = accent, size = 38.dp, iconSize = 19.dp)
                Spacer(Modifier.width(12.dp))
                Column(Modifier.weight(1f)) {
                    Text(label, style = MaterialTheme.typography.labelMedium, color = MaterialTheme.colorScheme.onSurfaceVariant)
                    Text(
                        value,
                        style = MaterialTheme.typography.bodyMedium,
                        fontWeight = FontWeight.Medium,
                        maxLines = 3,
                        overflow = TextOverflow.Ellipsis
                    )
                }
            }
            if (i < rows.lastIndex) CardDivider()
        }
    }
}

@Composable
private fun MenuCard(
    onOpenSettings: () -> Unit,
    onChangePassword: () -> Unit,
    onLogout: () -> Unit
) {
    PremiumCard {
        MenuRow(Icons.Outlined.Settings, "Pengaturan", "Tampilan, notifikasi, tentang aplikasi", onClick = onOpenSettings)
        CardDivider()
        MenuRow(Icons.Outlined.Lock, "Ubah Password", "Perbarui kata sandi akun Anda", onClick = onChangePassword)
        CardDivider()
        MenuRow(
            Icons.AutoMirrored.Outlined.Logout,
            "Keluar",
            "Akhiri sesi di perangkat ini",
            tint = MaterialTheme.colorScheme.error,
            onClick = onLogout
        )
    }
}

@Composable
private fun MenuRow(
    icon: ImageVector,
    title: String,
    subtitle: String,
    tint: Color = MaterialTheme.colorScheme.primary,
    onClick: () -> Unit
) {
    Row(
        Modifier
            .fillMaxWidth()
            .heightIn(min = 64.dp)
            .clickable(onClick = onClick)
            .padding(horizontal = 16.dp, vertical = 12.dp),
        verticalAlignment = Alignment.CenterVertically
    ) {
        IconBadge(icon, tint = tint, size = 38.dp, iconSize = 19.dp)
        Spacer(Modifier.width(12.dp))
        Column(Modifier.weight(1f)) {
            Text(
                title,
                style = MaterialTheme.typography.bodyLarge,
                fontWeight = FontWeight.SemiBold,
                color = if (tint == MaterialTheme.colorScheme.error) tint else MaterialTheme.colorScheme.onSurface
            )
            Text(subtitle, style = MaterialTheme.typography.bodySmall, color = MaterialTheme.colorScheme.onSurfaceVariant)
        }
        Icon(
            Icons.AutoMirrored.Outlined.KeyboardArrowRight,
            contentDescription = null,
            tint = MaterialTheme.colorScheme.onSurfaceVariant.copy(alpha = 0.7f)
        )
    }
}

@Composable
private fun CardDivider() {
    HorizontalDivider(
        modifier = Modifier.padding(start = 66.dp),
        color = MaterialTheme.colorScheme.outlineVariant.copy(alpha = 0.45f)
    )
}

@Composable
private fun AppFooter() {
    Column(
        Modifier.fillMaxWidth().padding(top = 4.dp, bottom = 8.dp),
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        Row(verticalAlignment = Alignment.CenterVertically) {
            Icon(
                Icons.Outlined.CalendarMonth,
                contentDescription = null,
                tint = MaterialTheme.colorScheme.onSurfaceVariant.copy(alpha = 0.6f),
                modifier = Modifier.size(14.dp)
            )
            Spacer(Modifier.width(6.dp))
            Text(
                "eSIMKO v${BuildConfig.VERSION_NAME}",
                style = MaterialTheme.typography.labelSmall,
                color = MaterialTheme.colorScheme.onSurfaceVariant.copy(alpha = 0.8f)
            )
        }
        Text(
            "Koperasi Karyawan Satya Sejahtera",
            style = MaterialTheme.typography.labelSmall,
            color = MaterialTheme.colorScheme.onSurfaceVariant.copy(alpha = 0.6f)
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
                saldoSimpanan = 1_800_000,
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
            onOpenInstallments = {},
            onChangePassword = {},
            onLogout = {}
        )
    }
}
