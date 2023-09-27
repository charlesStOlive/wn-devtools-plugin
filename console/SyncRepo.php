<?php

namespace Waka\DevTools\Console;

use Illuminate\Console\Command;
use Winter\Storm\Support\Str;

/**
 * @TODO:
 * - Support creating related permissions and navigation items and injecting them into the plugin
 */
class SyncRepo extends Command
{
    use \Waka\DevTools\Classes\Traits\WakaConsoleHelperTrait;
    /**
     * @var string|null The default command name for lazy loading.
     */
    protected static $defaultName = 'waka:syncrepo';

    /**
     * @var string The name and signature of this command.
     */
    protected $signature = 'waka:syncrepo';

    /**
     * @var string The console command description.
     */
    protected $description = 'Sync les repo';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $repoPath = [
            'cloudis',
            'devtools',
            'docser',
            'ds',
            'maatexcel',
            'maillog',
            'mailmjml',
            'phpw',
            'productor',
            'salesforce',
            'snappypdf',
            'spatiebu',
            'vimeo',
            'wakablocs',
            'wakajob',
            'wakapi',
            'wformwidgets',
            'wiki',
            'workflow',
            'wutils',
        ];
        $excludeDir = '.git';
        $envRepoPath = env('SRC_REPO');
        foreach($repoPath as $repo) {
            $folderRepoPath =  $envRepoPath.'/wn-'.$repo.'-plugin';
            $wakaPath = base_path('/plugins/waka/'.$repo);
            //trace_log($folderRepoPath);
            //trace_log($wakaPath);
            $command = "robocopy \"$wakaPath\" \"$folderRepoPath\" /MIR /XD \"$excludeDir\" 2>&1";
            $output = shell_exec($command);
            $this->info($output);
            chdir($folderRepoPath);
            $gitOutput = shell_exec('git add . 2>&1');
            $this->info($gitOutput);
        }
        
        
    }
}
