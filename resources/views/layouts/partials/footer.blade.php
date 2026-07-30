@php
  $versionInformation = app(\App\Support\VersionInformation::class);
  $latestVersions = $versionInformation->latest();
  $mysqlVersion = 'Unavailable';

  if (config('database.default') === 'mysql') {
      try {
          $mysqlVersion = \Illuminate\Support\Facades\DB::connection()
              ->getPdo()
              ->getAttribute(\PDO::ATTR_SERVER_VERSION);
      } catch (\Throwable) {
        // Keep the footer available when the database is temporarily unreachable.
      }
  }

  $usedVersions = [
      'laravel' => app()->version(),
      'mysql' => $mysqlVersion,
      'php' => PHP_VERSION,
  ];
@endphp

<!-- Footer -->
<footer class="content-footer footer bg-footer-theme">
  <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
    <div class="mb-2 mb-md-0">
      &copy;
      <script>
        document.write(new Date().getFullYear());
      </script>, Crafted by
      <a href="https://www.facebook.com/rajeshKothekar" target="_blank" class="footer-link fw-bolder">Rajesh</a>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2 small">
      <span title="Versions running in this application">
        Using: Laravel {{ $usedVersions['laravel'] }} &middot; MySQL {{ $usedVersions['mysql'] }} &middot; PHP {{ $usedVersions['php'] }}
      </span>
      <span class="text-muted">|</span>
      <span title="Latest upstream versions, refreshed daily from official release sources">
        Latest:
        <span @class(['text-success fw-semibold' => $versionInformation->isUpgradeAvailable($usedVersions['laravel'], $latestVersions['laravel'])])>Laravel {{ $latestVersions['laravel'] }}</span>
        &middot;
        <span @class(['text-success fw-semibold' => $versionInformation->isUpgradeAvailable($usedVersions['mysql'], $latestVersions['mysql'])])>MySQL {{ $latestVersions['mysql'] }}</span>
        &middot;
        <span @class(['text-success fw-semibold' => $versionInformation->isUpgradeAvailable($usedVersions['php'], $latestVersions['php'])])>PHP {{ $latestVersions['php'] }}</span>
      </span>
    </div>
  </div>
</footer>
<!-- / Footer -->
