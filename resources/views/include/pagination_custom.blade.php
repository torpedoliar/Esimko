@php
    $url=url('simpanan/buku_simpanan?anggota='.$request->anggota.'&jenis='.$request->jenis.'&tanggal='.$request->tanggal.'&offset='.$request->offset.'&spacing='.$request->spacing);
    if($data['pagetotal'] <= 10){
        $awal = 1;
        $akhir = $data['pagetotal'];

        $shortAwal = false;
        $shortAkhir = false;
    }else{
        if(($data['pageposition']+5) >= $data['pagetotal']){
            $awal = $data['pagetotal']-7;
            $akhir = $data['pagetotal'];
            $shortAwal = true;
            $shortAkhir = false;
        }elseif($data['pageposition'] > 7){
            $awal = $data['pageposition']-4;
            $akhir = $data['pageposition']+4;
            $shortAwal = true;
            $shortAkhir = true;
        }else{
            $awal = 1;
            $akhir = 10;
            $shortAwal = false;
            $shortAkhir = true;
        }
    }
@endphp

<div class="row">
    <div class="col-sm-6">
        <ul class="pagination">
            <li class="page-item"><a @if($data['pageposition'] != 1) href="{{$url}}" @endif class="page-link"><i class="mdi mdi-chevron-double-left"></i></a></li>
            <li class="page-item"><a @if($data['pageposition'] != 1) href="{{$url}}&page={{$data['pageposition']-1}}" @endif class="page-link"><i class="mdi mdi-chevron-left"></i></a></li>
            @if($shortAwal == true)
                <li class="page-item"><a class="page-link">...</a></li>
            @endif
            @for($i=$awal; $i <= $akhir; $i++)
                <li class="page-item @if($data['pageposition']==$i) active @endif"><a href="{{$url}}&page={{$i}}" class="page-link">{{ $i }}</a></li>
            @endfor
            @if($shortAkhir == true)
                <li class="page-item"><a class="page-link">...</a></li>
            @endif
            <li class="page-item"><a @if($data['pageposition'] != $data['pagetotal']) href="{{$url}}&page={{$data['pageposition']+1}}" @endif class="page-link"><i class="mdi mdi-chevron-right"></i></a></li>
            <li class="page-item"><a @if($data['pageposition'] != $data['pagetotal']) href="{{$url}}&page={{$data['pagetotal']}}" @endif class="page-link"><i class="mdi mdi-chevron-double-right"></i></a></li>
        </ul>
    </div>
    <div class="col-sm-6 text-right">
        <p style="margin-bottom: 0;margin-top: 10px;">Page {{ $data['pageposition'] }} of {{ $data['pagetotal'] }} from {{ $data['datatotal'] }} entries</p>
    </div>
</div>
