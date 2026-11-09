<div class="filters" role="group" aria-label="Filter kategori" data-reveal>
  <span class="filters-thumb" aria-hidden="true"></span>
  <button class="filter-btn" type="button" data-filter="Semua" aria-pressed="true">Semua</button>
  @foreach (\App\Models\Portfolio::CATEGORIES as $category)
    <button class="filter-btn" type="button" data-filter="{{ $category }}" aria-pressed="false">{{ $category }}</button>
  @endforeach
</div>
