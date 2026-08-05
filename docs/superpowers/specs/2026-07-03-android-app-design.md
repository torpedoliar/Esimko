# Design Spec — Aplikasi Android eSIMKO

**Tanggal:** 2026-07-03
**Status:** Approved for implementation
**Backend:** Laravel 7.x, MySQL, API endpoints di `/api/mobile/*`
**Commit referensi:** `84a4529fa` (main)

---

## 1. Architecture Overview

**Pattern:** MVVM (Model-View-ViewModel) dengan Clean Architecture principles, single-module.

**Layer Structure:**
```
app/src/main/java/com/esimko/mobile/
├── di/                          # Hilt modules (dependency injection)
├── data/                        # Data layer
│   ├── remote/
│   │   ├── api/                 # Retrofit service interfaces
│   │   ├── dto/                 # Data Transfer Objects (Moshi)
│   │   └── interceptor/         # Auth, logging interceptors
│   ├── repository/              # Repository implementations
│   ├── local/                   # TokenStore, Preferences
│   └── mapper/                  # DTO → Domain mappers
├── domain/                      # Domain layer
│   ├── model/                   # Business models (pure Kotlin)
│   ├── repository/              # Repository interfaces
│   └── usecase/                 # Business logic (optional)
├── ui/                          # Presentation layer
│   ├── auth/                    # Login, Register, BiometricSetup
│   ├── home/                    # Dashboard, BottomNav
│   ├── savings/                 # Simpanan screens
│   ├── loans/                   # Pinjaman & angsuran
│   ├── shopping/                # POS & belanja
│   ├── news/                    # Berita
│   ├── profile/                 # Profile & settings
│   ├── common/                  # Shared UI components
│   └── theme/                   # Material 3 theme
├── core/                        # App-wide utilities
│   ├── network/                 # NetworkMonitor, error handling
│   ├── security/                # BiometricManager, encryption
│   └── util/                    # Extensions, helpers
├── EsimkoApp.kt                 # Application class (@HiltAndroidApp)
└── MainActivity.kt              # Single activity, Compose navigation
```

**Data Flow:**
```
UI (Activity/Fragment/Composable)
  ↓ observes StateFlow
ViewModel (StateFlow<UiState>)
  ↓ calls
Repository (interface di domain)
  ↓ implements
Repository Impl (data layer)
  ↓ calls
API Service (Retrofit)
  ↓ returns
DTO → Mapper → Domain Model
  ↓ emits
StateFlow<UiState> → UI
```

**Key Principles:**
- ViewModel hanya expose `StateFlow<UiState>` (immutable state)
- UI observe state & render, tidak ada business logic di Activity/Fragment
- Repository interface di domain, implementasi di data (dependency inversion)
- UseCase optional — hanya untuk business logic complex
- Error handling centralized di `Result<T>` wrapper

---

## 2. Authentication & Security Flow

**Login Flow:**
```
App Start
  ↓
TokenCheck (EncryptedSharedPreferences)
  ├─ Has token → BiometricPrompt (if enabled)
  │   ├─ Success → HomeScreen
  │   └─ Fail/Fallback → LoginScreen
  └─ No token → LoginScreen
        ↓
  LoginScreen → POST /api/mobile/auth/login
        ↓
  Success → Store token (EncryptedSharedPreferences)
        ↓
  HomeScreen
```

**Token Management:**
- Token disimpan di `EncryptedSharedPreferences` (AES-256, Android Keystore)
- `AuthInterceptor` (OkHttp) otomatis inject `Authorization: Bearer <token>` ke setiap request
- Jika server return 401 → token dihapus → redirect ke LoginScreen
- `AuthManager` sebagai single source of truth untuk auth state

**Biometric Auth:**
- `BiometricManager` wrapper untuk `BiometricPrompt` API
- Login pertama: password → token disimpan → opsi aktifkan biometric
- Login berikutnya: biometric unlock → token sudah ada
- Fallback: jika biometric gagal → tetap bisa login manual (password)
- Biometric key di-encrypt dengan `AndroidKeyStore`

