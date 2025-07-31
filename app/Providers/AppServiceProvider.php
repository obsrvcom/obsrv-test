<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

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
        // Custom Blade directive for precise time formatting
        Blade::directive('preciseTimeAgo', function ($expression) {
            return "<?php echo app()->call(function(\$date) {
                if (!\$date instanceof \Carbon\Carbon) {
                    \$date = \Carbon\Carbon::parse(\$date);
                }

                \$now = \Carbon\Carbon::now();
                \$diff = \$now->diff(\$date);

                if (\$diff->y > 0) {
                    return \$diff->y . ' yr' . (\$diff->y > 1 ? 's' : '') . ' ago';
                } elseif (\$diff->m > 0) {
                    return \$diff->m . ' mo' . (\$diff->m > 1 ? 's' : '') . ' ago';
                } elseif (\$diff->d > 0) {
                    if (\$diff->d >= 7) {
                        \$weeks = floor(\$diff->d / 7);
                        \$days = \$diff->d % 7;
                        return \$weeks . ' wk' . (\$weeks > 1 ? 's' : '') . (\$days > 0 ? ' ' . \$days . 'd' : '') . ' ago';
                    }
                    return \$diff->d . ' day' . (\$diff->d > 1 ? 's' : '') . ' ago';
                } elseif (\$diff->h > 0) {
                    \$result = \$diff->h . ' hr' . (\$diff->h > 1 ? 's' : '');
                    if (\$diff->i > 0) {
                        \$result .= ' ' . \$diff->i . 'm';
                    }
                    return \$result . ' ago';
                } elseif (\$diff->i > 0) {
                    return \$diff->i . ' min' . (\$diff->i > 1 ? 's' : '') . ' ago';
                } else {
                    return 'Just now';
                }
            }, ['date' => $expression]); ?>";
        });
    }
}
