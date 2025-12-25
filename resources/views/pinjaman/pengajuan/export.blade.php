<table>
    <thead>
    <tr>
        <th colspan="11">PERIODE : {{ $bulan != 'all' ? list_bulan()[$bulan] : '' }} {{ $tahun }}</th>
    </tr>
    <tr>
        <th class="center">Tanggal</th>
        <th>No.Anggota</th>
        <th>Nama Lengkap</th>
        <th class="center" width="200px">Jenis Pinjaman</th>
        <th style="text-align:right">Jumlah Pinjaman</th>
        <th style="text-align:right">Total Angsuran</th>
        <th style="text-align:right">Sisa Pinjaman</th>
        <th style="text-align:right">Sisa Tenor</th>
        <th style="text-align:right">Angsuran Ke</th>
        <th style="text-align:right">Angsuran Total</th>
        <th style="text-align:right">Status</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($data as $key => $value)
        <tr>
            <td class="center" style="width:1px;white-space:nowrap;border-color:{{$value->color}}">{{\App\Helpers\GlobalHelper::dateFormat($value->tanggal,'d/m/Y')}}</td>
            <td>{{$value->fid_anggota}}</td>
            <td>{{$value->nama_lengkap}}</td>
            <td class="center">{{$value->jenis_transaksi}}</td>
            <td style="text-align:right;white-space:nowrap">Rp {{ format_number(str_replace('-','',$value->nominal)) }}</td>
            <td style="text-align:right;white-space:nowrap">Rp {{ format_number(str_replace('-','',$value->total_angsuran)) }}</td>
            <td style="text-align:right;white-space:nowrap"><h6>Rp {{ format_number(str_replace('-','',$value->sisa_pinjaman)) }}</h6></td>
            <td style="text-align: right">{{$value->sisa_tenor}}</td>
            <td style="text-align: right">{{$value->tenor-$value->sisa_tenor}}</td>
            <td>{{$value->tenor}}</td>
            <td style="text-align: right">{{ $value->status }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
