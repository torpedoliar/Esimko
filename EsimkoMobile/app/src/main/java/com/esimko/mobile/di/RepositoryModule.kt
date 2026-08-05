package com.esimko.mobile.di

import com.esimko.mobile.data.repository.AuthRepositoryImpl
import com.esimko.mobile.data.repository.ProfileRepositoryImpl
import com.esimko.mobile.data.repository.TransactionRepositoryImpl
import com.esimko.mobile.data.repository.InstallmentRepositoryImpl
import com.esimko.mobile.data.repository.ShoppingRepositoryImpl
import com.esimko.mobile.data.repository.NewsRepositoryImpl
import com.esimko.mobile.data.repository.MasterRepositoryImpl
import com.esimko.mobile.data.repository.VersionRepositoryImpl
import com.esimko.mobile.data.repository.TransactionHistoryRepositoryImpl
import com.esimko.mobile.domain.repository.AuthRepository
import com.esimko.mobile.domain.repository.ProfileRepository
import com.esimko.mobile.domain.repository.TransactionRepository
import com.esimko.mobile.domain.repository.InstallmentRepository
import com.esimko.mobile.domain.repository.ShoppingRepository
import com.esimko.mobile.domain.repository.NewsRepository
import com.esimko.mobile.domain.repository.MasterRepository
import com.esimko.mobile.domain.repository.VersionRepository
import com.esimko.mobile.domain.repository.TransactionHistoryRepository
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
    abstract fun bindInstallmentRepository(impl: InstallmentRepositoryImpl): InstallmentRepository

    @Binds @Singleton
    abstract fun bindShoppingRepository(impl: ShoppingRepositoryImpl): ShoppingRepository

    @Binds @Singleton
    abstract fun bindNewsRepository(impl: NewsRepositoryImpl): NewsRepository

    @Binds @Singleton
    abstract fun bindMasterRepository(impl: MasterRepositoryImpl): MasterRepository

    @Binds @Singleton
    abstract fun bindVersionRepository(impl: VersionRepositoryImpl): VersionRepository

    @Binds @Singleton
    abstract fun bindTransactionHistoryRepository(impl: TransactionHistoryRepositoryImpl): TransactionHistoryRepository
}
