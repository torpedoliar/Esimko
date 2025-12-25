@php
  $app='pos';
  $page='Penjualan';
  $subpage='Penjualan';
  $readonly=(!empty($data['penjualan']) && ($data['penjualan']->fid_status == 3 || $data['penjualan']->fid_status == 4 ) ? 'readonly' : '');
@endphp
@extends('layouts.kasir')
@section('title')
  Penjualan |
@endsection
@section('css')
  <link href="{{asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css')}}" rel="stylesheet" />
  <style>
  .list-anggota{
    padding-bottom:10px;
    border-bottom: 1px solid #f2f2f2;
    margin-top:10px;
    cursor: pointer;
  }

  .pos-container{
    display: flex !important;
  }
  .pos-container .coloum-left{
    width:100%;
    background:#fff;
  }
  .pos-container .coloum-right{
    width:440px;
    background:#eaecef;
  }
  </style>
@endsection
@section('content')
<div class="container-fluid">
  <div class="pos-container">
    <div class="coloum-left">
      <div style="padding:20px;background:#f2f2f2">
        <form action="{{url('pos/penjualan/proses_items')}}" method="post" id="add_items">
          {{ csrf_field() }}
          <input type="hidden" name="id" value="{{$id}}">
          <div class="input-group">
            <input type="text" style="margin:0px" id="kode" name="kode" class="form-control" {{($readonly=='readonly' ? 'disabled' : '')}} placeholder="Cari Data Barang">
            <div class="input-group-append">
              <button class="btn btn-secondary" type="submit" >Tambahkan</button>
            </div>
          </div>
        </form>
      </div>
    </div>
    <div class="coloum-right">
      <div style="padding:20px;">
        <div class="media" style="cursor:pointer" @if($readonly!='readonly') onclick="pilih_anggota('show')" @endif>
          <div class="avatar-wrapper" id="avatar" style="height:60px;width:60px">
            <img src="{{(!empty($data['setoran-berkala']->avatar) ? asset('storage/'.$data['setoran-berkala']->avatar) : asset('assets/images/user-avatar-placeholder.png') )}}" alt="" />
          </div>
          <div class="align-self-center media-body">
            <div id="no_anggota" >
              @if(!empty($data['penjualan']->no_anggota))
              <span>No. {{$data['penjualan']->no_anggota}}</span>
              @else
              <div style="height:15px;width:80%;background:whitesmoke"></div>
              @endif
            </div>
            <div id="nama_lengkap">
              @if(!empty($data['penjualan']->nama_lengkap))
              <h5>{{$data['penjualan']->nama_lengkap}}</h5>
              @else
              <div style="height:20px;width:100%;background:whitesmoke" class="mt-2"></div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <form action="{{url('pos/penjualan/proses')}}" method="post" id="proses_penjualan">
    {{ csrf_field() }}
    <div class="pos-container" style="height:calc(100vh - 280px)">
      <div class="coloum-left" style="position:relative">
        <div style="height:calc(100vh - 370px);margin-bottom:20px;overflow: scroll;margin-top:-25px">
          <table class="table table-middle table-hover">
            <thead class="thead-light">
              <tr>
                <th width="20px">No</th>
                <th>Nama Barang</th>
                <th class="center" style="width:150px">Jumlah</th>
                <th style="text-align:right;width:120px">Harga</th>
                <th class="center" style="width:90px">Diskon (%)</th>
                <th style="text-align:right;width:120px">Sub Total</th>
                <th style="width:50px"></th>
              </tr>
            </thead>
            <tbody>
              @foreach ($data['items'] as $key => $value)
              <tr>
                <td width="20px">{{$key+1}}</td>
                <td>
                  <span>Kode. {{$value->kode}}</span>
                  <h6>{{$value->nama_produk}}</h6>
                </td>
                <td>
                  <input id="jumlah_{{$value->id}}" {{($readonly=='readonly' ? 'disabled' : '')}} onchange="calc_items()" name="jumlah[{{$value->id}}]" value="{{$value->jumlah}}" data-toggle="touchspin" class="center"  type="text" data-max="{{$value->sisa}}">
                </td>
                <td><input type="text" style="text-align:right;" class="form-control" value="{{number_format($value->harga,0,',','.')}}" readonly ></td>
                <td class="center">
                  <input class="center form-control"  type="text" id="diskon_{{$value->id}}" {{($readonly=='readonly' ? 'disabled' : '')}} onchange="calc_items()" name="diskon[{{$value->id}}]" value="{{$value->diskon}}" >
                </td>
                <td style="text-align:right">
                  <h6 style="font-weight:500;font-size:16px" id="total_{{$value->id}}">{{number_format($value->total,0,',','.')}}</h6>
                </td>
                <td style="width:50px">
                  <div class="text-center">
                    <a href="javascript:;" onclick="confirmDelete({{ $value->id }})" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div style="background:#f8f9fa;padding:20px;width:100%;position:absolute;bottom:0px;border-top:3px solid #eff2f7">
          <div class="row">
            <div class="col-md-4"></div>
            <div class="col-md-3">
              <div class="form-group mb-0" style="text-align:right">
                <label>Sub Total (Rp)</label>
                <input type="text" class="form-control autonumeric" id="subtotal" readonly style="text-align:right" value="{{(!empty($data['penjualan']) ? $data['penjualan']->subtotal : 0 )}}" data-a-dec="," data-a-sep=".">
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group mb-0" style="text-align:right">
                <label>Diskon (%)</label>
                <input type="text" class="form-control" onchange="calc_items()" {{$readonly}} id="diskon" style="text-align:right" name="diskon" value="{{(!empty($data['penjualan']) ? $data['penjualan']->diskon : 0 )}}">
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group mb-0" style="text-align:right">
                <label>Total (Rp)</label>
                <input type="text" class="form-control autonumeric" name="total_pembayaran" id="total" readonly style="text-align:right" value="{{(!empty($data['penjualan']) ? $data['penjualan']->total : 0 )}}" data-a-dec="," data-a-sep="." >
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="coloum-right" style="position:relative;">
        <div style="padding:20px;margin-top:-20px">
          <input type="hidden" name="fid_anggota" value="{{(!empty($data['penjualan']->no_anggota) ? $data['penjualan']->no_anggota : null)}}" id="fid_anggota">
          <input type="hidden" id="anggota_id" value="{{(!empty($data['penjualan']) ? $data['penjualan']->anggota_id : '')}}" >
          <div class="form-group">
            <label>No. Transaksi</label>
            <input type="text" class="form-control" name="no_transaksi" value="{{(!empty($data['penjualan']) ? $data['penjualan']->no_transaksi : null)}}" readonly>
          </div>
          <div class="form-group">
            <label>Metode Pembayaran</label>
            <select class="form-control select2" name="metode_pembayaran" {{($readonly=='readonly' ? 'disabled' : '')}} id="metode_pembayaran" >
              @foreach ($data['metode-pembayaran'] as $key => $value)
              <option value="{{$value->id}}" {{(!empty($data['penjualan']) && $data['penjualan']->fid_metode_pembayaran == $value->id ? 'selected' : '' )}} >{{$value->keterangan}}</option>
              @endforeach
            </select>
          </div>
          <div id="form_1" class="form-payment">
            <div class="form-group">
              <label>Tunai (Rp)</label>
              <input type="text" class="form-control autonumeric" {{$readonly}} onchange="calc_items()" autocomplete="off" id="tunai" name="tunai" value="{{(!empty($data['penjualan']) ? $data['penjualan']->tunai : 0)}}" data-a-dec="," data-a-sep="." >
            </div>
            <div class="form-group">
              <label>Kembali (Rp)</label>
              <input type="text" class="form-control autonumeric" id="kembali" name="kembali" value="{{(!empty($data['penjualan']) ? $data['penjualan']->kembali : 0)}}" data-a-dec="," data-a-sep="." readonly >
            </div>
          </div>
          <div id="form_3" class="form-payment">
            <input type="hidden" name="bulan"  id="bulan">
            <div class="form-group">
              <label>Update Gaji Pokok (Rp)</label>
              <input type="text" class="form-control autonumeric" {{$readonly}} onkeyup="calc_items()" autocomplete="off" id="gaji_pokok" name="gaji_pokok"  data-a-dec="," data-a-sep="." >
              <div id="bulan_gaji" style="text-align:right;margin-top:5px;font-size:12px"></div>
            </div>
          </div>
          <div id="form_5" class="form-payment">
            <div class="form-group">
              <label>Nomor Debet Card</label>
              <input type="text" class="form-control" {{$readonly}} name="no_debit_card" value="{{(!empty($data['penjualan']) ? $data['penjualan']->no_debit_card : 0)}}" >
            </div>
          </div>
          <div id="form_7" class="form-payment">
            <div class="form-group">
              <label>Nomor Akun <span id="payment_online_name"></span></label>
              <input type="text" class="form-control" {{$readonly}} name="account_number" value="{{(!empty($data['penjualan']) ? $data['penjualan']->account_number : 0)}}" >
            </div>
          </div>
          <div id="alert"></div>
        </div>
        @if($action=='edit')
        <div style="width:100%;position:absolute;bottom:0px;padding:20px">
          @if($data['penjualan']->fid_status==4)
            <div class="row gutter-2">
              <div class="col-md-6">
                <a href="{{url('pos/penjualan')}}" class="btn btn-secondary btn-block">Kembali</a>
              </div>
              <div class="col-md-6">
                <button class="btn btn-info btn-block" type="button" onclick="batalkan('open')" >Buka Pembatalan</button>
              </div>
            </div>
          @else
            <div class="row gutter-2">
              <div class="col-md-6">
                <button class="btn btn-danger btn-block" type="button" onclick="batalkan('close')">Batalkan</button>
              </div>
              <div class="col-md-6">
                <button class="btn btn-primary btn-block" name="action" value="simpan" {{($readonly=='readonly' ? 'disabled' : '')}} >Simpan</button>
              </div>
            </div>
            <div class="row gutter-2" style="margin-top:10px">
              <div class="col-md-6">
                <a href="{{url('pos/penjualan')}}" class="btn btn-secondary btn-block">Kembali</a>
              </div>
              <div class="col-md-6">
                @if($data['penjualan']->fid_status==3)
                <button class="btn btn-info btn-block" type="button" onclick="cetak()">Cetak Struk</button>
                @else
                <button class="btn btn-info btn-block" name="action" value="bayar">Bayar</button>
                @endif
              </div>
            </div>
          @endif
        </div>
        @endif
      </div>
    </div>
    <input type="hidden" name="id" value="{{$id}}">
  </form>
