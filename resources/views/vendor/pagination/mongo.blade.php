@php($function = $function ?? 'search_data')
<?php
$page_count = round(($total ?? 1) / ($limit ?? 1));
$limit_page = 7;

$start = $page ?? 1;
if ($start >= 1) $start = $start - ($page_count % $limit_page);
if ($start <= 0) $start = 1;
$end = $start + $limit_page;
if ($end > $page_count) $end = $page_count;
?>

@if($page !== null && $limit !== null && $page_count > 1)
    <nav>
        <ul class="pagination">
            @if($page === 1)
                <li class="page-item disabled">
                    <a class="page-link" href="javascript:void(0)" rel="prev">&lsaquo;</a>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="javascript:void(0)" onclick="{{ "$function('-1')" }}" rel="prev">&lsaquo;</a>
                </li>
            @endif

            @if($start > 1)
                    <li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="{{ "$function(1)" }}">1</a></li>
                    <li class="page-item disabled"><a class="page-link" href="javascript:void(0)" >...</a></li>
            @endif
            @for($i = $start; $i <= $end; $i++)
                @if($page === $i)
                    <li class="page-item active"><a class="page-link" href="javascript:void(0)" >{{ $i }}</a></li>
                @else
                    <li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="{{ "$function($i)" }}">{{ $i }}</a></li>
                @endif
            @endfor
            @if($page_count > $end)
                <li class="page-item disabled"><a class="page-link" href="javascript:void(0)" >...</a></li>
                <li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="{{ "$function($page_count)" }}">{{ $page_count }}</a></li>
            @endif

            @if($page === $total)
                <li class="page-item">
                    <a class="page-link disabled" href="javascript:void(0)">&rsaquo;</a>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="javascript:void(0)" onclick="{{ "$function('+1')" }}" rel="next" aria-label="@lang('pagination.next')">&rsaquo;</a>
                </li>
            @endif
        </ul>
    </nav>
@endif

{{--@if ($paginator->hasPages())--}}
{{--    <nav>--}}
{{--        <ul class="pagination">--}}
{{--            --}}{{-- Previous Page Link --}}
{{--            @if ($paginator->onFirstPage())--}}
{{--                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">--}}
{{--                    <span class="page-link" aria-hidden="true">&lsaquo;</span>--}}
{{--                </li>--}}
{{--            @else--}}
{{--                <li class="page-item">--}}
{{--                    <a class="page-link" href="javascript:void(0)" onclick="{{ "$function('-1')" }}" rel="prev" aria-label="@lang('pagination.previous')">&lsaquo;</a>--}}
{{--                </li>--}}
{{--            @endif--}}

{{--            --}}{{-- Pagination Elements --}}
{{--            @foreach ($elements as $element)--}}
{{--                --}}{{-- "Three Dots" Separator --}}
{{--                @if (is_string($element))--}}
{{--                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>--}}
{{--                @endif--}}

{{--                --}}{{-- Array Of Links --}}
{{--                @if (is_array($element))--}}
{{--                    @foreach ($element as $page => $url)--}}
{{--                        @if ($page == $paginator->currentPage())--}}
{{--                            <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>--}}
{{--                        @else--}}
{{--                            <li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="{{ "$function($page)" }}">{{ $page }}</a></li>--}}
{{--                        @endif--}}
{{--                    @endforeach--}}
{{--                @endif--}}
{{--            @endforeach--}}

{{--            --}}{{-- Next Page Link --}}
{{--            @if ($paginator->hasMorePages())--}}
{{--                <li class="page-item">--}}
{{--                    <a class="page-link" href="javascript:void(0)" onclick="{{ "$function('+1')" }}" rel="next" aria-label="@lang('pagination.next')">&rsaquo;</a>--}}
{{--                </li>--}}
{{--            @else--}}
{{--                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">--}}
{{--                    <span class="page-link" aria-hidden="true">&rsaquo;</span>--}}
{{--                </li>--}}
{{--            @endif--}}
{{--        </ul>--}}
{{--    </nav>--}}
{{--@endif--}}
