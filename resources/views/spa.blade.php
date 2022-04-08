@php
$config = [
    'appName' => config('app.name'),
    'locale' => $locale = app()->getLocale(),
    'locales' => config('app.locales'),
    'githubAuth' => config('services.github.client_id'),
];
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
  <head>
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ mix('dist/css/app.css') }}">
    <link rel="stylesheet" href="/css/custom.css">
  </head>

  <body style>

    <div id="app"></div>

    <script>
      window.config = @json($config);
      {{--@auth
      window.Permissions = {!! json_encode(Auth::user()->allPermissions, true) !!};
      @else
      window.Permissions = [];
      @endauth--}}
    </script>

    {{-- Load the application scripts --}}
    <script src="{{ mix('dist/js/app.js') }}"></script>
  </body>
</html>
