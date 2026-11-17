<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\User;
use App\Models\Warranty;
use App\Support\PortalRoute;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PDOException;

class SaleController extends Controller
{
    public function index(Request $r)
    {
        try {
            $sales = Sale::with(['product', 'customer', 'salesperson'])
                ->when(auth()->user()->role === 'sales', fn ($q) => $q->where('salesperson_id', auth()->id()))
                ->when(auth()->user()->role === 'pelanggan', fn ($q) => $q->where('customer_id', auth()->id()))
                ->when($r->q, fn ($q, $v) => $q->where('invoice_number', 'like', "%$v%"))
                ->latest('sold_at')->paginate(12)->withQueryString();
        } catch (QueryException | PDOException $e) {
            Log::warning('Daftar penjualan tidak dapat dibaca dari database.', ['error' => $e->getMessage()]);
            $sales = new LengthAwarePaginator([], 0, 12, LengthAwarePaginator::resolveCurrentPage(), [
                'path' => $r->url(), 'query' => $r->query(),
            ]);
            session()->flash('error', 'Database penjualan belum tersedia. Daftar sementara ditampilkan kosong.');
        }
        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        abort_unless(in_array(auth()->user()->role, ['admin', 'sales']), 403);
        $products = collect();
        $customers = collect();
        try {
            $products = Product::where('status', 'available')->orderBy('model')->get();
            $customers = User::where('role', 'pelanggan')->orderBy('name')->get();
        } catch (QueryException | PDOException $e) {
            Log::warning('Form transaksi tidak dapat memuat data database.', ['error' => $e->getMessage()]);
            $products = collect();
            $customers = collect();
            session()->flash('error', 'Database belum siap. Transaksi tidak dapat dibuat.');
        }
        return view('sales.form', compact('products', 'customers'));
    }

    public function store(Request $r)
    {
        abort_unless(in_array(auth()->user()->role, ['admin', 'sales']), 403);
        $this->ensureStorage();
        $priceInput = $r->input('selling_price');
        if ($priceInput !== null) {
            $r->merge(['selling_price' => preg_replace('/[^0-9]/', '', (string) $priceInput)]);
        }
        $data = $r->validate([
            'product_id' => 'required|exists:products,id',
            'customer_id' => 'required|exists:users,id,role,pelanggan',
            'selling_price' => 'required|integer|min:0',
            'sold_at' => 'required|date',
            'payment_status' => 'required|in:unpaid,paid,partial',
            'notes' => 'nullable|string',
            'warranty_months' => 'required|integer|min:0|max:60',
        ]);
        DB::transaction(function () use ($data) {
            $product = Product::whereKey($data['product_id'])->lockForUpdate()->firstOrFail();
            if ($product->status !== 'available') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'product_id' => 'Unit ini sudah tidak tersedia.',
                ]);
            }
            $sale = Sale::create([
                'invoice_number' => 'INV-'.now()->format('Ymd').'-'.strtoupper(Str::random(5)),
                'product_id' => $product->id,
                'customer_id' => $data['customer_id'],
                'salesperson_id' => auth()->id(),
                'selling_price' => $data['selling_price'],
                'sold_at' => $data['sold_at'],
                'payment_status' => $data['payment_status'],
                'notes' => $data['notes'] ?? null,
            ]);
            $product->update(['status' => 'sold']);
            if ((int) $data['warranty_months'] > 0) {
                $start = \Carbon\Carbon::parse($data['sold_at']);
                Warranty::create([
                    'sale_id' => $sale->id,
                    'warranty_code' => 'WAR-'.strtoupper(Str::random(8)),
                    'start_date' => $start,
                    'end_date' => $start->copy()->addMonths((int) $data['warranty_months']),
                    'status' => 'active',
                ]);
            }
        });
        return redirect()->route(PortalRoute::resolve('sales.index', auth()->user()->role))->with('success', 'Transaksi tersimpan; status inventaris dan garansi sudah diperbarui.');
    }

    public function show(Sale $sale)
    {
        $this->authorizeSale($sale);
        $sale->load(['product', 'customer', 'salesperson', 'warranty']);
        return view('sales.show', compact('sale'));
    }

    public function destroy(Sale $sale)
    {
        abort_unless(auth()->user()->role === 'admin', 403);
        return back()->with('error', 'Transaksi penjualan tidak dihapus untuk menjaga audit dan riwayat serial number.');
    }

    private function authorizeSale(Sale $sale): void
    {
        if (auth()->user()->role === 'pelanggan' && $sale->customer_id !== auth()->id()) abort(403);
        if (auth()->user()->role === 'sales' && $sale->salesperson_id !== auth()->id()) abort(403);
    }

    private function ensureStorage(): void
    {
        try {
            foreach (['products', 'users', 'sales', 'warranties'] as $table) {
                if (!\Illuminate\Support\Facades\Schema::hasTable($table)) {
                    abort(503, 'Database transaksi belum siap. Data tidak disimpan.');
                }
            }
        } catch (QueryException | PDOException $e) {
            Log::warning('Penyimpanan transaksi tidak tersedia.', ['error' => $e->getMessage()]);
            abort(503, 'Database transaksi belum siap. Data tidak disimpan.');
        }
    }
}
