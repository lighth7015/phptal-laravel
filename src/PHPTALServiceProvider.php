<?php

namespace MarcosHoo\LaravelPHPTAL;

use Illuminate\Support\ServiceProvider as Provider;
use lighth7015\LaravelPHPTAL\Engines\PHPTALEngine;

class ServiceProvider extends Provider {
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $app = $this->app;
        $factory = $app->make('view');

        // This is only for the template system.
        $templateIdString = '';
        $templateIdsArray = $this->app['config']->get('template.templateIds');
        if (is_array($templateIdsArray) && !empty($templateIdsArray) && !empty($_SERVER['HTTP_HOST'])) {
            $templateIdString = array_search($_SERVER['HTTP_HOST'], $templateIdsArray);
            $templateIdString .= '/';
        }
        define('TEMPLATE_ID', $templateIdString);

        // Config
        $configPath = __DIR__ . '/../config/config.php';
        $this->mergeConfigFrom($configPath, 'phptal');
        $this->publishes([
            $configPath => config_path('phptal.php')
        ]);

        // Set extensions
        $extensions = $app['config']['phptal.extensions'];
        $extensions = (!is_array($extensions) ? [ explode(',', $extensions) ] : $extensions);
        foreach ($extensions as $extension) {
            $factory->addExtension(trim($extension), 'tal');
        }

        // Set template paths
        $templateRepositories = $app['config']['phptal.templateRepositories'];
        $templateRepositories = !is_array($templateRepositories) ? explode(',', $templateRepositories) : $templateRepositories;
        $paths = $app['config']['view.paths'];
        if ($templateRepositories) {
            foreach ($templateRepositories as $repo) {
                if (!in_array($repo, $paths)) {
                    $app['config']['view.paths'] = array_merge($app['config']['view.paths'], [
                        $repo
                    ]);
                }
            }
        }

        $this->app->extend('view.engine.resolver', function () use($factory) {
            $resolver = $factory->getEngineResolver();
            $this->registerPHPTALEngine($resolver);
            return $resolver;
        });
    }

    /**
     * Register the PHPTAL engine implementation.
     *
     * @param \Illuminate\View\Engines\EngineResolver $resolver
     * @return void
     */
    public function registerPHPTALEngine($resolver)
    {
        $resolver->register('tal', function () {
            return new PHPTALEngine($this->app);
        });
    }
}
