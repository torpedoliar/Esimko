package com.esimko.mobile.ui.auth.register

import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material3.Button
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedButton
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.res.painterResource
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import com.esimko.mobile.R
import com.esimko.mobile.ui.common.EsimkoPreview
import com.esimko.mobile.ui.common.LightDarkPreview
import com.esimko.mobile.ui.common.heroBackground
import com.esimko.mobile.ui.theme.OnHero

@Composable
fun RegisterScreen(
    onRegisterSuccess: () -> Unit,
    onBackToLogin: () -> Unit,
    viewModel: RegisterViewModel = hiltViewModel()
) {
    val s = viewModel.uiState

    // Navigasi aman: LaunchedEffect, bukan if + return awal (mencegah keluar compose di tengah).
    LaunchedEffect(s.isRegistered) {
        if (s.isRegistered) onRegisterSuccess()
    }

    Column(Modifier.fillMaxSize().background(MaterialTheme.colorScheme.background)) {
        // Hero header hijau pekat + tombol kembali + wordmark putih
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .background(heroBackground())
                .padding(top = 16.dp, bottom = 24.dp),
            horizontalAlignment = Alignment.Start
        ) {
            IconButton(onClick = onBackToLogin, enabled = !s.isLoading) {
                Icon(
                    Icons.AutoMirrored.Filled.ArrowBack,
                    contentDescription = "Kembali",
                    tint = OnHero
                )
            }
            Image(
                painter = painterResource(R.drawable.logo_esimko_wordmark_light),
                contentDescription = "esimko",
                modifier = Modifier
                    .height(24.dp)
                    .padding(horizontal = 24.dp)
            )
            Spacer(Modifier.height(4.dp))
            Text(
                "Daftar Anggota",
                style = MaterialTheme.typography.headlineSmall,
                color = OnHero,
                fontWeight = FontWeight.Bold,
                modifier = Modifier.padding(horizontal = 24.dp)
            )
            Spacer(Modifier.height(4.dp))
            Text(
                "Bergabung dengan Koperasi SIMKO",
                style = MaterialTheme.typography.bodyMedium,
                color = OnHero.copy(alpha = 0.85f),
                modifier = Modifier.padding(horizontal = 24.dp)
            )
        }
        Column(
            modifier = Modifier
                .fillMaxSize()
                .verticalScroll(rememberScrollState())
                .padding(24.dp),
            horizontalAlignment = Alignment.CenterHorizontally
        ) {
            Spacer(Modifier.height(16.dp))
            OutlinedTextField(
                value = s.nama,
                onValueChange = viewModel::setNama,
                label = { Text("Nama Lengkap") },
                modifier = Modifier.fillMaxWidth(),
                enabled = !s.isLoading,
                singleLine = true
            )
            Spacer(Modifier.height(12.dp))
            OutlinedTextField(
                value = s.noKtp,
                onValueChange = viewModel::setNoKtp,
                label = { Text("No. KTP") },
                modifier = Modifier.fillMaxWidth(),
                keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Number),
                enabled = !s.isLoading,
                singleLine = true
            )
            Spacer(Modifier.height(12.dp))
            OutlinedTextField(
                value = s.telepon,
                onValueChange = viewModel::setTelepon,
                label = { Text("No. Handphone") },
                modifier = Modifier.fillMaxWidth(),
                keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Phone),
                enabled = !s.isLoading,
                singleLine = true
            )
            Spacer(Modifier.height(12.dp))
            OutlinedTextField(
                value = s.password,
                onValueChange = viewModel::setPassword,
                label = { Text("Password") },
                modifier = Modifier.fillMaxWidth(),
                visualTransformation = PasswordVisualTransformation(),
                keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Password),
                enabled = !s.isLoading,
                singleLine = true
            )
            Spacer(Modifier.height(12.dp))
            OutlinedTextField(
                value = s.confirmPassword,
                onValueChange = viewModel::setConfirmPassword,
                label = { Text("Konfirmasi Password") },
                modifier = Modifier.fillMaxWidth(),
                visualTransformation = PasswordVisualTransformation(),
                keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Password),
                enabled = !s.isLoading,
                singleLine = true
            )
            s.error?.let { error ->
                Spacer(Modifier.height(8.dp))
                Text(
                    text = error,
                    color = MaterialTheme.colorScheme.error,
                    style = MaterialTheme.typography.bodySmall
                )
            }
            Spacer(Modifier.height(24.dp))
            Button(
                onClick = viewModel::register,
                modifier = Modifier.fillMaxWidth().height(52.dp),
                enabled = !s.isLoading
            ) {
                if (s.isLoading) {
                    CircularProgressIndicator(
                        Modifier.size(24.dp),
                        color = MaterialTheme.colorScheme.onPrimary
                    )
                } else {
                    Text("Daftar", style = MaterialTheme.typography.labelLarge)
                }
            }
            Spacer(Modifier.height(12.dp))
            OutlinedButton(
                onClick = onBackToLogin,
                enabled = !s.isLoading,
                modifier = Modifier.fillMaxWidth().height(52.dp)
            ) {
                Text("Kembali ke Login")
            }
        }
    }
}

@LightDarkPreview
@Composable
private fun RegisterBrandHeaderPreview() {
    EsimkoPreview {
        Column(
            Modifier
                .fillMaxWidth()
                .background(heroBackground())
                .padding(top = 16.dp, bottom = 24.dp),
            horizontalAlignment = Alignment.Start
        ) {
            Image(
                painter = painterResource(R.drawable.logo_esimko_wordmark_light),
                contentDescription = "esimko",
                modifier = Modifier
                    .height(24.dp)
                    .padding(horizontal = 24.dp)
            )
            Spacer(Modifier.height(4.dp))
            Text(
                "Daftar Anggota",
                style = MaterialTheme.typography.headlineSmall,
                color = OnHero,
                fontWeight = FontWeight.Bold,
                modifier = Modifier.padding(horizontal = 24.dp)
            )
        }
    }
}
