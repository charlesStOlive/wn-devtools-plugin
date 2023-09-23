<?php

namespace Waka\DevTools\Console;

use System\Console\BaseScaffoldCommand;
use Winter\Storm\Support\Str;

/**
 * @TODO:
 * - Support creating related permissions and navigation items and injecting them into the plugin
 */
class CreateRelation extends BaseScaffoldCommand
{
    use \Waka\DevTools\Classes\Traits\WakaConsoleHelperTrait;
    /**
     * @var string|null The default command name for lazy loading.
     */
    protected static $defaultName = 'waka:relation';

    /**
     * @var string The name and signature of this command.
     */
    protected $signature = 'waka:relation
        {plugin : The name of the plugin. <info>(eg: Winter.Blog)</info>}
        {controller : Le nom du controlleur cible. <info>(eg: Posts)</info>}
        {relation_name  : Nom de la relation}
        {--model= : Defines the model name to use. If not provided, the singular name of the controller is used.}
        {--relation_pLugin= :  Le nom du plugin en relation}
        {--force : Overwrite existing files with generated files.}
        {--uninspiring : Disable inspirational quotes}
    ';

    /**
     * @var string The console command description.
     */
    protected $description = 'Creates a new controller relation.';

    /**
     * @var string The type of class being generated.
     */
    protected $type = 'Controller';

    /**
     * @var string The argument that the generated class name comes from
     */
    protected $nameFrom = 'controller';

    /**
     * @var array A mapping of stub to generated file.
     */
    protected $stubs = [
        'scaffold/relation/config_relation.stub'   => 'controllers/{{lower_name}}/config_relation.yaml',
        'scaffold/relation/field_relation.stub'   => 'controllers/{{lower_name}}/_field_{{snake_relation_name}}.php',
    ];

    /**
     * Prepare variables for stubs.
     */
    protected function prepareVars(): array
    {
        
        $vars = parent::prepareVars();

        $model = $this->option('model');
        if (!$model) {
            $model = Str::singular($vars['name']);
        }
        $vars['model'] = $model;
        $relationPLugin = $this->option('relation_pLugin') ?: $this->argument('plugin');
        trace_log($relationPLugin);
        $pluginCode = $this->getPluginIdentifier($relationPLugin);
        $parts = explode('.', $pluginCode);
        if (count($parts) !== 2) {
            throw new \InvalidArgumentException("Invalid plugin name, either too many dots or not enough. Example: Author.PluginName");
        }
        $pluginRelationName = array_pop($parts);
        $authorRelationName = array_pop($parts);
        $vars['plugin_relation_url'] = strtolower($pluginRelationName.'/'.$authorRelationName);
        $vars['relation_name'] = $this->argument('relation_name');
        return $vars;
    }

    /**
     * Adds controller & model lang helpers to the vars
     */
    protected function processVars($vars): array
    {
        $vars = parent::processVars($vars);
        $vars['controller_url'] = "{$vars['plugin_url']}/{$vars['lower_name']}";
        $vars['model_lang_key_short'] = "models.{$vars['lower_model']}";
        $vars['controller_lang_key_short'] = "controllers.{$vars['lower_plural_model']}";
        $vars['model_lang_key'] = "{$vars['plugin_id']}::lang.{$vars['model_lang_key_short']}";
        $vars['controller_lang_key'] = "{$vars['plugin_id']}::lang.{$vars['controller_lang_key_short']}";
        return $vars;
    }
}
