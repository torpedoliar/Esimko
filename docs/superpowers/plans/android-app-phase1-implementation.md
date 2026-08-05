# [IMPLEMENTATION PLAN — Minimax M2.7] Android App eSIMKO Phase 1

> 🤖 **AGENT-TO-AGENT SPEC.** Baca seluruh dokumen sebelum menulis kode.
> Format: instruksi imperatif + step deterministik + VERIFY gate.
> Jangan menafsirkan ulang. Jangan menambah scope.

---

## 0. CONTEXT

```yaml
repo: torpedoliar/Esimko  (Android project — NEW, di subfolder EsimkoMobile/)
spec: docs/superpowers/specs/2026-07-03-android-app-design.md
stack: Kotlin 1.9.22, Jetpack Compose, Material 3, Hilt, Retrofit, Moshi, Coil
backend: Laravel 7.x @ https://esimko.com/api/ (sudah LIVE)
minSdk: 28   targetSdk: 34   compileSdk: 34
architecture: MVVM + Clean Architecture (single-module)
```

## 1. PRIME DIRECTIVE

> ⛔ **Membuat file tanpa menghubungkannya ke navigation graph / DI / API = TASK FAILED.**
> Definisi "selesai" = APK bisa di-build, login bisa dijalankan ke server lokal, dan navigasi antar tab berfungsi.

---

## 2. GUARDRAILS

```yaml
RULES:
  - Gunakan Jetpack Compose (BUKAN XML layout).
  - Gunakan Hilt untuk semua DI (BUKAN manual constructor injection).
  - Gunakan Moshi untuk JSON parsing (BUKAN Gson).
  - Gunakan Kotlin Coroutines + Flow (BUKAN RxJava).
  - Gunakan Navigation Compose (BUKAN Fragment navigation).
  - Semua API call melalui Retrofit suspend function.
  - Semua state di ViewModel menggunakan StateFlow<UiState>.
  - JANGAN hardcode URL — gunakan BuildConfig.BASE_URL.
  - JANGAN commit secrets (token, password) ke source code.
  - SETIAP Phase harus bisa di-build (./gradlew assembleDebug) sebelum lanjut Phase berikutnya.
```

---

## 3. PHASE BREAKDOWN

Phase 1 dibagi menjadi 6 sub-phase yang harus dikerjakan **berurutan**. Setiap sub-phase punya VERIFY gate.

---

### PHASE 1A — Project Setup & Configuration

**GOAL:** Android project baru yang bisa di-build dengan semua dependencies terpasang.

#### Step 1 — Create Android Project

```bash
# Di Android Studio atau command line:
# Project name: EsimkoMobile
# Package: com.esimko.mobile
# Min SDK: 28
# Language: Kotlin
# Build: Kotlin DSL (build.gradle.kts)
# Template: Empty Compose Activity
```

#### Step 2 — Configure build.gradle.kts (app-level)

Tambahkan semua dependencies berikut di `dependencies {}`:

