@extends('layouts.portal')
@section('title', 'Buat Transaksi')
@section('content')
<div class="card table-card form-card">
    <div class="card-body p-4">
        <a href="{{ portal_route('sales.index') }}" class="text-decoration-none">← Kembali ke transaksi</a>
        <h1 class="h3 fw-bold mt-3">Buat transaksi penjualan</h1>
        <p class="muted">Saat transaksi disimpan, unit otomatis menjadi terjual dan garansi dibuat sesuai durasi.</p>
        @if($products->isEmpty())
            <div class="alert alert-warning">Belum ada unit siap dijual, atau database belum siap. Hubungi Admin untuk menambahkan stok / memeriksa database.</div>
        @else
            <form method="POST" action="{{ portal_route('sales.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Unit / serial number *</label>
                        <select class="form-select" name="product_id" id="product" required>
                            <option value="">Pilih unit</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->selling_price }}" @selected(old('product_id') == $product->id)>{{ $product->brand }} {{ $product->model }} — {{ $product->serial_number }} (Rp {{ number_format($product->selling_price, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Pelanggan *</label>
                        <select class="form-select" name="customer_id" required>
                            <option value="">Pilih pelanggan</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>{{ $customer->name }} ({{ $customer->email }})</option>
                            @endforeach
                        </select>
                        @if($customers->isEmpty())<div class="form-text">Belum ada akun pelanggan. Minta pelanggan daftar terlebih dahulu.</div>@endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Harga jual aktual (Rp) *</label>
                        <input id="price" type="text" inputmode="numeric" autocomplete="off" data-money class="form-control" name="selling_price" value="{{ old('selling_price') }}" placeholder="Contoh: 15.000.000" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tanggal penjualan *</label>
                        <input type="date" class="form-control" name="sold_at" value="{{ old('sold_at', now()->toDateString()) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status pembayaran *</label>
                        <select class="form-select" name="payment_status">
                            <option value="paid" @selected(old('payment_status', 'paid') === 'paid')>Lunas</option>
                            <option value="partial" @selected(old('payment_status') === 'partial')>Sebagian</option>
                            <option value="unpaid" @selected(old('payment_status') === 'unpaid')>Belum bayar</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Masa garansi (bulan) *</label>
                        <select class="form-select" name="warranty_months">
                            @foreach([0, 3, 6, 12, 24, 36] as $m)
                                <option value="{{ $m }}" @selected(old('warranty_months', 12) == $m)>{{ $m === 0 ? 'Tanpa garansi' : $m . ' bulan' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Catatan transaksi</label>
                        <textarea class="form-control" rows="3" name="notes">{{ old('notes') }}</textarea>
                    </div>
                </div>
                <button class="btn btn-primary mt-4" @disabled($customers->isEmpty())>Simpan transaksi</button>
            </form>
        @endif
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
    document.getElementById('product')?.addEventListener('change', function () {
        const price = this.selectedOptions[0]?.dataset.price;
        if (price) document.getElementById('price').value = formatRupiahInput(price);
    });
})();
</script>
@endsection
