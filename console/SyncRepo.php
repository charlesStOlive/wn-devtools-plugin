<?php

namespace Waka\DevTools\Console;

use Illuminate\Console\Command;
use Winter\Storm\Support\Str;

class SyncRepo extends Command
{
    use \Waka\DevTools\Classes\Traits\WakaConsoleHelperTrait;

    protected static $defaultName = 'waka:syncrepo';
    protected $signature = 'waka:syncrepo';
    protected $description = 'Synchronise les dépôts';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Récupération des chemins des dépôts à partir du fichier .env
        $repoPath = env('REPOS_TO_SYNC', []);
        if (empty($repoPath)) {
            $this->error('La liste de plugins/waka n\'est pas définie dans REPOS_TO_SYNC');
            return;
        } else {
            $repoPath = explode(',', $repoPath);
        }

        $excludeDir = '.git';
        $envRepoPath = env('SRC_REPO');
        if (!$envRepoPath) {
            $this->error('Le chemin SRC_REPO n\'est pas défini dans le fichier .env');
            return;
        }

        $mode = $this->choice('mode', ['stage, only', 'stage,commit,push', 'stage,commit', 'push only'], 0, null, false);
        $commitName = null;
        if (in_array($mode, ['stage,commit,push', 'stage,commit'])) {
            $commitName = $this->ask('Nom du commit global :', 'update plugin');
        }

        foreach ($repoPath as $repo) {
            $folderRepoPath = $envRepoPath . '/wn-' . $repo . '-plugin';
            $wakaPath = base_path('/plugins/waka/' . $repo);

            if (!file_exists($wakaPath) || !is_dir($wakaPath)) {
                $this->error("Le chemin spécifié pour le plugin waka {$repo} n'existe pas ou n'est pas un dossier.");
                continue;
            }

            // Copie des fichiers avec robocopy
            $command = "robocopy \"$wakaPath\" \"$folderRepoPath\" /MIR /XD \"$excludeDir\" 2>&1";
            exec($command, $output, $returnVar);
            trace_log($returnVar);
            if ($returnVar >3) { // robocopy retourne 1 pour une copie réussie avec des fichiers copiés
                $this->error("Erreur lors de la copie des fichiers pour {$repo}:\n" . implode("\n", $output));
                continue;
            }

            $this->info("Copie réussie pour {$repo}");

            // Changement de répertoire
            chdir($folderRepoPath);

            // Ajout des fichiers à git
            exec('git add . 2>&1', $output, $returnVar);
            if ($returnVar !== 0) {
                $this->error("Erreur lors de l'ajout des fichiers pour {$repo}.");
                continue;
            }

            // Vérification des changements
            exec('git diff --cached --quiet', $output, $returnVar);
            if ($returnVar !== 0) {
                $this->info("Changements détectés pour {$repo}.");
                if (in_array($mode, ['stage,commit,push', 'stage,commit'])) {
                    exec('git commit -m "' . escapeshellcmd($commitName) . '" 2>&1', $output, $returnVar);
                    if ($returnVar !== 0) {
                        $this->error("Erreur lors du commit pour {$repo}.");
                        continue;
                    }
                    $this->info("Commit réussi pour {$repo}");
                }
            } else {
                $this->info("Aucun changement à committer pour {$repo}.");
            }

            // Push des changements
            if (in_array($mode, ['stage,commit,push', 'push only'])) {
                exec('git push 2>&1', $output, $returnVar);
                if ($returnVar !== 0) {
                    $this->error("Erreur lors du push pour {$repo}.");
                    continue;
                }
                $this->info("Push réussi pour {$repo}");
            }
        }
    }
}
