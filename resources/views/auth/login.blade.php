<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Macbook Nusantara</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body{font-family:'Space Grotesk',sans-serif}</style>
</head>
<body class="bg-[#050505] text-white min-h-screen flex items-center justify-center p-4">
<div class="w-full max-w-md">
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold tracking-tighter">Macbook Nusantara</h1>
        <p class="text-gray-500 mt-2 tracking-widest text-xs uppercase">Premium • Original • Garansi Resmi</p>
    </div>
    <div class="bg-[#121212]/80 backdrop-blur border border-white/10 p-8 rounded-[24px] shadow-[0_20px_80px_rgba(0,0,0,0.8)]">
        @if(request()->boolean('registered'))<p class="text-green-400 text-sm mb-4">Pendaftaran berhasil. Silakan login sebagai Pelanggan.</p>@endif
        @if(!$loginRole)
            <h2 class="text-xl font-bold mb-2">Pilih portal login</h2>
            <p class="text-gray-400 text-sm mb-6">Setiap role memiliki sesi login terpisah, sehingga dapat dibuka bersamaan di tab browser berbeda.</p>
            <div class="space-y-3">
                <a href="{{ route('admin.login') }}" class="block w-full text-center bg-white text-black p-3.5 rounded-xl font-bold hover:bg-gray-200">LOGIN ADMIN</a>
                <a href="{{ route('sales.login') }}" class="block w-full text-center bg-gray-800 text-white p-3.5 rounded-xl font-bold hover:bg-gray-700">LOGIN SALES</a>
                <a href="{{ route('customer.login') }}" class="block w-full text-center bg-gray-800 text-white p-3.5 rounded-xl font-bold hover:bg-gray-700">LOGIN PELANGGAN</a>
            </div>
            <div class="mt-6 text-center text-sm text-gray-400">Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-white underline">Daftar pelanggan</a></div>
        @else
            @php($roleLabels = ['admin' => 'ADMIN', 'sales' => 'SALES', 'customer' => 'PELANGGAN'])
            <div class="flex items-center justify-between mb-6">
                <div><p class="text-xs tracking-widest text-gray-400 uppercase">Portal {{ $roleLabels[$loginRole] }}</p><h2 class="text-2xl font-bold mt-1">Masuk akun</h2></div>
                <a href="{{ route('login') }}" class="text-sm text-gray-400 underline">Ganti role</a>
            </div>
            <form method="POST" action="{{ route($loginRole.'.login.store') }}">
                @csrf
                <div class="mb-4"><label class="text-[11px] tracking-widest text-gray-400 uppercase">Email</label><input type="email" name="email" value="{{ old('email') }}" class="w-full mt-2 p-3.5 rounded-xl bg-black/50 border border-white/10 text-white focus:border-white focus:outline-none" placeholder="email@toko.com" required autofocus></div>
                <div class="mb-6"><label class="text-[11px] tracking-widest text-gray-400 uppercase">Password</label><input type="password" name="password" class="w-full mt-2 p-3.5 rounded-xl bg-black/50 border border-white/10 text-white focus:border-white focus:outline-none" placeholder="••••••••" required></div>
                <button type="submit" class="w-full bg-white text-black p-3.5 rounded-xl font-bold tracking-widest hover:bg-gray-200 transition">LOGIN {{ $roleLabels[$loginRole] }}</button>
            </form>
            @if($loginRole==='customer')<div class="mt-6 text-center text-sm text-gray-400">Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-white underline">Daftar sekarang</a></div>@endif
            @if($errors->any())<p class="text-red-400 text-xs mt-4 text-center">{{ $errors->first() }}</p>@endif
        @endif
    </div>
</div>
</body>
</html>
