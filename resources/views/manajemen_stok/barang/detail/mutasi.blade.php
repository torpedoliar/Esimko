<table class="table table-middle table-custom">
    <thead>
    <tr>
        <th>Tanggal</th>
        <th>Keterangan</th>
        <th class="text-right">Jumlah</th>
        <th class="text-right">Stok</th>
    </tr>
    </thead>
    <tbody>
    @php($stok = 0)
    @foreach($mutasi as $item)
        @php($stok += $item['jumlah'])
        <tr>
            <td>{{ format_date($item['tanggal']) }}</td>
            <td>{{ $item['keterangan'] }}</td>
            <td class="text-right">{{ $item['jumlah'] }}</td>
            <td class="text-right">{{ $stok }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