**Auth State:**
```kotlin
sealed class AuthState {
    object Loading : AuthState()
    object Unauthenticated : AuthState()
    data class Authenticated(val noAnggota: String, val nama: String) : AuthState()
}
```

**Security:**
- HTTPS only (certificate pinning optional untuk production)
- Token auto-refresh: jika 401, coba re-login silent. Jika gagal → logout
- Sensitive data (token, password) tidak di-log
- Network security config: allow cleartext HTTP untuk localhost/IP lokal (development only)

**Development Network Config:**
```xml
<!-- res/xml/network_security_config.xml -->
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

---

## 3. API Layer & Networking

**API Service Interfaces (Retrofit):**

```kotlin
// Auth
interface AuthApi {
    @POST("mobile/auth/login")
    suspend fun login(@Body request: LoginRequest): ApiResponse<LoginResponse>

    @POST("mobile/auth/register")
    suspend fun register(@Body request: RegisterRequest): ApiResponse<RegisterResponse>

    @POST("mobile/auth/logout")
    suspend fun logout(): ApiResponse<Unit>
}

// Profile
interface ProfileApi {
    @GET("mobile/anggota/profil")
    suspend fun getProfile(): ApiResponse<ProfileResponse>

    @POST("mobile/anggota/ubah_password")
    suspend fun changePassword(@Body request: ChangePasswordRequest): ApiResponse<Unit>

    @Multipart
    @POST("mobile/upload_avatar")
    suspend fun uploadAvatar(@Part avatar: MultipartBody.Part): ApiResponse<AvatarResponse>
}

// Master Data
interface MasterApi {
    @GET("mobile/master/jenis_transaksi/{modul}")
    suspend fun getTransactionTypes(@Path("modul") modul: String): ApiResponse<List<TransactionTypeResponse>>

    @GET("mobile/master/status_transaksi/{modul}")
    suspend fun getTransactionStatuses(@Path("modul") modul: String): ApiResponse<List<TransactionStatusResponse>>
}

// Transactions (simpanan & pinjaman)
interface TransactionApi {
    @GET("mobile/transaksi/{modul}")
    suspend fun getTransactions(
        @Path("modul") modul: String,
        @Query("jenis") jenis: Int? = null,
        @Query("status") status: Int? = null,
        @Query("tanggal_mulai") tanggalMulai: String? = null,
        @Query("tanggal_akhir") tanggalAkhir: String? = null,
        @Query("page") page: Int? = null,
        @Query("per_page") perPage: Int? = null
    ): ApiResponse<List<TransactionResponse>>

    @GET("mobile/transaksi/{modul}/detail")
    suspend fun getTransactionDetail(
        @Path("modul") modul: String,
        @Query("id") id: Long
    ): ApiResponse<TransactionDetailResponse>

    @POST("mobile/transaksi/{jenis}/proses")
    suspend fun processTransaction(
        @Path("jenis") jenis: String,
        @Body request: TransactionRequest
    ): ApiResponse<TransactionResponse>

    @Multipart
    @POST("mobile/transaksi/upload_bukti_transaksi")
    suspend fun uploadTransactionProof(
        @Part("id") id: RequestBody,
        @Part bukti: MultipartBody.Part
    ): ApiResponse<Unit>

    @POST("mobile/transaksi/batalkan")
    suspend fun cancelTransaction(@Body request: CancelRequest): ApiResponse<Unit>
}

// Installments & Salary
interface InstallmentApi {
    @GET("mobile/angsuran")
    suspend fun getLoanInstallments(): ApiResponse<List<InstallmentResponse>>

    @GET("mobile/gaji_pokok")
    suspend fun getBaseSalary(): ApiResponse<SalaryResponse>
}

// Shopping / POS
interface ShoppingApi {
    @GET("mobile/produk")
    suspend fun getProducts(@Query("page") page: Int, @Query("per_page") perPage: Int = 20): ApiResponse<List<ProductResponse>>

