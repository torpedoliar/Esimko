<table class="table table-middle table-custom">
    <thead>
    <tr>
        <th>No. Anggota</th>
        <th>Nama Lengkap</th>
        <th style="text-align:right;width:150px">Simpanan<br>Pokok</th>
        <th style="text-align:right;width:150px">Simpanan<br>Wajib</th>
        <th style="text-align:right;width:150px">Simpanan<br>Sukarela</th>
        <th style="text-align:right;width:150px">Simpanan<br>Hari Raya</th>
        <th style="text-align:right;width:150px">Total Saldo</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($data as $key => $value)
        <tr>
            
            <td>{{ $value->no_anggota }}</td>
            <td>{{ $value->nama_lengkap }}</td>
            <td style="text-align:right">{{ $value->simpanan_pokok }}</td>
            <td style="text-align:right">{{ $value->simpanan_wajib }}</td>
            <td style="text-align:right">{{ round($value->simpanan_sukarela, 0) }}</td>
            <td style="text-align:right">{{ $value->simpanan_hari_raya }}</td>
            <td style="text-align:right">{{ round($value->total_simpanan) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