```kotlin
plugins {
    id("com.android.application")
    id("org.jetbrains.kotlin.android")
    id("com.google.dagger.hilt.android")
    id("com.google.devtools.ksp")
}

android {
    namespace = "com.esimko.mobile"
    compileSdk = 34

    defaultConfig {
        applicationId = "com.esimko.mobile"
        minSdk = 28
        targetSdk = 34
        versionCode = 1
        versionName = "1.0.0"
    }

    buildTypes {
        debug {
            buildConfigField("String", "BASE_URL", "\"http://10.0.2.2:8080/api/\"")
        }
        release {
            isMinifyEnabled = true
            proguardFiles(getDefaultProguardFile("proguard-android-optimize.txt"), "proguard-rules.pro")
            buildConfigField("String", "BASE_URL", "\"https://esimko.com/api/\"")
        }
    }

    buildFeatures {
        compose = true
        buildConfig = true
    }
}

dependencies {
    // Compose BOM
    val composeBom = platform("androidx.compose:compose-bom:2024.02.00")
    implementation(composeBom)
    implementation("androidx.compose.ui:ui")
    implementation("androidx.compose.material3:material3")
    implementation("androidx.compose.ui:ui-tooling-preview")
    debugImplementation("androidx.compose.ui:ui-tooling")

    // Navigation
    implementation("androidx.navigation:navigation-compose:2.7.7")

    // Hilt
    implementation("com.google.dagger:hilt-android:2.50")
    ksp("com.google.dagger:hilt-android-compiler:2.50")
    implementation("androidx.hilt:hilt-navigation-compose:1.2.0")

    // Retrofit + OkHttp + Moshi
    implementation("com.squareup.retrofit2:retrofit:2.9.0")
    implementation("com.squareup.retrofit2:converter-moshi:2.9.0")
    implementation("com.squareup.okhttp3:okhttp:4.12.0")
    implementation("com.squareup.okhttp3:logging-interceptor:4.12.0")
    implementation("com.squareup.moshi:moshi-kotlin:1.15.0")
    ksp("com.squareup.moshi:moshi-kotlin-codegen:1.15.0")

    // Coil (image loading)
    implementation("io.coil-kt:coil-compose:2.6.0")

    // Security (EncryptedSharedPreferences)
    implementation("androidx.security:security-crypto:1.1.0-alpha06")

    // Biometric
    implementation("androidx.biometric:biometric:1.2.0-alpha05")

    // DataStore
    implementation("androidx.datastore:datastore-preferences:1.0.0")

    // Coroutines
    implementation("org.jetbrains.kotlinx:kotlinx-coroutines-android:1.8.0")

    // Lifecycle
    implementation("androidx.lifecycle:lifecycle-viewmodel-compose:2.7.0")
    implementation("androidx.lifecycle:lifecycle-runtime-compose:2.7.0")

    // Testing
    testImplementation("junit:junit:4.13.2")
    testImplementation("io.mockk:mockk:1.13.9")
    testImplementation("app.cash.turbine:turbine:1.0.0")
    testImplementation("com.google.truth:truth:1.4.0")
    testImplementation("org.jetbrains.kotlinx:kotlinx-coroutines-test:1.8.0")
}
```

#### Step 3 — Network Security Config

**CREATE:** `app/src/main/res/xml/network_security_config.xml`

```xml
<?xml version="1.0" encoding="utf-8"?>
<network-security-config>
    <domain-config cleartextTrafficPermitted="true">
        <domain includeSubdomains="true">10.0.2.2</domain>
        <domain includeSubdomains="true">localhost</domain>
        <domain includeSubdomains="true">192.168.1.100</domain>
    </domain-config>
    <domain-config cleartextTrafficPermitted="false">
        <domain includeSubdomains="true">esimko.com</domain>
    </domain-config>
</network-security-config>
```

**UPDATE** `AndroidManifest.xml`:
```xml
<application
    android:name=".EsimkoApp"
    android:networkSecurityConfig="@xml/network_security_config"
    android:usesCleartextTraffic="true"
    ...>
```

#### Step 4 — Create folder structure

```
app/src/main/java/com/esimko/mobile/
├── di/
├── data/
│   ├── remote/
│   │   ├── api/
│   │   ├── dto/
│   │   └── interceptor/
│   ├── repository/
│   ├── local/
│   └── mapper/
├── domain/
│   ├── model/
│   ├── repository/
│   └── usecase/
├── ui/
│   ├── auth/
│   ├── home/
│   ├── savings/
│   ├── loans/
│   ├── shopping/
│   ├── news/
│   ├── profile/
│   ├── common/
│   └── theme/
├── core/
│   ├── network/
│   └── util/
├── EsimkoApp.kt
└── MainActivity.kt
```

#### VERIFY-1A:
```bash
./gradlew assembleDebug
# Harus SUCCESS. Jika gagal, perbaiki sebelum lanjut.
```

---

### PHASE 1B — Core Infrastructure (DI, Network, Auth Store)

**GOAL:** Hilt module, Retrofit client, token storage, interceptors — semua terhubung.

#### Step 1 — EsimkoApp.kt (Hilt Application)

**CREATE:** `com/esimko/mobile/EsimkoApp.kt`

```kotlin
package com.esimko.mobile

import android.app.Application
import dagger.hilt.android.HiltAndroidApp

@HiltAndroidApp
class EsimkoApp : Application()
```

#### Step 2 — Token Store

**CREATE:** `data/local/TokenStore.kt`

