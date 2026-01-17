@extends('layouts.admin')
@section('title', 'System Update | ')

@section('content')
<div class="page-content">
    <div class="container-fluid">

        {{-- Page Title --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0 font-size-18">
                        <i class="bx bx-refresh mr-2"></i> System Update
                    </h4>
                    <div class="page-title-right">
                        <button type="button" class="btn btn-outline-primary" id="btnCheckUpdate">
                            <i class="bx bx-sync"></i> Check for Updates
                        </button>
                    </div>
                </div>
                <p class="text-muted mb-4">Kelola update aplikasi ESIMKO</p>
            </div>
        </div>

        {{-- Current Version Card --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                            <div class="avatar-sm mr-3">
                                <span class="avatar-title rounded-circle bg-soft-primary text-primary font-size-20">
                                    <i class="bx bx-download"></i>
                                </span>
                            </div>
                            <div class="flex-1">
                                <h5 class="mb-0">Versi Saat Ini</h5>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3 col-6 mb-3">
                                <p class="text-muted mb-1">Versi</p>
                                <h4 class="text-primary mb-0">
                                    v{{ $version->version ?? 'N/A' }}
                                </h4>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <p class="text-muted mb-1">Tanggal Rilis</p>
                                <h5 class="mb-0">{{ $version->releaseDate ?? 'N/A' }}</h5>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <p class="text-muted mb-1">Branch</p>
                                <h5 class="mb-0">
                                    <span class="badge badge-soft-success">{{ $version->branch ?? 'main' }}</span>
                                </h5>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <p class="text-muted mb-1">Min. Database</p>
                                <h5 class="mb-0">{{ $version->minDatabaseVersion ?? 'N/A' }}</h5>
                            </div>
                        </div>

                        @if(isset($version->changelog) && count($version->changelog) > 0)
                        <hr>
                        <h6 class="text-primary mb-3">Changelog:</h6>
                        <ul class="list-unstyled mb-0">
                            @foreach($version->changelog as $change)
                            <li class="mb-2">
                                <i class="bx bx-check-circle text-success mr-2"></i>
                                {{ $change }}
                            </li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Update Instructions Card --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                            <div class="avatar-sm mr-3">
                                <span class="avatar-title rounded-circle bg-soft-info text-info font-size-20">
                                    <i class="bx bx-terminal"></i>
                                </span>
                            </div>
                            <div class="flex-1">
                                <h5 class="mb-0">Cara Update Aplikasi</h5>
                            </div>
                        </div>

                        <p class="text-muted mb-3">
                            Untuk update aplikasi, jalankan script berikut di server/host machine:
                        </p>

                        <div class="bg-dark text-light p-3 rounded">
<pre class="mb-0 text-light" style="font-family: 'Consolas', monospace; font-size: 13px;">
<span class="text-muted"># Masuk ke direktori project</span>
<span class="text-warning">cd</span> /path/to/esimko

<span class="text-muted"># Jalankan script update</span>
<span class="text-success">.\update.ps1</span>
<span class="text-muted"># atau di Linux: ./update.sh</span>
</pre>
                        </div>

                        <div class="alert alert-info mt-4 mb-0">
                            <i class="bx bx-info-circle mr-2"></i>
                            <strong>Catatan:</strong> Script update akan otomatis backup database sebelum update. 
                            Data Anda aman!
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Repository Info --}}
        @if(isset($version->repository))
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted mb-3">Repository</h6>
                        <a href="{{ $version->repository }}" target="_blank" class="text-primary">
                            <i class="bx bxl-github mr-1"></i>
                            {{ $version->repository }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    $('#btnCheckUpdate').on('click', function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i> Checking...');
        
        $.ajax({
            url: '{{ route("system-update.check") }}',
            type: 'GET',
            success: function(response) {
                if (response.updateAvailable) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Update Tersedia!',
                        html: 'Versi baru: <strong>' + response.latestVersion + '</strong><br>Versi Anda: ' + response.currentVersion,
                        confirmButtonText: 'OK'
                    });
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: 'Versi Terbaru',
                        text: response.message,
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal memeriksa update',
                    confirmButtonText: 'OK'
                });
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="bx bx-sync"></i> Check for Updates');
            }
        });
    });
});
</script>
@endsection
