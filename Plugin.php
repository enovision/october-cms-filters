<?php
namespace Enovision\Filters;

use Enovision\Filters\Services\FilterService;
use Illuminate\Support\Facades\App;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    protected $app;

    public function __construct(\Illuminate\Contracts\Foundation\Application $app)
    {
        parent::__construct($app);

        $this->app = $app;
    }

    public function registerComponents()
    {
        return [
            'Enovision\Filters\Components\Filters' => 'Filters'
        ];
    }

    public function register()
    {
        $this->app->singleton('Enovision\FilterService', function ($app) {
            return new FilterService($app);
        });
    }
}