```kotlin
package com.esimko.mobile.data.local

import android.content.Context
import androidx.security.crypto.EncryptedSharedPreferences
import androidx.security.crypto.MasterKey
import dagger.hilt.android.qualifiers.ApplicationContext
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class TokenStore @Inject constructor(@ApplicationContext context: Context) {
    private val masterKey = MasterKey.Builder(context)
        .setKeyScheme(MasterKey.KeyScheme.AES256_GCM)
        .build()

    private val prefs = EncryptedSharedPreferences.create(
        context, "esimko_secure_prefs", masterKey,
        EncryptedSharedPreferences.PrefKeyEncryptionScheme.AES256_SIV,
        EncryptedSharedPreferences.PrefValueEncryptionScheme.AES256_GCM
    )

    fun saveToken(token: String) = prefs.edit().putString("auth_token", token).apply()
    fun getToken(): String? = prefs.getString("auth_token", null)
    fun clearToken() = prefs.edit().remove("auth_token").apply()

    fun saveUser(noAnggota: String, nama: String) {
        prefs.edit().putString("no_anggota", noAnggota).putString("nama", nama).apply()
    }
    fun getNoAnggota(): String? = prefs.getString("no_anggota", null)
    fun getNama(): String? = prefs.getString("nama", null)
    fun clearAll() = prefs.edit().clear().apply()
}
```

#### Step 3 — Auth Interceptor

**CREATE:** `data/remote/interceptor/AuthInterceptor.kt`

```kotlin
package com.esimko.mobile.data.remote.interceptor

import com.esimko.mobile.data.local.TokenStore
import okhttp3.Interceptor
import okhttp3.Response
import javax.inject.Inject

class AuthInterceptor @Inject constructor(
    private val tokenStore: TokenStore
) : Interceptor {
    override fun intercept(chain: Interceptor.Chain): Response {
        val request = chain.request().newBuilder()
        tokenStore.getToken()?.let { token ->
            request.addHeader("Authorization", "Bearer $token")
        }
        request.addHeader("Accept", "application/json")
        return chain.proceed(request.build())
    }
}
```

#### Step 4 — API Response DTO

**CREATE:** `data/remote/dto/ApiResponse.kt`

```kotlin
package com.esimko.mobile.data.remote.dto

import com.squareup.moshi.JsonClass

@JsonClass(generateAdapter = true)
data class ApiResponse<T>(
    val success: Boolean,
    val message: String?,
    val data: T?,
    val meta: MetaResponse?
)

@JsonClass(generateAdapter = true)
data class MetaResponse(
    val current_page: Int?,
    val last_page: Int?,
    val per_page: Int?,
    val total: Int?
)
```

#### Step 5 — Result Wrapper

**CREATE:** `core/network/Result.kt`

```kotlin
package com.esimko.mobile.core.network

sealed class Result<out T> {
    data class Success<T>(val data: T) : Result<T>()
    data class Error(val message: String, val code: Int? = null) : Result<Nothing>()
    object Loading : Result<Nothing>()
}
```

#### Step 6 — Network Module (Hilt)

**CREATE:** `di/NetworkModule.kt`

```kotlin
package com.esimko.mobile.di

import com.esimko.mobile.BuildConfig
import com.esimko.mobile.data.local.TokenStore
import com.esimko.mobile.data.remote.api.*
import com.esimko.mobile.data.remote.interceptor.AuthInterceptor
import com.squareup.moshi.Moshi
import com.squareup.moshi.kotlin.reflect.KotlinJsonAdapterFactory
import dagger.Module
import dagger.Provides
import dagger.hilt.InstallIn
import dagger.hilt.components.SingletonComponent
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.moshi.MoshiConverterFactory
import java.util.concurrent.TimeUnit
import javax.inject.Singleton

@Module
@InstallIn(SingletonComponent::class)
object NetworkModule {

    @Provides @Singleton
    fun provideMoshi(): Moshi = Moshi.Builder()
        .addLast(KotlinJsonAdapterFactory())
        .build()

    @Provides @Singleton
    fun provideOkHttpClient(authInterceptor: AuthInterceptor): OkHttpClient {
        val builder = OkHttpClient.Builder()
            .addInterceptor(authInterceptor)
            .connectTimeout(30, TimeUnit.SECONDS)
            .readTimeout(30, TimeUnit.SECONDS)

        if (BuildConfig.DEBUG) {
            builder.addInterceptor(
                HttpLoggingInterceptor().setLevel(HttpLoggingInterceptor.Level.BODY)
            )
        }
        return builder.build()
    }

    @Provides @Singleton
    fun provideRetrofit(client: OkHttpClient, moshi: Moshi): Retrofit = Retrofit.Builder()
        .baseUrl(BuildConfig.BASE_URL)
        .client(client)
        .addConverterFactory(MoshiConverterFactory.create(moshi))
        .build()

    @Provides @Singleton
    fun provideAuthApi(retrofit: Retrofit): AuthApi = retrofit.create(AuthApi::class.java)

    @Provides @Singleton
    fun provideProfileApi(retrofit: Retrofit): ProfileApi = retrofit.create(ProfileApi::class.java)

    @Provides @Singleton
    fun provideTransactionApi(retrofit: Retrofit): TransactionApi = retrofit.create(TransactionApi::class.java)

    @Provides @Singleton
    fun provideShoppingApi(retrofit: Retrofit): ShoppingApi = retrofit.create(ShoppingApi::class.java)

    @Provides @Singleton
    fun provideNewsApi(retrofit: Retrofit): NewsApi = retrofit.create(NewsApi::class.java)

    @Provides @Singleton
    fun provideMasterApi(retrofit: Retrofit): MasterApi = retrofit.create(MasterApi::class.java)

    @Provides @Singleton
    fun provideInstallmentApi(retrofit: Retrofit): InstallmentApi = retrofit.create(InstallmentApi::class.java)

    @Provides @Singleton
    fun provideVersionApi(retrofit: Retrofit): VersionApi = retrofit.create(VersionApi::class.java)
}
```

