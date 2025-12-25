<div class="table-responsive mt-4 mb-4">
    <table class="table table-middle table-custom">
        <thead>
        <tr>
            <th>Kode / Nama Produk</th>
            <th class="center">Kategori Produk</th>
            <th style="text-align:right">Harga Beli</th>
            <th style="text-align:right">Margin</th>
            <th style="text-align:right">Harga Jual</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($produk as $key => $value)
            @if($key == 0)
                <script>
                    produk_pertama = '{{ $value->kode ?? '' }}';
                </script>
            @endif
            <tr onclick="pilih_produk('{{ $value->kode ?? '' }}')">
                <td>
                    <div class="media">
                        <div class="rounded mr-3 produk-wrapper" style="height:50px;width:50px">
                            <img src="{{ $value->foto_url }}" alt="" />
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
                <td style="text-align:right;white-space:nowrap">Rp {{number_format($value->harga_beli,0,',','.')}}</td>
                <td style="text-align:right;white-space:nowrap">({{$value->margin}}%)<br>Rp {{number_format($value->margin_nominal,0,',','.')}}</td>
                <td style="text-align:right;white-space:nowrap">Rp {{number_format($value->harga_jual,0,',','.')}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
