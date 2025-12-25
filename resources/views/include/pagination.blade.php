
@php
  if($pagination->lastPage() <= 10){
      $awal = 1;
      $akhir = $pagination->lastPage();
      $shortAwal = false;
      $shortAkhir = false;

  }
  else{
      if(($pagination->currentPage()+5) >= $pagination->lastPage()){
          $awal = $pagination->lastPage()-5;
          $akhir = $pagination->lastPage();
          $shortAwal = true;
          $shortAkhir = false;
      }elseif($pagination->currentPage() > 5){
          $awal = $pagination->currentPage()-2;
          $akhir = $pagination->currentPage()+2;
          $shortAwal = true;
          $shortAkhir = true;
      }else{
          $awal = 1;
          $akhir = 5;
          $shortAwal = false;
          $shortAkhir = true;
      }
  }

@endphp
<div class="row">
    <div class="col-sm-6">
        <ul class="pagination pagination-primary">
            <li class="page-item"><a @if($pagination->currentPage() != 1) href="{{ $pagination->url(1) }}" @endif class="page-link"><i class="fa fa-angle-double-left"></i></a></li>
            <li class="page-item"><a @if($pagination->currentPage() != 1) href="{{ $pagination->url(($pagination->currentPage()-1)) }}" @endif class="page-link"><i class="fa fa-angle-left"></i></a></li>

            @if($shortAwal == true)
              <li class="page-item"><a class="page-link">...</a></li>
            @endif
            @for($i=$awal; $i <= $akhir; $i++)
                <li class="page-item @if($pagination->currentPage()==$i) active @endif"><a href="{{ $pagination->url($i) }}" class="page-link">{{ $i }}</a></li>
            @endfor
            @if($shortAkhir == true)
                <li class="page-item"><a class="page-link">...</a></li>
            @endif

            <li class="page-item"><a @if($pagination->currentPage() != $pagination->lastPage()) href="{{ $pagination->url(($pagination->currentPage()+1)) }}" @endif class="page-link"><i class="fa fa-angle-right"></i></a></li>
            <li class="page-item"><a @if($pagination->currentPage() != $pagination->lastPage()) href="{{ $pagination->url($pagination->lastPage()) }}" @endif class="page-link"><i class="fa fa-angle-double-right"></i></a></li>
        </ul>
    </div>
    <div class="col-sm-6 text-right">
        <p style="margin-bottom: 0;margin-top: 10px;">Page {{ $pagination->currentPage() }} of {{ $pagination->lastPage() }} from {{ $pagination->total() }} entries</p>
    </div>
</div>