#### Step 7 — Repository Module (Hilt)

**CREATE:** `di/RepositoryModule.kt`

```kotlin
package com.esimko.mobile.di

import com.esimko.mobile.data.repository.*
import com.esimko.mobile.domain.repository.*
import dagger.Binds
import dagger.Module
import dagger.hilt.InstallIn
import dagger.hilt.components.SingletonComponent
import javax.inject.Singleton

@Module
@InstallIn(SingletonComponent::class)
abstract class RepositoryModule {

    @Binds @Singleton
    abstract fun bindAuthRepository(impl: AuthRepositoryImpl): AuthRepository

    @Binds @Singleton
    abstract fun bindProfileRepository(impl: ProfileRepositoryImpl): ProfileRepository

    @Binds @Singleton
    abstract fun bindTransactionRepository(impl: TransactionRepositoryImpl): TransactionRepository

    @Binds @Singleton
    abstract fun bindShoppingRepository(impl: ShoppingRepositoryImpl): ShoppingRepository

    @Binds @Singleton
    abstract fun bindNewsRepository(impl: NewsRepositoryImpl): NewsRepository
}
```

#### VERIFY-1B:
```bash
./gradlew assembleDebug
# Harus SUCCESS. Semua DI module harus resolve.
```

---

### PHASE 1C — API Interfaces & DTOs

**GOAL:** Semua Retrofit service interface + DTO sesuai spec.

#### Step 1 — Auth API + DTOs

**CREATE:** `data/remote/api/AuthApi.kt`

```kotlin
package com.esimko.mobile.data.remote.api

import com.esimko.mobile.data.remote.dto.*
import retrofit2.http.*

interface AuthApi {
    @POST("mobile/auth/login")
    suspend fun login(@Body request: LoginRequest): ApiResponse<LoginResponse>

    @POST("mobile/auth/register")
    suspend fun register(@Body request: RegisterRequest): ApiResponse<RegisterResponse>

    @POST("mobile/auth/logout")
    suspend fun logout(): ApiResponse<Unit>
}
```

**CREATE:** `data/remote/dto/AuthDto.kt`

```kotlin
package com.esimko.mobile.data.remote.dto

import com.squareup.moshi.JsonClass

@JsonClass(generateAdapter = true)
data class LoginRequest(val username: String, val password: String)

@JsonClass(generateAdapter = true)
data class LoginResponse(
    val token: String,
    val no_anggota: String,
    val nama: String,
    val avatar: String?
)

@JsonClass(generateAdapter = true)
data class RegisterRequest(
    val no_anggota: String,
    val password: String,
    val password_confirmation: String,
    val no_ktp: String,
    val telepon: String
)

@JsonClass(generateAdapter = true)
data class RegisterResponse(
    val token: String,
    val no_anggota: String,
    val nama: String
)
```

#### Step 2 — Profile API + DTOs

**CREATE:** `data/remote/api/ProfileApi.kt`

```kotlin
interface ProfileApi {
    @GET("mobile/anggota/profil")
    suspend fun getProfile(): ApiResponse<ProfileResponse>

    @POST("mobile/anggota/ubah_password")
    suspend fun changePassword(@Body request: ChangePasswordRequest): ApiResponse<Unit>

    @Multipart
    @POST("mobile/upload_avatar")
    suspend fun uploadAvatar(@Part avatar: MultipartBody.Part): ApiResponse<AvatarResponse>
}
```