    @GET("mobile/produk/detail")
    suspend fun getProductDetail(@Query("id") id: Long): ApiResponse<ProductDetailResponse>

    @GET("mobile/belanja/keranjang")
    suspend fun getCart(): ApiResponse<CartResponse>

    @POST("mobile/belanja/keranjang/proses")
    suspend fun updateCart(@Body request: CartRequest): ApiResponse<CartResponse>

    @POST("mobile/belanja/keranjang/checkout")
    suspend fun checkout(@Body request: CheckoutRequest): ApiResponse<CheckoutResponse>

    @POST("mobile/belanja/batalkan")
    suspend fun cancelPurchase(@Body request: CancelPurchaseRequest): ApiResponse<Unit>

    @GET("mobile/belanja/riwayat/{jenis}")
    suspend fun getPurchaseHistory(
        @Path("jenis") jenis: String,
        @Query("page") page: Int? = null,
        @Query("per_page") perPage: Int? = null
    ): ApiResponse<List<PurchaseResponse>>

    @GET("mobile/belanja/riwayat/{jenis}/detail")
    suspend fun getPurchaseDetail(
        @Path("jenis") jenis: String,
        @Query("id") id: Long
    ): ApiResponse<PurchaseDetailResponse>

    @GET("mobile/belanja/angsuran")
    suspend fun getShoppingInstallments(@Query("id") id: Long? = null): ApiResponse<List<ShoppingInstallmentResponse>>

    @GET("mobile/belanja/retur")
    suspend fun getReturns(
        @Query("search") search: String? = null,
        @Query("page") page: Int? = null,
        @Query("per_page") perPage: Int? = null
    ): ApiResponse<List<ReturnResponse>>
}

// News
interface NewsApi {
    @GET("mobile/berita")
    suspend fun getNews(
        @Query("search") search: String? = null,
        @Query("page") page: Int? = null,
        @Query("per_page") perPage: Int? = null
    ): ApiResponse<List<NewsResponse>>

    @GET("mobile/berita/detail")
    suspend fun getNewsDetail(@Query("id") id: Long): ApiResponse<NewsDetailResponse>
}

// Version
interface VersionApi {
    @GET("version")
    suspend fun getVersion(): ApiResponse<VersionResponse>

    @GET("version/check")
    suspend fun checkVersion(@Query("version") version: String): ApiResponse<VersionCheckResponse>
}
```

**Response Envelope (Moshi):**
```kotlin
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

**Interceptor Chain:**
```
Request → AuthInterceptor (inject Bearer token)
       → LoggingInterceptor (debug only, filter sensitive data)
       → NetworkInterceptor (cache policy)
       → Server

Response → ErrorInterceptor (401 → auto logout, 500 → global error)
        → Client
```

**Error Handling:**
```kotlin
sealed class ApiError {
    data class Network(val message: String) : ApiError()
    data class Http(val code: Int, val message: String) : ApiError()
    data class Server(val message: String) : ApiError()
    data class Auth(val message: String) : ApiError()
    object Timeout : ApiError()
    object Unknown : ApiError()
}

sealed class Result<out T> {
    data class Success<T>(val data: T) : Result<T>()
    data class Error(val error: ApiError) : Result<Nothing>()
    object Loading : Result<Nothing>()
}
```

**Base URL Configuration (BuildConfig):**
- `debug`: `http://10.0.2.2:8000/api/`
- `release`: `https://esimko.com/api/`

---

## 4. Navigation & Screen Structure

