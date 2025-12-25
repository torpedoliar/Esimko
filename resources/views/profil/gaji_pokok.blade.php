<div class="card">
  <div class="card-header">
    <h5>Riwayat Gaji Pokok</h5>
  </div>
  <div class="card-body">
    @if(count($data['gaji-pokok'])==0)

    @else
    <table class="table table-bordered table-middle mb-0">
      <thead>
        <tr class="thead-light">
          <th class="center">Bulan</th>
          <th style="text-align:right">Gaji Pokok</th>
          <th class="center">Slip Gaji</th>
          <th>Created By</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($data['gaji-pokok'] as $key => $value)
          <tr >
            <td class="center" >{{$value->bulan}}</td>
            <td style="text-align:right">Rp {{number_format($value->gaji_pokok,'0',',','.')}}</td>
            <td></td>
            <td>
              <h6>{{$value->nama_lengkap}}</h6>
              at {{\App\Helpers\GlobalHelper::tgl_indo($value->created_at)}} {{\App\Helpers\GlobalHelper::dateFormat($value->created_at,'H:i:s')}}
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
    @endif
  </div>
</div>
