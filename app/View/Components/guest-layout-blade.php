<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Toko Macbook Nusantara</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0a0a0a] text-white font-sans antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        <div class="mb-6 text-center">
            <h1 class="text-3xl font-bold tracking-tight">🍎 Toko Macbook Nusantara</h1>
            <p class="text-gray-400 text-sm mt-1">Premium • Original • Garansi Resmi</p>
        </div>
        <div class="w-full sm:max-w-md mt-2 px-8 py-8 bg-[#1a1a1a] border border-gray-800 shadow-2xl rounded-2xl">
            {{ $slot }}
        </div>
    </div>
</body>
</html>