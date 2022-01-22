<?php

namespace JocelimJr\LumenDTSS\Providers;

use JocelimJr\LumenDTSS\Interfaces\DTSSRepositoryInterface;
use JocelimJr\LumenDTSS\Repositories\DTSSRepository;
use Illuminate\Support\ServiceProvider;

class LumenDTSSServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(DTSSRepositoryInterface::class, DTSSRepository::class);
    }
}
