<div class="filters filters--pills" role="group" aria-label="Filter kategori" data-reveal style="--d:4">
  <span class="filters-thumb" aria-hidden="true"></span>
  <button class="filter-btn" type="button" data-filter="Semua" aria-pressed="true">Semua <span>{{ $prints->count() }}</span></button>
  @foreach (\App\Models\Portfolio::CATEGORIES as $category)
    <button class="filter-btn" type="button" data-filter="{{ $category }}" aria-pressed="false">{{ $category }} <span>{{ $counts[$category] ?? 0 }}</span></button>
  @endforeach
</div>
