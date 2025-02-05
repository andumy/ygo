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
        <a href="/orders" class="text-blue-500 hover:text-blue-800 ps-5">Orders</a>
        <a href="/tradable" class="text-blue-500 hover:text-blue-800 ps-5">Tradable</a>
        <a href="/purchase-recommendation" class="text-blue-500 hover:text-blue-800 ps-5">Purchase Recommendation</a>
        <a href="/bulk" class="text-blue-500 hover:text-blue-800 ps-5">Bulk upload</a>
    </div>
        {{ $slot }}
    <script>
        function copyName(selector) {
            const name = document.getElementById(selector).innerText;
            navigator.clipboard.writeText(name);
        }
    </script>
    </body>
</html>
