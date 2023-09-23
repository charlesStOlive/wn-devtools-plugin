<?php

namespace Waka\DevTools\Console;

use System\Console\BaseScaffoldCommand;
use Winter\Storm\Support\Str;

/**
 * @TODO:
 * - Support creating related permissions and navigation items and injecting them into the plugin
 */
class CreateAll extends BaseScaffoldCommand
{
    use \Waka\DevTools\Classes\Traits\WakaConsoleHelperTrait;
    /**
     * @var string|null The default command name for lazy loading.
     */
    protected static $defaultName = 'waka:all';

    /**
     * @var string The name and signature of this command.
     */
    protected $signature = 'waka:all
        {plugin : The name of the plugin. <info>(eg: Winter.Blog)</info>}
        {model : The name of the controller to generate. <info>(eg: Posts)</info>}

        { --m|migration : Creer la migration }

        {--mode= : inject mode choice when command is called by another commande }
        {--finalTraits= : inject finalTraits choice when command is called by another commande }
        {--finalInterfaces= : inject finalInterfaces choice when command is called by another commande }
        
        {--force : Overwrite existing files with generated files.}
        {--uninspiring : Disable inspirational quotes}
    ';

    /**
     * @var string The console command description.
     */
    protected $description = 'Creates controller, model, yaml, etc.';



    /**
     * Execute the console command.
     *
     * @return int|bool|null
     */
    public function handle()
    {
        $this->getChoices();

        $this->info('Controller création--------------------');

        $this->call('waka:controller', [
            'plugin' => $this->argument('plugin'),
            'controller' => $this->argument('model') . 's',
            '--mode' => $this->mode,
            '--finalTraits' => implode(',', array_values($this->finalTraits)),
            '--finalInterfaces' => implode(',', array_values($this->finalInterfaces)),
            '--force' => $this->option('force'),
        ]);

        $this->info('Model création--------------------');

        $this->call('waka:model', [
            'plugin' => $this->argument('plugin'),
            'model' => $this->argument('model'),
            '--mode' => $this->mode,
            '--finalTraits' => implode(',', array_values($this->finalTraits)),
            '--finalInterfaces' => implode(',', array_values($this->finalInterfaces)),
            '--force' => $this->option('force'),
        ]);

        if ($this->option('migration')) {
            $this->info('Model création--------------------');

            $this->call('waka:migration', [
                'plugin' => $this->argument('plugin'),
                '--model' => $this->argument('model'),
                '--create' => true,
                '--mode' => $this->mode,
                '--finalTraits' => implode(',', array_values($this->finalTraits)),
                '--finalInterfaces' => implode(',', array_values($this->finalInterfaces)),
                '--force' => $this->option('force'),
            ]);
        }
    }
}
