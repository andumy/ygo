<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="https://cdn.tailwindcss.com"></script>

        <title>YGO Tracker</title>
    </head>
    <body class="dark:bg-gray-100">
    <div class="w-full flex px-10 pt-10">
        <a href="/" class="text-blue-500 hover:text-blue-800 pe-5">Library</a>
        <a href="/sets-instances" class="text-blue-500 hover:text-blue-800 ps-5">Sets and Instances</a>
    </div>
        {{ $slot }}
    </body>
</html>
