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
    @foreach($jurnal as $value)
        <tr class="{{ $value->balance == true ? '' : 'bg-danger' }}">
            <td>{{ $value->no_jurnal }}</td>
            <td>{{ format_date($value->tanggal) }}</td>
            <td colspan="2">
                {{ $value->keterangan }}
            </td>
            <td class="py-0 align-middle text-right">
                <a href="javascript:void(0)" onclick="info({{ $value->id }})" class="text-dark"><i class="bx bx-edit h3 m-0"></i></a>
                <a href="javascript:void(0)" onclick="delete_data({{ $value->id }})" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
            </td>
        </tr>
        @foreach($value->details as $detail)
            <tr>
                <td></td>
                <td></td>
                <td>{{ $detail->akun->kode_tampil . ' - ' . $detail->akun->nama }}</td>
                <td class="text-right">{{ format_number($detail->debit) }}</td>
                <td class="text-right">{{ format_number($detail->kredit) }}</td>
            </tr>
        @endforeach
    @endforeach
    </tbody>
</table>
{{ $jurnal->links('vendor.pagination.custom') }}
