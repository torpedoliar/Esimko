<div class="table-responsive mt-4 mb-4">
    <table class="table table-middle table-custom">
        <thead>
        <tr>
            <th>No. Retur Penjualan</th>
            <th>Tanggal</th>
            <th>Barang</th>
            <th>Kategori</th>
            <th style="text-align:right">Harga</th>
            <th style="text-align:right">Jumlah</th>
            <th style="text-align:right">Subtotal</th>
            <th>Satuan</th>
        </tr>
        </thead>
        <tbody>
        @php($temp = '')
        @php($total_before = 0)
        @php($total = 0)
        @foreach ($retur_penjualan as $key => $value)
            @if($temp != $value->retur_penjualan->fid_anggota)
                @if($total_before > 0)
                    <tr>
                        <td colspan="6" style="text-align: right;"><b>Sub Total</b></td>
                        <td style="text-align: right;"><b>{{ format_number($total_before) }}</b></td>
                        <td></td>
                    </tr>
                @endif
                <tr>
                    <td colspan="8">
                        <b>{{ $value->retur_penjualan->fid_anggota ?? '' }} - {{ $value->retur_penjualan->anggota->nama_lengkap ?? '' }}, {{ $value->retur_penjualan->penjualan->metode_pembayaran->keterangan ?? '' }}</b>
                    </td>
                </tr>
                @php($total_before = 0)
            @endif
            <tr>
                <td>{{ $value->retur_penjualan->no_retur ?? '' }}</td>
                <td>{{ format_date($value->retur_penjualan->created_at ?? '') }}</td>
                <td>{{ $value->produk->nama_produk }} - {{ $value->produk->kode }}</td>
                <td>{{ $value->produk->kategori_produk->nama_kategori ?? '' }}</td>
                <td style="text-align:right">{{ format_number($value->produk->harga_jual) }}</td>
                <td style="text-align:right">{{ format_number($value->jumlah) }}</td>
                <td style="text-align:right">{{ format_number($value->produk->harga_jual * $value->jumlah) }}</td>
                <td>{{ $value->produk->satuan_barang->satuan }}</td>
            </tr>
            @php($temp = $value->retur_penjualan->fid_anggota)
            @php($total_before += ($value->produk->harga_jual * $value->jumlah))
            @php($total += ($value->produk->harga_jual * $value->jumlah))
        @endforeach
        <tr>
            <td colspan="6" style="text-align: right;"><b>Sub Total</b></td>
            <td style="text-align: right;"><b>{{ format_number($total_before) }}</b></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="6"><b>TOTAL</b></td>
            <td style="text-align:right"><b>{{ format_number($total) }}</b></td>
            <td></td>
        </tr>
        </tbody>
    </table>
</div>
{{ $retur_penjualan->links('include.custom', ['function' => 'search_data']) }}
