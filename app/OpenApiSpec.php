<?php

namespace App;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Mujeza API',
    description: 'API documentation for Mujeza — products, orders, complaints, and WhatsApp integrations. Order statuses: طلب جديد، تم التأكيد، قيد التجهيز، خرج للتوصيل، مكتمل، ملغي.'
)]
#[OA\Server(
    url: 'https://app.taheelplus.com',
    description: 'Production API server'
)]
#[OA\Server(
    url: 'http://127.0.0.1:8000',
    description: 'Local development server'
)]
class OpenApiSpec
{
}
