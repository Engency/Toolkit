<?php

namespace Engency\Providers;

use Engency\Validators\HtmlValidator;
use Engency\Validators\YoutubeUrlValidator;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class EngencyServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function boot(Request $request)
    {
        HtmlValidator::register();
        YoutubeUrlValidator::register();
    }
}