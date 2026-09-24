<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>SIMPM — PG Rendeng</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>

@include('partials.logout-splash')
@include('partials.app-shell')

      {{-- ===== SUPERVISOR ===== --}}
      @include('partials.screens.sup-dashboard')
      @include('partials.screens.sup-master')
      @include('partials.screens.sup-monitoring')
      @include('partials.screens.sup-detail-mesin')
      @include('partials.screens.sup-jadwal')
      @include('partials.screens.sup-jadwal-tambah')
      @include('partials.screens.sup-validasi')
      @include('partials.screens.sup-validasi-detail')
      @include('partials.screens.sup-riwayat')
      @include('partials.screens.sup-laporan')
      @include('partials.screens.sup-profil')

      {{-- ===== TEKNISI ===== --}}
      @include('partials.screens.tek-dashboard')
      @include('partials.screens.tek-detail-jadwal')
      @include('partials.screens.tek-kalender')
      @include('partials.screens.tek-riwayat')
      @include('partials.screens.tek-performa')
      @include('partials.screens.tek-profil')

      {{-- ===== MANAJER ===== --}}
      @include('partials.screens.man-dashboard')
      @include('partials.screens.man-performa')
      @include('partials.screens.man-maintenance')
      @include('partials.screens.man-laporan')
      @include('partials.screens.man-profil')

@include('partials.modal-master')

<script>
  window.__SIMPM_BOOTSTRAP__ = @json($bootstrap);
</script>
<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('js/master-crud.js') }}"></script>
<script src="{{ asset('js/pm-crud.js') }}"></script>
<script src="{{ asset('js/teknisi-pm.js') }}"></script>
<script src="{{ asset('js/role-guard.js') }}"></script>
<script src="{{ asset('js/simpm-hardening.js') }}"></script>
<script src="{{ asset('js/manager-hardening.js') }}"></script>
<script src="{{ asset('js/manager-report-fix.js') }}"></script>
<script src="{{ asset('js/supervisor-report-export.js') }}"></script>
</body>
</html>
