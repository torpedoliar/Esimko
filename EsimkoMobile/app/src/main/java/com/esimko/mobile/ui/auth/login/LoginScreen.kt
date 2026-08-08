package com.esimko.mobile.ui.auth.login

import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.offset
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Lock
import androidx.compose.material.icons.filled.Person
import androidx.compose.material3.Button
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.res.painterResource
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardCapitalization
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
fun LoginScreen(
    onLoginSuccess: () -> Unit,
    onNavigateToRegister: () -> Unit = {},
    viewModel: LoginViewModel = hiltViewModel()
) {
    val uiState = viewModel.uiState

    // Navigasi aman: LaunchedEffect, bukan panggilan langsung di compose (mencegah recoil berulang).
    LaunchedEffect(uiState.isLoggedIn) {
        if (uiState.isLoggedIn) onLoginSuccess()
    }

    Column(Modifier.fillMaxSize().background(MaterialTheme.colorScheme.background)) {
        // Hero header hijau pekat + wordmark putih
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .background(heroBackground())
                .padding(top = 72.dp, bottom = 96.dp, start = 24.dp, end = 24.dp),
            horizontalAlignment = Alignment.CenterHorizontally
        ) {
            Image(
                painter = painterResource(R.drawable.logo_esimko_wordmark_light),
                contentDescription = "esimko",
                modifier = Modifier.height(28.dp)
            )
            Spacer(Modifier.height(8.dp))
            Text(
                text = "Koperasi SIMKO",
                style = MaterialTheme.typography.bodyMedium,
                color = OnHero.copy(alpha = 0.85f)
            )
        }
        // Form card — overlap header, hanya warna token dirombak
        Card(
            modifier = Modifier
                .fillMaxWidth()
                .padding(horizontal = 20.dp)
                .offset(y = (-48).dp),
            shape = MaterialTheme.shapes.large,
            colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface)
        ) {
            Column(Modifier.padding(24.dp)) {
                Text("Masuk", style = MaterialTheme.typography.headlineSmall, fontWeight = FontWeight.SemiBold)
                Spacer(Modifier.height(4.dp))
                Text(
                    "Masukkan No. Anggota dan password Anda",
                    style = MaterialTheme.typography.bodyMedium,
                    color = MaterialTheme.colorScheme.onSurfaceVariant
                )
                Spacer(Modifier.height(24.dp))
                OutlinedTextField(
                    value = uiState.username,
                    onValueChange = viewModel::onUsernameChange,
                    label = { Text("No. Anggota") },
                    leadingIcon = { Icon(Icons.Default.Person, contentDescription = null) },
                    modifier = Modifier.fillMaxWidth(),
                    keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Text, capitalization = KeyboardCapitalization.Characters),
                    enabled = !uiState.isLoading,
                    singleLine = true
                )
                Spacer(Modifier.height(16.dp))
                OutlinedTextField(
                    value = uiState.password,
                    onValueChange = viewModel::onPasswordChange,
                    label = { Text("Password") },
                    leadingIcon = { Icon(Icons.Default.Lock, contentDescription = null) },
                    modifier = Modifier.fillMaxWidth(),
                    visualTransformation = PasswordVisualTransformation(),
                    keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Password),
                    enabled = !uiState.isLoading,
                    singleLine = true
                )
                uiState.error?.let { error ->
                    Spacer(Modifier.height(8.dp))
                    Text(
                        text = error,
                        color = MaterialTheme.colorScheme.error,
                        style = MaterialTheme.typography.bodySmall
                    )
                }
                Spacer(Modifier.height(24.dp))
                Button(
                    onClick = viewModel::login,
                    modifier = Modifier.fillMaxWidth().height(52.dp),
                    enabled = !uiState.isLoading
                ) {
                    if (uiState.isLoading) {
                        CircularProgressIndicator(
                            Modifier.size(24.dp),
                            color = MaterialTheme.colorScheme.onPrimary
                        )
                    } else {
                        Text("Login", style = MaterialTheme.typography.labelLarge)
                    }
                }
                Spacer(Modifier.height(12.dp))
                TextButton(
                    onClick = onNavigateToRegister,
                    enabled = !uiState.isLoading,
                    modifier = Modifier.align(Alignment.CenterHorizontally)
                ) {
                    Text("Belum punya akun? Daftar")
                }
            }
        }
    }
}

@LightDarkPreview
@Composable
private fun LoginBrandHeaderPreview() {
    EsimkoPreview {
        Column(
            Modifier
                .fillMaxWidth()
                .background(heroBackground())
                .padding(top = 72.dp, bottom = 96.dp, start = 24.dp, end = 24.dp),
            horizontalAlignment = Alignment.CenterHorizontally
        ) {
            Image(
                painter = painterResource(R.drawable.logo_esimko_wordmark_light),
                contentDescription = "esimko",
                modifier = Modifier.height(28.dp)
            )
            Spacer(Modifier.height(8.dp))
            Text(
                "Koperasi SIMKO",
                style = MaterialTheme.typography.bodyMedium,
                color = OnHero.copy(alpha = 0.85f)
            )
        }
    }
}
