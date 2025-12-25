<div class="row mt-3">
    <div class="col-md-6">
        <h2># Pembelian</h2>
        <table class="table table-middle table-custom">
            <thead>
            <tr>
                <th>No.Pembelian</th>
                <th>Tanggal</th>
                <th class="center">Jumlah</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($pembelian as $key => $value)
                <tr>
                    <td>{{ $value->pembelian->no_pembelian }}</td>
                    <td style="width:1px;white-space:nowrap;border-color:{{$value->color}}">{{ \App\Helpers\GlobalHelper::dateFormat($value->pembelian->tanggal,'d/m/Y') }}</td>
                    <td class="center">{{ $value->jumlah }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2">Total</td>
                <td class="center">{{ $pembelian->sum('jumlah') }}</td>
            </tr>
            </tbody>
        </table>
        <h2 class="mt-3"># Retur Pembelian</h2>
        <table class="table table-middle table-custom">
            <thead>
            <tr>
                <th>No. Retur Pembelian</th>
                <th>Tanggal</th>
                <th class="center">Jumlah</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($retur_pembelian as $key => $value)
                <tr>
                    <td>{{ $value->retur_pembelian->no_retur }}</td>
                    <td style="width:1px;white-space:nowrap;border-color:{{$value->color}}">{{ \App\Helpers\GlobalHelper::dateFormat($value->retur_pembelian->tanggal,'d/m/Y') }}</td>
                    <td class="center">{{ $value->jumlah }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2">Total</td>
                <td class="center">{{ $retur_pembelian->sum('jumlah') }}</td>
            </tr>
            </tbody>
        </table>
        <h2 class="mt-3"># Penyesuaian Stok</h2>
        <table class="table table-middle table-custom">
            <thead>
            <tr>
                <th>Keterangan</th>
                <th>Tanggal</th>
                <th class="center">Jumlah</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($opname as $key => $value)
                <tr>
                    <td>{{ $value->keterangan }}</td>
                    <td style="width:1px;white-space:nowrap;border-color:{{$value->color}}">{{ \App\Helpers\GlobalHelper::dateFormat($value->tanggal,'d/m/Y') }}</td>
                    <td class="center">{{ $value->jumlah }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2">Total</td>
                <td class="center">{{ $opname->sum('jumlah') }}</td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="col-md-6">
        <h2># Penjualan</h2>
        <table class="table table-middle table-custom">
            <thead>
            <tr>
                <th>No.Penjualan</th>
                <th>Tanggal</th>
                <th class="center">Jumlah</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($penjualan as $key => $value)
                <tr>
                    <td>{{ $value->penjualan->no_transaksi }}</td>
                    <td style="width:1px;white-space:nowrap;border-color:{{$value->color}}">{{ \App\Helpers\GlobalHelper::dateFormat($value->penjualan->tanggal,'d/m/Y') }}</td>
                    <td class="center">{{ $value->jumlah }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2">Total</td>
                <td class="center">{{ $penjualan->sum('jumlah') }}</td>
            </tr>
            </tbody>
        </table>
        <h2 class="mt-3"># Retur Penjualan</h2>
        <table class="table table-middle table-custom">
            <thead>
            <tr>
                <th>No. Retur Penjualan</th>
                <th>Tanggal</th>
                <th class="center">Jumlah</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($retur_penjualan as $key => $value)
                <tr>
                    <td>{{ $value->retur_penjualan->no_retur }}</td>
                    <td style="width:1px;white-space:nowrap;border-color:{{$value->color}}">{{ \App\Helpers\GlobalHelper::dateFormat($value->retur_penjualan->tanggal,'d/m/Y') }}</td>
                    <td class="center">{{ $value->jumlah }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2">Total</td>
                <td class="center">{{ $retur_penjualan->sum('jumlah') }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
