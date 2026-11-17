<?php
namespace Database\Seeders;
use App\Models\User; use Illuminate\Database\Seeder; use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder { public function run(): void { User::updateOrCreate(['email'=>'admin@macnus.com'],['name'=>'Admin Macnus','password'=>Hash::make('admin123'),'role'=>'admin']); User::updateOrCreate(['email'=>'sales@macnus.com'],['name'=>'Sales Demo','password'=>Hash::make('sales123'),'role'=>'sales']); User::updateOrCreate(['email'=>'user@macnus.com'],['name'=>'Pelanggan Demo','password'=>Hash::make('user123'),'role'=>'pelanggan']); } }
