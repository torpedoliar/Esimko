<div class="table-responsive mt-4 mb-4">
    <table class="table table-middle table-custom">
        <thead>
        <tr>
            <th>Tanggal</th>
            <th>Barang</th>
            <th class="center">Jumlah</th>
            <th class="center">Jumlah * Harga</th>
            <th class="center">Jenis</th>
            <th>Keterangan</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($penyesuaian as $key => $value)
            <tr>
                <td style="width:1px;white-space:nowrap;border-color:{{$value->color}}">{{\App\Helpers\GlobalHelper::dateFormat($value->tanggal,'d/m/Y')}}</td>
                <td>
                    <div class="media">
                        <div class="rounded mr-3 produk-wrapper" style="height:50px;width:50px">
                            <img src="{{(!empty($value->produk->foto) ? asset('storage/'.$value->produk->foto) : asset('assets/images/produk-default.jpg')) }}" alt="" />
                        </div>
                        <div class="align-self-center media-body">
                            <span>Kode. {{$value->produk->kode ?? ''}}</span>
                            <h6>{{$value->produk->nama_produk ?? ''}}</h6>
                        </div>
                    </div>
                </td>
                <td class="center">{{$value->jumlah}}</td>
                <td class="center">{{format_number($value->jumlah * $value->hpp)}}</td>
                <td class="center">{{$value->jenis}}</td>
                <td>{{$value->keterangan}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{ $penyesuaian->links('include.custom', ['function' => 'search_data']) }}