**CREATE:** `data/remote/dto/ProfileDto.kt`

```kotlin
@JsonClass(generateAdapter = true)
data class ProfileResponse(
    val no_anggota: String, val nama: String, val no_ktp: String,
    val alamat: String, val telepon: String, val email: String?,
    val avatar: String?, val saldo_simpanan: Long, val saldo_pinjaman: Long,
    val angsuran_bulan: Long
)

@JsonClass(generateAdapter = true)
data class ChangePasswordRequest(val password_lama: String, val password_baru: String)

@JsonClass(generateAdapter = true)
data class AvatarResponse(val avatar_url: String)
```

#### Step 3 — Transaction API + DTOs

**CREATE:** `data/remote/api/TransactionApi.kt` (salin dari spec Section 3)
**CREATE:** `data/remote/dto/TransactionDto.kt`

#### Step 4 — Shopping API + DTOs

**CREATE:** `data/remote/api/ShoppingApi.kt` (salin dari spec Section 3)
**CREATE:** `data/remote/dto/ShoppingDto.kt`

#### Step 5 — News API + DTOs

**CREATE:** `data/remote/api/NewsApi.kt`
**CREATE:** `data/remote/dto/NewsDto.kt`

#### Step 6 — Master, Installment, Version APIs

**CREATE:** `data/remote/api/MasterApi.kt`
**CREATE:** `data/remote/api/InstallmentApi.kt`
**CREATE:** `data/remote/api/VersionApi.kt`
**CREATE:** `data/remote/dto/MasterDto.kt`, `InstallmentDto.kt`, `VersionDto.kt`

#### VERIFY-1C:
```bash
./gradlew assembleDebug
grep -r "interface.*Api" app/src/main/java/com/esimko/mobile/data/remote/api/ | wc -l
# Harus >= 8 (AuthApi, ProfileApi, MasterApi, TransactionApi, InstallmentApi, ShoppingApi, NewsApi, VersionApi)
```

---

### PHASE 1D — Domain Models & Repository Implementations

**GOAL:** Domain models (pure Kotlin), repository interfaces + implementations.

#### Step 1 — Domain Models

**CREATE semua file di `domain/model/`:**

```kotlin
// domain/model/User.kt
data class User(val noAnggota: String, val nama: String, val token: String, val avatar: String?)

// domain/model/Profile.kt
data class Profile(
    val noAnggota: String, val nama: String, val ktp: String,
    val alamat: String, val telepon: String, val email: String?,
    val avatar: String?, val saldoSimpanan: Long, val saldoPinjaman: Long,
    val angsuranBulan: Long
)

// domain/model/Transaction.kt
data class Transaction(
    val id: Long, val jenis: String, val modul: String,
    val nominal: Long, val tanggal: String, val status: Int,
    val statusLabel: String, val keterangan: String?
)

// domain/model/Product.kt
data class Product(
    val id: Long, val nama: String, val harga: Long,
    val stok: Int, val satuan: String, val kategori: String?, val foto: String?
)

// domain/model/News.kt
data class News(
    val id: Long, val judul: String, val ringkasan: String,
    val tanggal: String, val penulis: String
)

// domain/model/CartItem.kt
data class CartItem(
    val id: Long, val produkId: Long, val namaProduk: String,
    val harga: Long, val jumlah: Int, val subtotal: Long
)
```

#### Step 2 — Repository Interfaces

**CREATE di `domain/repository/`:** (sesuai spec Section 5)

- `AuthRepository.kt`
- `ProfileRepository.kt`
- `TransactionRepository.kt`
- `ShoppingRepository.kt`
- `NewsRepository.kt`

#### Step 3 — Repository Implementations

**CREATE di `data/repository/`:**

Pattern wajib untuk setiap implementation:
1. `@Singleton class XxxRepositoryImpl @Inject constructor(private val api: XxxApi, ...)`
2. Wrap API call dalam `try/catch`
3. Return `Result<T>`
4. Map DTO → Domain Model

