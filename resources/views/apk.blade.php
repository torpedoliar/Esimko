<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Unduh Aplikasi Anggota eSIMKO</title>
  <link rel="icon" href="{{asset('assets/images/logo-esimko-landing-page.png')}}">
  <style>
    :root { --hijau: #118334; --hijau-tua: #0A3B1E; --tinta: #17231A; --abu: #3F4A42; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
      background: #F6FBF4; color: var(--tinta);
      min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px;
    }
    .kartu {
      background: #fff; border-radius: 20px; max-width: 520px; width: 100%;
      box-shadow: 0 4px 24px rgba(10,59,30,.08); overflow: hidden;
    }
    .hero { background: var(--hijau); color: #fff; padding: 36px 28px; text-align: center; }
    .hero img { height: 56px; margin-bottom: 12px; }
    .hero h1 { font-size: 22px; font-weight: 700; }
    .hero p { opacity: .85; font-size: 14px; margin-top: 6px; }
    .isi { padding: 28px; }
    .versi { display: flex; align-items: baseline; gap: 8px; margin-bottom: 12px; }
    .versi strong { font-size: 20px; color: var(--hijau-tua); }
    .versi span { color: var(--abu); font-size: 13px; }
    .changelog {
      background: #D8EADB; border-radius: 12px; padding: 12px 16px; font-size: 13px;
      color: var(--hijau-tua); margin-bottom: 20px; white-space: pre-line;
    }
    .btn-unduh {
      display: flex; align-items: center; justify-content: center; gap: 10px;
      background: var(--hijau); color: #fff; text-decoration: none; font-weight: 600;
      font-size: 16px; border-radius: 14px; padding: 16px; min-height: 52px;
    }
    .btn-unduh:hover { background: #0d6b2a; }
    .meta { text-align: center; color: var(--abu); font-size: 12px; margin-top: 10px; }
    .qr { text-align: center; margin-top: 24px; }
    .qr img { width: 140px; height: 140px; background: #fff; padding: 8px; border: 1px solid #DCE7DE; border-radius: 12px; }
    .qr p { font-size: 12px; color: var(--abu); margin-top: 8px; }
    .langkah { margin-top: 24px; border-top: 1px solid #DCE7DE; padding-top: 20px; }
    .langkah h3 { font-size: 14px; color: var(--hijau-tua); margin-bottom: 8px; }
    .langkah ol { padding-left: 20px; font-size: 13px; color: var(--abu); }
    .langkah li { margin-bottom: 4px; }
  </style>
</head>
<body>
  <div class="kartu">
    <div class="hero">
      <img src="{{asset('assets/images/logo-esimko-landing-page.png')}}" alt="eSIMKO" onerror="this.style.display='none'">
      <h1>Aplikasi Anggota eSIMKO</h1>
      <p>Keuangan koperasi Anda, dalam genggaman</p>
    </div>
    <div class="isi">
      @php $r = $rilis ?? null; @endphp
      @if(!empty($r))
        <div class="versi">
          <strong>Versi {{$r->versi}}</strong>
          <span>· {{date('d M Y', strtotime($r->created_at))}} · {{number_format($r->ukuran / 1048576, 1)}} MB</span>
        </div>
        @if(!empty($r->changelog))
          <div class="changelog">{{$r->changelog}}</div>
        @endif
        <a class="btn-unduh" href="{{url('apk/unduh')}}" download>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Unduh Aplikasi (APK)
        </a>
        <div class="meta">Kompatibel Android 9 ke atas · Instal di atas versi lama tanpa hapus data</div>
      @else
        <div class="changelog">Aplikasi sedang disiapkan. Silakan kembali lagi nanti.</div>
      @endif

      <div class="qr">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{url('apk')}}" alt="QR halaman unduh" onerror="this.parentElement.style.display='none'">
        <p>Scan untuk membuka halaman ini di HP</p>
      </div>

      <div class="langkah">
        <h3>Langkah instalasi</h3>
        <ol>
          <li>Unduh APK lewat tombol di atas.</li>
          <li>Buka file dari notifikasi atau menu <em>Downloads</em>.</li>
          <li>Bila diminta, izinkan "Install dari sumber tidak dikenal" (sekali saja per HP).</li>
          <li>Tap <strong>Install</strong> — selesai. Login seperti biasa.</li>
        </ol>
      </div>
    </div>
  </div>
</body>
</html>
