<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'serial_number',
        'brand',
        'model',
        'specification',
        'purchase_price',
        'selling_price',
        'status',
        'notes',
    ];

    protected $casts = [
        'purchase_price' => 'integer',
        'selling_price' => 'integer',
    ];

    public function sale()
    {
        return $this->hasOne(Sale::class);
    }

    /**
     * Resolve the latest uploaded product photo from public/uploads/products.
     * This intentionally avoids a database column or migration.
     */
    public function getImageUrlAttribute(): ?string
    {
        $directory = public_path('uploads/products');
        $files = array_values(array_filter(
            glob($directory . '/' . $this->id . '-*') ?: [],
            static fn ($file) => !str_contains(basename($file), '-condition-')
        ));

        if (empty($files)) {
            return null;
        }

        usort($files, static fn ($a, $b) => filemtime($b) <=> filemtime($a));

        return asset('uploads/products/' . basename($files[0]));
    }

    /** @return array<int, string> */
    public function getConditionImageUrlsAttribute(): array
    {
        $directory = public_path('uploads/products');
        $files = glob($directory . '/' . $this->id . '-condition-*') ?: [];
        usort($files, static fn ($a, $b) => filemtime($a) <=> filemtime($b));

        return array_map(
            static fn ($file) => asset('uploads/products/' . basename($file)),
            $files
        );
    }
}
