<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-4">
                    <label class="text-[11px] tracking-widest text-gray-400 uppercase">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full mt-2 p-3.5 rounded-xl bg-black/50 border border-white/10 text-white focus:border-white focus:outline-none" placeholder="admin@toko.com" required>
                </div>
                <div class="mb-6">
                    <label class="text-[11px] tracking-widest text-gray-400 uppercase">Password</label>
                    <input type="password" name="password" class="w-full mt-2 p-3.5 rounded-xl bg-black/50 border border-white/10 text-white focus:border-white focus:outline-none" placeholder="••••••••" required>
                </div>
                <button type="submit" class="w-full bg-white text-black p-3.5 rounded-xl font-bold tracking-widest hover:bg-gray-200 transition">LOGIN</button>
            </form>
            @if($errors->any())
                <p class="text-red-400 text-xs mt-4 text-center">{{ $errors->first() }}</p>
            @endif
        </div>
    </div>
</body>
</html>