
    <h3 style="text-align: center;margin: 0;">Kopkar Satya Sejahtera</h3>
    <h1 style="text-align: center;margin: 0;">LAPORAN STOK PRODUK</h1>
<table class="table table-bordered table-sm">
    <thead>
    <tr>
        <th>Kode Produk</th>
        <th>Nama Produk</th>
        <th class="center">Kategori Produk</th>
        <th class="center">Stok Awal</th>
        <th class="center">Stok Masuk</th>
        <th class="center">Stok Keluar</th>
        <th class="center">Sisa Penyesuaian</th>
        <th class="center">Sisa Stok</th>
        <th class="center">Satuan</th>
        <th style="text-align:right">Harga Beli</th>
        <th style="text-align:right">Margin %</th>
        <th style="text-align:right">Margin Rp.</th>
        <th style="text-align:right">Harga Jual</th>
    </tr>
    </thead>
    <tbody>
    @php($total_stok_awal = 0)
    @php($total_stok_masuk = 0)
    @php($total_stok_keluar = 0)
    @php($total_stok_peny = 0)
    @php($total_stok_sisa = 0)
    @php($total_harga_beli = 0)
    @foreach ($produk as $key => $value)
        <tr>
            <td>'{{$value['kode'] ?? ''}}</td>
            <td>{{$value['nama_produk'] ?? ''}}</td>
            <td>{{ $value['kategori_produk']['nama_kategori'] }}</td>
            <td class="center">{{$value['stok']['stok_awal']}}</td>
            <td class="center">{{ $value['stok']['pembelian'] - $value['stok']['retur'] }}</td>
            <td class="center">{{$value['stok']['terjual']}}</td>
            <td class="center">{{$value['stok']['penyesuaian']}}</td>
            <td class="center">{{$value['stok']['sisa']}}</td>
            <td>{{$value['satuan_barang']['satuan']}}</td>
            <td style="text-align:right;white-space:nowrap">{{ $value['harga_beli'] }}</td>
            <td style="text-align:right;white-space:nowrap">{{$value['margin']}}%</td>
            <td>{{ $value['margin_nominal']}}</td>
            <td style="text-align:right;white-space:nowrap">{{ $value['harga_jual'] }}</td>
        </tr>
        @php($total_stok_awal += $value['stok']['stok_awal'])
        @php($total_stok_masuk += ($value['stok']['pembelian'] - $value['stok']['retur']))
        @php($total_stok_keluar += ($value['stok']['terjual']))
        @php($total_stok_peny += $value['stok']['penyesuaian'])
        @php($total_stok_sisa += $value['stok']['sisa'])
    @endforeach
    <tr>
        <th colspan="3"><b>TOTAL</b></th>
        <th class="text-left">{{ ($total_stok_awal) }}</th>
        <th class="text-left">{{ ($total_stok_masuk) }}</th>
        <th class="text-left">{{ ($total_stok_keluar) }}</th>
        <th class="text-left">{{ ($total_stok_peny) }}</th>
        <th class="text-left">{{ ($total_stok_sisa) }}</th>
        <th></th>
        <th class="text-right">{{ array_sum(array_column($produk, 'harga_beli')) }}</th>
        <th></th>
        <th class="text-right">{{ array_sum(array_column($produk, 'margin_nominal')) }}</th>
        <th class="text-right">{{ array_sum(array_column($produk, 'harga_jual')) }}</th>
    </tr>
    </tbody>
</table>

