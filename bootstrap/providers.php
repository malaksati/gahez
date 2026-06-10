<?php

use App\Providers\AppServiceProvider;
use App\Providers\AuthServiceProvider;
use App\V1\Providers\V1ServiceProvider;

return [
    AppServiceProvider::class,
    AuthServiceProvider::class,
    V1ServiceProvider::class,
];
