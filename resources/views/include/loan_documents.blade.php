@php
    $loanDraft = isset($data['pinjaman']) ? json_decode($data['pinjaman']->draft_pengajuan ?? '', true) : [];
    $loanDraft = is_array($loanDraft) ? $loanDraft : [];
    $slips = $loanDraft['slip'] ?? [];
    if (!is_array($slips)) $slips = ['bulan_ini' => $slips];
    $docs = $loanDraft['dokumen'] ?? [];
    $fields = [
        'attachment' => ['Slip gaji bulan ini', $slips['bulan_ini'] ?? null],
        'slip_bulan_lalu' => ['Slip gaji satu bulan lalu', $slips['bulan_lalu'] ?? null],
        'slip_dua_bulan_lalu' => ['Slip gaji dua bulan lalu', $slips['dua_bulan_lalu'] ?? null],
        'dokumen_kk' => ['Kartu Keluarga', $docs['kk'] ?? null],
        'dokumen_ktp1' => ['KTP peminjam', $docs['ktp1'] ?? null],
        'dokumen_kartu_anggota' => ['Kartu anggota', $docs['kartu_anggota'] ?? null],
        'dokumen_id_card' => ['ID Card pegawai', $docs['id_card'] ?? null],
        'dokumen_surat_hutang' => ['Surat hutang bermaterai', $docs['surat_hutang'] ?? null],
        'dokumen_ktp_penjamin' => ['KTP penjamin (jenis 9)', $docs['ktp_penjamin'] ?? null],
        'dokumen_ktp2' => ['KTP suami/istri (nominal di atas Rp10 juta)', $docs['ktp2'] ?? null],
    ];
    if ($adminLoanDocuments ?? false) $fields['bukti_persetujuan'] = ['Bukti persetujuan anggota ditandatangani', $docs['bukti_persetujuan'] ?? null];
@endphp
<section class="mt-3">
    <h5>Dokumen pengajuan</h5>
    <p>JPG, PNG, atau PDF, maksimal 5 MB per file. Pilih file baru untuk mengganti yang sudah ada.</p>
    @if(isset($errors) && $errors->any()) <div class="alert alert-danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div> @endif
    @foreach($fields as $name => $info)
        <div class="form-group">
            <label for="loan_{{ $name }}">{{ $info[0] }} @if($info[1]) <span class="text-success">(sudah dilampirkan)</span> @endif</label>
            <input type="file" class="form-control" id="loan_{{ $name }}" name="{{ $name }}" accept="image/jpeg,image/png,application/pdf" {{ $disabled ?? '' }}>
        </div>
    @endforeach
    @if(!($adminLoanDocuments ?? false))
        <h5>Persetujuan data pribadi</h5>
        <p>{{ \App\Http\Controllers\MobileController::PERSETUJUAN_TEKS }}</p>
        <input type="hidden" name="consent_version" value="{{ \App\Http\Controllers\MobileController::PERSETUJUAN_VERSI }}">
        <label><input type="checkbox" name="consent" value="1" {{ $disabled ?? '' }}> Saya menyetujui penggunaan data pribadi.</label>
        <h5>Persetujuan pengajuan pinjaman</h5>
        <p>{{ \App\Http\Controllers\MobileController::PERSETUJUAN_PINJAMAN_TEKS }}</p>
        <input type="hidden" name="loan_consent_version" value="{{ \App\Http\Controllers\MobileController::PERSETUJUAN_PINJAMAN_VERSI }}">
        <label><input type="checkbox" name="loan_consent" value="1" {{ $disabled ?? '' }}> Saya menyetujui syarat pengajuan pinjaman.</label>
    @else
        <p>Bukti yang ditandatangani anggota diunggah oleh petugas; sistem mencatat identitas petugas dan waktu unggah.</p>
    @endif
</section>