Contoh AuthRepositoryImpl:
```kotlin
@Singleton
class AuthRepositoryImpl @Inject constructor(
    private val api: AuthApi,
    private val tokenStore: TokenStore
) : AuthRepository {

    override suspend fun login(username: String, password: String): Result<User> {
        return try {
            val response = api.login(LoginRequest(username, password))
            if (response.success && response.data != null) {
                val data = response.data
                tokenStore.saveToken(data.token)
                tokenStore.saveUser(data.no_anggota, data.nama)
                Result.Success(User(data.no_anggota, data.nama, data.token, data.avatar))
            } else {
                Result.Error(response.message ?: "Login gagal")
            }
        } catch (e: Exception) {
            Result.Error(e.message ?: "Network error")
        }
    }

    override suspend fun logout(): Result<Unit> {
        return try {
            api.logout()
            tokenStore.clearAll()
            Result.Success(Unit)
        } catch (e: Exception) {
            tokenStore.clearAll()
            Result.Success(Unit)
        }
    }

    override fun isLoggedIn(): Boolean = tokenStore.getToken() != null
}
```

#### VERIFY-1D:
```bash
./gradlew assembleDebug
```

---

### PHASE 1E — UI: Theme, Common Components & Auth Screens

**GOAL:** Material 3 theme, shared components, Login & Register screens yang fungsional.

#### Step 1 — Theme

**CREATE:** `ui/theme/Color.kt`, `ui/theme/Type.kt`, `ui/theme/Theme.kt`

```kotlin
// Color.kt
val Primary = Color(0xFF1B5E20)        // Green 900
val OnPrimary = Color.White
val PrimaryContainer = Color(0xFFA5D6A7)
val Secondary = Color(0xFF0D47A1)      // Blue 900
val OnSecondary = Color.White
val SecondaryContainer = Color(0xFF90CAF9)
val Error = Color(0xFFB00020)
val Background = Color(0xFFFFFBFE)
val Surface = Color(0xFFFFFBFE)

// Dark theme
val DarkPrimary = Color(0xFF8BC34A)
val DarkBackground = Color(0xFF1C1B1F)
val DarkSurface = Color(0xFF1C1B1F)
```

#### Step 2 — Common Components

**CREATE di `ui/common/`:**
- `EsimkoButton.kt` — primary/secondary/outlined buttons
- `EsimkoTextField.kt` — styled text field dengan validation
- `EsimkoCard.kt` — elevated card dengan shadow
- `LoadingOverlay.kt` — fullscreen loading indicator
- `ErrorView.kt` — error state dengan retry button
- `EmptyStateView.kt` — empty data illustration
- `AmountFormatter.kt` — util format "Rp 1.000.000"

#### Step 3 — UiState sealed class

**CREATE:** `ui/common/UiState.kt`

```kotlin
sealed class UiState<out T> {
    object Idle : UiState<Nothing>()
    object Loading : UiState<Nothing>()
    data class Success<T>(val data: T) : UiState<T>()
    data class Error(val message: String) : UiState<Nothing>()
}
```

#### Step 4 — Auth ViewModel

**CREATE:** `ui/auth/AuthViewModel.kt`

```kotlin
@HiltViewModel
class AuthViewModel @Inject constructor(
    private val authRepository: AuthRepository
) : ViewModel() {

    private val _loginState = MutableStateFlow<UiState<User>>(UiState.Idle)
    val loginState: StateFlow<UiState<User>> = _loginState.asStateFlow()

    fun login(username: String, password: String) {
        viewModelScope.launch {
            _loginState.value = UiState.Loading
            when (val result = authRepository.login(username, password)) {
                is Result.Success -> _loginState.value = UiState.Success(result.data)
                is Result.Error -> _loginState.value = UiState.Error(result.message)
                else -> {}
            }
        }
    }
}
```

#### Step 5 — Login Screen

**CREATE:** `ui/auth/LoginScreen.kt`

Komponen wajib:
- Logo eSIMKO di atas
- Username field (format "K 1234")
- Password field (visibility toggle)
- Login button (disabled saat loading)
- Error snackbar
- Link ke Register screen

#### Step 6 — Register Screen

**CREATE:** `ui/auth/RegisterScreen.kt`

Komponen wajib:
- No Anggota, No KTP, Telepon, Password, Confirm Password
- Inline validation
- Register button

#### VERIFY-1E:
```bash
./gradlew assembleDebug
# APK harus bisa di-install dan menampilkan LoginScreen
```

---

### PHASE 1F — Navigation, Home Dashboard & Bottom Navigation

**GOAL:** Full navigation graph, 4-tab bottom nav, dashboard screen yang menampilkan data profil.

#### Step 1 — Navigation Routes

**CREATE:** `ui/navigation/Routes.kt`

