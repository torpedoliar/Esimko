<div class="table-responsive mt-4 mb-4">
    <table class="table table-custom table-sm">
        <thead>
        <tr>
            <th>Anggota</th>
            <th>No. Penjualan</th>
            <th>Tanggal</th>
            <th>Barang</th>
            <th>Kategori</th>
            <th style="text-align:right">Harga</th>
            <th style="text-align:right">Jumlah</th>
            <th style="text-align:right">Subtotal</th>
            <th>Satuan</th>
            <th style="text-align:right">Laba</th>
        </tr>
        </thead>
        <tbody>
        @php($temp = '')
        @php($temp2 = '')
        @php($total_before = 0)
        @php($total_before2 = 0)
        @php($sub_diskon = 0)
        @php($sub_margin = 0)
        @php($total_diskon = 0)
        @php($total_margin = 0)
        @php($total_diskon2 = 0)
        @foreach ($penjualan as $key => $value)
            @if($key > 0)
                @if($temp2 != $value->fid_penjualan)
                    <tr>
                        <td colspan="7" style="text-align: right;">Diskon</td>
                        <td style="text-align: right;">{{ format_number($sub_diskon) }}</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="7" style="text-align: right;">Total Transaksi</td>
                        <td style="text-align: right;">{{ format_number($total_before2 - $sub_diskon) }}</td>
                        <td></td>
                        <td style="text-align: right;">{{ format_number($sub_margin) }}</td>
                    </tr>
                    @php($total_diskon += $sub_diskon)
                    @php($total_diskon2 += $sub_diskon)
                    @php($total_margin += $sub_margin)
                    @php($sub_diskon = 0)
                    @php($sub_margin = 0)
                    @php($total_before2 = 0)
                @endif
                @if($temp != $value->penjualan->fid_anggota)
                    <tr>
                        <td colspan="7" style="text-align: right;"><b>Sub Diskon</b></td>
                        <td style="text-align: right;"><b>{{ format_number($total_diskon2) }}</b></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="7" style="text-align: right;"><b>Sub Total</b></td>
                        <td style="text-align: right;"><b>{{ format_number($total_before - $total_diskon2) }}</b></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="9"><b>{{ $value->penjualan->fid_anggota ?? '' }} - {{ $value->penjualan->anggota->nama_lengkap ?? '' }}</b></td>
                    </tr>
                    @php($total_before = 0)
                    @php($total_diskon2 = 0)
                @endif
            @else
                <tr>
                    <td colspan="10"><b>{{ $value->penjualan->fid_anggota ?? '' }} - {{ $value->penjualan->anggota->nama_lengkap ?? '' }}</b></td>
                </tr>
            @endif
            <tr>
                <td></td>
                <td>{{ $value->penjualan->no_transaksi ?? '' }}</td>
                <td>{{ format_date($value->penjualan->created_at ?? '') }}</td>
                <td>
                    @if(!empty($value->produk))
                        {{ $value->produk->nama_produk ?? '' }} - {{ $value->produk->kode ?? '' }}
                    @else
                        {{ $value->nama_barang }}
                    @endif
                </td>
                <td>{{ $value->produk->kategori_produk->nama_kategori ?? '' }}</td>
                <td style="text-align:right">{{ format_number($value->harga) }}</td>
                <td style="text-align:right">{{ format_number($value->jumlah) }}</td>
                <td style="text-align:right">{{ format_number($value->total) }}</td>
                <td>{{ $value->produk->satuan_barang->satuan ?? '' }}</td>
                <td style="text-align:right">{{ format_number($value->margin_nominal) }}</td>
            </tr>
            @php($temp = $value->penjualan->fid_anggota)
            @php($temp2 = $value->fid_penjualan)
            @php($total_before += $value->total)
            @php($total_before2 += $value->total)
            @php($sub_diskon = $value->penjualan->diskon)
            @php($sub_margin = $value->margin_nominal)
        @endforeach
        <tr>
            <td colspan="7" style="text-align: right;">Diskon</td>
            <td style="text-align: right;">{{ format_number($sub_diskon) }}</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="7" style="text-align: right;">Total Transaksi</td>
            <td style="text-align: right;">{{ format_number($total_before2 - $sub_diskon) }}</td>
            <td></td>
            <td style="text-align: right;">{{ format_number($sub_margin) }}</td>
        </tr>
        @php($total_diskon += $sub_diskon)
        @php($total_diskon2 += $sub_diskon)
        <tr>
            <td colspan="7" style="text-align: right;"><b>Sub Diskon</b></td>
            <td style="text-align: right;"><b>{{ format_number($total_diskon2) }}</b></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="7" style="text-align: right;"><b>Sub Total</b></td>
            <td style="text-align: right;"><b>{{ format_number($total_before - $total_diskon2) }}</b></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="7" style="text-align: right;"><b>Total</b></td>
            <td style="text-align: right;"><b>{{ format_number($penjualan->sum('total') - $total_diskon) }}</b></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="7" style="text-align: right;"><b>Total Laba</b></td>
            <td style="text-align: right;"><b>{{ format_number($total_margin) }}</b></td>
            <td></td>
            <td></td>
        </tr>
        </tbody>
    </table>
</div>
{{ $penjualan->links('include.custom', ['function' => 'search_data']) }}
