<div class="row mt-4">
  <div class="col-md-9">
    @if(!empty($data['detail-transaksi']))
    <div class="card">
      <div class="card-body">
        @if(!empty($data['keterangan']))
        <div class="center mb-5">
          <img src="{{asset('assets/images/'.$data['detail-transaksi']->icon)}}" style="width:80px">
          <h4 class="mt-3">{{$data['keterangan']->label}}</h4>
          <p>{{$data['keterangan']->keterangan}}</p>
        </div>
        @else
          <div class="center mb-5">
            <img src="{{asset('assets/images/canceled.png')}}" style="width:80px">
            <h4 class="mt-3">Transaksi Dibatalkan</h4>
            <p>Transaksi ini sudah dibatalkan, silahkan melakukan transaksi yang lain</p>
          </div>
        @endif
        <h5 class="mb-3">Informasi Transaksi</h5>
        <table class="table table-informasi">
          <tr>
            <th width="180px">No. Anggota</th>
            <th width="10px">:</th>
            <td>{{$data['detail-transaksi']->no_anggota}}</td>
          </tr>
          <tr>
            <th>Nama Lengkap</th>
            <th>:</th>
            <td>{{$data['detail-transaksi']->nama_lengkap}}</td>
          </tr>
          <tr>
            <th>Jenis Transaksi</th>
            <th>:</th>
            <td>{{$data['detail-transaksi']->jenis_transaksi}}</td>
          </tr>
          <tr>
            <th>Metode Transaksi</th>
            <th>:</th>
            <td>{{$data['detail-transaksi']->metode_transaksi}}</td>
          </tr>
          <tr>
            <th>Jumlah Simpanan</th>
            <th>:</th>
            <td>Rp {{number_format($data['detail-transaksi']->nominal,0,',','.')}}</td>
          </tr>
          <tr>
            <th>Keterangan</th>
            <th>:</th>
            <td>{{(!empty($data['detail-transaksi']->keterangan) ? $data['detail-transaksi']->keterangan : 'Tidak ada keterangan')}}</td>
          </tr>
        </table>
        <h5 class="mb-3 mt-4">Riwayat Transaksi</h5>
        <ul class="verti-timeline list-unstyled">
          <li class="event-list">
            <div class="event-timeline-dot">
              <i class="bx bx-right-arrow-circle"></i>
            </div>
            <h6>{{\App\Helpers\GlobalHelper::tgl_indo($data['detail-transaksi']->created_at)}}, {{\App\Helpers\GlobalHelper::dateFormat($data['detail-transaksi']->created_at,'H:i:s')}}</h6>
            <p class="text-muted">Transaksi dibuat oleh <span style="font-weight:500">{{$data['detail-transaksi']->nama_petugas}}</span></p>
          </li>
          @foreach (\App\Helpers\GlobalHelper::get_verifikasi_transaksi($id,'transaksi') as $key => $value)
          <li class="event-list">
            <div class="event-timeline-dot">
              <i class="bx bx-right-arrow-circle"></i>
            </div>
            <h6>{{\App\Helpers\GlobalHelper::tgl_indo($value->created_at)}}, {{\App\Helpers\GlobalHelper::dateFormat($value->created_at,'H:i:s')}}</h6>
            <p class="text-muted">{{$value->caption}} <span style="font-weight:500">{{$value->nama_lengkap}}</span></p>
          </li>
          @endforeach
        </ul>
      </div>
      <div class="card-footer">
        <a href="{{url('simpanan')}}" class="btn btn-dark pull-right">Kembali</a>
      </div>
    </div>
    @else
    <form action="{{url('main/transaksi/filter')}}" method="post" class="mt-4 mb-4" id="filter_transaksi">
      @php($filter=Session::get('filter_transaksi'))
      {{ csrf_field() }}
      <div class="row">
        <div class="col-md-3">
          <select name="jenis" class="form-control select2" onchange="javascript:submit()">
            <option value="all"  >Semua Jenis Transaksi</option>
            @foreach ($data['jenis-transaksi'] as $key => $value)
            <option value="{{$value->id}}" {{(!empty($filter['pinjaman']) ? ($filter['pinjaman']['jenis']==$value->id ? 'selected' : '') : '' )}}  >{{$value->jenis_transaksi}}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <input type="hidden" id="status_id" name="status" value="{{(!empty($filter['pinjaman']) ? $filter['pinjaman']['status'] : 'all' )}}">
          <select class="select2-status" id="status_color" style="width:100%" onchange="pilih_status()">
            <option value="#282828" data-id="all">Semua Status</option>
            @foreach ($data['status-transaksi'] as $key => $value)
            <option value="{{$value->color}}" {{(!empty($filter['pinjaman']) ? ($filter['pinjaman']['status']==$value->id ? 'selected' : '') : '' )}}  data-id="{{ $value->id}}" >{{$value->status}}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-6">
          <div>
            <div class="input-daterange input-group" data-date-format="dd-mm-yyyy" data-provide="datepicker">
              <input type="text" class="form-control" value="{{(!empty($filter['pinjaman']) ? $filter['pinjaman']['from'] : '' )}}" autocomplete="off" id="from" onchange="javascript:submit()" name="from" placeholder="Dari Tanggal" />
              <input type="text" class="form-control" value="{{(!empty($filter['pinjaman']) ? $filter['pinjaman']['to'] : '' )}}" autocomplete="off" id="to" onchange="javascript:submit()" name="to" placeholder="Sampai Tanggal" />
            </div>
          </div>
        </div>
      </div>
      <input type="hidden" name="modul" value="pinjaman">
    </form>
    <hr>
    @if(count($data['transaksi'])==0)
    <div style="width:100%;text-align:center" class="mb-5 mt-4">
      <img src="{{asset('assets/images/not-found.png')}}" class="mt-3" style="width:180px">
      <h5 class="mt-3">Data Simpanan Tidak Ditemukan</h5>
    </div>
    @else
    <div class="table-responsive mt-4">
      <table class="table table-middle table-custom">
        <thead>
          <tr>
            <th class="center">Tanggal</th>
            <th>Jenis Pinjaman</th>
            <th style="text-align:right">Jumlah<br>Pinjaman<br></th>
            <th style="text-align:right">Total<br>Angsuran<br></th>
            <th class="center">Sisa<br>Tenor</th>
            <th style="text-align:right">Sisa<br>Pinjaman<br></th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach ($data['transaksi'] as $key => $value)
            <tr>
              <td class="center" style="width:1px;white-space:nowrap;border-color:{{$value->color}}">{{\App\Helpers\GlobalHelper::dateFormat($value->tanggal,'d/m/Y')}}</td>
              <td style="font-weight:500">{{$value->jenis_transaksi}}</td>
              <td style="text-align:right">{{number_format(str_replace('-','',$value->nominal),0,',','.')}}</td>
              <td style="text-align:right">{{number_format(str_replace('-','',$value->total_angsuran),0,',','.')}}</td>
              <td class="center">{{$value->sisa_tenor}} dari {{$value->tenor}}</td>
              <td style="text-align:right">{{number_format(str_replace('-','',$value->sisa_pinjaman),0,',','.')}}</td>
              <td style="width:1px;white-space:nowrap">
                <div class="text-center">
                  <a href="{{url('anggota/detail?anggota='.$data['anggota']->no_anggota.'&tab='.$tab.'&id='.$value->id)}}" class="text-dark"><i class="bx bx-search h3 m-0"></i></a>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="mb-4">
      {{ $data['transaksi']->links('include.pagination', ['pagination' => $data['transaksi']] ) }}
    </div>
    @endif
    @endif
  </div>
  <div class="col-md-3">
    <div class="mb-4 pt-4">
      @foreach ($data['jenis-transaksi'] as $key => $value)
        <div class="form-group">
          <p class="text-muted text-truncate mb-2">Sisa {{$value->jenis_transaksi}}</p>
          <div class="font-size-15">Rp {{number_format($value->sisa_pinjaman,0,'.',',')}}</div>
        </div>
      @endforeach
      <div class="form-group">
        <p class="text-muted text-truncate mb-2">Total Sisa Pinjaman</p>
        <div class="font-size-15">Rp {{number_format($data['total-sisa'],0,'.',',')}}</div>
      </div>
    </div>
  </div>
</div>
