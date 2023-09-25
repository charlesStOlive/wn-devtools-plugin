<?php namespace Waka\DevTools;

use Backend;
use Backend\Models\UserRole;
use System\Classes\PluginBase;

/**
 * devTools Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     */
    public function pluginDetails(): array
    {
        return [
            'name'        => 'waka.devtools::lang.plugin.name',
            'description' => 'waka.devtools::lang.plugin.description',
            'author'      => 'waka',
            'icon'        => 'icon-leaf'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     */
    public function register(): void
    {
        $this->registerConsoleCommand('waka:trad', 'Waka\DevTools\Console\PluginTrad');
        $this->registerConsoleCommand('waka:excelTrad', 'Waka\DevTools\Console\ExcelTrad');
        $this->registerConsoleCommand('waka:checktrads', 'Waka\DevTools\Console\PluginscheckAllTrad');
        $this->registerConsoleCommand('waka:tradauto', 'Waka\DevTools\Console\TradautoCommand');
        $this->registerConsoleCommand('waka.uicolors', 'Waka\DevTools\Console\CreateUiColors');
        $this->registerConsoleCommand('waka:all', 'Waka\DevTools\Console\CreateAll');
        $this->registerConsoleCommand('waka:controller', 'Waka\DevTools\Console\CreateController');
        $this->registerConsoleCommand('waka:model', 'Waka\DevTools\Console\CreateModel');
        $this->registerConsoleCommand('waka:migration', 'Waka\DevTools\Console\CreateMigration');
        $this->registerConsoleCommand('waka:relation', 'Waka\DevTools\Console\CreateRelation');
        $this->registerConsoleCommand('waka:syncRepo', 'Waka\DevTools\Console\SyncRepo');
        //$this->registerConsoleCommand('waka.mc', 'Waka\DevTools\Console\CreateModelController');

    }

    /**
     * Boot method, called right before the request route.
     */
    public function boot(): void
    {

    }

    public function registerMarkupTags()
    {
        return [
            'functions' => [
                'stubCreator' => function ($template, $allData, $secificData, $dataName = null) {
                    $allData['specific'] = $secificData;
                    $allData['dataName'] = $dataName;
                    $templatePath = plugins_path('waka/devtools/console/' . $template);
                    $templateContent = \File::get($templatePath);
                    $content = \Twig::parse($templateContent, $allData);
                    return $content;
                },
                'var_dump' => function ($expression) {
                    ob_start();
                    var_dump($expression);
                    $result = ob_get_clean();

                    return $result;
                },

            ],
        ];
    }

    /**
     * Registers any frontend components implemented in this plugin.
     */
    public function registerComponents(): array
    {
        return []; // Remove this line to activate

        return [
            'Waka\DevTools\Components\MyComponent' => 'myComponent',
        ];
    }

    /**
     * Registers any backend permissions used by this plugin.
     */
    public function registerPermissions(): array
    {
        return []; // Remove this line to activate

        return [
            'waka.devtools.some_permission' => [
                'tab' => 'waka.devtools::lang.plugin.name',
                'label' => 'waka.devtools::lang.permissions.some_permission',
                'roles' => [UserRole::CODE_DEVELOPER, UserRole::CODE_PUBLISHER],
            ],
        ];
    }

    /**
     * Registers backend navigation items for this plugin.
     */
    public function registerNavigation(): array
    {
        return []; // Remove this line to activate

        return [
            'devtools' => [
                'label'       => 'waka.devtools::lang.plugin.name',
                'url'         => Backend::url('waka/devtools/mycontroller'),
                'icon'        => 'icon-leaf',
                'permissions' => ['waka.devtools.*'],
                'order'       => 500,
            ],
        ];
    }
}
