<?php namespace Waka\DevTools\Console;

use Winter\Storm\Scaffold\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;
use Mexitek\PHPColors\Color;
use Twig;
class ReplaceModules extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'waka:ReplaceModules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Ui Less files.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Less';

    /**
     * A mapping of stub to generated file.
     *
     * @var array
     */

    protected $stubsForBehaviors = [
        'behavior_replace/FormController.stub' => '/modules/backend/behaviors/FormController.php',
        'behavior_replace/js/RelationController.stub' => '/modules/backend/behaviors/RelationController.php',
    ];

    protected $stubsForRepeater = [
        'repeater_replace/partials/_repeater_item.stub' => 'modules/backend/formwidgets/repeater/partials/_repeater_item.php',
        'repeater_replace/js/repeater.stub' => '/modules/backend/formwidgets/repeater/assets/js/repeater.js',
        'repeater_replace/Repeater.stub' => '/modules/backend/formwidgets/Repeater.php',
        
    ];


    protected $stubsForColors = [
        'ui_less/global.variables.stub' => '/modules/system/assets/ui/less/global.variables.less',
        'ui_less/global.mixins.triangle.stub' => '/modules/system/assets/ui/less/global.mixins.triangle.less',
        'ui_less/vars.stub' => '/plugins/wcli/wconfig/assets/css/vars.less',
        
    ];

    

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        //$this->vars = $this->processVars($this->prepareVars());
        $this->vars = $this->prepareVars();

        $stubsForBehaviors = array_keys($this->stubsForBehaviors);

        foreach ($stubsForBehaviors as $stub) {
            $this->makeStub($stub);
        }

        $this->info('update des behaviors successfull.');


        $stubsForRepeater = array_keys($this->stubsForRepeater);

        foreach ($stubsForRepeater as $stub) {
            $this->makeStub($stub);
        }

        $this->info('update du repeater successfull.');

        //trace_log($this->vars);

        $stubsForColors = array_keys($this->stubsForColors);

        foreach ($stubsForColors as $stub) {
            $this->makeStub($stub);
        }

        $this->info('update des fichiers couleurs.');

        $sourceFilePath = $this->getSourcePath() . '/../../../../modules/system/assets/ui/less';

        $files = \File::allFiles($sourceFilePath);
        foreach($files as $file) {
            $filePath = $file->getRealPath();
            $stringContent = $file->getContents();
            $stringContent = $this->updateColorContent($stringContent);
            $this->files->put($filePath, $stringContent);
        }

        $this->info('Less created successfully.');

        $this->call('mix:compile');
    }

    public function updateColorContent($content) {
        $replaceColors = $this->vars['replace'];
        foreach($replaceColors as $key=>$color) {
            $content = str_replace($key, $color, $content);
        }
        return $content;

    }

    protected function getSourcePath(): string
    {
        $className = get_class($this);
        $class = new \ReflectionClass($className);

        return dirname($class->getFileName());
    }

    public function makeStub($stubName)
    {
        //trace_log($stubName);
        if (!isset($this->stubs[$stubName])) {
            return;
        }

        $sourceFile = $this->getSourcePath() . '/' . $stubName;
        $destinationFile = $this->getDestinationPath() . '/' . $this->stubs[$stubName];
        $destinationContent = $this->files->get($sourceFile);

        /*
         * Parse each variable in to the destination content and path
         */
        $destinationContent = Twig::parse($destinationContent, $this->vars);
        $destinationFile = Twig::parse($destinationFile, $this->vars);

        $this->makeDirectory($destinationFile);

        /*
         * Make sure this file does not already exist
         */
        // if ($this->files->exists($destinationFile)) {
        //     $this->files->put($destinationFile, $destinationContent);
        // } else {

        // }
        //trace_log($destinationFile);
        $this->files->put($destinationFile, $destinationContent);
        //trace_log($destinationFile);
        //trace_log($destinationContent);

        // $this->files->put($destinationFile, $destinationContent);
    }

    /**
     * Prepare variables for stubs.
     *
     * return @array
     */
    protected function prepareVars():array
    {

        $primaryColor = new Color(\Config::get('wcli.wconfig::brand_data.primaryColor'));
        $secondaryColor = new Color(\Config::get('wcli.wconfig::brand_data.secondaryColor'));
        $accentColor = new Color(\Config::get('wcli.wconfig::brand_data.accentColor'));

        $primary  = '#'.$primaryColor->getHex();
        $primary_dark =  '#'.$primaryColor->darken(10);
        $primary_light = '#'.$primaryColor->lighten(10);
        $primary_light2 = '#'.$primaryColor->lighten(20);
        $secondary = '#'.$secondaryColor->getHex();
        $accent = '#'.$accentColor->getHex();
        $accent_light = '#'.$accentColor->lighten(10);

        $replaceColors = [];

       
        $replaceColors = [
            '#34495e' => '@brand-primary',
            '#0181b9' => '@color-accent',
            '#1681BA' => '@brand-primary',
            '#1F99DC' => '@color-accent',
            '#3498db' => '@color-accent',
            '#4da7e8' => 'darken(@color-primary, 12%)',
            '#385487' => 'darken(@color-primary, 25%)',
            '#405261' => '@brand-secondary',
            '#e67e22' => 'darken(@brand-accent,15%)',
            '#2A3E51' => 'darken(@brand-primary, 40%)',
            '#1F99DC' => 'lighten(@color-accent, 12%)',
        ];


        //BrandPrimary 34495e

        //Primary button/link 0181b9 balloon 1681BA  checkbox : 1F99DC  brand-accentPRIMARY/3498db  active-bg/4da7e8

        //Accent da5700

        //secondary : 405261 (btn_sec)  brand-secondaryACCENT:e67e22

        //other/default bcc3c7 (defaut balloon)

        return [
            'primary' => $primary,
            'secondary' => $secondary,
            'accent' => $accent,
            'primary_dark' => $primary_dark,
            'primary_light' => $primary_light,
            'primary_light2' => $primary_light2,
            'replace' => $replaceColors,
        ];
    }
}
