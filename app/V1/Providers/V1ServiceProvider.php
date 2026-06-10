<?php

namespace App\V1\Providers;

use App\V1\Repositories\Contracts\ProductRepositoryInterface;
use App\V1\Repositories\ProductRepository;
use Illuminate\Support\ServiceProvider;

class V1ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
