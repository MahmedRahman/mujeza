<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'OrderStatus',
    type: 'string',
    enum: ['طلب جديد', 'تم التأكيد', 'قيد التجهيز', 'خرج للتوصيل', 'مكتمل', 'ملغي'],
    example: 'طلب جديد'
)]
#[OA\Schema(
    schema: 'OrderLineItem',
    properties: [
        new OA\Property(property: 'product_id', type: 'integer', nullable: true, example: 12),
        new OA\Property(property: 'product_title', type: 'string', example: 'عسل سدر'),
        new OA\Property(property: 'unit_price', type: 'number', format: 'float', example: 5.5),
        new OA\Property(property: 'quantity', type: 'integer', example: 2),
        new OA\Property(property: 'line_total', type: 'number', format: 'float', example: 11),
    ]
)]
#[OA\Schema(
    schema: 'OrderStatusHistoryEntry',
    properties: [
        new OA\Property(property: 'status', ref: '#/components/schemas/OrderStatus'),
        new OA\Property(property: 'note', type: 'string', nullable: true, example: 'إنشاء الطلب عبر API'),
        new OA\Property(property: 'changed_by', type: 'string', nullable: true, example: 'api'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'Order',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'order_number', type: 'integer', example: 1024),
        new OA\Property(property: 'remote_jid', type: 'string', nullable: true, example: '96550000000@s.whatsapp.net'),
        new OA\Property(property: 'customer_name', type: 'string', nullable: true, example: 'محمد أحمد'),
        new OA\Property(property: 'phone', type: 'string', nullable: true, example: '96550000000'),
        new OA\Property(property: 'delivery_address', type: 'string', nullable: true, example: 'الكويت - حولي'),
        new OA\Property(property: 'status', ref: '#/components/schemas/OrderStatus'),
        new OA\Property(
            property: 'available_statuses',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/OrderStatus')
        ),
        new OA\Property(property: 'status_changed_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'subtotal', type: 'number', format: 'float', example: 11),
        new OA\Property(property: 'delivery_fee', type: 'number', format: 'float', example: 1.5),
        new OA\Property(property: 'grand_total', type: 'number', format: 'float', example: 12.5),
        new OA\Property(property: 'total_amount', type: 'number', format: 'float', example: 12.5),
        new OA\Property(property: 'items', type: 'string', example: 'عسل سدر 2 عبوة + عسل كشميري 1 عبوة'),
        new OA\Property(
            property: 'line_items',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/OrderLineItem')
        ),
        new OA\Property(
            property: 'status_history',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/OrderStatusHistoryEntry')
        ),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'OrderMutationResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'message', type: 'string', example: 'تم إنشاء الطلب بنجاح.'),
        new OA\Property(property: 'data', ref: '#/components/schemas/Order'),
    ]
)]
#[OA\Schema(
    schema: 'OrderShowResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'data', ref: '#/components/schemas/Order'),
    ]
)]
#[OA\Schema(
    schema: 'OrderStatusSearchResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'found', type: 'boolean', example: true),
        new OA\Property(property: 'latest_status', ref: '#/components/schemas/OrderStatus', nullable: true),
        new OA\Property(property: 'count', type: 'integer', example: 2),
        new OA\Property(property: 'latest_order', ref: '#/components/schemas/Order', nullable: true),
        new OA\Property(
            property: 'orders',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Order')
        ),
    ]
)]
#[OA\Schema(
    schema: 'OrdersByPhoneResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'phone', type: 'string', example: '96550000000'),
        new OA\Property(property: 'count', type: 'integer', example: 2),
        new OA\Property(
            property: 'orders',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Order')
        ),
    ]
)]
#[OA\Schema(
    schema: 'ApiErrorResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string', example: 'الطلب غير موجود.'),
    ]
)]
class OrderSchemas
{
}
