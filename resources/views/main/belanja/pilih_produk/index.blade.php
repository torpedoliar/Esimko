@php
  $subpage='Pilih Produk';
  $keterangan='Silahkan melihat dan memilih produk yang dijual di toko kami';
@endphp
@extends('main.belanja.layout')
@section('content_belanja')
<div class="card">
  <div class="card-header">
    <div class="row">
      <div class="col-md-9">
        <form action="" method="get">
          <input type="hidden" name="kategori" value="{{$kategori}}">
          <div class="input-group">
            <input type="text" class="form-control" value="{{$search}}" name="search" placeholder="Cari Data Produk">
            <div class="input-group-append">
              <button class="btn btn-dark" type="submit">Search</button>
            </div>
          </div>
        </form>
      </div>
      <div class="col-md-3">
        <button class="btn btn-primary btn-block" data-toggle="modal" data-target='#filter-barang' >Filter Barang</button>
      </div>
    </div>
  </div>
</div>
<div class="row mt-4">
  @foreach ($data['produk'] as $key => $value)
  <div class="col-xl-3 col-sm-4 col-6">
    <a href="{{url('main/belanja/produk/detail?id='.$value->kode)}}">
      <div class="card">
        <div class="produk">
          <img class="card-img-top img-fluid" src="{{(!empty($value->foto) ? asset('storage/'.$value->foto) : asset('assets/images/produk-default.jpg')) }}">
          <div class="card-body">
            <h6 class="title"><a href="" class="text-secondary">{{$value->nama_produk}}</a></h6>
            <h6 class="price mt-2">Rp {{number_format($value->harga_jual,0,',','.')}}</h6>
          </div>
        </div>
      </div>
    </a>
  </div>
  @endforeach
</div>
<div class="mb-4 mt-5">
  {{ $data['produk']->links('include.pagination', ['pagination' => $data['produk']] ) }}
</div>
<div id="filter-barang" class="modal fade right">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5>Filter Barang</h5>
      </div>
      <div class="modal-body">
        <form action="{{url('main/belanja/produk/filter')}}" method="post">
          {{ csrf_field() }}
          <div class="form-group">
            <label>Kelompok Barang</label>
            <select class="select2" style="width:100%" id="kelompok" name="kelompok"></select>
          </div>
          <div class="form-group">
            <label>Kategori</label>
            <select class="select2" style="width:100%" id="kategori" name="kategori"></select>
          </div>
          <div class="form-group">
            <label>Sub Kategori</label>
            <select class="select2" style="width:100%" id="sub_kategori" name="sub_kategori"></select>
          </div>
          <button class="btn btn-primary btn-block">Filter Barang </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
@section('add_js')
<script>
selected_kelompok = '{{(!empty(Session::get('filter_produk')) && Session::get('filter_produk')['kelompok'] !='all'  ? Session::get('filter_produk')['kelompok'] : 'all')}}';
selected_kategori = '{{(!empty(Session::get('filter_produk')) && Session::get('filter_produk')['kategori'] !='all' ? Session::get('filter_produk')['kategori'] : 'all')}}';
selected_subkategori = '{{(!empty(Session::get('filter_produk')) && Session::get('filter_produk')['sub_kategori'] !='all' ? Session::get('filter_produk')['sub_kategori'] : 'all')}}';
function get_kategori(select_target, parent_id, selected){
  $.get("{{ url('api/get_kategori') }}/"+parent_id+'/'+selected, function (result) {
    $selectElement = $('#'+select_target);
    $selectElement.empty();
    $.each(result, function (i, value) {
      $selectElement.append('<option data-id="'+value.id+'" value="'+value.id+'" '+value.selected+' >'+value.nama_kategori+'</option>');
    });
    $selectElement.trigger('change');
  });
}

get_kategori('kelompok','0', selected_kelompok);
$('#kelompok').change(function () {
    let id = $(this).find('option:selected').attr('data-id');
    get_kategori('kategori',id, selected_kategori);
});
$('#kategori').change(function () {
    let id = $(this).find('option:selected').attr('data-id');
    get_kategori('sub_kategori',id, selected_subkategori);
});
</script>
@endsection
