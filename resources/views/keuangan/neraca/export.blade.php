<table class="table table-bordered table-sm">
    <thead>
    <tr>
        <th>Akun</th>
        <th class="text-right">Nominal</th>
        <th></th>
        <th>Akun</th>
        <th class="text-right">Nominal</th>
    </tr>
    </thead>
    <tbody>
    @php($labarugi = false)
    @foreach($list_akun as $key => $value)
        <tr>
            <td>@for($i = 3; $i < strlen($value->kode); $i++) &nbsp; @endfor {{ $value->kode_tampil . ' - ' . $value->nama }}</td>
            <td class="text-right">{{ format_number($value->nominal) }}</td>
            <td>&nbsp;</td>
            @php($value2 = $list_akun2[$key] ?? [])
            @if(!empty($value2))
                <td>@for($i = 3; $i < strlen($value2->kode); $i++) &nbsp; @endfor {{ $value2->kode_tampil . ' - ' . $value2->nama }}</td>
                <td class="text-right">{{ ($value2->nominal * -1) }}</td>
            @endif
            @if(empty($value2) && $labarugi === false)
                @php($labarugi = true)
                <td>Laba Rugi</td>
                <td class="text-right">{{ format_number($laba_rugi) }}</td>
            @endif
        </tr>
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <td class="font-weight-bold">Total</td>
        <td class="font-weight-bold text-right">{{ ($list_akun->sum('nominal')) }}</td>
        <td></td>
        <td class="font-weight-bold">Total</td>
        <td class="font-weight-bold text-right">{{ (($list_akun2->sum('nominal') * -1) + $laba_rugi) }}</td>
    </tr>
    </tfoot>
</table>