```kotlin
sealed class Screen(val route: String) {
    object Login : Screen("login")
    object Register : Screen("register")
    object Home : Screen("home")
    object SavingsDetail : Screen("savings/{id}") { fun create(id: Long) = "savings/$id" }
    object LoansList : Screen("loans")
    object ProductList : Screen("products")
    object ProductDetail : Screen("products/{id}") { fun create(id: Long) = "products/$id" }
    object Cart : Screen("cart")
    object Checkout : Screen("checkout")
    object NewsList : Screen("news")
    object NewsDetail : Screen("news/{id}") { fun create(id: Long) = "news/$id" }
    object Profile : Screen("profile")
    object ChangePassword : Screen("change_password")
}
```

#### Step 2 — Navigation Graph

**CREATE:** `ui/navigation/NavGraph.kt`

```kotlin
@Composable
fun EsimkoNavGraph(navController: NavHostController) {
    NavHost(navController, startDestination = Screen.Login.route) {
        // Auth
        composable(Screen.Login.route) { LoginScreen(navController) }
        composable(Screen.Register.route) { RegisterScreen(navController) }
        // Main (setelah login)
        composable(Screen.Home.route) { HomeScreen(navController) }
        // Detail screens
        composable(Screen.ProductDetail.route) { ... }
        composable(Screen.NewsDetail.route) { ... }
        composable(Screen.Profile.route) { ... }
        composable(Screen.ChangePassword.route) { ... }
    }
}
```

#### Step 3 — MainActivity.kt

**UPDATE:** `MainActivity.kt`

```kotlin
@AndroidEntryPoint
class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContent {
            EsimkoTheme {
                val navController = rememberNavController()
                EsimkoNavGraph(navController)
            }
        }
    }
}
```

#### Step 4 — Home Screen with Bottom Navigation

**CREATE:** `ui/home/HomeScreen.kt`

4 tabs: Dashboard, Simpanan, Belanja, Profil

```kotlin
enum class BottomNavItem(val label: String, val icon: ImageVector, val route: String) {
    HOME("Home", Icons.Default.Home, "dashboard"),
    SAVINGS("Simpanan", Icons.Default.AccountBalance, "savings"),
    SHOPPING("Belanja", Icons.Default.ShoppingCart, "shopping"),
    PROFILE("Profil", Icons.Default.Person, "profile")
}
```

#### Step 5 — Dashboard Tab

**CREATE:** `ui/home/DashboardTab.kt`

Komponen wajib:
- Greeting card ("Selamat datang, {nama}")
- Saldo simpanan card
- Saldo pinjaman card
- Angsuran bulan ini card
- Recent news carousel (3 item)

#### Step 6 — Stub screens untuk tab lain

**CREATE:** placeholder composable untuk SavingsTab, ShoppingTab, ProfileTab — cukup tampilkan nama tab + icon. Akan di-implement di phase berikutnya.

#### VERIFY-1F (FINAL):
```bash
./gradlew assembleDebug

# Manual test:
# 1. Install APK di emulator
# 2. Login dengan credentials user yang valid di server lokal
# 3. Dashboard muncul dengan data profil
# 4. Bottom navigation berfungsi (4 tab bisa diklik)
# 5. Logout berfungsi (kembali ke LoginScreen)
```

---

## 4. FILE MANIFEST (Expected output setelah Phase 1)

```
EsimkoMobile/
├── app/
│   ├── build.gradle.kts
│   └── src/main/
│       ├── AndroidManifest.xml
│       ├── res/xml/network_security_config.xml
│       └── java/com/esimko/mobile/
│           ├── EsimkoApp.kt
│           ├── MainActivity.kt
│           ├── di/
│           │   ├── NetworkModule.kt
│           │   └── RepositoryModule.kt
│           ├── data/
│           │   ├── local/TokenStore.kt
│           │   ├── remote/
│           │   │   ├── api/ (8 interfaces)
│           │   │   ├── dto/ (8+ DTO files)
│           │   │   └── interceptor/AuthInterceptor.kt
│           │   ├── repository/ (5 implementations)
│           │   └── mapper/ (optional)
│           ├── domain/
│           │   ├── model/ (6+ data classes)
│           │   └── repository/ (5 interfaces)
│           ├── ui/
│           │   ├── auth/
│           │   │   ├── AuthViewModel.kt
│           │   │   ├── LoginScreen.kt
│           │   │   └── RegisterScreen.kt
│           │   ├── home/
│           │   │   ├── HomeScreen.kt
│           │   │   ├── HomeViewModel.kt
│           │   │   ├── DashboardTab.kt
│           │   │   ├── SavingsTab.kt (stub)
│           │   │   ├── ShoppingTab.kt (stub)
│           │   │   └── ProfileTab.kt (stub)
│           │   ├── common/ (7 shared components)
│           │   ├── navigation/
│           │   │   ├── Routes.kt
│           │   │   └── NavGraph.kt
│           │   └── theme/
│           │       ├── Color.kt
│           │       ├── Type.kt
│           │       └── Theme.kt
│           └── core/
│               └── network/Result.kt
├── build.gradle.kts (project-level)
└── settings.gradle.kts
```

