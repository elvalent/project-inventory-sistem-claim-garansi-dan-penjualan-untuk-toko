<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use PDOException;

class ProductController extends Controller
{
    public function index(Request $r)
    {
        try {
            $products = Product::when($r->q, fn ($q, $v) => $q->where(fn ($x) => $x
                ->where('serial_number', 'like', "%$v%")
                ->orWhere('model', 'like', "%$v%")
                ->orWhere('brand', 'like', "%$v%")))
                ->when($r->status, fn ($q, $v) => $q->where('status', $v))
                ->latest()->paginate(12)->withQueryString();
        } catch (QueryException | PDOException $e) {
            Log::warning('Inventaris produk tidak dapat dibaca dari database.', ['error' => $e->getMessage()]);
            $products = new LengthAwarePaginator([], 0, 12, LengthAwarePaginator::resolveCurrentPage(), [
                'path' => $r->url(), 'query' => $r->query(),
            ]);
            session()->flash('error', 'Database produk belum tersedia. Daftar sementara ditampilkan kosong; data belum bisa disimpan.');
        }

        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.form', ['product' => new Product]);
    }

    public function store(Request $r)
    {
        $this->ensureStorage();
        $data = $r->validate([
            'serial_number' => 'required|string|max:100|unique:products',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:150',
            'specification' => 'nullable|string|max:1000',
            'purchase_price' => 'required|integer|min:0',
            'selling_price' => 'required|integer|min:0',
            'status' => ['required', Rule::in(['available', 'service'])],
            'notes' => 'nullable|string',
        ]);
        Product::create($data);
        return redirect()->route('products.index')->with('success', 'Unit produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        if ($product->status === 'sold') {
            return back()->with('error', 'Produk yang sudah terjual tidak bisa diedit status inventarisnya.');
        }
        return view('products.form', compact('product'));
    }

    public function update(Request $r, Product $product)
    {
        $this->ensureStorage();
        if ($product->status === 'sold') {
            return back()->with('error', 'Unit yang sudah terjual tidak boleh diubah kembali ke stok tersedia.');
        }
        $data = $r->validate([
            'serial_number' => ['required', 'string', 'max:100', Rule::unique('products')->ignore($product->id)],
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:150',
            'specification' => 'nullable|string|max:1000',
            'purchase_price' => 'required|integer|min:0',
            'selling_price' => 'required|integer|min:0',
            'status' => ['required', Rule::in(['available', 'service'])],
            'notes' => 'nullable|string',
        ]);
        $product->update($data);
        return redirect()->route('products.index')->with('success', 'Data produk diperbarui.');
    }

    public function destroy(Product $product)
    {
        $this->ensureStorage();
        if ($product->sale()->exists()) {
            return back()->with('error', 'Produk sudah tercatat dalam penjualan; data tidak boleh dihapus.');
        }
        $product->delete();
        return back()->with('success', 'Produk dihapus.');
    }

    private function ensureStorage(): void
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('products')) {
                return;
            }
        } catch (QueryException | PDOException $e) {
            Log::warning('Penyimpanan produk tidak tersedia.', ['error' => $e->getMessage()]);
        }
        abort(503, 'Database produk belum siap. Data tidak disimpan.');
    }
}
