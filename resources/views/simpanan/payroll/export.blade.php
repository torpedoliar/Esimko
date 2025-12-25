<table>
    <thead>
    <tr>
        <th>No. Anggota<hr class="line-xs">Nama Lengkap</th>
        @foreach ($data['jenis-simpanan'] as $key => $value)
            <th style="text-align:right;width:120px">Jumlah {{str_replace('Setoran','',$value->jenis_transaksi)}}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach ($data['payroll']->data as $key => $value)
        <tr>
            <td>
                <div class="media">
                    <div class="avatar-thumbnail avatar-sm rounded-circle mr-2">
                        <img src="{{(!empty($value->avatar) ? asset('storage/'.$value->avatar) : asset('assets/images/user-avatar-placeholder.png') )}}" alt="" class="rounded-circle">
                    </div>
                    <div class="media-body align-self-center">
                        <p class="text-muted mb-0">No. {{$value->no_anggota}}</p>
                        <h5 class="text-truncate font-size-15"><a href="{{url('anggota/detail?id='.$value->id)}}" class="text-dark">{{$value->nama_lengkap}}</a></h5>
                    </div>
                </div>
            </td>
            @foreach ($data['jenis-simpanan'] as $key2 => $value2)
                @php
                    $label=str_replace(' ','_',strtolower($value2->jenis_transaksi));
                @endphp
                <td style="text-align:right">{{number_format($value->$label,'0',',','.')}}</td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>
