<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Limit to 5 login attempts per minute per email + IP combination
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->input('email').'|'.$request->ip())
                ->response(function () {
                    return response()->json([
                        'message' => 'Too many login attempts. Please try again in 1 minute.'
                    ], 429);
                });
        });

         // Configure Scramble to use a Bearer token security scheme globally
        Scramble::configure()
        ->withDocumentTransformers(function (OpenApi $openApi) {
            $openApi->secure(
                SecurityScheme::http('bearer')
            );
        });
    }
}
