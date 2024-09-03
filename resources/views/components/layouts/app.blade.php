<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="https://cdn.tailwindcss.com"></script>

        <title>YGO Tracker</title>
    </head>
    <body class="dark:bg-gray-100">
        {{ $slot }}
    </body>
</html>
