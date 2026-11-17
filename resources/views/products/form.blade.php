@extends('layouts.portal')
@section('title', $product->exists ? 'Edit Produk' : 'Tambah Produk')
@section('content')
<div class="card table-card form-card">
    <div class="card-body p-4">
        <a href="{{ portal_route('products.index') }}" class="text-decoration-none">← Kembali ke inventaris</a>
        <h1 class="h3 fw-bold mt-3">{{ $product->exists ? 'Edit unit' : 'Tambah unit baru' }}</h1>
        <p class="muted">Lengkapi foto kondisi fisik, keterangan, deskripsi, dan spesifikasi MacBook agar pelanggan dapat melihat detail unit dari katalog.</p>

        <form method="POST" enctype="multipart/form-data" action="{{ $product->exists ? portal_route('products.update', $product) : portal_route('products.store') }}">
            @csrf
            @if($product->exists) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Serial number *</label>
                    <input class="form-control" name="serial_number" value="{{ old('serial_number', $product->serial_number) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Merek *</label>
                    <input class="form-control" name="brand" value="{{ old('brand', $product->brand ?? 'Apple') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Model *</label>
                    <input class="form-control" name="model" value="{{ old('model', $product->model) }}" placeholder="MacBook Air M3 13-inch" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Harga beli (Rp) *</label>
                    <input type="text" inputmode="numeric" autocomplete="off" data-money class="form-control" name="purchase_price" value="{{ old('purchase_price', $product->purchase_price ?? 0) }}" placeholder="Contoh: 12.500.000" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Harga jual (Rp) *</label>
                    <input type="text" inputmode="numeric" autocomplete="off" data-money class="form-control" name="selling_price" value="{{ old('selling_price', $product->selling_price) }}" placeholder="Contoh: 15.000.000" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status inventaris *</label>
                    <select class="form-select" name="status">
                        <option value="available" @selected(old('status', $product->status ?? 'available') === 'available')>Tersedia</option>
                        <option value="service" @selected(old('status', $product->status) === 'service')>Servis</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Foto utama unit / foto depan MacBook (JPG, PNG, WEBP; maks. 4 MB)</label>
                    <input type="file" class="form-control" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    <div class="form-text">Foto ini menjadi foto sampul yang tetap tampil pada kartu katalog. Foto kondisi fisik di bawah tetap terpisah dan tidak akan menggantikan foto utama.</div>
                    @if($product->image_url)
                        <div class="mt-3">
                            <img src="{{ $product->image_url }}" alt="Foto utama {{ $product->brand }} {{ $product->model }}" style="width:180px;height:130px;object-fit:cover;border-radius:10px;border:1px solid #dee2e6;">
                            <div class="small text-muted mt-1">Foto utama saat ini</div>
                        </div>
                    @else
                        <div class="form-text text-warning">Belum ada foto utama untuk unit ini.</div>
                    @endif
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi dan spesifikasi unit</label>
                    <textarea class="form-control" rows="4" name="specification" placeholder="Deskripsi: kondisi pemakaian dan keunggulan unit.&#10;Spesifikasi: chip/prosesor, RAM, kapasitas SSD, ukuran layar, warna, dan lainnya.">{{ old('specification', $product->specification) }}</textarea>
                    <div class="form-text">Contoh: Deskripsi: MacBook terawat dan cocok untuk kerja/kuliah. Spesifikasi: Apple M2, RAM 8 GB, SSD 256 GB, layar 13 inci, warna Midnight.</div>
                </div>
                <div class="col-12">
                    <label class="form-label">Keterangan kondisi fisik</label>
                    <textarea class="form-control" rows="4" name="notes" placeholder="Contoh: body mulus, ada goresan tipis di sisi kiri, layar normal tanpa dead pixel, keyboard normal, baterai sehat, semua port berfungsi.">{{ old('notes', $product->notes) }}</textarea>
                    <div class="form-text">Jelaskan kondisi nyata unit, termasuk lecet, dent, kondisi layar, keyboard, baterai, dan fungsi port.</div>
                </div>
                <div class="col-12">
                    <label class="form-label">Foto kondisi fisik MacBook (maks. 8 foto, masing-masing 4 MB)</label>
                    <input type="file" class="form-control" name="condition_images[]" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" multiple>
                    <div class="form-text">Upload foto unit yang sebenarnya: bagian depan, belakang, layar menyala, keyboard, sisi kanan/kiri, port, dan bagian yang memiliki bekas pemakaian. Foto ini yang akan tampil saat unit diklik di katalog. Jika memilih foto baru, galeri kondisi lama akan diganti.</div>
                    @if($product->exists && count($product->condition_image_urls))
                        <div class="mt-3">
                            <div class="small fw-semibold mb-2">Foto kondisi yang sudah tersimpan</div>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($product->condition_image_urls as $conditionImage)
                                    <img src="{{ $conditionImage }}" alt="Foto kondisi unit" style="width:130px;height:100px;object-fit:cover;border-radius:8px;border:1px solid #dee2e6;">
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <button class="btn btn-primary mt-4">Simpan produk</button>
        </form>
    </div>
</div>

<script>
(function () {
    const formatRupiahInput = (value) => {
        const digits = String(value ?? '').replace(/\D/g, '');
        return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    };
    document.querySelectorAll('[data-money]').forEach((input) => {
        input.value = formatRupiahInput(input.value);
        input.addEventListener('input', () => {
            input.value = formatRupiahInput(input.value);
        });
    });
})();
</script>
@endsection