**Navigation Graph:**
```
NavHost (MainActivity)
├── auth_graph (start destination)
│   ├── LoginScreen
│   ├── RegisterScreen
│   └── BiometricSetupScreen
│
├── main_graph (after auth)
│   ├── HomeScreen (bottom nav host)
│   │   ├── Tab: Dashboard
│   │   ├── Tab: Simpanan
│   │   ├── Tab: Belanja
│   │   └── Tab: Profil
│   │
│   ├── savings_detail_graph
│   │   ├── SavingsDetailScreen
│   │   └── TransactionFormScreen (setoran/penarikan)
│   │
│   ├── loans_graph
│   │   ├── LoansListScreen
│   │   ├── LoanDetailScreen
│   │   ├── LoanApplicationScreen
│   │   └── InstallmentScreen
│   │
│   ├── shopping_graph
│   │   ├── ProductListScreen
│   │   ├── ProductDetailScreen
│   │   ├── CartScreen
│   │   ├── CheckoutScreen
│   │   ├── PurchaseHistoryScreen
│   │   ├── PurchaseDetailScreen
│   │   └── ReturnScreen
│   │
│   ├── news_graph
│   │   ├── NewsListScreen
│   │   └── NewsDetailScreen
│   │
│   └── profile_graph
│       ├── ProfileScreen
│       ├── ChangePasswordScreen
│       ├── SettingsScreen
│       └── AboutScreen
│
└── admin_graph (role-based, phase 2)
    ├── AdminDashboardScreen
    ├── MemberManagementScreen
    └── ...
```

**Bottom Navigation (4 tabs):**
```
┌─────────────────────────────────────┐
│         Content Area                │
├────────┬────────┬────────┬──────────┤
│ 🏠     │ 💰     │ 🛒     │ 👤       │
│ Home   │ Simpan │ Belanja│ Profil   │
└────────┴────────┴────────┴──────────┘
```

**Role-Based Access:**
```kotlin
enum class UserRole { ANGGOTA, PENGURUS, ADMIN }
```

**Deep Link Support:**
```kotlin
deepLink { uriPattern = "esimko://news/{newsId}" }
```

**Screen Count:** ~22 screens (phase 1)

---

## 5. Data Models & Domain Layer

**Domain Models:**
```kotlin
// Auth & User
data class User(val noAnggota: String, val nama: String, val token: String, val avatar: String?, val role: UserRole)
data class Profile(val noAnggota: String, val nama: String, val ktp: String, val alamat: String, val telepon: String, val email: String?, val avatar: String?, val saldoSimpanan: Long, val saldoPinjaman: Long, val angsuranBulan: Long)

// Simpan-Pinjam
data class Transaction(val id: Long, val jenis: TransactionType, val modul: TransactionModule, val nominal: Long, val tanggal: LocalDate, val status: TransactionStatus, val statusLabel: String, val keterangan: String?, val sisaAngsuran: Int?, val tenor: Int?)
data class TransactionDetail(val id: Long, val jenis: TransactionType, val nominal: Long, val tanggal: LocalDate, val status: TransactionStatus, val statusLabel: String, val keterangan: String?, val buktiTransaksi: String?, val items: List<TransactionItem>?)
data class Installment(val id: Long, val ke: Int, val nominal: Long, val bunga: Long, val pokok: Long, val tanggalJatuhTempo: LocalDate, val tanggalBayar: LocalDate?, val status: String)

// Belanja
data class Product(val id: Long, val nama: String, val harga: Long, val stok: Int, val satuan: String, val kategori: String?, val foto: String?, val barcode: String?)
data class CartItem(val id: Long, val produkId: Long, val namaProduk: String, val harga: Long, val jumlah: Int, val subtotal: Long)
data class PurchaseHistory(val id: Long, val jenis: PurchaseType, val tanggal: LocalDate, val total: Long, val status: String, val itemCount: Int)

// Berita
data class News(val id: Long, val judul: String, val ringkasan: String, val tanggal: LocalDate, val penulis: String)
data class NewsDetail(val id: Long, val judul: String, val konten: String, val tanggal: LocalDate, val penulis: String, val attachments: List<Attachment>)
data class Attachment(val id: Long, val nama: String, val url: String, val tipe: String)

// Enums
enum class TransactionModule { SIMPANAN, PINJAMAN }
enum class TransactionType { SETORAN, PENARIKAN, PINJAMAN }
enum class TransactionStatus(val code: Int) { AKTIF(1), PENDING(2), PROSES(3), LUNAS(4), BATAL(5) }
enum class PurchaseType { TOKO, KONSINYASI, ONLINE }
enum class UserRole { ANGGOTA, PENGURUS, ADMIN }
```

