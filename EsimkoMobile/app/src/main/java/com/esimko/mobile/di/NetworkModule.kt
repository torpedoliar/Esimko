package com.esimko.mobile.di

import android.content.Context
import com.esimko.mobile.BuildConfig
import com.esimko.mobile.data.local.TokenStore
import com.esimko.mobile.data.remote.interceptor.AuthInterceptor
import com.squareup.moshi.Moshi
import com.squareup.moshi.kotlin.reflect.KotlinJsonAdapterFactory
import dagger.Module
import dagger.Provides
import dagger.hilt.InstallIn
import dagger.hilt.android.qualifiers.ApplicationContext
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
    fun provideTokenStore(@ApplicationContext context: Context): TokenStore = TokenStore(context)

    @Provides @Singleton
    fun provideAuthInterceptor(tokenStore: TokenStore): AuthInterceptor = AuthInterceptor(tokenStore)

    @Provides @Singleton
    fun provideOkHttpClient(authInterceptor: AuthInterceptor): OkHttpClient {
        val builder = OkHttpClient.Builder()
            .addInterceptor(authInterceptor)
            .connectTimeout(30, TimeUnit.SECONDS)
            .readTimeout(30, TimeUnit.SECONDS)

        if (BuildConfig.DEBUG) {
            // BASIC never logs credentials, JSON bodies, or buffers a streaming PDF.
            // Receipt requests bypass logging entirely, including failures with JSON bodies.
            val logger = HttpLoggingInterceptor().setLevel(HttpLoggingInterceptor.Level.BASIC)
            builder.addInterceptor { chain ->
                if (chain.request().url.encodedPath.contains("/mobile/bukti/")) {
                    chain.proceed(chain.request())
                } else logger.intercept(chain)
            }
        }
        return builder.build()
    }

    @Provides @Singleton
    fun provideMoshi(): Moshi = Moshi.Builder()
        .add(KotlinJsonAdapterFactory())
        .build()

    @Provides @Singleton
    fun provideRetrofit(okHttpClient: OkHttpClient, moshi: Moshi): Retrofit =
        Retrofit.Builder()
            .baseUrl(BuildConfig.BASE_URL)
            .client(okHttpClient)
            .addConverterFactory(MoshiConverterFactory.create(moshi))
            .build()

    @Provides @Singleton
    fun provideAuthApi(retrofit: Retrofit): com.esimko.mobile.data.remote.api.AuthApi =
        retrofit.create(com.esimko.mobile.data.remote.api.AuthApi::class.java)

    @Provides @Singleton
    fun provideProfileApi(retrofit: Retrofit): com.esimko.mobile.data.remote.api.ProfileApi =
        retrofit.create(com.esimko.mobile.data.remote.api.ProfileApi::class.java)

    @Provides @Singleton
    fun provideTransactionApi(retrofit: Retrofit): com.esimko.mobile.data.remote.api.TransactionApi =
        retrofit.create(com.esimko.mobile.data.remote.api.TransactionApi::class.java)

    @Provides @Singleton
    fun provideShoppingApi(retrofit: Retrofit): com.esimko.mobile.data.remote.api.ShoppingApi =
        retrofit.create(com.esimko.mobile.data.remote.api.ShoppingApi::class.java)

    @Provides @Singleton
    fun provideNewsApi(retrofit: Retrofit): com.esimko.mobile.data.remote.api.NewsApi =
        retrofit.create(com.esimko.mobile.data.remote.api.NewsApi::class.java)

    @Provides @Singleton
    fun provideMasterApi(retrofit: Retrofit): com.esimko.mobile.data.remote.api.MasterApi =
        retrofit.create(com.esimko.mobile.data.remote.api.MasterApi::class.java)

    @Provides @Singleton
    fun provideInstallmentApi(retrofit: Retrofit): com.esimko.mobile.data.remote.api.InstallmentApi =
        retrofit.create(com.esimko.mobile.data.remote.api.InstallmentApi::class.java)

    @Provides @Singleton
    fun provideVersionApi(retrofit: Retrofit): com.esimko.mobile.data.remote.api.VersionApi =
        retrofit.create(com.esimko.mobile.data.remote.api.VersionApi::class.java)

    @Provides @Singleton
    fun provideTransactionHistoryApi(retrofit: Retrofit): com.esimko.mobile.data.remote.api.TransactionHistoryApi =
        retrofit.create(com.esimko.mobile.data.remote.api.TransactionHistoryApi::class.java)

    @Provides @Singleton
    fun provideReceiptApi(retrofit: Retrofit): com.esimko.mobile.data.remote.api.ReceiptApi =
        retrofit.create(com.esimko.mobile.data.remote.api.ReceiptApi::class.java)

    @Provides @Singleton
    fun provideDeviceApi(retrofit: Retrofit): com.esimko.mobile.data.remote.api.DeviceApi =
        retrofit.create(com.esimko.mobile.data.remote.api.DeviceApi::class.java)
}
