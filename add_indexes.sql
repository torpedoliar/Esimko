-- ============================================
-- PERFORMANCE OPTIMIZATION: DATABASE INDEXES
-- ============================================

-- TRANSAKSI TABLE (Most Critical)
ALTER TABLE transaksi ADD INDEX idx_transaksi_anggota (fid_anggota);
ALTER TABLE transaksi ADD INDEX idx_transaksi_status (fid_status);
ALTER TABLE transaksi ADD INDEX idx_transaksi_jenis (fid_jenis_transaksi);
ALTER TABLE transaksi ADD INDEX idx_transaksi_tanggal (tanggal);
ALTER TABLE transaksi ADD INDEX idx_transaksi_combo (fid_anggota, fid_status, fid_jenis_transaksi);

-- ANGSURAN TABLE
ALTER TABLE angsuran ADD INDEX idx_angsuran_transaksi (fid_transaksi);
ALTER TABLE angsuran ADD INDEX idx_angsuran_status (fid_status);
ALTER TABLE angsuran ADD INDEX idx_angsuran_payroll (fid_payroll);

-- PENJUALAN TABLE
ALTER TABLE penjualan ADD INDEX idx_penjualan_anggota (fid_anggota);
ALTER TABLE penjualan ADD INDEX idx_penjualan_metode (fid_metode_pembayaran);
ALTER TABLE penjualan ADD INDEX idx_penjualan_jenis (jenis_belanja);
ALTER TABLE penjualan ADD INDEX idx_penjualan_created (created_at);

-- ANGSURAN_BELANJA TABLE
ALTER TABLE angsuran_belanja ADD INDEX idx_ab_penjualan (fid_penjualan);
ALTER TABLE angsuran_belanja ADD INDEX idx_ab_status (fid_status);
ALTER TABLE angsuran_belanja ADD INDEX idx_ab_payroll (fid_payroll);

-- ANGGOTA TABLE
ALTER TABLE anggota ADD INDEX idx_anggota_status (fid_status);

-- OTORITAS_USER TABLE
ALTER TABLE otoritas_user ADD INDEX idx_ou_akses_modul (fid_hak_akses, fid_modul);

-- Update statistics
ANALYZE TABLE transaksi, angsuran, penjualan, angsuran_belanja, anggota, otoritas_user;
