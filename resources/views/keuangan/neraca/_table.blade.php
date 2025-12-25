<div class="d-flex flex-row justify-content-between" style="gap: 20px;">
    <div class="" style="flex-grow: 1">
        <table class="table table-bordered table-sm">
            <thead>
            <tr>
                <th>Akun</th>
                <th class="text-right">Nominal</th>
            </tr>
            </thead>
            <tbody>
            @foreach($list_akun as $value)
                <tr>
                    <td>@for($i = 3; $i < strlen($value->kode); $i++) &nbsp; @endfor {{ $value->kode_tampil . ' - ' . $value->nama }}</td>
                    <td class="text-right">{{ format_number($value->nominal) }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr>
                <td class="font-weight-bold">Total</td>
                <td class="font-weight-bold text-right">{{ format_number($list_akun->sum('nominal')) }}</td>
            </tr>
            </tfoot>
        </table>
    </div>
    <div class="" style="flex-grow: 1">
        <table class="table table-bordered table-sm">
            <thead>
            <tr>
                <th>Akun</th>
                <th class="text-right">Nominal</th>
            </tr>
            </thead>
            <tbody>
            @foreach($list_akun2 as $value)
                <tr>
                    <td>@for($i = 3; $i < strlen($value->kode); $i++) &nbsp; @endfor {{ $value->kode_tampil . ' - ' . $value->nama }}</td>
                    <td class="text-right">{{ format_number($value->nominal * -1) }}</td>
                </tr>
            @endforeach
            <tr>
                <td>Laba Rugi</td>
                <td class="text-right">{{ format_number($laba_rugi) }}</td>
            </tr>
            </tbody>
            <tfoot>
            <tr>
                <td class="font-weight-bold">Total</td>
                <td class="font-weight-bold text-right">{{ format_number(($list_akun2->sum('nominal') * -1) + $laba_rugi) }}</td>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
