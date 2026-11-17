@extends('layouts.portal')
@section('title', 'Akun Sales')
@section('content')
<div class="mb-4">
    <h1 class="h3">Kelola Akun Sales</h1>
    <p class="text-secondary">Hanya Admin yang dapat membuat akun Sales dan mengganti passwordnya.</p>
</div>
@if(session('status'))
    <div class="alert alert-success" role="alert">{{ session('status') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger" role="alert">
        <strong>Periksa data berikut:</strong>
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif
<div class="card mb-4"><div class="card-body">
    <h2 class="h5 mb-3">Buat Akun Sales Baru</h2>
    <form method="POST" action="{{ portal_route('admin.sales-accounts.store') }}" autocomplete="off">
        @csrf
        <div class="mb-3"><label class="form-label" for="sales_name">Nama</label>
            <input id="sales_name" name="name" class="form-control" required maxlength="255" value="{{ old('name') }}"></div>
        <div class="mb-3"><label class="form-label" for="sales_email">Email untuk Login</label>
            <input id="sales_email" type="email" name="email" class="form-control" required maxlength="255" value="{{ old('email') }}"></div>
        <div class="mb-3"><label class="form-label" for="sales_password">Password (minimal 10 karakter)</label>
            <input id="sales_password" type="password" name="password" class="form-control" required minlength="10" autocomplete="new-password"></div>
        <div class="mb-3"><label class="form-label" for="sales_password_confirmation">Ulangi Password</label>
            <input id="sales_password_confirmation" type="password" name="password_confirmation" class="form-control" required minlength="10" autocomplete="new-password"></div>
        <button type="submit" class="btn btn-primary">Buat Akun Sales</button>
    </form>
</div></div>
<div class="card"><div class="card-body">
    <h2 class="h5 mb-3">Akun Sales yang Ada</h2>
    @forelse($salesUsers as $salesUser)
        <div class="border rounded p-3 mb-3">
            <strong>{{ $salesUser->name }}</strong>
            <div class="small text-secondary mb-2">{{ $salesUser->email }}</div>
            <form method="POST" action="{{ portal_route('admin.sales-accounts.password', $salesUser) }}" autocomplete="off">
                @csrf
                @method('PUT')
                <div class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Password baru (min. 10 karakter)</label>
                        <input type="password" class="form-control" name="password" minlength="10" required autocomplete="new-password">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Ulangi password</label>
                        <input type="password" class="form-control" name="password_confirmation" minlength="10" required autocomplete="new-password">
                    </div>
                    <div class="col-md-2"><button class="btn btn-outline-primary w-100" type="submit">Ganti</button></div>
                </div>
            </form>
        </div>
    @empty
        <p class="text-secondary mb-0">Belum ada akun Sales. Buat akun lewat form di atas.</p>
    @endforelse
</div></div>
@endsection