</div>
@foreach ($data['items'] as $key => $value)
  <form action="{{url('pos/penjualan/proses_items')}}" method="post" id="hapus{{$value->id}}">
    {{ csrf_field()}}
    <input type="hidden" name="id" value="{{$id}}">
    <input type="hidden" name="kode" value="{{$value->kode}}">
    <input type="hidden" name="action" value="delete">
  </form>
@endforeach
<form action="{{url('pos/penjualan/proses_pembatalan')}}" method="post" id="proses_pembatalan">
  {{ csrf_field()}}
  <input type="hidden" name="id" value="{{$id}}">
  <input type="hidden" name="status" id="status">
</form>
<div id="modal-anggota" class="modal fade right">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5>Pilih Anggota</h5>
      </div>
      <div class="modal-body">
        <div class="input-group mb-3">
          <input type="text" class="form-control" value="" id="search" name="search" placeholder="Cari Anggota">
          <div class="input-group-append">
            <button class="btn btn-dark" id="btn-search" onclick="search_anggota()">Search</button>
          </div>
        </div>
        <div id="loading"><img src="{{asset('assets/images/loading.gif')}}" style="width:100px"></div>
        <div id="list-anggota" ></div>
      </div>
    </div>
  </div>
</div>

<div id="detail_angsuran" class="modal fade right">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5>Detail Angsuran</h5>
      </div>
      <div class="modal-body">
        <div class="media mb-4">
          <div class="avatar-wrapper" id="avatar" style="height:60px;width:60px">
            <img src="{{asset('assets/images/user-avatar-placeholder.png')}}" alt="" />
          </div>
          <div class="align-self-center media-body">
            <div id="no_anggota" >
              <div style="height:15px;width:80%;background:whitesmoke"></div>
            </div>
            <div id="nama_lengkap">
              <div style="height:20px;width:100%;background:whitesmoke" class="mt-2"></div>
            </div>
          </div>
        </div>
        <div class="list-content">
          <span>Angsuran Simpanan</span>
          <div id="angsuran_simpanan" class="info-content"></div>
        </div>
        <div class="list-content">
          <span>Setoran Berkala</span>
          <div id="setoran_berkala" class="info-content"></div>
        </div>
        <div class="list-content">
          <span>Angsuran Pinjaman</span>
          <div id="angsuran_pinjaman" class="info-content"></div>
        </div>
        <div class="list-content">
          <span>Angsuran Belanja</span>
          <div id="angsuran_belanja" class="info-content"></div>
        </div>
        <div class="list-content">
          <span>Total Angsuran</span>
          <div id="total_angsuran" class="info-content"></div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection
