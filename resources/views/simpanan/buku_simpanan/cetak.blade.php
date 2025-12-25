<html lang="en">
<head>
    <style media="all">
        body {
            font-family: "Courier New", Courier, monospace!important;
        }
        #table_main {
            margin-left: 0;
            font-size: 7pt;
        }
        /*th {*/
        /*    border-left: 1px solid #eaeaea;*/
        /*    border-right: 1px solid #eaeaea;*/
        /*}*/

        th.bg-gray {
            background-color: #fff;
            line-height: 10pt;
        }
        td {
            line-height: 12pt;
            vertical-align: middle;
        }
    </style>
</head>
<body>
<table id="table_main" cellpadding="0" style="margin-top: 1.9cm;">
    @php($index = 0)
    @for($i = 0; $i < 25; $i++)
        @if($i == 11)
            <tr><th colspan="7" style="padding-top: 0.2cm">&nbsp;</th></tr>
            <tr><th colspan="7" style="padding-top: 0.2cm">&nbsp;</th></tr>
        @endif
        <tr>
            @php($item = $data_cetak[$i] ?? [])
            @if(!empty($item))
                <th style="width: 0.6cm;text-align: left;padding-left: 1cm;" class="bg-gray">{{ $item['nomor'] }}</th>
                <th style="width: 2cm;text-align: right;padding-left: 0.1cm">{{ date('d-m-y', strtotime($item['tanggal'])) }}</th>
                <th style="width: 3.5cm;">{{ $item['sandi'] }}</th>
                <th style="width: 2cm;text-align: right;">{{ format_number($item['debit'] * -1) }}</th>
                <th style="width: 2cm;text-align: right;">&nbsp; {{ format_number($item['kredit']) }}</th>
                <th style="width: 2.1cm;text-align: right;">&nbsp; {{ $index == 0 ? format_number($item['saldo']) : format_number($item['saldo']) }}</th>
                <th style="width: 1.9cm;">{{ $item['operator'] }}</th>

                @php($index++)
            @else
                <th colspan="7" style="padding-top: 0.1cm">&nbsp;</th>
            @endif
        </tr
    @endfor
</table>

</body>
</html>
