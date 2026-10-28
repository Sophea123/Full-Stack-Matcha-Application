<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'image_url',
        'price',
        'category',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    /**
     * Resolve the catalog image for the web dashboard.
     *
     * Returns an absolute URL for remote images, a public URL for uploaded
     * files, or null when the file is not available.
     */
    public function previewImageUrl(): ?string
    {
        if ($this->image_url === null || $this->image_url === '') {
            return null;
        }

        if (Str::startsWith($this->image_url, ['http://', 'https://'])) {
            return $this->image_url;
        }

        if (file_exists(public_path('images/products/'.$this->image_url))) {
            return asset('images/products/'.$this->image_url);
        }

        return null;
    }
}
