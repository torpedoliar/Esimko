-- ============================================
-- PERFORMANCE OPTIMIZATION: STOCK INDEXES
-- Run this on MySQL to improve laporan_stock performance
-- ============================================

-- Item Pembelian (for stock queries)
ALTER TABLE item_pembelian ADD INDEX idx_ip_produk (fid_produk);
ALTER TABLE item_pembelian ADD INDEX idx_ip_pembelian (fid_pembelian);

-- Item Penjualan (for stock queries)
ALTER TABLE item_penjualan ADD INDEX idx_ipj_produk (fid_produk);

-- Item Retur Pembelian
ALTER TABLE item_retur_pembelian ADD INDEX idx_irp_produk (fid_produk);

-- Item Retur Penjualan
ALTER TABLE item_retur_penjualan ADD INDEX idx_irpj_produk (fid_produk);

-- Stok Opname
ALTER TABLE stok_opname ADD INDEX idx_so_produk (fid_produk);
ALTER TABLE stok_opname ADD INDEX idx_so_tanggal (tanggal);

-- Pembelian (for date filtering)
ALTER TABLE pembelian ADD INDEX idx_pembelian_tanggal (tanggal);
ALTER TABLE pembelian ADD INDEX idx_pembelian_status (status);

-- Penjualan (for date filtering)
ALTER TABLE penjualan ADD INDEX idx_penjualan_tanggal_status (tanggal, fid_status);

-- Retur Pembelian
ALTER TABLE retur_pembelian ADD INDEX idx_retur_pembelian_tanggal (tanggal);

-- Retur Penjualan
ALTER TABLE retur_penjualan ADD INDEX idx_retur_penjualan_tanggal (tanggal);

-- Update statistics
ANALYZE TABLE item_pembelian, item_penjualan, item_retur_pembelian, item_retur_penjualan, stok_opname, pembelian, penjualan;

