<?php

namespace Modules\Zoom\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Zoom\Repositories\Interfaces\VirtualClassRepositoryInterface;
use Modules\Zoom\Repositories\VirtualClassRepository;

class ZoomServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
{
    \Log::info('ZoomServiceProvider est chargé');
    $this->app->bind(VirtualClassRepositoryInterface::class, VirtualClassRepository::class);
}


    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
