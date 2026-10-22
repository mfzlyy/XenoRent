@if ($paginator->hasPages())
    <nav class="pagination-nav" role="navigation" aria-label="Navigasi halaman">
        @if ($paginator->onFirstPage())
            <span aria-disabled="true">Sebelumnya</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev">Sebelumnya</a>
        @endif

        <div class="pagination-pages">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span aria-disabled="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" aria-label="Halaman {{ $page }}">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" aria-label="Ke halaman {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya</a>
        @else
            <span aria-disabled="true">Berikutnya</span>
        @endif

        <p class="pagination-summary">
            Menampilkan {{ $paginator->firstItem() }} hingga {{ $paginator->lastItem() }} dari {{ $paginator->total() }} data
        </p>
    </nav>
@endif
