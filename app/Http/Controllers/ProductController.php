<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\PortalRoute;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
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

    public function catalog(Request $request)
    {
        $products = Product::query()
            ->where('status', 'available')
            ->when($request->query('q'), fn ($query, $term) => $query->where(function ($q) use ($term) {
                $q->where('brand', 'like', "%{$term}%")
                  ->orWhere('model', 'like', "%{$term}%");
            }))
            ->orderBy('brand')->orderBy('model')->paginate(12)->withQueryString();

        return view('products.catalog', compact('products'));
    }

    public function create()
    {
        return view('products.form', ['product' => new Product]);
    }

    public function store(Request $r)
    {
        $this->ensureStorage();
        $this->normalizeMoneyInputs($r, ['purchase_price', 'selling_price']);
        $data = $r->validate([
            'serial_number' => 'required|string|max:100|unique:products',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:150',
            'specification' => 'nullable|string|max:1000',
            'purchase_price' => 'required|integer|min:0',
            'selling_price' => 'required|integer|min:0',
            'status' => ['required', Rule::in(['available', 'service'])],
            'notes' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'condition_images' => 'nullable|array|max:8',
            'condition_images.*' => 'image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);
        $image = $data['image'] ?? null;
        $conditionImages = $data['condition_images'] ?? [];
        unset($data['image'], $data['condition_images']);
        $product = Product::create($data);
        if ($image instanceof UploadedFile) {
            $this->saveProductImage($image, $product);
        }
        if (!empty($conditionImages)) {
            $this->saveConditionImages($conditionImages, $product);
        }
        return redirect()->route(PortalRoute::resolve('products.index', auth()->user()->role))->with('success', 'Unit produk berhasil ditambahkan.');
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
        $this->normalizeMoneyInputs($r, ['purchase_price', 'selling_price']);
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
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'condition_images' => 'nullable|array|max:8',
            'condition_images.*' => 'image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);
        $image = $data['image'] ?? null;
        $conditionImages = $data['condition_images'] ?? [];
        unset($data['image'], $data['condition_images']);
        $product->update($data);
        if ($image instanceof UploadedFile) {
            $this->saveProductImage($image, $product);
        }
        if (!empty($conditionImages)) {
            $this->saveConditionImages($conditionImages, $product);
        }
        return redirect()->route(PortalRoute::resolve('products.index', auth()->user()->role))->with('success', 'Data produk diperbarui.');
    }

    public function destroy(Product $product)
    {
        $this->ensureStorage();
        if ($product->sale()->exists()) {
            return back()->with('error', 'Produk sudah tercatat dalam penjualan; data tidak boleh dihapus.');
        }
        $this->deleteProductImages($product);
        $product->delete();
        return back()->with('success', 'Produk dihapus.');
    }


    private function normalizeMoneyInputs(Request $request, array $fields): void
    {
        foreach ($fields as $field) {
            $value = $request->input($field);
            if ($value !== null) {
                $request->merge([$field => preg_replace('/[^0-9]/', '', (string) $value)]);
            }
        }
    }

    private function saveProductImage(UploadedFile $image, Product $product): void
    {
        $directory = public_path('uploads/products');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $extension = strtolower($image->guessExtension() ?: $image->getClientOriginalExtension());
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $extension = 'jpg';
        }

        $filename = $product->id . '-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(6)) . '.' . $extension;
        $image->move($directory, $filename);

        // Replace only the main product photo; keep condition gallery photos intact.
        foreach (glob($directory . '/' . $product->id . '-*') ?: [] as $oldFile) {
            if (!str_contains(basename($oldFile), '-condition-') && basename($oldFile) !== $filename && is_file($oldFile)) {
                @unlink($oldFile);
            }
        }
    }

    /** @param array<int, UploadedFile> $images */
    private function saveConditionImages(array $images, Product $product): void
    {
        $directory = public_path('uploads/products');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // A newly selected gallery replaces the old gallery, while the main photo stays untouched.
        $savedFiles = [];
        foreach ($images as $image) {
            if (!$image instanceof UploadedFile) {
                continue;
            }

            $extension = strtolower($image->guessExtension() ?: $image->getClientOriginalExtension());
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $extension = 'jpg';
            }

            $filename = $product->id . '-condition-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(6)) . '.' . $extension;
            $image->move($directory, $filename);
            $savedFiles[] = $filename;
        }

        if (!empty($savedFiles)) {
            foreach (glob($directory . '/' . $product->id . '-condition-*') ?: [] as $oldFile) {
                if (!in_array(basename($oldFile), $savedFiles, true) && is_file($oldFile)) {
                    @unlink($oldFile);
                }
            }
        }
    }

    private function deleteProductImages(Product $product): void
    {
        $directory = public_path('uploads/products');
        foreach (glob($directory . '/' . $product->id . '-*') ?: [] as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
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
