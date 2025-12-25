@extends('layouts.report')
@section('title')
  Cetak Formulir Anggota
@endsection
@section('css')
  <style>
  .form-container{
    padding: 30px;
  }
  .form-container .header{
    border-bottom: 5px solid #000;
    text-align: center
  }
  .form-container .content{
    font-size: 12pt;
    line-height: 18pt
  }
  .form-container .content table tr td{
    padding:3px
  }
  .line{
    /* border-bottom: 1px solid #373737;
    height:15px */
  }
  </style>
@endsection
@section('content')
<div class="form-container">
  <div class="header">
    <div style="font-weight:600;font-size:25px">KOPERASI KARYAWAN SATYA SEJAHTERA</div>
    <div style="font-weight:600;font-size:35px">PT SANTOS JAYA ABADI</div>
    <p style="font-size:18px;margin-top:0px">Jl. Raya Gilang 159, Taman - Sidoarjo</p>
  </div>
  <div class="content">
    <div style="text-align:center;font-size:20px;font-weight:600;margin-top:20px;margin-bottom:30px;text-decoration:underline">FORMULIR PENDAFTARAN ANGGOTA BARU</div>
    <p>Yang bertanda tangan dibawah ini :</p>
    <table style="width:100%;">
      <tr>
        <td width="150px">No Anggota</td>
        <td width="5px">:</td>
        <td>{!!(!empty($data->no_anggota) ? $data->no_anggota : '<div class="line"></div>')!!}</td>
        <td width="150px">Nama Lengkap</td>
        <td width="5px">:</td>
        <td>{!!(!empty($data->nama_lengkap) ? $data->nama_lengkap : '<div class="line"></div>')!!}</td>
      </tr>
      <tr>
        <td>Divisi</td>
        <td>:</td>
        <td>{!!(!empty($data->divisi) ? $data->divisi : '<div class="line"></div>>')!!}</td>
        <td width="150px">Bagian</td>
        <td width="5px">:</td>
        <td>{!!(!empty($data->bagian) ? $data->bagian : '<div class="line"></div>>')!!}</td>
      </tr>
      <tr>
        <td>Alamat</td>
        <td>:</td>
        <td colspan="4">{!!(!empty($data->alamat) ? $data->alamat : '<div class="line"></div>')!!}</td>
      </tr>
      <tr>
        <td>No. NIK</td>
        <td>:</td>
        <td>{!!(!empty($data->no_ktp) ? $data->no_ktp : '<div class="line"></div>>')!!}</td>
        <td>No. HIRS</td>
        <td>:</td>
        <td>{!!(!empty($data->no_hirs) ? $data->no_hirs : '<div class="line"></div>>')!!}</td>
      </tr>
      <tr>
        <td colspan="3"></td>
        <td>No. Rekening</td>
        <td>:</td>
        <td>{!!(!empty($data->no_rekening) ? $data->no_rekening : '<div class="line"></div>')!!}</td>
      </tr>
    </table>
    <p>Mengajukan permohonan  untuk menjadi anggota Koperasi Karyawan "Satya Sejahtera" PT Santos Jaya Abadi. Atas permohonan tersebut  kami bersedia  mentaati  dan mematuhi segala peraturan perkoperasian yang berlaku.</p>
    <p>Demikian surat permohonan ini kami buat dengan sebenarnya dan kami sampaikan terima kasih.</p>
    <table style="width:100%;margin-bottom:50px;margin-top:50px">
      <tr>
        <td style="width:50%;text-align:center">
          <div>Pemohon</div>
          <div style="height:80px"></div>
          <div style="text-decoration:underline;font-weight:600">{{$data->nama_lengkap}}</div>
        </td>
        <td style="width:50%;text-align:center">
          <div>Sepanjang, {{\App\Helpers\GlobalHelper::tgl_indo($data->created_at)}}</div>
          <div>Petugas</div>
          <div style="height:80px"></div>
          <div style="text-decoration:underline">________________</div>
        </td>
      </tr>
    </table>
    <table style="width:100%">
      <tr>
        <td style="border:1px solid #1a1a1a;padding:15px;width:350px">
          <h4>Persyaratan Keanggotaan Baru</h4>
          <ol>
            <li>Pas Foto ukuran 3 x 3 sebanyak 2 lembar</li>
            <li>Fotocopy KTP sebanyak 1 lembar</li>
            <li>Fotocopy ID Card sebanyak 1 lembar</li>
            <li>Fotocopy Slip Gaji sebanyak 1 lembar</li>
            <li>Fotocopy SK/PKWTT sebanyak 1 lembar</li>
          </ol>
        </td>
        <td style="text-align:right;vertical-align:top">
          <div id="qrcode"></div>
        </td>
      </tr>
    </table>
  </div>
</div>
@endsection
@section('js')
  <script>
  $('#qrcode').qrcode({
    width: 100,
    height: 100,
  	text	: "{{$data->no_anggota}}"
  });
  </script>
@endsection
