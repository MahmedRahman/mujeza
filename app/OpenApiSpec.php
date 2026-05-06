<?php

namespace App;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Mujeza API',
    description: 'API documentation for Mujeza products and related endpoints.'
)]
#[OA\Server(
    url: 'https://app.taheelplus.com',
    description: 'Production API server'
)]
class OpenApiSpec
{
}
