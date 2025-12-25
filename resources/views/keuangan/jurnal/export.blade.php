<table class="table table table-bordered table-sm">
    <thead>
    <tr>
        <th>No.Jurnal</th>
        <th>Tanggal</th>
        <th>Akun</th>
        <th class="text-right">Debit</th>
        <th class="text-right">Kredit</th>
    </tr>
    </thead>
    <tbody>
    @foreach($jurnals as $value)
        <tr class="{{ $value->balance == true ? '' : 'bg-danger' }}">
            <td>{{ $value->no_jurnal }}</td>
            <td>{{ format_date($value->tanggal) }}</td>
            <td colspan="2">
                {{ $value->keterangan }}
            </td>
        </tr>
        @foreach($value->details as $detail)
            <tr>
                <td></td>
                <td></td>
                <td>{{ $detail->akun->kode_tampil . ' - ' . $detail->akun->nama }}</td>
                <td class="text-right">{{ ($detail->debit) }}</td>
                <td class="text-right">{{ ($detail->kredit) }}</td>
            </tr>
        @endforeach
    @endforeach
    </tbody>
</table>
