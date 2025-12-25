@php
    $app='manajemen_barang';
    $page='Penyesuaian Stok';
    $subpage='Penyesuaian Stok';
@endphp
@extends('layouts.admin')
@section('title')
    Penyesuaian Stok |
@endsection
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="media">
                <img src="{{asset('assets/images/icon-page/boxes.png')}}" class="avatar-md mr-3">
                <div class="media-body align-self-center">
                    <h4 class="mb-0 font-size-18">Penyesuaian Stok</h4>
                    <p class="text-muted m-0">Memproses penyesuaian jumlah stok barang</p>
                </div>
            </div>
        </div>
        <form action="{{ !empty($stokOpname) ? url('manajemen_stok/stok_opname/'. $stokOpname->id) : url('manajemen_stok/stok_opname') }}" method="post" enctype="multipart/form-data">
            @if(!empty($stokOpname))
                <input type="hidden" name="_method" value="put">
            @endif
            {{ csrf_field() }}
            <div class="card">
                <div class="card-header">
                    <h5>{{(empty($stokOpname) ? 'Tambah' : 'Edit')}} Stok Opname</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Kode</label>
                                        <input type="text" class="form-control" name="kode" id="kode" value="{{ $stokOpname->produk->kode ?? '' }}" required />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Nama Produk</label>
                                        <input type="hidden" id="fid_produk" name="fid_produk" value="{{ $stokOpname->fid_produk ?? '' }}">
                                        <input type="text" class="form-control" name="nama_produk" id="nama_produk" value="{{ $stokOpname->produk->nama_produk ?? '' }}" readonly required />
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label>Stok</label>
                                        <input type="text" class="form-control" name="stok" id="stok" autocomplete="off" required >
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Stok * Harga</label>
                                        <input type="text" class="form-control" name="stok_harga" id="stok_harga" autocomplete="off" required >
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Stok * Selisih</label>
                                        <input type="text" class="form-control" name="stok_selisih" id="stok_selisih" autocomplete="off" required >
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Jumlah Fisik</label>
                                        <input name="jumlah_fisik" id="jumlah_fisik" class="form-control" value="{{ $stokOpname->jumlah_fisik ?? '' }}" type="text" >
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Selisih</label>
                                        <input name="jumlah" id="jumlah" class="form-control" value="{{ $stokOpname->jumlah ?? '' }}" type="text" >
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Harga Beli</label>
                                        <input type="text" class="form-control" name="hpp" id="hpp" value="{{ format_number($stokOpname->hpp ?? '') }}" required />
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Jenis</label>
                                        <select class="select2" style="width:100%" name="jenis" id="jenis">
                                            <option>Penyesuaian Stok</option>
                                            <option>Rusak</option>
                                            <option>Hilang</option>
                                            <option>Kadaluarsa</option>
                                            <option>Tambahan</option>
                                        </select>
                                        @php($jenis = $stokOpname->jenis ?? '')
                                        <script>
                                            document.getElementById('jenis').value = '{{ $jenis }}';
                                        </script>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Tanggal</label>
                                        <input type="text" class="form-control datepicker" name="tanggal" value="{{ date('d-m-Y', strtotime($stokOpname->tanggal ?? date('Y-m-d'))) }}" required />
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Keterangan</label>
                                <textarea class="form-control" name="keterangan" style="height:117px" >{{ $stokOpname->keterangan ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <input type="hidden" name="halaman" value="{{ $page }}">
                    <div class="pull-right">
                        <a class="btn btn-secondary" href="{{url('manajemen_stok/stok_opname')}}" >Kembali</a>
                        <button class="btn btn-primary" type="submit">Simpan</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

@endsection
@section('js')
    <script src="{{asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js')}}"></script>
    <script src="{{asset('assets/js/pages/form-advanced.init.js')}}"></script>
    <script>
        add_commas = (nStr) =>{
            nStr += '';
            let x = nStr.split('.');
            let x1 = x[0];
            let x2 = x.length > 1 ? '.' + x[1] : '';
            let rgx = /(\d+)(\d{3})/;
            while (rgx.test(x1)) {
                x1 = x1.replace(rgx, '$1' + '.' + '$2');
            }
            return x1 + x2;
        }
        remove_commas = (nStr) => {
            nStr = nStr.replace(/\./g,'');
            nStr = nStr.replace(/\,/g,'.');
            return nStr;
        }

        let $kode = $('#kode'), $nama_produk = $('#nama_produk'), $jumlah_fisik = $('#jumlah_fisik'), $jumlah = $('#jumlah');
        $kode.change(() => {
            let kode = $kode.val();
            if (kode !== '') {
                $.get("{{ url('manajemen_stok/stok_opname/search/produk') }}?kode=" + kode, (result) => {
                    console.log(result);
                    $('#nama_produk').val(result.nama_produk);
                    $('#fid_produk').val(result.id);
                    $('#stok').val(result.stok.sisa);
                    $('#hpp').val(add_commas(result.harga_beli));
                    $('#stok_harga').val(add_commas(result.harga_beli * result.stok.sisa));
                }).fail((xhr) => {
                    console.log(xhr.responseText);
                });
            }
        });
        $kode.change();

        $jumlah_fisik.change(() => {
            let stok = $('#stok').val();
            let harga = $('#hpp').val();
            harga = parseInt(remove_commas(harga));
            if (stok !== '') {
                stok = parseInt(stok);
                let jumlah_fisik = $jumlah_fisik.val();
                let jumlah = jumlah_fisik - stok;
                $jumlah.val(jumlah);

                $('#stok_selisih').val(add_commas(jumlah * harga));
            }
        });
        $jumlah_fisik.change();

    </script>
@endsection
