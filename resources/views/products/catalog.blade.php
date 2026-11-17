@extends('layouts.portal')
@section('title', 'Katalog Barang')
@section('content')
<style>
    .catalog-product-card { overflow:hidden; border:1px solid #e7ebf0; border-radius:16px; transition:transform .18s ease, box-shadow .18s ease; }
    .catalog-product-card:hover { transform:translateY(-3px); box-shadow:0 12px 28px #1f29371a; }
    .catalog-card-button { display:block; width:100%; height:100%; padding:0; border:0; background:#fff; color:inherit; text-align:left; border-radius:16px; }
    .catalog-card-button:focus-visible { outline:3px solid #93c5fd; outline-offset:3px; }
    .catalog-cover-photo { display:block; width:100%; height:220px; object-fit:contain; background:#f8fafc; border-bottom:1px solid #e7ebf0; }
    .catalog-cover-placeholder { width:100%; height:220px; display:flex; align-items:center; justify-content:center; background:#f8fafc; color:#94a3b8; border-bottom:1px solid #e7ebf0; }
    .detail-section-title { font-size:.82rem; letter-spacing:.04em; text-transform:uppercase; color:#64748b; font-weight:700; }
    .condition-photo { display:block; width:100%; height:190px; object-fit:cover; background:#f3f5f8; border:1px solid #e5e7eb; border-radius:12px; }
    .detail-text { white-space:pre-line; overflow-wrap:anywhere; }
</style>

<div class="hero mb-4">
    <h1 class="h2">Katalog Barang Tersedia</h1>
    <p class="mb-0 text-white-50">Pilih unit untuk melihat foto kondisi fisik, keterangan, deskripsi, dan spesifikasinya.</p>
</div>

<form method="GET" action="{{ portal_route('products.catalog') }}" class="d-flex gap-2 mb-4">
    <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari merek atau model">
    <button class="btn btn-primary">Cari</button>
</form>

<div class="row g-3">
    @forelse($products as $product)
        <div class="col-md-6 col-lg-4">
            <div class="card stat h-100 catalog-product-card">
                <button
                    type="button"
                    class="catalog-card-button"
                    data-bs-toggle="modal"
                    data-bs-target="#unitDetail{{ $product->id }}"
                    aria-label="Lihat kondisi dan spesifikasi {{ $product->brand }} {{ $product->model }}"
                >
                    @if($product->image_url)
                        <img
                            class="catalog-cover-photo"
                            src="{{ $product->image_url }}"
                            alt="Foto utama {{ $product->brand }} {{ $product->model }}"
                            loading="lazy"
                        >
                    @else
                        <div class="catalog-cover-placeholder">
                            <span>Foto utama unit belum tersedia</span>
                        </div>
                    @endif

                    <div class="card-body p-4">
                        <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                            <span class="badge bg-success">Tersedia</span>
                            <span class="small text-primary fw-semibold">Lihat detail →</span>
                        </div>
                        <h2 class="h5 mb-2">{{ $product->brand }} {{ $product->model }}</h2>
                        <div class="fw-bold fs-5">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</div>
                        <p class="small text-muted mb-0 mt-3">Klik unit untuk melihat foto kondisi fisik, keterangan, deskripsi, dan spesifikasi.</p>
                    </div>
                </button>
            </div>
        </div>

        <div class="modal fade" id="unitDetail{{ $product->id }}" tabindex="-1" aria-labelledby="unitDetailLabel{{ $product->id }}" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 rounded-4 overflow-hidden">
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <div class="detail-section-title">Detail kondisi unit</div>
                            <h2 class="modal-title fs-4 fw-bold" id="unitDetailLabel{{ $product->id }}">{{ $product->brand }} {{ $product->model }}</h2>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <div class="modal-body p-4">
                        <section class="mb-4">
                            <div class="detail-section-title mb-3">Foto kondisi fisik MacBook</div>
                            @if(count($product->condition_image_urls))
                                <div class="row g-3">
                                    @foreach($product->condition_image_urls as $conditionImage)
                                        <div class="col-6 col-md-4">
                                            <a href="{{ $conditionImage }}" target="_blank" rel="noopener" title="Buka foto ukuran penuh">
                                                <img class="condition-photo" src="{{ $conditionImage }}" alt="Foto kondisi {{ $product->brand }} {{ $product->model }}" loading="lazy">
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-light border mb-0">Foto kondisi fisik unit belum diunggah. Silakan hubungi Admin/Sales untuk melengkapi foto unit ini.</div>
                            @endif
                        </section>

                        <section class="mb-4">
                            <div class="detail-section-title mb-2">Keterangan kondisi fisik</div>
                            <div class="detail-text rounded-3 bg-light p-3">{{ $product->notes ?: 'Keterangan kondisi fisik belum ditambahkan.' }}</div>
                        </section>

                        <section>
                            <div class="detail-section-title mb-2">Deskripsi dan spesifikasi</div>
                            <div class="detail-text rounded-3 bg-light p-3">{{ $product->specification ?: 'Deskripsi dan spesifikasi unit belum ditambahkan.' }}</div>
                        </section>
                    </div>

                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Kembali ke katalog</button>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12"><p class="text-muted">Belum ada produk tersedia.</p></div>
    @endforelse
</div>

<div class="mt-3">{{ $products->links() }}</div>
@endsection
