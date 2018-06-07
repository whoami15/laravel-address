<?php

namespace Yajra\Address;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Yajra\Address\Controllers\BarangaysController;
use Yajra\Address\Controllers\CitiesController;
use Yajra\Address\Controllers\ProvincesController;
use Yajra\Address\Controllers\RegionsController;
use Yajra\Address\Repositories\Barangays\BarangaysRepository;
use Yajra\Address\Repositories\Barangays\BarangaysRepositoryEloquent;
use Yajra\Address\Repositories\Barangays\CachingBarangaysRepository;
use Yajra\Address\Repositories\Cities\CachingCitiesRepository;
use Yajra\Address\Repositories\Cities\CitiesRepository;
use Yajra\Address\Repositories\Cities\CitiesRepositoryEloquent;
use Yajra\Address\Repositories\Provinces\CachingProvincesRepository;
use Yajra\Address\Repositories\Provinces\ProvincesRepository;
use Yajra\Address\Repositories\Provinces\ProvincesRepositoryEloquent;
use Yajra\Address\Repositories\Regions\CachingRegionsRepository;
use Yajra\Address\Repositories\Regions\RegionsRepository;
use Yajra\Address\Repositories\Regions\RegionsRepositoryEloquent;

class AddressServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->mergeConfigFrom(__DIR__ . '/../config/address.php', 'address');

        Route::group([
            'prefix'     => config('address.prefix'),
            'middleware' => config('address.middleware'),
            'as'         => 'address.',
        ], function () {
            Route::get('regions', RegionsController::class . '@all')->name('regions.all');
            Route::get('provinces', ProvincesController::class . '@all')->name('provinces.all');
            Route::get('provinces/{regionId}', ProvincesController::class . '@getByRegion')->name('provinces.region');
            Route::get('cities/{provinceId}', CitiesController::class . '@getByProvince')->name('cities.province');
            Route::get('cities/{regionId}/{provinceId}', CitiesController::class . '@getByRegionAndProvince')
                 ->name('cities.region.province');
            Route::get('barangays/{cityId}', BarangaysController::class . '@getByCity')->name('barangay.city');
        });
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(RegionsRepository::class, function () {
            return new CachingRegionsRepository(
                $this->app->make(RegionsRepositoryEloquent::class),
                $this->app['cache.store']
            );
        });
        $this->app->singleton(ProvincesRepository::class, function () {
            return new CachingProvincesRepository(
                $this->app->make(ProvincesRepositoryEloquent::class),
                $this->app['cache.store']
            );
        });
        $this->app->singleton(CitiesRepository::class, function () {
            return new CachingCitiesRepository(
                $this->app->make(CitiesRepositoryEloquent::class),
                $this->app['cache.store']
            );
        });
        $this->app->singleton(BarangaysRepository::class, function () {
            return new CachingBarangaysRepository(
                $this->app->make(BarangaysRepositoryEloquent::class),
                $this->app['cache.store']
            );
        });
    }
}