@section('js')
  <script src="{{asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js')}}"></script>
  <script src="{{asset('assets/js/pages/form-advanced.init.js')}}"></script>
  <script src="{{asset('assets/js/accounting.js')}}"></script>
  <script>
  $(function () {
    $('#metode_pembayaran').trigger('change');
    calc_items();
  });

  function search_anggota(){
    var search = $('#search').val();
    if(search !== ''){ search = '/'+search }
    else{ search = '/all'}
    $('#loading').show();
    $('#list-anggota').hide();
    $.get("{{ url('api/get_anggota/aktif/') }}"+search,function (result) {
      $('#list-anggota').html('');
      $.each(result,function(i,value){
      $('#list-anggota').append('<div class="list-anggota" onclick="pilih_anggota('+value.id+')">'+
        '<div class="media">'+
          '<div class="avatar-thumbnail avatar-sm rounded-circle mr-2">'+
            '<img style="margin-right:10px;" src="'+value.avatar+'" alt="" style="max-width:none" class="rounded-circle">'+
          '</div>'+
          '<div class="media-body align-self-center" >'+
            '<p class="text-muted mb-0">No. '+value.no_anggota+'</p>'+
            '<h5 class="text-truncate font-size-16">'+value.nama_lengkap+'</h5>'+
          '</div>'+
        '</div>'+
      '</div>');
      });
      $('#loading').hide();
      $('#list-anggota').show();
    });
  };

  function batalkan(jenis){
    if(jenis == 'open'){
      msg = 'Apakah anda yakin ingin membuka pembatalan transaksi ini';
      status = 1;
    }
    else{
      msg = 'Apakah anda yakin ingin mebatalkan transaksi ini';
      status = 4;
    }
    Swal.fire({
      title: "Are you sure?",
      text: msg,
      type:"question",
      showCancelButton: true,
      confirmButtonColor: '#d63030',
      cancelButtonColor: '#cbcbcb',
      confirmButtonText: 'Yes'
    }).then((result) => {
      if (result.value == true) {
        $('#status').val(status);
        $('#proses_pembatalan').submit();
      }
    });
  }

  @if(!empty($data['penjualan']))
  pilih_anggota('{{$data['penjualan']->anggota_id}}');
  @endif

  function pilih_anggota(id){
    if(id=='show'){
      search_anggota();
      $('#modal-anggota').modal('show');
    }
    else{
      $.get("{{ url('api/find_anggota') }}/"+id,function(result){
        $('#anggota_id').val(id);
        $('#avatar').html('<img src="'+result.avatar+'" alt="" >');
        $('#no_anggota').html('<span>No. '+result.no_anggota+'</span>');
        $('#nama_lengkap').html('<h5>'+result.nama_lengkap+'</h5>');
        $('#fid_anggota').val(result.no_anggota);
        $('#gaji_pokok').val(accounting.formatNumber(result.gaji_pokok,0,'.',','));
        $('#bulan').val(result.bulan);
        $('#bulan_gaji').html('Bulan '+result.bulan_tampil);
        calc_items();
        $('#modal-anggota').modal('hide');
      });
    }
  }

  function detail_angsuran(id){
    $.get("{{ url('api/find_anggota') }}/"+id,function(result){
      $('#detail_angsuran #no_anggota').html('<span>No. '+result.no_anggota+'</span>');
      $('#detail_angsuran #nama_lengkap').html('<h5>'+result.nama_lengkap+'</h5>');
      $('#detail_angsuran #avatar').html('<img src="'+result.avatar+'" alt="" >');
      $('#detail_angsuran #angsuran_simpanan').html('Rp 350.000');
      $('#detail_angsuran #setoran_berkala').html('Rp '+accounting.formatNumber(result.setoran_berkala,0,'.',','));
      total_angsuran_pinjaman=result.angsuran_jangka_panjang+result.angsuran_jangka_pendek+result.angsuran_barang;
      $('#detail_angsuran #angsuran_pinjaman').html('Rp '+accounting.formatNumber(total_angsuran_pinjaman,0,'.',','));
      total_angsuran_belanja=result.angsuran_belanja_toko+result.angsuran_belanja_online+result.angsuran_belanja_konsinyasi;
      $('#detail_angsuran #angsuran_belanja').html('Rp '+accounting.formatNumber(total_angsuran_belanja,0,'.',','));
      total_angsuran=total_angsuran_pinjaman+total_angsuran_belanja+result.setoran_berkala+350000;
      $('#detail_angsuran #total_angsuran').html('Rp '+accounting.formatNumber(total_angsuran,0,'.',','));
      $('#detail_angsuran').modal('show');
    });
  }

  function calc_items(){
    $.get("{{ url('api/get_items_penjualan/'.$id) }}",function (result) {
      subtotal=0;

      $.each(result,function(i,value){
        jumlah=$('#jumlah_'+value.id).val();
        diskon=$('#diskon_'+value.id).val();
        harga_diskon=value.harga_jual - (value.harga_jual*diskon/100);
        harga=harga_diskon*jumlah;
        $('#total_'+value.id).html(accounting.formatNumber(harga,0,'.',','));
        subtotal=subtotal+harga;
      });

      $('#subtotal').val(accounting.formatNumber(subtotal,0,'.',','));
      diskon=$('#diskon').val()*subtotal/100;
      total=subtotal-diskon;
      $('#total').val(accounting.formatNumber(total,0,'.',','));

      gaji_pokok =$('#gaji_pokok').val();
      gaji_pokok = gaji_pokok.split('.').join('');

      metode_pembayaran=$('#metode_pembayaran').val();

      $.get("{{ url('api/find_metode_pembayaran') }}/"+metode_pembayaran,function(result){
        if(result.fid_metode_pembayaran==1){
          tunai=$('#tunai').val();
          tunai=tunai.split('.').join('')
          $('#kembali').val(accounting.formatNumber(tunai-total,0,'.',','));
          $('#alert').hide();
        }
        else if(result.fid_metode_pembayaran==3){
          $('#alert').show();
          anggota_id = $('#anggota_id').val();
          if(anggota_id != 0 ){
            $.get("{{ url('api/find_anggota') }}/"+anggota_id,function(result2){
              total_angsuran_pinjaman=result2.angsuran_jangka_panjang+result2.angsuran_jangka_pendek+result2.angsuran_barang;
              total_angsuran_belanja=result2.angsuran_belanja_toko+result2.angsuran_belanja_online+result2.angsuran_belanja_konsinyasi+total;
              total_angsuran=total_angsuran_pinjaman+total_angsuran_belanja+350000+result2.setoran_berkala;
              if(total_angsuran > gaji_pokok){
                color='danger';
                note='Belum bisa belanja dengan pembayaran secara kredit sebesar Rp '+accounting.formatNumber(total,0,'.',',')+', karena total angsuran per bulan melebihi gaji pokok sebesar Rp '+accounting.formatNumber(gaji_pokok,0,'.',',');
                link='<div class="mt-2"><a href="javascript:;" style="color:#b80000" onclick="detail_angsuran('+anggota_id+')">Lihat Detail Angsuran</a></div>';
                $("[name='action']").prop('disabled', true);
              }
              else{
                if(total_angsuran_belanja > 1500000){
                  color='danger';
                  note='Belum bisa belanja dengan pembayaran secara kredit sebesar Rp '+accounting.formatNumber(total,0,'.',',')+', karena angsuran kredit belanja melebihi limit Rp 1.500.000';
                  link='<div class="mt-2"><a href="javascript:;" style="color:#b80000" onclick="detail_angsuran('+anggota_id+')">Lihat Detail Angsuran</a></div>';
                  $("[name='action']").prop('disabled', true);
                }
                else{
                  color='success';
                  note='Silahkan lanjutkan belanja dengan pembayaran secara kredit sebesar Rp '+accounting.formatNumber(total,0,'.',',');
                  link='';
                  $("[name='action']").prop('disabled', false);
                }
              }
              alert='<div class="alert alert-'+color+'" role="alert">'+
                      note+' '+link
                    '</div>';
              $('#alert').html(alert);
            });
          }
        }
        else{
          $('#alert').hide();
        }
      });
    });
  }

  $('#metode_pembayaran').on('change', function() {
    id=this.value;
    $.get("{{ url('api/find_metode_pembayaran') }}/"+id,function(result){
      $('.form-payment').hide();
      $('#form_'+result.fid_metode_pembayaran).show();
      if(result.fid_metode_pembayaran==7){
        $('#payment_online_name').html(result.keterangan);
      }
      calc_items();
    });
  });
  </script>
@endsection
