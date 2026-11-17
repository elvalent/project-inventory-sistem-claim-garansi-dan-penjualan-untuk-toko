<?php

namespace App\Support;

class PortalRoute
{
    public static function resolve(string $name, ?string $role = null): string
    {
        $role = $role ?: auth()->user()?->role;

        $routes = [
            'dashboard' => [
                'admin' => 'admin.dashboard',
                'sales' => 'sales.dashboard',
                'pelanggan' => 'customer.dashboard',
            ],
            'logout' => [
                'admin' => 'admin.logout',
                'sales' => 'sales.logout',
                'pelanggan' => 'customer.logout',
            ],
            'products.catalog' => [
                'admin' => 'admin.products.catalog',
                'sales' => 'sales.products.catalog',
                'pelanggan' => 'customer.catalog',
            ],
            'products.index' => [
                'admin' => 'admin.products.index',
                'sales' => 'sales.products.index',
            ],
            'products.create' => [
                'admin' => 'admin.products.create',
                'sales' => 'sales.products.create',
            ],
            'products.store' => [
                'admin' => 'admin.products.store',
                'sales' => 'sales.products.store',
            ],
            'products.edit' => [
                'admin' => 'admin.products.edit',
                'sales' => 'sales.products.edit',
            ],
            'products.update' => [
                'admin' => 'admin.products.update',
                'sales' => 'sales.products.update',
            ],
            'products.destroy' => [
                'admin' => 'admin.products.destroy',
                'sales' => 'sales.products.destroy',
            ],
            'sales.index' => [
                'admin' => 'admin.sales.index',
                'sales' => 'sales.transactions.index',
                'pelanggan' => 'customer.transactions.index',
            ],
            'sales.create' => [
                'admin' => 'admin.sales.create',
                'sales' => 'sales.transactions.create',
            ],
            'sales.store' => [
                'admin' => 'admin.sales.store',
                'sales' => 'sales.transactions.store',
            ],
            'sales.show' => [
                'admin' => 'admin.sales.show',
                'sales' => 'sales.transactions.show',
                'pelanggan' => 'customer.transactions.show',
            ],
            'warranties.index' => [
                'admin' => 'admin.warranties.index',
                'sales' => 'sales.warranties.index',
                'pelanggan' => 'customer.warranties.index',
            ],
            'warranties.show' => [
                'admin' => 'admin.warranties.show',
                'sales' => 'sales.warranties.show',
                'pelanggan' => 'customer.warranties.show',
            ],
            'warranties.claim' => [
                'pelanggan' => 'customer.warranties.claim',
            ],
            'warranties.update' => [
                'admin' => 'admin.warranties.update',
            ],
        ];

        return $routes[$name][$role] ?? $name;
    }
}
