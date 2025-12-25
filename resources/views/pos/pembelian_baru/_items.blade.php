<table class="table table-middle table-hover m-0 mt-3">
    <thead>
    <tr style="background:#eff2f7">
        <th width="20px">No</th>
        <th>Nama Barang</th>
        <th class="center" style="width:100px">Jumlah</th>
        <th style="text-align:center;width:120px">Harga Beli<br>Satuan</th>
        <th style="text-align:center;width:120px">Harga Jual<br>Satuan</th>
        <th style="text-align:center;width:120px">Margin<br>Penjualan</th>
        <th style="text-align:right;width:120px">Sub Total</th>
        <th style="width:50px"></th>
    </tr>
    </thead>
    <tbody>
    @foreach($items as $key => $item)
        <tr>
            <td>{{ $key+1 }}</td>
            <td>
                <div class="media">
                    <div class="rounded mr-3 produk-wrapper" style="height:50px;width:50px;border:1px solid #e4e4e4">
                        <img src="{{ $item->produk->foto_url ?? '' }}" alt="" />
                    </div>
                    <div class="align-self-center media-body">
                        <span>Kode. {{ $item->produk->kode ?? '' }}</span>
                        <h6>{{ $item->produk->nama_produk ?? '' }}</h6>
                    </div>
                </div>
            </td>
            <td class="align-middle" style="width: 80px;">
                <input id="jumlah_{{ $item->id }}" value="{{ $item->jumlah }}" class="form-control text-center autonumeric" type="text" onchange="update_item('{{ $item->id }}')" data-a-dec="," data-a-sep="." />
            </td>
            <td class="align-middle">
                <input id="harga_{{ $item->id }}" value="{{ ($item->harga) }}" class="form-control text-center autonumeric" type="text" onchange="update_item('{{ $item->id }}')" data-a-dec="," data-a-sep="." />
            </td>
            <td class="align-middle">
                <input id="harga_jual_{{ $item->id }}" value="{{ ($item->harga_jual) }}" class="form-control text-center autonumeric" type="text" onkeyup="hitung_harga_jual({{ $item->id }})" onchange="update_item('{{ $item->id }}')" data-a-dec="," data-a-sep="." />
            </td>
            <td style="text-align:right;width:250px">
                <div style="display:flex">
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                        <input type="text" class="form-control autonumeric" id="margin_nominal_{{ $item->id }}" value="{{ $item->margin_nominal }}" data-a-dec="," data-a-sep="." style="border-radius:0px" onkeyup="hitung_nominal({{ $item->id }})" onchange="update_item('{{ $item->id }}')">
                    </div>
                    <div class="input-group" style="width:150px">
                        <input type="text" class="form-control" style="border-radius:0px;margin-left:-1px;text-align:center" id="margin_{{ $item->id }}" value="{{ $item->margin }}" required="" onkeyup="hitung_persen({{ $item->id }})" onchange="update_item('{{ $item->id }}')">
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="mdi mdi-percent-outline"></i></span>
                        </div>
                    </div>
                </div>
            </td>
            <td class="align-middle text-right">{{ format_number($item->total) }}</td>
            <td style="width:50px">
                <div class="text-center">
                    <a href="javascript:void(0)" onclick="delete_item({{ $item->id }})" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
                </div>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<script>
    sub_total = parseFloat('{{ $items->sum('total') }}');
    hitung_total();
    $('.autonumeric').autoNumeric({mDec: '2',aPad:false,vMin:'-9999999999999999999999999',vMax:'9999999999999999999999999'});

    @if(count($items) == 0)
        $('#button_bayar').hide();
    @else
    $('#button_bayar').show();
    @endif
</script>
