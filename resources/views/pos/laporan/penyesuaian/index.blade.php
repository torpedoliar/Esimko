@php
    $app='pos';
    $page='Laporan';
    $subpage='Laporan Penyesuaian';
@endphp
@extends('layouts.admin')
@section('title')
    Laporan Penyesuaian |
@endsection
@section('content')
    <div class="container-fluid">
        <div class="content-breadcrumb mb-2">
            <div class="page-title-box">
                <div class="media">
                    <img src="{{asset('assets/images/icon-page/market.png')}}" class="avatar-md mr-3">
                    <div class="media-body align-self-center">
                        <h4 class="mb-0 font-size-18">Data Laporan Penyesuaian</h4>
                        <p class="text-muted m-0">Menampilkan laporan hasil penyesuaian</p>
                    </div>
                </div>
            </div>

            <form id="form_search">
                @csrf
                <div class="row">
                    <div class="col-md-2">
                        <input type="text" class="form-control datepicker" name="tanggal_awal" placeholder="Tanggal Awal" />
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control datepicker" name="tanggal_akhir" placeholder="Tanggal Akhir" />
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-dark btn-block" type="submit">Search</button>
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-success btn-block" type="button" onclick="export_Excel()">Export</button>
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-primary btn-block" type="button" onclick="cetak()">Cetak</button>
                    </div>
                </div>
            </form>

            <div id="table"></div>
        </div>
    </div>
@endsection
@section('js')
    <script>
        let $form_search = $('#form_search'),
            $table = $('#table'),
            selected_page = 1;

        $form_search.submit((e) => {
            e.preventDefault();
            search_data();
        });

        let search_data = (page = 1) => {
            if (page.toString() === '+1') selected_page++;
            else if (page.toString() === '-1') selected_page--;
            else selected_page = page;

            let data = get_form_data($form_search);
            data.paginate = 10;
            data.page = page;
            $.post("{{ url('pos/laporan_penyesuaian/search') }}", data, (result) => {
                $table.html(result);
            }).fail((xhr) => {
                $table.html(xhr.responseText);
            });
        }
        search_data();

        let export_Excel = () => {
            let data = $form_search.serialize();
            window.open("{{ url('pos/laporan_penyesuaian/excel') }}?" + data, '_blank');
        }

        let cetak = () => {
            let data = $form_search.serialize();
            window.open("{{ url('pos/laporan_penyesuaian/cetak') }}?" + data, '_blank');
        }
    </script>
@endsection