**Repository Interfaces:**
```kotlin
interface AuthRepository {
    suspend fun login(username: String, password: String): Result<User>
    suspend fun register(request: RegisterRequest): Result<User>
    suspend fun logout(): Result<Unit>
    fun isLoggedIn(): Flow<Boolean>
    fun getUser(): Flow<User?>
}

interface ProfileRepository {
    suspend fun getProfile(): Result<Profile>
    suspend fun changePassword(lama: String, baru: String): Result<Unit>
    suspend fun uploadAvatar(file: File): Result<String>
}

interface TransactionRepository {
    suspend fun getTransactions(modul: String, filter: TransactionFilter): Result<List<Transaction>>
    suspend fun getTransactionDetail(modul: String, id: Long): Result<TransactionDetail>
    suspend fun processTransaction(jenis: String, request: TransactionRequest): Result<Transaction>
    suspend fun uploadProof(id: Long, file: File): Result<Unit>
    suspend fun cancelTransaction(id: Long): Result<Unit>
    suspend fun getInstallments(): Result<List<Installment>>
    suspend fun getBaseSalary(): Result<Long>
}

interface ShoppingRepository {
    suspend fun getProducts(page: Int): Result<List<Product>>
    suspend fun getProductDetail(id: Long): Result<Product>
    suspend fun getCart(): Result<List<CartItem>>
    suspend fun updateCart(request: CartRequest): Result<List<CartItem>>
    suspend fun checkout(items: List<CheckoutItem>): Result<CheckoutResult>
    suspend fun getPurchaseHistory(jenis: String?, page: Int): Result<List<PurchaseHistory>>
    suspend fun getPurchaseDetail(jenis: String, id: Long): Result<PurchaseHistory>
    suspend fun getShoppingInstallments(id: Long?): Result<List<Installment>>
    suspend fun getReturns(page: Int): Result<List<ReturnItem>>
    suspend fun cancelPurchase(id: Long, jenis: String): Result<Unit>
}

interface NewsRepository {
    suspend fun getNews(page: Int, search: String?): Result<List<News>>
    suspend fun getNewsDetail(id: Long): Result<NewsDetail>
}

interface VersionRepository {
    suspend fun getVersion(): Result<VersionInfo>
    suspend fun checkVersion(currentVersion: String): Result<VersionCheck>
}
```

---

## 6. UI/UX Design System

**Design Foundation:** Material Design 3 + Koperasi Branding

