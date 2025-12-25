<table class="table table-bordered table-sm">
    <thead>
    <tr>
        <th colspan="5">{{ $akun->kode_tampil . ' - ' . $akun->nama }}</th>
    </tr>
    <tr>
        <th>Tanggal</th>
        <th>Keterangan</th>
        <th>Debit</th>
        <th>Kredit</th>
        <th>Saldo</th>
    </tr>
    </thead>
    <tbody>
    @php($saldo = 0)
    @foreach($jurnals as $jurnal)
        @php($saldo += ($jurnal->nominal))
        <tr>
            <td>{{ format_date($jurnal->jurnal->tanggal) }}</td>
            <td>{{ $jurnal->jurnal->keterangan }}</td>
            <td class="text-right">{{ ($jurnal->debit) }}</td>
            <td class="text-right">{{ ($jurnal->kredit) }}</td>
            <td class="text-right">{{ ($saldo) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
