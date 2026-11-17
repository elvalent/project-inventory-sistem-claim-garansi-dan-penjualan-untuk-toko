<?php

namespace App\Http\Controllers;

use App\Models\Warranty;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use PDOException;

class WarrantyController extends Controller
{
    public function index(Request $r)
    {
        try {
            $items = Warranty::with(['sale.product', 'sale.customer'])
                ->when(auth()->user()->role === 'pelanggan', fn ($q) => $q->whereHas('sale', fn ($s) => $s->where('customer_id', auth()->id())))
                ->when(auth()->user()->role === 'sales', fn ($q) => $q->whereHas('sale', fn ($s) => $s->where('salesperson_id', auth()->id())))
                ->when($r->q, fn ($q, $v) => $q->where('warranty_code', 'like', "%$v%"))
                ->latest()->paginate(12)->withQueryString();
        } catch (QueryException | PDOException $e) {
            Log::warning('Daftar garansi tidak dapat dibaca dari database.', ['error' => $e->getMessage()]);
            $items = new LengthAwarePaginator([], 0, 12, LengthAwarePaginator::resolveCurrentPage(), [
                'path' => $r->url(), 'query' => $r->query(),
            ]);
            session()->flash('error', 'Database garansi belum tersedia. Daftar sementara ditampilkan kosong.');
        }
        return view('warranties.index', ['warranties' => $items]);
    }

    public function show(Warranty $warranty)
    {
        $this->authorizeWarranty($warranty);
        $warranty->load(['sale.product', 'sale.customer', 'sale.salesperson']);
        return view('warranties.show', compact('warranty'));
    }

    public function claim(Request $r, Warranty $warranty)
    {
        // Hanya akun pembeli yang tercatat dalam transaksi dapat mengajukan klaim.
        abort_unless(auth()->user()->role === 'pelanggan' && (int) $warranty->sale->customer_id === (int) auth()->id(), 403);
        if ($warranty->status !== 'active' || today()->toDateString() < $warranty->start_date->toDateString() || today()->toDateString() > $warranty->end_date->toDateString()) {
            return back()->with('error', 'Garansi tidak aktif atau sudah berakhir.');
        }
        $data = $r->validate(['claim_description' => 'required|string|min:10|max:2000']);
        $warranty->update(['status' => 'claimed', 'claim_description' => $data['claim_description']]);
        return back()->with('success', 'Pengajuan klaim garansi berhasil dikirim.');
    }

    public function update(Request $r, Warranty $warranty)
    {
        abort_unless(auth()->user()->role === 'admin', 403);
        $data = $r->validate(['status' => 'required|in:active,claimed,expired', 'admin_notes' => 'nullable|string|max:2000']);
        $warranty->update($data);
        return back()->with('success', 'Status garansi diperbarui.');
    }

    private function authorizeWarranty(Warranty $warranty): void
    {
        if (auth()->user()->role === 'pelanggan' && $warranty->sale->customer_id !== auth()->id()) abort(403);
        if (auth()->user()->role === 'sales' && $warranty->sale->salesperson_id !== auth()->id()) abort(403);
    }
}
