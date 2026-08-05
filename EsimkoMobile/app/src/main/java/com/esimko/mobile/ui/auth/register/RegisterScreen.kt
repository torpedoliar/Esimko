package com.esimko.mobile.ui.auth.register

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import com.esimko.mobile.ui.common.EsimkoButton
import com.esimko.mobile.ui.common.EsimkoOutlinedButton
import com.esimko.mobile.ui.theme.Primary
import com.esimko.mobile.ui.theme.OnPrimary

@Composable
fun RegisterScreen(
    onRegisterSuccess: () -> Unit,
    onBackToLogin: () -> Unit,
    viewModel: RegisterViewModel = hiltViewModel()
) {
    val s = viewModel.uiState

    if (s.isRegistered) {
        onRegisterSuccess()
        return
    }

    Column(
        modifier = Modifier
            .fillMaxSize()
            .background(MaterialTheme.colorScheme.background)
    ) {
        // Brand header hijau
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .background(Primary)
                .padding(top = 16.dp, bottom = 24.dp),
            horizontalAlignment = Alignment.Start
        ) {
            IconButton(
                onClick = onBackToLogin,
                enabled = !s.isLoading
            ) {
                Icon(
                    Icons.AutoMirrored.Filled.ArrowBack,
                    contentDescription = "Kembali",
                    tint = OnPrimary
                )
            }
            Text(
                text = "Daftar Anggota",
                style = MaterialTheme.typography.headlineSmall,
                color = OnPrimary,
                modifier = Modifier.padding(horizontal = 24.dp)
            )
            Spacer(modifier = Modifier.height(4.dp))
            Text(
                text = "Bergabung dengan Koperasi SIMKO",
                style = MaterialTheme.typography.bodyMedium,
                color = OnPrimary.copy(alpha = 0.85f),
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
        Spacer(modifier = Modifier.height(16.dp))

        OutlinedTextField(
            value = s.nama,
            onValueChange = viewModel::setNama,
            label = { Text("Nama Lengkap") },
            modifier = Modifier.fillMaxWidth(),
            enabled = !s.isLoading
        )
        Spacer(modifier = Modifier.height(12.dp))

        OutlinedTextField(
            value = s.noKtp,
            onValueChange = viewModel::setNoKtp,
            label = { Text("No. KTP") },
            modifier = Modifier.fillMaxWidth(),
            keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Number),
            enabled = !s.isLoading
        )
        Spacer(modifier = Modifier.height(12.dp))

        OutlinedTextField(
            value = s.telepon,
            onValueChange = viewModel::setTelepon,
            label = { Text("No. Handphone") },
            modifier = Modifier.fillMaxWidth(),
            keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Phone),
            enabled = !s.isLoading
        )
        Spacer(modifier = Modifier.height(12.dp))

        OutlinedTextField(
            value = s.password,
            onValueChange = viewModel::setPassword,
            label = { Text("Password") },
            modifier = Modifier.fillMaxWidth(),
            visualTransformation = PasswordVisualTransformation(),
            keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Password),
            enabled = !s.isLoading
        )
        Spacer(modifier = Modifier.height(12.dp))

        OutlinedTextField(
            value = s.confirmPassword,
            onValueChange = viewModel::setConfirmPassword,
            label = { Text("Konfirmasi Password") },
            modifier = Modifier.fillMaxWidth(),
            visualTransformation = PasswordVisualTransformation(),
            keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Password),
            enabled = !s.isLoading
        )

        s.error?.let { error ->
            Spacer(modifier = Modifier.height(8.dp))
            Text(
                text = error,
                color = MaterialTheme.colorScheme.error,
                style = MaterialTheme.typography.bodySmall
            )
        }

        Spacer(modifier = Modifier.height(24.dp))

        EsimkoButton(
            text = "Daftar",
            onClick = viewModel::register,
            isLoading = s.isLoading
        )

        Spacer(modifier = Modifier.height(12.dp))

        EsimkoOutlinedButton(
            text = "Kembali ke Login",
            onClick = onBackToLogin,
            enabled = !s.isLoading
        )
        }
    }
}
