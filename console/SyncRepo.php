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
        $repoPath = env('REPOS_TO_SYNC', []);
        if (empty($repoPath)) {
            $this->info('Il n y a pas de liste de plugins/waka dans le fichier env REPOS_TO_SYNC');
            return;
        }  else {
            $repoPath = explode(',', $repoPath);
        }
        $excludeDir = '.git';
        $envRepoPath = env('SRC_REPO');
        $commitAndPush = $this->ask('Voulez vous faire un comit et un push si il y a des modifications dans les repos ? laisser à null pour ne pas le faire.', null);
        foreach ($repoPath as $repo) {
            $folderRepoPath =  $envRepoPath . '/wn-' . $repo . '-plugin';
            $wakaPath = base_path('/plugins/waka/' . $repo);
            //trace_log($folderRepoPath);
            //trace_log($wakaPath);
            $command = "robocopy \"$wakaPath\" \"$folderRepoPath\" /MIR /XD \"$excludeDir\" 2>&1";
            $output = shell_exec($command);
            $this->info($output);
            chdir($folderRepoPath);
            shell_exec('git add . 2>&1');
            // Vérifier s'il y a des changements dans l'index par rapport à HEAD
            exec('git diff --cached --quiet', $output, $return_var);
            // Si $return_var est différent de 0, alors il y a des changements à committer
            if ($return_var !== 0) {
                $this->info("Des changements sont en attente de commit pour le repo : ".$repo);
                if($commitAndPush) {
                    $this->info("Je procède au comit & push pour le repo : ".$repo);
                }
                // ... vous pouvez exécuter `git commit` ici si vous le souhaitez
            } else {
                $this->info("Aucun changement à committer.");
            }
            
        }
    }
}