## 5. API ENDPOINT MAPPING (Backend ↔ Android)

| # | Backend Route | Android API Interface | Method |
|---|---|---|---|
| 1 | `POST mobile/auth/login` | `AuthApi.login()` | ✅ |
| 2 | `POST mobile/auth/register` | `AuthApi.register()` | ✅ |
| 3 | `POST mobile/auth/logout` | `AuthApi.logout()` | ✅ |
| 4 | `GET mobile/anggota/profil` | `ProfileApi.getProfile()` | ✅ |
| 5 | `POST mobile/anggota/ubah_password` | `ProfileApi.changePassword()` | ✅ |
| 6 | `POST mobile/upload_avatar` | `ProfileApi.uploadAvatar()` | ✅ |
| 7 | `GET mobile/master/jenis_transaksi/{modul}` | `MasterApi.getTransactionTypes()` | ✅ |
| 8 | `GET mobile/master/status_transaksi/{modul}` | `MasterApi.getTransactionStatuses()` | ✅ |
| 9 | `GET mobile/transaksi/{modul}` | `TransactionApi.getTransactions()` | ✅ |
| 10 | `GET mobile/transaksi/{modul}/detail` | `TransactionApi.getTransactionDetail()` | ✅ |
| 11 | `POST mobile/transaksi/{jenis}/proses` | `TransactionApi.processTransaction()` | ✅ |
| 12 | `POST mobile/transaksi/upload_bukti_transaksi` | `TransactionApi.uploadTransactionProof()` | ✅ |
| 13 | `POST mobile/transaksi/batalkan` | `TransactionApi.cancelTransaction()` | ✅ |
| 14 | `GET mobile/angsuran` | `InstallmentApi.getLoanInstallments()` | ✅ |
| 15 | `GET mobile/gaji_pokok` | `InstallmentApi.getBaseSalary()` | ✅ |
| 16 | `GET mobile/produk` | `ShoppingApi.getProducts()` | ✅ |
| 17 | `GET mobile/produk/detail` | `ShoppingApi.getProductDetail()` | ✅ |
| 18 | `GET mobile/belanja/keranjang` | `ShoppingApi.getCart()` | ✅ |
| 19 | `POST mobile/belanja/keranjang/proses` | `ShoppingApi.updateCart()` | ✅ |
| 20 | `POST mobile/belanja/keranjang/checkout` | `ShoppingApi.checkout()` | ✅ |
| 21 | `POST mobile/belanja/batalkan` | `ShoppingApi.cancelPurchase()` | ✅ |
| 22 | `GET mobile/belanja/riwayat/{jenis}` | `ShoppingApi.getPurchaseHistory()` | ✅ |
| 23 | `GET mobile/belanja/riwayat/{jenis}/detail` | `ShoppingApi.getPurchaseDetail()` | ✅ |
| 24 | `GET mobile/belanja/angsuran` | `ShoppingApi.getShoppingInstallments()` | ✅ |
| 25 | `GET mobile/belanja/retur` | `ShoppingApi.getReturns()` | ✅ |
| 26 | `GET mobile/berita` | `NewsApi.getNews()` | ✅ |
| 27 | `GET mobile/berita/detail` | `NewsApi.getNewsDetail()` | ✅ |
| 28 | `GET version` | `VersionApi.getVersion()` | ✅ |
| 29 | `GET version/check` | `VersionApi.checkVersion()` | ✅ |

## 6. SUCCESS CRITERIA

```yaml
BUILD: ./gradlew assembleDebug SUKSES tanpa error
LOGIN: User bisa login dengan no_anggota + password → masuk ke Dashboard
DASHBOARD: Menampilkan saldo simpanan, pinjaman, angsuran dari API
NAVIGATION: Bottom nav 4 tab berfungsi
LOGOUT: Tombol logout → kembali ke LoginScreen, token dihapus
SECURITY: Token tersimpan di EncryptedSharedPreferences
NETWORK: AuthInterceptor inject Bearer token ke semua protected request
ERROR: 401 → auto logout. No internet → ErrorView dengan retry.
```
