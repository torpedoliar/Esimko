<table>
    <thead>
    <tr>
        <th>No. Anggota</th>
        <th>Nama Lengkap</th>
        @foreach ($list_jenis_simpanan as  $value)
            <th style="text-align:right;">Jumlah {{str_replace('Setoran','',$value->jenis_transaksi)}}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach ($anggota as $key => $value)
        <tr>
            <td>{{ $value->no_anggota }}</td>
            <td>{{ $value->nama_lengkap }}</td>
            @foreach ($list_jenis_simpanan as $item)
                <td style="text-align: right">{{ number_format($value->data_simpanan[$item->id] ?? 0, 0, ',', '.') }}</td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>
