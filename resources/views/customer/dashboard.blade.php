@extends('layouts.portal')
@section('title', 'Produk & Garansi Saya')
@section('content')
<div class="hero mb-4">
    <div class="small text-info text-uppercase fw-bold">Akun pelanggan</div>
    <h1 class="h2 mt-2">Halo, {{ auth()->user()->name }}</h1>
    <p class="mb-0 text-white-50">Produk yang telah dibeli, transaksi, dan klaim garansi tampil otomatis setelah Admin atau Sales mencatat transaksi untuk akun ini.</p>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card stat h-100"><div class="card-body"><div class="muted">Produk saya</div><div class="h2 fw-bold">{{ $sales->count() }}</div></div></div></div>
    <div class="col-md-4"><div class="card stat h-100"><div class="card-body"><div class="muted">Garansi aktif</div><div class="h2 fw-bold">{{ $sales->filter(fn ($s) => $s->warranty && $s->warranty->status === 'active' && $s->warranty->start_date->startOfDay()->lte(today()) && $s->warranty->end_date->startOfDay()->gte(today()))->count() }}</div></div></div></div>
    <div class="col-md-4"><div class="card stat h-100"><div class="card-body"><div class="muted">Klaim terkirim</div><div class="h2 fw-bold">{{ $sales->filter(fn ($s) => $s->warranty && $s->warranty->status === 'claimed')->count() }}</div></div></div></div>
</div>
<h2 class="h4 fw-bold mb-3">Produk yang saya beli</h2>
<div class="row g-3 mb-4">
@forelse($sales as $sale)
    @php
        $w = $sale->warranty;
        $active = $w && $w->status === 'active' && $w->start_date->toDateString() <= today()->toDateString() && $w->end_date->toDateString() >= today()->toDateString();
    @endphp
    <div class="col-md-6 col-xl-4"><div class="card stat h-100"><div class="card-body d-flex flex-column">
        <div class="d-flex justify-content-between align-items-start gap-2 mb-2"><span class="small muted">{{ $sale->invoice_number }}</span>
        @if($w && $w->status === 'claimed')<span class="badge text-bg-warning">Sedang diklaim</span>
        @elseif($active)<span class="badge text-bg-success">Garansi aktif</span>
        @elseif($w)<span class="badge text-bg-secondary">Garansi tidak aktif</span>
        @else<span class="badge text-bg-secondary">Tanpa garansi</span>@endif</div>
        <h3 class="h5 fw-bold mb-1">{{ $sale->product?->brand }} {{ $sale->product?->model }}</h3>
        <div class="small muted mb-2">Serial: <strong>{{ $sale->product?->serial_number ?? '-' }}</strong></div>
        <div class="small mb-1">Dibeli: {{ $sale->sold_at?->format('d M Y') }}</div>
        <div class="small mb-1">Harga: Rp {{ number_format($sale->selling_price, 0, ',', '.') }}</div>
        <div class="small mb-3">Garansi sampai: {{ $w?->end_date?->format('d M Y') ?? '-' }}</div>
        <div class="mt-auto d-flex flex-wrap gap-2"><a class="btn btn-sm btn-outline-dark" href="{{ portal_route('sales.show', $sale) }}">Detail transaksi</a>
        @if($w)<a class="btn btn-sm {{ $active ? 'btn-primary' : 'btn-outline-secondary' }}" href="{{ portal_route('warranties.show', $w) }}">{{ $active ? 'Klaim garansi' : 'Detail garansi' }}</a>@endif</div>
    </div></div></div>
@empty
    <div class="col-12"><div class="card table-card"><div class="card-body py-5 text-center"><h3 class="h5">Belum ada produk terhubung</h3><p class="muted mb-0">Saat Admin/Sales menyimpan transaksi dengan memilih akun pelanggan ini, barang otomatis muncul di sini.</p></div></div></div>
@endforelse
</div>
<div class="card table-card"><div class="card-body">
    <h2 class="h5 fw-bold">Riwayat transaksi</h2>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Invoice</th><th>Produk</th><th>Tanggal</th><th>Status pembayaran</th><th>Detail</th></tr></thead><tbody>
    @forelse($sales as $sale)<tr><td>{{ $sale->invoice_number }}</td><td>{{ $sale->product?->brand }} {{ $sale->product?->model }}</td><td>{{ $sale->sold_at?->format('d M Y') }}</td><td>{{ ucfirst($sale->payment_status) }}</td><td><a href="{{ portal_route('sales.show', $sale) }}">Lihat</a></td></tr>
    @empty<tr><td colspan="5" class="text-center muted py-3">Belum ada transaksi.</td></tr>@endforelse
    </tbody></table></div>
</div></div>
@endsection
