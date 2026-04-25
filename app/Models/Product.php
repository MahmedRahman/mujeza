<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title',
        'price',
        'discount_price',
        'description',
        'benefits',
        'diseases',
        'usage_methods',
        'sizes',
        'cover_image',
        'gallery_images',
        'primary_gallery_image',
        'promo_videos',
    ];

    protected $casts = [
        'benefits' => 'array',
        'diseases' => 'array',
        'usage_methods' => 'array',
        'sizes' => 'array',
        'gallery_images' => 'array',
        'promo_videos' => 'array',
    ];
}
