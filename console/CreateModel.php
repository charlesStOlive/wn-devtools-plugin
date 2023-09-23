<?php namespace Waka\DevTools\Console;

use Winter\Storm\Support\Str;
use System\Console\BaseScaffoldCommand;

class CreateModel extends BaseScaffoldCommand
{
    use \Waka\DevTools\Classes\Traits\WakaConsoleHelperTrait;
    /**
     * @var string|null The default command name for lazy loading.
     */
    protected static $defaultName = 'waka:model';

    /**
     * @var string The name and signature of this command.
     */
    protected $signature = 'waka:model
        {plugin : The name of the plugin. <info>(eg: Winter.Blog)</info>}
        {model : The name of the model to generate. <info>(eg: Post)</info>}
        {--f|force : Overwrite existing files with generated files.}

        {--mode= : inject mode choice when command is called by another commande }
        {--finalTraits= : inject finalTraits choice when command is called by another commande }
        {--finalInterfaces= : inject finalInterfaces choice when command is called by another commande }

        {--no-migration : Don\'t create a migration file for the model}
        {--uninspiring : Disable inspirational quotes}
    ';

    /**
     * @var string The console command description.
     */
    protected $description = 'Creates a new model.';

    /**
     * @var string The type of class being generated.
     */
    protected $type = 'Model';

    /**
     * @var string The argument that the generated class name comes from
     */
    protected $nameFrom = 'model';

    /**
     * @var array A mapping of stubs to generated files.
     */
    protected $stubs = [
        'scaffold/model/model.stub'   => 'models/{{studly_name}}.php',
        'scaffold/model/fields.stub'  => 'models/{{lower_name}}/fields.yaml',
        'scaffold/model/columns.stub' => 'models/{{lower_name}}/columns.yaml',
        'scaffold/model/map.stub' => 'models/{{lower_name}}/map.yaml',
    ];

    /**
     * Prepare variables for stubs.
     */
    protected function prepareVars(): array
    {
        
        $this->getChoices();

        
        
        $vars = parent::prepareVars();
        $vars = array_merge(array_fill_keys($this->finalTraits,true), $vars);
        $vars = array_merge(array_fill_keys($this->finalInterfaces, true), $vars);

        if(!$ds = $vars['has_ds'] ?? false) {
            unset($this->stubs['scaffold/model/map.stub']);
        }

        return $vars;
    }

    /**
     * Adds controller & model lang helpers to the vars
     */
    protected function processVars($vars): array
    {
        $vars = parent::processVars($vars);
        $vars['table_name'] = "{$vars['lower_author']}_{$vars['lower_plugin']}_{$vars['snake_plural_name']}";
        $vars['model_lang_key_short'] = "models.{$vars['lower_name']}";
        $vars['model_lang_key'] = "{$vars['plugin_id']}::lang.{$vars['model_lang_key_short']}";
        return $vars;
    }

    /**
     * Gets the localization keys and values to be stored in the plugin's localization files
     * Can reference $this->vars and $this->laravel->getLocale() internally
     */
    protected function getLangKeys(): array
    {
        return [
            'models.general.id' => 'ID',
            'models.general.created_at' => 'Created At',
            'models.general.updated_at' => 'Updated At',
        ];
    }

   
}
