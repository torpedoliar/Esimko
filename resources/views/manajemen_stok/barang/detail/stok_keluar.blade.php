<table class="table table-middle table-custom">
  <thead>
    <tr>
      <th>Tanggal</th>
      <th>Uraian Aktifitas</th>
      <th class="center">Jumlah</th>
      <th style="text-align:right">Harga Beli</th>
      <th style="text-align:right">Margin</th>
      <th style="text-align:right">Harga Jual</th>
      <th>Petugas</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($data['stok-keluar'] as $key => $value)
    <tr>
      <td style="width:1px;white-space:nowrap">{{\App\Helpers\GlobalHelper::dateFormat($value->created_at,'d/m/Y')}}</td>
      <td>{{$value->uraian}}</td>
      <td class="center">{{$value->jumlah}}<br>{{$data['produk']->satuan}}</td>
      <td style="text-align:right">Rp {{number_format($value->harga_beli,0,',','.')}}</td>
      <td style="text-align:right">
        ({{$value->margin}}%)
        <div>Rp {{number_format($value->margin_nominal,0,',','.')}}</div>
      </td>
      <td style="text-align:right">Rp {{number_format($value->harga_jual,0,',','.')}}</td>
      <td>
        <div class="media">
          <div class="avatar-thumbnail avatar-sm rounded-circle mr-2">
            <img src="{{(!empty($value->avatar) ? asset('storage/'.$value->avatar) : asset('assets/images/user-avatar-placeholder.png') )}}" alt="" class="rounded-circle">
          </div>
          <div class="media-body align-self-center">
            <p class="text-muted mb-0">No. {{$value->no_anggota}}</p>
            <h5 class="text-truncate font-size-13">{{$value->nama_lengkap}}</h5>
          </div>
        </div>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>
