<table>
    <thead>
    <tr>
        <th colspan="11">PERIODE : {{ $bulan != 'all' ? list_bulan()[$bulan] : '' }} {{ $tahun }}</th>
    </tr>
    <tr>
        <th rowspan="2">NO</th>
        <th rowspan="2">TANGGAL</th>
        <th rowspan="2">NO. ANGGOTA</th>
        <th rowspan="2">NAMA<br>ANGGOTA</th>
        <th rowspan="2">NOMINAL<br>PINJAMAN</th>
        <th rowspan="2">JML<br>ANGSURAN</th>
        <th colspan="4">{{ $bulan != 'all' ? list_bulan()[$bulan] : $tahun }}</th>
    </tr>
    <tr>
        <th>POKOK</th>
        <th>BUNGA</th>
        <th>TOTAL</th>
        <th>KE</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $key => $value)
        <tr>
            <td>{{ $key+1 }}</td>
            <td>{{ format_date($value->tanggal) }}</td>
            <td>{{ $value->no_anggota ?? '-' }}</td>
            <td>{{ $value->nama_lengkap ?? '-' }}</td>
            <td>{{ $value->nominal }}</td>
            <td>{{ $value->tenor }}</td>
            <td>{{ $value->pokok }}</td>
            <td>{{ $value->bunga }}</td>
            <td>{{ intval($value->pokok) + intval($value->bunga) }}</td>
            <td>{{ $value->angsuran_ke }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
