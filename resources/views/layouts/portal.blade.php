<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title','Dashboard') · Macbook Nusantara</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{background:#f5f7fb;color:#172033}.navbar{background:#111827}.navbar-brand{font-weight:800;letter-spacing:.2px}
        .page{max-width:1200px;margin:28px auto;padding:0 18px}.hero{background:linear-gradient(120deg,#111827,#334155);color:#fff;border-radius:20px;padding:28px}
        .stat{border:0;border-radius:16px;box-shadow:0 6px 22px #1f29370d}.table-card{border:0;border-radius:16px;overflow:hidden;box-shadow:0 6px 22px #1f29370d}
        .btn-primary{background:#2563eb}.muted{color:#6b7280}.form-card{max-width:850px;margin:auto}.nav-link{color:#e5e7eb}.nav-link:hover{color:#fff}.badge{font-weight:600}
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid px-lg-5">
        <a class="navbar-brand" href="{{ portal_route('dashboard') }}">Macbook <span class="text-info">Nusantara</span></a>
        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav me-auto">
                @auth
                    <li class="nav-item"><a class="nav-link" href="{{ portal_route('dashboard') }}">Dashboard</a></li>
                    @if(in_array(auth()->user()->role,['admin','sales']))
                        <li class="nav-item"><a class="nav-link" href="{{ portal_route('products.index') }}">Inventory</a></li>
                    @endif
                    <li class="nav-item"><a class="nav-link" href="{{ portal_route('products.catalog') }}">Katalog</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ portal_route('sales.index') }}">Penjualan</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ portal_route('warranties.index') }}">Garansi</a></li>
                    @if(auth()->user()->role==='admin')
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.sales-accounts.index') }}">Kelola Akun Sales</a></li>
                    @endif
                @endauth
            </ul>
            <div class="d-flex align-items-center gap-3 text-white">
                @auth
                    <span class="small">{{ auth()->user()->name }} · {{ ucfirst(auth()->user()->role) }}</span>
                    <form method="POST" action="{{ portal_route('logout') }}">@csrf<button class="btn btn-sm btn-outline-light">Keluar</button></form>
                @else
                    <a class="btn btn-sm btn-outline-light" href="{{ route('login') }}">Login</a>
                    <a class="btn btn-sm btn-info" href="{{ route('register') }}">Daftar pelanggan</a>
                @endauth
            </div>
        </div>
    </div>
</nav>
<main class="page">
    @if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><strong>Periksa input:</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @yield('content')
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
