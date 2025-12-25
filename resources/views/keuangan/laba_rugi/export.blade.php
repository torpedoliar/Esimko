<table class="table table-sm table-bordered">
    <thead>
    <tr>
        <th>Akun</th>
        <th class="text-right">Nominal</th>
    </tr>
    </thead>
    <tbody>
    @php($laba = 0)
    @php($pendapatan = 0)
    @php($pengeluaran = 0)
    @foreach($list_akun as $value)
        @if($value->kode_tampil == '4')
            <tr class="border-top">
                <td><b>Total Pendapatan</b></td>
                <td class="text-right">{{ format_number($pendapatan) }}</td>
            </tr>
            <tr>
                <td colspan="2">&nbsp;</td>
            </tr>
        @endif

        @php($laba += ($value->nominal))
        @if(substr($value->kode_tampil, 0, 1) == '3')
            @php($pendapatan += (-1 * $value->nominal))
        @endif
        @if(substr($value->kode_tampil, 0, 1) == '4')
            @php($pengeluaran += ($value->nominal))
        @endif
        <tr>
            <td>@for($i = 3; $i < strlen($value->kode); $i++) &nbsp; @endfor {{ $value->kode_tampil . ' - ' . $value->nama }}</td>
            <td class="text-right">{{ (substr($value->kode_tampil, 0, 1) == '3' ? (-1 * $value->nominal) : $value->nominal) }}</td>
        </tr>
    @endforeach
    <tr class="border-top">
        <td><b>Total Pengeluaran</b></td>
        <td class="text-right">{{ ($pengeluaran) }}</td>
    </tr>
    <tr>
        <td colspan="2"></td>
    </tr>
    <tr class="border-top">
        <td><b>Laba/rugi</b></td>
        <td class="text-right">{{ ($pendapatan - $pengeluaran) }}</td>
    </tr>
    </tbody>
</table>
