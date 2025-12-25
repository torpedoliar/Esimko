<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Display | eSIMKO</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            color: #fff;
            overflow: hidden;
        }
        
        .header {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
        }
        
        .logo h1 {
            font-size: 28px;
            font-weight: 700;
            color: #00d9ff;
        }
        
        .logo span {
            font-size: 14px;
            opacity: 0.7;
        }
        
        .datetime {
            text-align: right;
        }
        
        .datetime .time {
            font-size: 36px;
            font-weight: 600;
            color: #00d9ff;
        }
        
        .datetime .date {
            font-size: 16px;
            opacity: 0.7;
        }
        
        .main-content {
            display: flex;
            height: calc(100vh - 110px);
        }
        
        .items-section {
            flex: 1;
            padding: 30px 40px;
            overflow-y: auto;
        }
        
        .items-header {
            display: flex;
            background: rgba(255,255,255,0.1);
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .items-header .col-name { flex: 2; }
        .items-header .col-qty { flex: 0.5; text-align: center; }
        .items-header .col-price { flex: 1; text-align: right; }
        .items-header .col-subtotal { flex: 1; text-align: right; }
        
        .items-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .item-row {
            display: flex;
            background: rgba(255,255,255,0.05);
            padding: 20px;
            border-radius: 10px;
            align-items: center;
            animation: slideIn 0.3s ease;
            border-left: 4px solid #00d9ff;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .item-row .col-name {
            flex: 2;
        }
        
        .item-row .col-name h3 {
            font-size: 18px;
            font-weight: 500;
        }
        
        .item-row .col-name small {
            opacity: 0.6;
        }
        
        .item-row .col-qty {
            flex: 0.5;
            text-align: center;
            font-size: 20px;
            font-weight: 600;
            color: #00d9ff;
        }
        
        .item-row .col-price,
        .item-row .col-subtotal {
            flex: 1;
            text-align: right;
            font-size: 18px;
        }
        
        .item-row .col-subtotal {
            font-weight: 600;
            color: #4ade80;
        }
        
        .summary-section {
            width: 400px;
            background: rgba(255,255,255,0.05);
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            border-left: 1px solid rgba(255,255,255,0.1);
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 18px;
        }
        
        .summary-item.total {
            border-top: 2px solid rgba(255,255,255,0.2);
            padding-top: 30px;
            margin-top: 20px;
        }
        
        .summary-item.total .label {
            font-size: 24px;
        }
        
        .summary-item.total .value {
            font-size: 48px;
            font-weight: 700;
            color: #4ade80;
            text-shadow: 0 0 30px rgba(74, 222, 128, 0.5);
        }
        
        .customer-name {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            background: rgba(0,217,255,0.1);
            border-radius: 10px;
            border: 1px solid rgba(0,217,255,0.3);
        }
        
        .customer-name small {
            opacity: 0.7;
            display: block;
            margin-bottom: 5px;
        }
        
        .customer-name h2 {
            color: #00d9ff;
            font-size: 24px;
        }
        
        .no-items {
            text-align: center;
            padding: 100px 20px;
            opacity: 0.5;
        }
        
        .no-items h2 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .welcome-message {
            text-align: center;
            padding: 50px;
        }
        
        .welcome-message h1 {
            font-size: 48px;
            color: #00d9ff;
            margin-bottom: 20px;
        }
        
        .welcome-message p {
            font-size: 24px;
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" onerror="this.style.display='none'">
            <div>
                <h1>eSIMKO</h1>
                <span>Koperasi Karyawan Satya Sejahtera</span>
            </div>
        </div>
        <div class="datetime">
            <div class="time" id="clock">--:--:--</div>
            <div class="date" id="date">--</div>
        </div>
    </div>
    
    <div class="main-content">
        <div class="items-section">
            @if(!$penjualan || count($items) == 0)
                <div class="no-items">
                    <div class="welcome-message">
                        <h1>Selamat Datang</h1>
                        <p>Silakan scan produk untuk memulai transaksi</p>
                    </div>
                </div>
            @else
                <div class="items-header">
                    <div class="col-name">Nama Produk</div>
                    <div class="col-qty">Qty</div>
                    <div class="col-price">Harga</div>
                    <div class="col-subtotal">Subtotal</div>
                </div>
                <div class="items-list" id="items-list">
                    @foreach($items as $item)
                    <div class="item-row">
                        <div class="col-name">
                            <h3>{{ $item->produk->nama_produk ?? $item->nama_barang ?? 'Produk' }}</h3>
                            <small>{{ $item->produk->kode_produk ?? '' }}</small>
                        </div>
                        <div class="col-qty">{{ $item->jumlah }}</div>
                        <div class="col-price">Rp {{ number_format($item->harga ?? 0, 0, ',', '.') }}</div>
                        <div class="col-subtotal">Rp {{ number_format($item->total ?? 0, 0, ',', '.') }}</div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
        
        <div class="summary-section">
            <div class="summary-item">
                <span class="label">Jumlah Item</span>
                <span class="value">{{ count($items) }}</span>
            </div>
            <div class="summary-item">
                <span class="label">Diskon</span>
                <span class="value">Rp {{ number_format($penjualan->diskon ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="summary-item total">
                <span class="label">TOTAL</span>
                @php
                    // Calculate total from items to get real-time value
                    $totalFromItems = $items->sum('total') ?? 0;
                    $displayTotal = $totalFromItems > 0 ? $totalFromItems : ($penjualan->total_pembayaran ?? 0);
                @endphp
                <span class="value">Rp {{ number_format($displayTotal, 0, ',', '.') }}</span>
            </div>
            
            @if($penjualan && $penjualan->anggota)
            <div class="customer-name">
                <small>Pelanggan</small>
                <h2>{{ $penjualan->anggota->nama_lengkap }}</h2>
            </div>
            @endif
        </div>
    </div>
    
    <script>
        // Update clock
        function updateClock() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            const dateStr = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            document.getElementById('clock').textContent = timeStr;
            document.getElementById('date').textContent = dateStr;
        }
        
        // Update clock every second
        setInterval(updateClock, 1000);
        updateClock();
        
        // Check localStorage for penjualan_id and sync
        function checkAndSync() {
            const storedId = localStorage.getItem('pos_penjualan_id');
            const currentPath = window.location.pathname;
            const currentId = currentPath.split('/').pop();
            
            // If there's a stored ID and it's different from current, redirect
            if (storedId && storedId !== currentId && storedId !== 'customer_display') {
                window.location.href = '{{ url("pos/customer_display") }}/' + storedId;
                return;
            }
            
            // Otherwise just reload to get latest data
            location.reload();
        }
        
        // Auto-refresh every 2 seconds to sync with POS
        setTimeout(checkAndSync, 2000);
    </script>
</body>
</html>
