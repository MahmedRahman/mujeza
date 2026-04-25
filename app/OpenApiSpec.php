<?php

namespace App;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Mujeza API',
    description: 'API documentation for Mujeza products and related endpoints.'
)]
#[OA\Server(
    url: 'http://127.0.0.1:8000',
    description: 'Default API server'
)]
class OpenApiSpec
{
}
