<?php

namespace Bigperson\VkGeo;

use ATehnix\VkClient\Client;
use Bigperson\VkGeo\Commands\ImportCitiesCommand;
use Bigperson\VkGeo\Commands\ImportCountryCommand;
use Bigperson\VkGeo\Commands\ImportRegionsCommand;
use Illuminate\Support\ServiceProvider;

/**
 * Class VkGeoServiceProvider
 *
 * @package Bigperson\VkGeo
 */
class VkGeoServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([__DIR__.'/../publish/config/vk-geo.php' => config_path('vk-geo.php')], 'config');
        $this->publishes([__DIR__.'/../publish/database/' => database_path('migrations')], 'database');

        $this->bootCommands();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Client::class, function () {
            $client = new Client(config('vk-geo.version', '5.53'));
            $client->setDefaultToken(config('vk-geo.token', 'some-token'));

            return $client;
        });
    }

    /**
     * Booting console commands
     *
     * @return void
     */
    private function bootCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ImportCountryCommand::class,
                ImportRegionsCommand::class,
                ImportCitiesCommand::class,
            ]);
        }
    }
}
