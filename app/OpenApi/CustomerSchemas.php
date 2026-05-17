<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Customer',
    properties: [
        new OA\Property(property: 'remote_jid', type: 'string', example: '96550000000@s.whatsapp.net'),
        new OA\Property(property: 'phone', type: 'string', nullable: true, example: '96550000000'),
        new OA\Property(property: 'name', type: 'string', nullable: true, example: 'محمد أحمد'),
        new OA\Property(property: 'address', type: 'string', nullable: true, example: 'الكويت - حولي'),
        new OA\Property(property: 'auto_reply', type: 'boolean', example: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'CustomerWithoutName',
    description: 'بيانات عميل بدون حقل name (legacy)',
    properties: [
        new OA\Property(property: 'remote_jid', type: 'string', example: '96550000000@s.whatsapp.net'),
        new OA\Property(property: 'phone', type: 'string', nullable: true, example: null),
        new OA\Property(property: 'address', type: 'string', nullable: true),
        new OA\Property(property: 'auto_reply', type: 'boolean', example: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'CustomerOrderSummary',
    properties: [
        new OA\Property(property: 'order_number', type: 'integer', example: 1024),
        new OA\Property(property: 'status', ref: '#/components/schemas/OrderStatus'),
        new OA\Property(
            property: 'items',
            type: 'string',
            example: '#12 عسل سدر x2',
            description: 'ملخّص نصي للمنتجات (يُولَّد من line_items عند الربط)'
        ),
    ]
)]
#[OA\Schema(
    schema: 'CustomerComplaintSummary',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: 'تأخر التوصيل'),
        new OA\Property(property: 'status', type: 'string', example: 'مفتوحة'),
    ]
)]
#[OA\Schema(
    schema: 'CustomerCheckRegisteredResponse',
    properties: [
        new OA\Property(property: 'registered', type: 'boolean', example: true),
        new OA\Property(property: 'remote_jid', type: 'string', example: '96550000000@s.whatsapp.net'),
        new OA\Property(property: 'phone', type: 'string', nullable: true, example: '96550000000'),
        new OA\Property(property: 'name', type: 'string', nullable: true, example: 'محمد أحمد'),
        new OA\Property(property: 'address', type: 'string', nullable: true),
        new OA\Property(property: 'auto_reply', type: 'boolean', example: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'CustomerCheckRequest',
    properties: [
        new OA\Property(property: 'phone', type: 'string', nullable: true, example: '96550000000', description: 'أرسل phone أو remote_jid (أحدهما على الأقل)'),
        new OA\Property(property: 'remote_jid', type: 'string', nullable: true, example: '96550000000@s.whatsapp.net'),
    ]
)]
#[OA\Schema(
    schema: 'CustomerCheckAndSaveRequest',
    required: ['remote_jid'],
    properties: [
        new OA\Property(property: 'remote_jid', type: 'string', example: '96550000000@s.whatsapp.net'),
        new OA\Property(property: 'name', type: 'string', nullable: true, example: 'محمد أحمد', description: 'اسم العميل — يُحفظ عند الإنشاء أو يُحدَّث إن وُجد العميل مسبقاً'),
    ]
)]
#[OA\Schema(
    schema: 'CustomerCheckUnregisteredResponse',
    properties: [
        new OA\Property(property: 'registered', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string', example: 'هذا الرقم غير مسجل.'),
    ]
)]
#[OA\Schema(
    schema: 'CustomerCheckAndSaveResponse',
    description: 'registered: true إذا كان مسجلاً مسبقاً، false إذا تم إنشاؤه الآن.',
    properties: [
        new OA\Property(property: 'registered', type: 'boolean', example: true),
        new OA\Property(property: 'newly_created', type: 'boolean', example: false),
        new OA\Property(property: 'global_auto_reply', type: 'boolean', example: true, description: 'الإعداد العام للرد التلقائي على مستوى النظام'),
        new OA\Property(property: 'remote_jid', type: 'string', example: '96550000000@s.whatsapp.net'),
        new OA\Property(property: 'phone', type: 'string', nullable: true, example: null),
        new OA\Property(property: 'name', type: 'string', nullable: true, example: 'محمد أحمد'),
        new OA\Property(property: 'address', type: 'string', nullable: true),
        new OA\Property(property: 'auto_reply', type: 'boolean', example: true, description: 'الرد التلقائي الفعلي لهذه المحادثة (بعد تطبيق override إن وُجد)'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(
            property: 'orders',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/CustomerOrderSummary')
        ),
        new OA\Property(
            property: 'complaints',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/CustomerComplaintSummary')
        ),
    ]
)]
#[OA\Schema(
    schema: 'CustomerListResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'query', type: 'string', example: ''),
        new OA\Property(property: 'count', type: 'integer', example: 5),
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Customer')
        ),
    ]
)]
#[OA\Schema(
    schema: 'CustomerShowResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'data', ref: '#/components/schemas/Customer'),
    ]
)]
#[OA\Schema(
    schema: 'AutoReplyToggleRequest',
    required: ['enabled'],
    properties: [
        new OA\Property(property: 'enabled', type: 'boolean', example: true, description: 'true = تفعيل الرد التلقائي، false = إيقافه'),
    ]
)]
#[OA\Schema(
    schema: 'CustomerAutoReplyRequest',
    required: ['remote_jid', 'enabled'],
    properties: [
        new OA\Property(property: 'remote_jid', type: 'string', example: '96550000000@s.whatsapp.net'),
        new OA\Property(property: 'enabled', type: 'boolean', example: false, description: 'true = تفعيل الرد التلقائي، false = إيقافه'),
    ]
)]
#[OA\Schema(
    schema: 'GlobalAutoReplyResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'message', type: 'string', example: 'تم تحديث الإعداد العام للرد التلقائي.'),
        new OA\Property(property: 'global_auto_reply', type: 'boolean', example: true),
    ]
)]
#[OA\Schema(
    schema: 'CustomerAutoReplyResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'message', type: 'string', example: 'تم تحديث الرد التلقائي للعميل.'),
        new OA\Property(property: 'remote_jid', type: 'string', example: '96550000000@s.whatsapp.net'),
        new OA\Property(property: 'global_auto_reply', type: 'boolean', example: true),
        new OA\Property(property: 'auto_reply', type: 'boolean', example: false),
        new OA\Property(property: 'auto_reply_overridden', type: 'boolean', example: true, description: 'true إذا كانت القيمة مختلفة عن الإعداد العام'),
    ]
)]
#[OA\Schema(
    schema: 'CustomerMutationResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'message', type: 'string', example: 'تم إضافة المستخدم بنجاح.'),
        new OA\Property(property: 'data', ref: '#/components/schemas/Customer'),
    ]
)]
class CustomerSchemas
{
}