**Color System:**
```kotlin
val Primary = Color(0xFF1B5E20)          // Green 900
val OnPrimary = Color.White
val PrimaryContainer = Color(0xFFA5D6A7) // Green 200
val Secondary = Color(0xFF0D47A1)        // Blue 900
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

**Typography:** MD3 Type Scale (display, headline, title, body, label)

**Shape System:** RoundedCornerShape (4dp, 8dp, 12dp, 16dp, 24dp)

**Component Library:**
- EsimkoCard, EsimkoButton, EsimkoTextField
- EsimkoTopBar, EsimkoBottomNav, EsimkoListItem
- EsimkoChip, EsimkoDialog, EsimkoSnackbar
- LoadingOverlay, ErrorView, EmptyStateView
- AmountFormatter (Rupiah: Rp 1.000.000)

**Theme:** Dynamic color (Android 12+) with fallback, dark mode support

**Accessibility:**
- Minimum touch target: 48dp
- Content description untuk semua icon
- Contrast ratio minimal 4.5:1 (WCAG AA)
- Screen reader support
- Font scaling support (sp, bukan dp)

**UI/UX Design Tools:** taste-skill dan ui-ux-pro-max untuk panduan desain visual

---

## 7. Error Handling & Edge Cases

**Global Error Handling:**
- Network layer: `NetworkResult<T>` wrapper
- Repository level: `Result<T>` + exception mapping
- ViewModel level: `UiState` sealed class (Loading, Success, Error)
- UI level: ErrorView, Snackbar, inline validation

**Edge Cases:**

| Edge Case | Handling |
|-----------|----------|
| Token expired (401) | Auto-logout, redirect ke LoginScreen |
| No internet | ErrorView dengan retry button |
| Server error (500) | ErrorView "Server error, coba lagi nanti" |
| Empty data | EmptyStateView dengan illustration |
| Pagination end | Disable "Load more", show "Tidak ada data lagi" |
| Form validation | Inline error di TextField |
| Double submit | Disable button saat loading |
| Image upload fail | Retry dialog, fallback default avatar |
| Biometric fail | Fallback ke password login |
| Deep link invalid | Redirect ke HomeScreen |
| Version force update | Block app, dialog "Update diperlukan" |

**401 Auto-Logout:**
```kotlin
class AuthInterceptor : Interceptor {
    override fun intercept(chain: Interceptor.Chain): Response {
        val request = chain.request().newBuilder()
            .addHeader("Authorization", "Bearer ${authManager.getToken()}")
            .build()
        val response = chain.proceed(request)
        if (response.code == 401) {
            authManager.logout()
            EventBus.emit(NavigateToLogin)
        }
        return response
    }
}
```

**Connectivity Monitoring:**
```kotlin
class NetworkMonitor {
    val isConnected: Flow<Boolean> = callbackFlow { ... }
}
```

---

## 8. Testing Strategy

**Testing Pyramid:**
- Unit Tests (80%): ViewModel, Mapper, UseCase, Helper
- Integration Tests (15%): Repository + Fake API
- UI Tests (5%): Espresso/Compose Test, critical flows only

**Tools:** JUnit 5, Mockk, Turbine (Flow testing), Google Truth, Espresso

**Coverage Target:**
| Layer | Coverage |
|-------|----------|
| ViewModel | 90%+ |
| Mapper | 100% |
| Repository | 80%+ |
| UseCase | 90%+ |
| UI (Compose) | Critical paths only |

**Test Execution:**
```bash
./gradlew testDebugUnitTest          # Unit tests
./gradlew connectedDebugAndroidTest  # UI tests
./gradlew jacocoTestReport           # Coverage report
```

---

## 9. Project Setup & Dependencies

**Project Configuration:**
- `minSdk = 28` (Android 9.0)
- `targetSdk = 34`
- `compileSdk = 34`
- Jetpack Compose UI (not XML)
- Single Activity architecture

**Key Dependencies:**
| Category | Library |
|----------|---------|
| UI | Jetpack Compose + Material 3 |
| Navigation | Navigation Compose |
| DI | Hilt |
| Networking | Retrofit + OkHttp + Moshi |
| Image Loading | Coil (Compose) |
| Auth | Biometric + EncryptedSharedPreferences |
| Storage | DataStore Preferences |
| Async | Kotlin Coroutines + Flow |
| Testing | JUnit, Mockk, Turbine, Truth, Espresso |

**Build Variants:**
- `debug` → local API (10.0.2.2:8000), logging enabled
- `release` → production API (esimko.com), minified, ProGuard

**Minimum Requirements:**
- Android Studio Hedgehog (2023.1.1)+
- JDK 17
- Kotlin 1.9.22
- Gradle 8.4+

---

## Endpoint Coverage (PRD → Spec)

| Kategori | Endpoint PRD | Status |
|----------|-------------|--------|
| Auth | login, register, logout | ✅ |
| Profile | profil, ubah_password, upload_avatar | ✅ |
| Master | jenis_transaksi, status_transaksi | ✅ |
| Transaksi | list, detail, proses, upload_bukti, batalkan | ✅ |
| Angsuran | angsuran, gaji_pokok | ✅ |
| Belanja | produk, detail, keranjang, checkout, riwayat, retur, angsuran, batalkan | ✅ |
| Berita | list, detail | ✅ |
| Version | version, check | ✅ |
