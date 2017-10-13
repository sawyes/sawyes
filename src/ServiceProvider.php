<?php 

namespace Sawyes;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider 
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot() 
    {

        $this->handleConfigs();
        // $this->handleMigrations();
        // $this->handleViews();
        // $this->handleTranslations();
        // $this->handleRoutes();
        // 
        
        $this->handleLogSQL();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() 
    {

        // Bind any implementations.

    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides() 
    {

        return [];
    }

    private function handleConfigs() 
    {
        $configPath = __DIR__ . '/../config/sawyes.php';

        $this->publishes([
            $configPath => config_path('sawyes.php')
        ], 'sawyes');
    }

    private function handleTranslations() 
    {

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'packagename');
    }

    private function handleViews() 
    {

        $this->loadViewsFrom(__DIR__.'/../views', 'packagename');

        $this->publishes([__DIR__.'/../views' => base_path('resources/views/vendor/packagename')]);
    }

    private function handleMigrations() {

        $this->publishes([__DIR__ . '/../migrations' => base_path('database/migrations')]);
    }

    private function handleRoutes() 
    {

        include __DIR__.'/../routes.php';
    }

    /**
     * Logging sql in log file
     * @return [type] [description]
     */
    private function handleLogSQL()
    {

        if ($this->shouldCollect('debug_log') && isset($this->app['db'])) {
            $db = $this->app['db'];
            $db->listen(function ($sql) {
                foreach ($sql->bindings as $i => $binding) {
                    if ($binding instanceof \DateTime) {
                        $sql->bindings[$i] = $binding->format('\'Y-m-d H:i:s\'');
                    } else {
                        if (is_string($binding)) {
                            $sql->bindings[$i] = "'$binding'";
                        }
                    }
                }

                // Insert bindings into query
                $query = str_replace(array('%', '?'), array('%%', '%s'), $sql->sql);

                $query = vsprintf($query, $sql->bindings);

                $time  = (int) $sql->time / 1000;


                $executeInfo  = vsprintf("connection: %s \t\t time: %s s\r\n", [
                    $sql->connectionName,
                    $time
                ]);

                \Sawyes\Log\LoggerHelper::write($executeInfo . $query, [], 'sql');
            });
        }
    }

    private function shouldCollect($name, $default = false)
    {
        if(app()->bound('config')) {
            return $this->app['config']->get('sawyes.' . $name, $default);
        }
        return false;
    }

}
