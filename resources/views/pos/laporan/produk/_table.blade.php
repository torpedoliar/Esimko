<div class="table-responsive mt-4 mb-4">
    <table class="table table-middle table-custom">
        <thead>
        <tr>
            <th>Kode / Nama Produk</th>
            <th class="center">Kategori Produk</th>
            <th class="center">Stok<br>Awal</th>
            <th class="center">Stok<br>Masuk</th>
            <th class="center">Stok<br>Keluar</th>
            <th class="center">Penyesuaian<br>Stok</th>
            <th class="center">Sisa<br>Stok</th>
            <th style="text-align:right">Harga Beli</th>
            <th style="text-align:right">Margin</th>
            <th style="text-align:right">Harga Jual</th>
        </tr>
        </thead>
        <tbody>
        @php($total_stok_awal = 0)
        @php($total_stok_masuk = 0)
        @php($total_stok_keluar = 0)
        @php($total_stok_peny = 0)
        @php($total_stok_sisa = 0)
        @foreach ($produk as $key => $value)
            <tr>
                <td>
                    <div class="media">
                        <div class="rounded mr-3 produk-wrapper" style="height:50px;width:50px">
                            <img src="{{(!empty($value->foto) ? asset('storage/'.$value->foto) : asset('assets/images/produk-default.jpg')) }}" alt="" />
                        </div>
                        <div class="align-self-center media-body">
                            <span>Kode. {{$value->kode}}</span>
                            <h6>{{$value->nama_produk}}</h6>
                        </div>
                    </div>
                </td>
                <td class="center">
                    <div style="font-weight:600">{{$value->kelompok}}</div>
                    <div>{{$value->kategori_produk->nama_kategori}}</div>
                    <div class="text-muted">{{$value->sub_kategori}}</div>
                </td>
                <td class="center">{{$value->stok['stok_awal']}}<br>{{$value->satuan_barang->satuan}}</td>
                <td class="center">{{ $value->stok['pembelian'] - $value->stok['retur'] }}<br>{{$value->satuan_barang->satuan}}</td>
                <td class="center">{{$value->stok['terjual']}}<br>{{$value->satuan_barang->satuan}}</td>
                <td class="center">{{$value->stok['penyesuaian']}}<br>{{$value->satuan_barang->satuan}}</td>
                <td class="center">{{$value->stok['sisa']}}<br>{{$value->satuan_barang->satuan}}</td>
                <td style="text-align:right;white-space:nowrap">Rp {{number_format($value->harga_beli,0,',','.')}}</td>
                <td style="text-align:right;white-space:nowrap">({{$value->margin}}%)<br>Rp {{number_format($value->margin_nominal,0,',','.')}}</td>
                <td style="text-align:right;white-space:nowrap">Rp {{number_format($value->harga_jual,0,',','.')}}</td>
            </tr>
            @php($total_stok_awal += $value->stok['stok_awal'])
            @php($total_stok_masuk += ($value->stok['pembelian'] - $value->stok['retur']))
            @php($total_stok_keluar += ($value->stok['terjual']))
            @php($total_stok_peny += $value->stok['penyesuaian'])
            @php($total_stok_sisa += $value->stok['sisa'])
        @endforeach
        <tr>
            <th colspan="2"><b>TOTAL</b></th>
            <th class="text-center">{{ format_number($total_stok_awal) }}</th>
            <th class="text-center">{{ format_number($total_stok_masuk) }}</th>
            <th class="text-center">{{ format_number($total_stok_keluar) }}</th>
            <th class="text-center">{{ format_number($total_stok_peny) }}</th>
            <th class="text-center">{{ format_number($total_stok_sisa) }}</th>
            <th class="text-right">{{ format_number($produk->sum('harga_beli')) }}</th>
            <th class="text-right">{{ format_number($produk->sum('margin_nominal')) }}</th>
            <th class="text-right">{{ format_number($produk->sum('harga_jual')) }}</th>
        </tr>
        </tbody>
    </table>
</div>
{{ $produk->links('include.custom', ['function' => 'search_data']) }}
