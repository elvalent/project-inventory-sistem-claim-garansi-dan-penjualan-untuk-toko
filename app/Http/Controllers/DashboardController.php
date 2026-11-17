<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Warranty;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use PDOException;

class DashboardController extends Controller
{
    public function index()
    {
        $role = Auth::user()->role;

        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        if ($role === 'sales') {
            return redirect()->route('sales.dashboard');
        }

        return redirect()->route('customer.dashboard');
    }

    public function admin()
    {
        $data = [
            'products' => 0,
            'available' => 0,
            'sales' => 0,
            'revenue' => 0,
            'warranties' => 0,
            'recentSales' => collect(),
        ];

        try {
            if (Schema::hasTable('products')) {
                $data['products'] = Product::count();
                $data['available'] = Product::where('status', 'available')->count();
            }
            if (Schema::hasTable('sales')) {
                $data['sales'] = Sale::count();
                $data['revenue'] = Sale::where('payment_status', 'paid')->sum('selling_price');
                $data['recentSales'] = Sale::with(['product', 'customer', 'salesperson'])
                    ->latest('sold_at')->take(6)->get();
            }
            if (Schema::hasTable('warranties')) {
                $data['warranties'] = Warranty::where('status', 'active')->count();
            }
        } catch (QueryException | PDOException $e) {
            Log::warning('Dashboard admin: database belum siap', ['error' => $e->getMessage()]);
        }

        return view('admin.dashboard', $data);
    }

    public function sales()
    {
        $data = [
            'sales' => collect(),
            'count' => 0,
            'revenue' => 0,
            'available' => 0,
        ];

        try {
            if (Schema::hasTable('sales')) {
                $data['sales'] = Sale::with(['product', 'customer'])
                    ->where('salesperson_id', Auth::id())
                    ->latest('sold_at')->take(8)->get();
                $data['count'] = Sale::where('salesperson_id', Auth::id())->count();
                $data['revenue'] = Sale::where('salesperson_id', Auth::id())
                    ->where('payment_status', 'paid')->sum('selling_price');
            }
            if (Schema::hasTable('products')) {
                $data['available'] = Product::where('status', 'available')->count();
            }
        } catch (QueryException | PDOException $e) {
            Log::warning('Dashboard sales: database belum siap', ['error' => $e->getMessage()]);
        }

        return view('sales.dashboard', $data);
    }

    public function customer()
    {
        $sales = collect();

        try {
            if (Schema::hasTable('sales')) {
                $sales = Sale::with(['product', 'warranty'])
                    ->where('customer_id', Auth::id())
                    ->latest('sold_at')->get();
            }
        } catch (QueryException | PDOException $e) {
            Log::warning('Dashboard customer: database belum siap', ['error' => $e->getMessage()]);
        }

        return view('customer.dashboard', ['sales' => $sales]);
    }
}
