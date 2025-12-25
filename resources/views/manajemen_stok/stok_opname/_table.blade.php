<div class="table-responsive mt-4 mb-4">
    <table class="table table-middle table-custom">
        <thead>
        <tr>
            <th>Tanggal</th>
            <th>Barang</th>
            <th class="center">Harga Beli</th>
            <th class="center">Jumlah</th>
            <th class="center">Jenis</th>
            <th>Keterangan</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach ($stokOpname as $key => $value)
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
                <td class="center">{{$value->hpp}}</td>
                <td class="center">{{$value->jumlah}}</td>
                <td class="center">{{$value->jenis}}</td>
                <td>{{$value->keterangan}}</td>
                <td style="width:1px;white-space:nowrap">
                    <a href="{{url('manajemen_stok/stok_opname/'. $value->id .'/edit?page='.$stokOpname->currentPage())}}" class="text-dark"><i class="bx bx-edit h3 m-0"></i></a>
                    <a href="javascript:;" onclick="confirmDelete({{ $value->id }})" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
                    <form action="{{url('manajemen_stok/stok_opname/' . $value->id. '?page=' . $stokOpname->currentPage())}}" method="post" id="hapus{{$value->id}}">
                        {{ csrf_field()}}
                        <input type="hidden" name="id" value="{{$value->id}}">
                        <input type="hidden" name="_method" value="delete">
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{ $stokOpname->links('include.custom', ['function' => 'search_data']) }}
