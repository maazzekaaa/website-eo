@if ($page->hasPages())
  <nav class="pager" aria-label="Halaman">
    @if ($page->onFirstPage())<span></span>@else<a class="btn btn-sm btn-light" href="{{ $page->previousPageUrl() }}">← Sebelumnya</a>@endif
    <span class="meta">Halaman {{ $page->currentPage() }} dari {{ $page->lastPage() }}</span>
    @if ($page->hasMorePages())<a class="btn btn-sm btn-light" href="{{ $page->nextPageUrl() }}">Berikutnya →</a>@else<span></span>@endif
  </nav>
@endif
