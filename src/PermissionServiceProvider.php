<?php

namespace Jobsys\Permission;


use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class PermissionServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot(Filesystem $filesystem): void
    {
        // Publish configuration files
        $this->publishes([
            __DIR__ . '/../config/permission.php' => config_path('permission.php')
        ], 'config');

        $this->publishes([
            __DIR__ . '/../database/migrations/create_permission_tables.php' => $this->getMigrationFileName($filesystem, 'create_permission_tables.php'),
        ], 'migrations');


        $this->publishes([
            __DIR__ . '/../views' => resource_path('views/vendor/permission'),
        ], 'views');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        //合并配置
        $this->mergeConfigFrom(
            __DIR__ . '/../config/permission.php', 'permission'
        );
    }

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     *
     * @param Filesystem $filesystem
     * @return string
     */
    protected function getMigrationFileName(Filesystem $filesystem, $migrationFileName): string
    {
        $timestamp = date('Y_m_d_His');

        return Collection::make($this->app->databasePath() . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem) {
                return $filesystem->glob($path . '*_create_permission_tables.php');
            })->push($this->app->databasePath() . "/migrations/{$timestamp}_create_permission_tables.php")
            ->flatMap(function ($path) use ($filesystem, $migrationFileName) {
                return $filesystem->glob($path . '*_' . $migrationFileName);
            })
            ->push($this->app->databasePath() . "/migrations/{$timestamp}_{$migrationFileName}")
            ->first();
    }
}
