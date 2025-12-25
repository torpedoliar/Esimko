<table>
    <thead>
    <tr>
        <th colspan="11">PERIODE : {{ $bulan  }}</th>
    </tr>
    <tr>
        <th rowspan="2">NO</th>
        <th rowspan="2">JENIS<br>TRANSAKSI</th>
        <th rowspan="2">NO. ANGGOTA</th>
        <th rowspan="2">NAMA<br>ANGGOTA</th>
        <th rowspan="2">NOMINAL<br>PINJAMAN</th>
        <th colspan="6">ANGSURAN</th>
        <th rowspan="2">KETERANGAN</th>
    </tr>
    <tr>
        <th>TENOR</th>
        <th>KE</th>
        <th>SISA</th>
        <th>POKOK</th>
        <th>BUNGA</th>
        <th>TOTAL</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $key => $value)
        <tr>
            <td>{{ $key+1 }}</td>
            <td>{{ $value->transaksi->jenis_transaksi->jenis_transaksi }}</td>
            <td>{{ $value->transaksi->anggota->no_anggota ?? '-' }}</td>
            <td>{{ $value->transaksi->anggota->nama_lengkap ?? '-' }}</td>
            <td>{{ $value->transaksi->nominal * -1 }}</td>
            <td>{{ $value->transaksi->tenor }}</td>
            <td>{{ $value->angsuran_ke }}</td>
            <td>{{ $value->transaksi->tenor - $value->angsuran_ke }}</td>
            <td>{{ \App\Helpers\GlobalHelper::pembulatan_nominal($value->angsuran_pokok) }}</td>
            <td>{{ \App\Helpers\GlobalHelper::pembulatan_nominal($value->angsuran_bunga) }}</td>
            <td>{{ \App\Helpers\GlobalHelper::pembulatan_nominal($value->angsuran_pokok + $value->angsuran_bunga) }}</td>
            <td>{{ $value->transaksi->keterangan ?? '-' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
