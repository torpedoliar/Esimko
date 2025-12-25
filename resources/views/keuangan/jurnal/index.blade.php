@php
    $app='laporan';
    $page='Jurnal';
    $subpage='Jurnal';
@endphp

@extends('layouts.admin')

@section('title')
    Jurnal |
@endsection

@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="media">
                <img src="{{ asset('assets/images/icon-page/organization-chart.png') }}" class="avatar-md mr-3">
                <div class="media-body align-self-center">
                    <h4 class="mb-0 font-size-18">Jurnal</h4>
                    <p class="text-muted m-0">Menampilkan transaksi jurnal</p>
                </div>
            </div>
        </div>
        <div id="info"></div>
        <div class="card" id="jurnal">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <button type="button" onclick="info()" class="btn btn-primary btn-block">Jurnal Baru</button>
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control monthpicker" id="bulan" name="bulan" value="{{ date('m-Y') }}" onchange="search_data()">
                    </div>
                    <div class="col-md-1 text-center d-flex flex-row justify-content-center align-items-center">
                        s/d
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control monthpicker" id="bulan_akhir" name="bulan_akhir" value="{{ date('m-Y') }}" onchange="search_data()">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-success text-nowrap" type="button" onclick="export_excel()">Export Excel</button>
                    </div>
                </div>
                <hr>
                <div id="table"></div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        let to, _token = '{{ csrf_token() }}', data = [], selected_page = 1;
        let $info = $('#info'), $table = $('#table'), $jurnal = $('#jurnal');

        let search_data = (page = 1) => {
            if (page.toString() === '+1') selected_page++;
            else if (page.toString() === '-1') selected_page--;
            else selected_page = page;

            let bulan = $('#bulan').val();
            let bulan_akhir = $('#bulan_akhir').val();
            $table.html('<h1>Loading ...</h1>');
            $.post("{{ url('keuangan/jurnal/search') }}", {_token, bulan, bulan_akhir, paginate: 10, page: selected_page}, (result) => {
                $table.html(result);
            }).fail((xhr) => {
                $table.html(xhr.responseText);
            })
        }

        let info = (id = '') => {
            $.get("{{ url('keuangan/jurnal') }}/" + (id === '' ? 'create' : (id + '/edit')), (result) => {
                $info.html(result);
                $jurnal.hide();
            }).fail((xhr) => {
                $info.html(xhr.responseText);
            });
        }

        let delete_data = (id) => {
            Swal.fire({
                title: 'Hapus Data Jurnal ?',
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                denyButtonText: 'Batal',
                confirmButtonColor: '#F46A6A',
                denyButtonColor: '#bdbdbd'
            }).then((result) => {
                if (result.value) {
                    $.post("{{ url('keuangan/jurnal') }}/" + id, {_method: 'delete', _token}, () => {
                        discard();
                    }).fail((xhr) => {
                        $info.html(xhr.responseText);
                    });
                }
            });
        }

        let discard = () => {
            $info.html('');
            $jurnal.show();
            search_data();
        }

        let export_excel = () => {
            let bulan = $('#bulan').val();
            let bulan_akhir = $('#bulan_akhir').val();
            window.open("{{ url('keuangan/jurnal/export') }}?bulan=" + bulan + '&bulan_akhir=' + bulan_akhir, '_blank');
        }

        search_data();
    </script>
@endsection
