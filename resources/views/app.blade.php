<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css"
        integrity="sha512-1PKOgIY59xJ8Co8+NE6FZ+LOAZKjy+KY8iq0G4B3CyeY6wYHN3yt9PW0XpSriVlkMXe40PTKnXrLnZ9+fkDaog=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="shortcut icon" href="{{ asset('/storage/icon/logo.svg') }}" type="image/x-icon">
    <title inertia>{{ config('app.name', 'Laravel') }}</title>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @routes
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead

</head>

<body class="sb-nav-fixed">
    @inertia
</body>

</html>
