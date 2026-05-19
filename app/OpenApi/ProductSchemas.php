<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Products',
    description: 'منتجات المتجر — القائمة الكاملة، البحث الذكي، ونص الأسماء للوكلاء'
)]
#[OA\Schema(
    schema: 'Product',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 12),
        new OA\Property(property: 'title', type: 'string', example: 'عسل سدر'),
        new OA\Property(property: 'price', type: 'number', format: 'float', example: 25.0),
        new OA\Property(property: 'discount_price', type: 'number', format: 'float', nullable: true, example: 20.0),
        new OA\Property(property: 'is_available', type: 'boolean', example: true, description: 'متاح — true = متاح، false = غير متاح'),
        new OA\Property(property: 'description', type: 'string', example: 'عسل سدر طبيعي'),
        new OA\Property(
            property: 'benefits',
            type: 'array',
            items: new OA\Items(type: 'string'),
            example: ['يدعم المناعة']
        ),
        new OA\Property(
            property: 'diseases',
            type: 'array',
            items: new OA\Items(type: 'string'),
            example: ['السكري']
        ),
        new OA\Property(
            property: 'usage_methods',
            type: 'array',
            items: new OA\Items(type: 'string'),
            example: ['ملعقة صباحاً']
        ),
        new OA\Property(
            property: 'sizes',
            type: 'array',
            items: new OA\Items(type: 'string'),
            example: ['500جم']
        ),
        new OA\Property(property: 'cover_image', type: 'string', nullable: true, example: 'products/cover.jpg'),
        new OA\Property(property: 'cover_image_url', type: 'string', nullable: true, example: 'https://app.taheelplus.com/storage/products/cover.jpg'),
        new OA\Property(
            property: 'gallery_images',
            type: 'array',
            items: new OA\Items(type: 'string')
        ),
        new OA\Property(
            property: 'gallery_image_urls',
            type: 'array',
            items: new OA\Items(type: 'string')
        ),
        new OA\Property(property: 'primary_gallery_image', type: 'string', nullable: true),
        new OA\Property(property: 'primary_gallery_image_url', type: 'string', nullable: true),
        new OA\Property(
            property: 'promo_videos',
            type: 'array',
            items: new OA\Items(type: 'string')
        ),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'ProductListResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Product')
        ),
    ]
)]
#[OA\Schema(
    schema: 'ProductSearchResponse',
    description: 'نتائج البحث الذكي عن المنتجات — مرتبة من الأقرب للأبعد',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(
            property: 'query',
            type: 'string',
            description: 'نص البحث الذي أُرسل في المعامل q',
            example: 'عسل سدر'
        ),
        new OA\Property(
            property: 'count',
            type: 'integer',
            description: 'عدد المنتجات المقترحة',
            example: 2
        ),
        new OA\Property(
            property: 'data',
            type: 'array',
            description: 'قائمة المنتجات المطابقة مرتبة حسب التقارب',
            items: new OA\Items(ref: '#/components/schemas/Product')
        ),
    ]
)]
#[OA\Schema(
    schema: 'ProductNamesTextResponse',
    description: 'كل المنتجات كنص واحد — كل منتج بالصيغة id|اسم المنتج والفاصل بين المنتجات فاصلة',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'string',
            description: 'نص مفصول بفواصل: id|اسم_المنتج,id|اسم_المنتج,...',
            example: '12|عسل سدر,15|عسل مانوكا,22|زيت زيتون'
        ),
    ]
)]
#[OA\Schema(
    schema: 'ProductSearchByDiseaseResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'disease', type: 'string', example: 'السكري'),
        new OA\Property(property: 'count', type: 'integer', example: 3),
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Product')
        ),
    ]
)]
class ProductSchemas
{
}
