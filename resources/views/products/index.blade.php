@extends('layouts.portal')
@section('title', 'Inventaris Serial Number')
@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h1 class="h2 fw-bold">Inventaris Produk</h1>
        <p class="muted mb-0">Satu baris mewakili satu unit fisik dengan serial number unik.</p>
    </div>
    <a class="btn btn-primary" href="{{ portal_route('products.create') }}">+ Tambah unit</a>
</div>
<div class="card table-card">
    <div class="card-body">
        <form class="row g-2 mb-3">
            <div class="col-md-6"><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari model, merek, serial number"></div>
            <div class="col-md-3">
                <select class="form-select" name="status">
                    <option value="">Semua status</option>
                    @foreach(['available'=>'Tersedia','sold'=>'Terjual','service'=>'Servis'] as $k=>$v)
                        <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-dark">Cari</button>
                <a class="btn btn-outline-secondary" href="{{ portal_route('products.index') }}">Reset</a>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Foto</th><th>Serial number</th><th>Produk</th><th>Harga jual</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>
                            @if($product->image_url)
                                <img src="{{ $product->image_url }}" alt="Foto {{ $product->model }}" style="width:64px;height:48px;object-fit:cover;border-radius:7px;">
                            @else
                                <span class="small text-muted">Belum ada</span>
                            @endif
                        </td>
                        <td class="fw-semibold">{{ $product->serial_number }}</td>
                        <td>{{ $product->brand }} {{ $product->model }}<div class="small muted">{{ $product->specification }}</div></td>
                        <td>Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                        <td><span class="badge text-bg-{{ $product->status === 'available' ? 'success' : ($product->status === 'sold' ? 'secondary' : 'warning') }}">{{ ['available'=>'Tersedia','sold'=>'Terjual','service'=>'Servis'][$product->status] }}</span></td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-primary" href="{{ portal_route('products.edit', $product) }}">Edit</a>
                            <form class="d-inline" method="POST" action="{{ portal_route('products.destroy', $product) }}" onsubmit="return confirm('Hapus unit ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center muted py-4">Belum ada produk.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $products->links() }}
    </div>
</div>
@endsection
