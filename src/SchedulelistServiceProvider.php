<?php 

namespace Sawyes;

use Illuminate\Support\ServiceProvider;

class SchedulelistServiceProvider extends ServiceProvider 
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * 在注册后进行服务的启动。
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__ . '/../config/sawyes.php';

        $this->publishes([
            $configPath => config_path('sawyes.php')
        ], 'Sawyes\SchedulelistServiceProvider');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([
            Commands\ScheduleDetail::class
        ]);
    }
}
