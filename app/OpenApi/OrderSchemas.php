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
    schema: 'OrderCreateRequest',
    required: ['items_id', 'items_qty'],
    properties: [
        new OA\Property(property: 'remote_jid', type: 'string', example: '96550000000@s.whatsapp.net', nullable: true, description: 'يُستخدم لجلب اسم العميل والهاتف والعنوان تلقائياً إن وُجد في customers'),
        new OA\Property(property: 'customer_name', type: 'string', example: 'محمد أحمد', nullable: true),
        new OA\Property(property: 'phone', type: 'string', example: '96550000000', nullable: true),
        new OA\Property(property: 'delivery_address', type: 'string', example: 'الكويت - حولي - شارع بيروت', nullable: true),
        new OA\Property(property: 'delivery_fee', type: 'number', format: 'float', example: 1.5, nullable: true),
        new OA\Property(property: 'status', ref: '#/components/schemas/OrderStatus'),
        new OA\Property(
            property: 'items_id',
            description: 'معرّفات المنتجات — نص مفصول بفواصل أو مصفوفة أرقام. الموضع يطابق items_qty',
            oneOf: [
                new OA\Schema(type: 'string', example: '12,5,8'),
                new OA\Schema(type: 'array', items: new OA\Items(type: 'integer'), example: [12, 5, 8]),
            ]
        ),
        new OA\Property(
            property: 'items_qty',
            description: 'كميات المنتجات — نص مفصول بفواصل أو مصفوفة أرقام بنفس ترتيب items_id',
            oneOf: [
                new OA\Schema(type: 'string', example: '2,1,4'),
                new OA\Schema(type: 'array', items: new OA\Items(type: 'integer'), example: [2, 1, 4]),
            ]
        ),
    ],
    example: [
        'remote_jid'       => '96550000000@s.whatsapp.net',
        'delivery_address' => 'الكويت - حولي',
        'delivery_fee'     => 1.5,
        'status'           => 'طلب جديد',
        'items_id'         => '12,5,8',
        'items_qty'        => '2,1,4',
    ]
)]
#[OA\Schema(
    schema: 'OrderUpdateRequest',
    properties: [
        new OA\Property(property: 'status', ref: '#/components/schemas/OrderStatus'),
        new OA\Property(property: 'delivery_address', type: 'string', nullable: true),
        new OA\Property(property: 'delivery_fee', type: 'number', format: 'float', example: 1.5),
        new OA\Property(
            property: 'items_id',
            description: 'معرّفات المنتجات (يُرسل مع items_qty لاستبدال بنود الطلب)',
            oneOf: [
                new OA\Schema(type: 'string', example: '12,5'),
                new OA\Schema(type: 'array', items: new OA\Items(type: 'integer')),
            ]
        ),
        new OA\Property(
            property: 'items_qty',
            description: 'كميات المنتجات المقابلة لـ items_id',
            oneOf: [
                new OA\Schema(type: 'string', example: '2,1'),
                new OA\Schema(type: 'array', items: new OA\Items(type: 'integer')),
            ]
        ),
        new OA\Property(property: 'status_note', type: 'string', nullable: true, description: 'تُسجَّل في سجل الحالات عند تغيير status'),
    ],
    example: [
        'status'    => 'تم التأكيد',
        'items_id'  => '12,5',
        'items_qty' => '3,1',
    ]
)]
#[OA\Schema(
    schema: 'OrderLineItem',
    required: ['product_id', 'product_title', 'unit_price', 'quantity', 'line_total'],
    properties: [
        new OA\Property(property: 'product_id', type: 'integer', example: 12, description: 'معرّف المنتج المربوط'),
        new OA\Property(property: 'product_title', type: 'string', example: 'عسل سدر'),
        new OA\Property(property: 'unit_price', type: 'number', format: 'float', example: 5.5, description: 'سعر الوحدة (يُستخدم سعر الخصم إن وُجد)'),
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
        new OA\Property(property: 'subtotal', type: 'number', format: 'float', example: 11, description: 'مجموع بنود المنتجات'),
        new OA\Property(property: 'delivery_fee', type: 'number', format: 'float', example: 1.5),
        new OA\Property(property: 'grand_total', type: 'number', format: 'float', example: 12.5, description: 'subtotal + delivery_fee'),
        new OA\Property(property: 'total_amount', type: 'number', format: 'float', example: 12.5, description: 'مطابق لـ grand_total'),
        new OA\Property(
            property: 'items',
            type: 'string',
            example: '#12 عسل سدر x2 + #5 عسل كشميري x1',
            description: 'ملخّص نصي يُولَّد تلقائياً من line_items'
        ),
        new OA\Property(
            property: 'line_items',
            type: 'array',
            description: 'المنتجات المربوطة بالطلب (المصدر الرئيسي)',
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
        new OA\Property(property: 'example', type: 'object', nullable: true, description: 'مثال على الصيغة الصحيحة'),
    ]
)]
class OrderSchemas
{
}
