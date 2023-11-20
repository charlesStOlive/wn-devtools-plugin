<?php

namespace Waka\DevTools\Console;

use System\Console\BaseScaffoldCommand;
use Arr;
use Winter\Storm\Parse\PHP\ArrayFile;

class CheckTrads extends BaseScaffoldCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected static $defaultName = 'waka:checktrads';

    /**
     * @var string The name and signature of this command.
     */
    protected $signature = 'waka:checktrads
        {--lg= : Force une langue autre que fr.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Met a jour les contenus de lang en fonction du contenu des fichiers.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'lang';

    /**
     * Lang.
     *
     * @var string
     */
    protected $lg = 'fr';

    /**
     * A mapping of stub to generated file.
     *
     * @var array
     */

    protected $stubs = [];

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        $directoriesPlugin = ['wcli', 'waka'];
        $directoriesAll = ['wcli', 'waka', '../themes'];
        //trace_log($this->option('lg'));
        if($lg = $this->option('lg')) {
            $this->lg = $lg;
        }
        //
        $analysed = $this->getAnalysedFiles($directoriesAll);
        $existing = $this->extractLanguageFiles($directoriesPlugin);

        [$DotedlangsContent, $keysAdded, $keyDeleted] = $this->synchronizeArrays($analysed['nested'], $existing);
        $this->updateLangFiles($DotedlangsContent);
    }

    function updateLangFiles($dotedVendorPluginLangs) {

        foreach($dotedVendorPluginLangs as $plugin => $langs) {
            $pluginExploded = explode('.', $plugin);
            $langFilePath = plugins_path(
            $pluginExploded[0]
            . DIRECTORY_SEPARATOR
            . $pluginExploded[1]
            . DIRECTORY_SEPARATOR
            . 'lang'
            . DIRECTORY_SEPARATOR
            . $this->lg
            . DIRECTORY_SEPARATOR
            . 'lang.php'
        );
        if (!file_exists($langFilePath)) {
            // $this->makeDirectory($langFilePath);
            $codeFile = $pluginExploded[0] . '.' . $pluginExploded[1] .'::lang';
            $comment = 'Le fichier existe pas il ne sera pas crée : ' . str_replace(base_path(), '', $codeFile);
            $this->comment($comment);
       } else {
            unlink($langFilePath);
            $comment = 'File updated: ' . str_replace(base_path(), '', $langFilePath);
            ArrayFile::open($langFilePath)->set($langs)->write();
            $this->info($comment);
        }

        // Store the localization messages to the determined file path
        
        // Inform the user
        
        }
    }

    function synchronizeArrays(array $filesContent, array $langsContent): array
    {
        $keysAdded = [];
        $keysDeleted = [];
        $newLangFile = [];

        $filesContent = Arr::dot($filesContent);
        $langsContent = Arr::dot($langsContent);

        foreach ($langsContent as $key => $content) {
            if (!array_key_exists($key, $filesContent)) {
                $langKey = str_replace('.lang', '::lang', $key);
                $keysDeleted[$langKey] = $content;
                unset($langsContent[$key]);
            }
        }

        foreach ($filesContent as $key => $content) {
            if (!array_key_exists($key, $langsContent)) {
                $myKey = explode('.', $key);
                $last_key = array_pop($myKey);
                $langKey = str_replace('.lang', '::lang', $key);
                $langsContent[$key] = 'Tr:' . $last_key;
                $keysAdded[$langKey] = 'Tr:' . $last_key;
            }
        }

        //On va reformater le tabelau pour qu'il soit groupé  en clef principal par vendor.plugin 

        foreach ($langsContent as $key => $value) {
            $exploded = explode('.lang.', $key);
            // $exploded[0] = waka.babyler, $exploded[1] = models.hotpoint.title, etc.
            if (count($exploded) == 2) {
                // Utilisez array_key_exists pour vérifier si la clé existe déjà dans $output
                if (!array_key_exists($exploded[0], $newLangFile)) {
                    $newLangFile[$exploded[0]] = []; // initialisez si ce n'est pas déjà fait
                }

                // Associez la sous-clé et la valeur
                $newLangFile[$exploded[0]][$exploded[1]] = $value;
            } else {
                $this->info('Cette clef est mal formaté et na pas été réutilisé dans le fichier finale'.$key.' => '.$value);
            }
        }
        $this->recursive_ksort($newLangFile);
        return [$newLangFile,  $keysAdded, $keysDeleted];
    }

    protected function recursive_ksort(&$array)
    {
        ksort($array);
        foreach ($array as &$value) {
            if (is_array($value)) {
                $this->recursive_ksort($value);
            }
        }
    }

    private function cleanArray(array &$array, int $level = 0)
    {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                $this->cleanArray($value, $level + 1);
                if (empty($value) && $level >= 2) {
                    unset($array[$key]);
                }
            } elseif ($value === null && $level >= 2) {
                unset($array[$key]);
            }
        }
    }



    private function extractLanguageFiles(array $vendors)
    {
        $basePath = base_path('plugins');
        $langArray = [];

        foreach ($vendors as $vendor) {
            $vendorPath = $basePath . '/' . $vendor;

            // Vérifie si le dossier du vendor existe
            if (is_dir($vendorPath)) {
                // Récupère la liste des dossiers de plugins
                $pluginFolders = glob($vendorPath . '/*', GLOB_ONLYDIR);

                foreach ($pluginFolders as $pluginFolder) {
                    $pluginName = basename($pluginFolder);
                    //trace_log($this->lg);
                    $langPath = $pluginFolder . sprintf('/lang/%s/lang.php', $this->lg);

                    // Vérifie si le dossier lang/fr existe pour ce plugin
                    if (file_exists($langPath)) {
                        $langContent = include $langPath;
                        $langArray[$vendor . '.' . $pluginName]['lang'] = $langContent;
                    }
                }
            }
        }

        return $langArray;
    }


    private function getAnalysedFiles($directories)
    {
        $strings = collect();
        $pattern = '/(?P<src>\w+\.\w+)::(?P<full_key>(?P<lg>lang)(\.(?P<mid_key>\w+(\.\w+)*))?\.(?P<key>\w+))/';
        foreach ($directories as $directory) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(plugins_path($directory)));
            foreach ($iterator as $file) {
                if ($file->isFile() && in_array($file->getExtension(), ['yaml', 'php', 'htm', 'md','block'])) {
                    $content = file_get_contents($file);
                    preg_match_all($pattern, $content, $matches, PREG_SET_ORDER, 0);
                    //trace_log($matches);
                    foreach ($matches as $i => $match) {
                        if (!$src = $match['src'] ?? false) {
                            continue;
                        }
                        if (!$lg = $match['lg'] ?? false) {
                            continue;
                        }
                        if (!$mid_key = $match['mid_key'] ?? false) {
                            $mid_key = $match['key'];
                        }
                        $strings->push([
                            'vp' => $src,
                            'file' => $lg,
                            'code' => $mid_key,
                            'key' => $match['key'],
                            'full' => $match[0] ?? null,
                        ]);
                    }
                } else {
                    // trace_log('refusé : '.trace_log($file->getFilename()));
                }
            }
        }


        $strings =  $strings->filter(function ($fileGroup, $vpKey) {
            $lang = \Lang::get($fileGroup['full']);
            if ($lang == $fileGroup['full']) {
                // trace_log('Pas de langue pour ' . $fileGroup['full']);
                //La trad n'existe pas on check si c'est un settings. 
                if (\Config::get($fileGroup['full'])) {
                    //trace_log('une config ! ' . $fileGroup['full']);
                    return false;
                }
            }
            return true;
        });
        $Collection = $strings->sortby('code')->sortby('file')->sortby('vp')->groupBy(['vp', 'file']);

        // $flattFileVpCollection = $Collection->map(function ($fileGroup, $vpKey) {
        //     return $fileGroup->map(function ($codeGroup, $fileKey) {
        //         $combined = [];
        //         foreach ($codeGroup as $item) {
        //             $combined[$item['code']] = null;
        //         }

        //         return $combined;
        //     });
        // });

        $nestedCollection = $Collection->map(function ($fileGroup, $vpKey) {
            // trace_log('--file group--');
            // trace_log($fileGroup);
            return $fileGroup->map(function ($codeGroup, $fileKey) {
                $combined = [];
                foreach ($codeGroup as $item) {
                    // trace_log($item);
                    $code = $item['code'] . '.' . $item['key'];
                    // trace_log($code);
                    array_set($combined, $code, null);
                }
                array_walk_recursive($combined, function (&$value, $key) {
                    if (is_array($value)) {
                        ksort($value);
                    }
                });

                return $combined;
            });
        });
        // trace_log($nestedCollection->toArray());
        return [
            'nested' => $nestedCollection->toArray(),
            // 'flatten' => $flattFileVpCollection->toArray(),
        ];
    }

    /**
     * Prepare variables for stubs.
     *
     * return @array
     */
    protected function prepareVars(): array
    {
        return [];

        // $this->p_model = $model = $this->option('model');
    }

    private function combine($directoriesPlugin, $directoriesAll)
    {
        //Etape 1 
        $test = $this->combineLanguageFiles($directoriesPlugin);
        //trace_log($test);
        // afin de decouper étape par étape je le transforme en json avant de le décoder à nouveau histoire de faire les chosés étape par étape. Attention le script modifie aussi cette page il faut donc remplacer le json en dur si il existe. 
        $elements = json_encode($test);
        //trace_log($elements);

        //Etape 2 
        // $elements = '{"movedToModels":["wcli.crm:...
        $elements = json_decode($elements);
        //trace_log($elements);
        $this->updatePageReferences($directoriesAll, $elements);

        //Etape 3
        $this->finalizeLanguageFiles($directoriesPlugin);
    }

    private function updatePageReferences(array $directories, $modifiedOccuences)
    {
        foreach ($directories as $directory) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(plugins_path($directory)));
            foreach ($iterator as $file) {
                if ($file->isFile() && in_array($file->getExtension(), ['yaml', 'php', 'htm', 'md', 'block'])) {
                    $content = file_get_contents($file);

                    // Pour les éléments movedToModels
                    foreach ($modifiedOccuences->movedToModels as $reference) {
                        $parts = explode("::", $reference);
                        $pattern = '/' . preg_quote($reference, '/') . '/';
                        $replacement = $parts[0] . '::lang.models.' . $parts[1];
                        $content = preg_replace($pattern, $replacement, $content);
                    }

                    // Pour les éléments movedToWorkflows
                    foreach ($modifiedOccuences->movedToWorkflows as $reference) {
                        //trace_log($reference);
                        $parts = explode("::", $reference);
                        $pattern = '/' . preg_quote($reference, '/') . '/';
                        $replacement = $parts[0] . '::lang.workflows.' . $parts[1];
                        $content = preg_replace($pattern, $replacement, $content);
                    }

                    // Sauvegarde du fichier mis à jour
                    file_put_contents($file, $content);
                }
            }
        }
    }

    private function finalizeLanguageFiles(array $directories)
    {
        foreach ($directories as $directory) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(plugins_path($directory)));

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getFilename() == 'lang_new.php') {
                    // Chemin vers l'ancien fichier lang.php
                    $oldLangFilePath = $file->getPath() . DIRECTORY_SEPARATOR . 'lang.php';

                    // Suppression de l'ancien fichier lang.php s'il existe
                    if (file_exists($oldLangFilePath)) {
                        unlink($oldLangFilePath);
                    }

                    // Renommer lang_new.php en lang.php
                    rename($file->getRealPath(), $oldLangFilePath);
                }
            }
        }
    }



    private function combineLanguageFiles(array $vendors)
    {
        $basePath = base_path('plugins');
        $movedToModels = [];
        $movedToWorkflows = [];

        foreach ($vendors as $vendor) {
            $vendorPath = $basePath . '/' . $vendor;

            if (is_dir($vendorPath)) {
                $pluginFolders = glob($vendorPath . '/*', GLOB_ONLYDIR);

                foreach ($pluginFolders as $pluginFolder) {
                    $pluginName = basename($pluginFolder);
                    $langPath = $pluginFolder . '/lang/'.$this->lang;

                    if (is_dir($langPath)) {
                        $newLangContent = [];
                        $langFiles = glob($langPath . '/*.php');

                        // Tri pour mettre lang.php en première position
                        usort($langFiles, function ($a, $b) {
                            if (basename($a) == 'lang.php') return -1;
                            if (basename($b) == 'lang.php') return 1;
                            return strcmp($a, $b);
                        });

                        foreach ($langFiles as $langFile) {
                            $langKey = basename($langFile, '.php');
                            $langContent = include $langFile;

                            if ($langKey !== "lang") {
                                if (isset($langContent['places'], $langContent['trans'])) {
                                    $newLangContent['workflows'][$langKey] = $langContent;
                                    $movedToWorkflows[] = $vendor . '.' . $pluginName . '::' . $langKey;
                                } else {
                                    $newLangContent['models'][$langKey] = $langContent;
                                    $movedToModels[] = $vendor . '.' . $pluginName . '::' . $langKey;
                                }
                            } else {
                                $newLangContent = array_merge($newLangContent, $langContent);
                            }
                        }

                        $this->recursive_ksort($newLangContent);

                        // Write to lang_new.php
                        $fileContent = '<?php' . PHP_EOL . PHP_EOL;
                        $fileContent .= 'return ' . \Brick\VarExporter\VarExporter::export($newLangContent) . ';' . PHP_EOL;
                        file_put_contents($langPath . '/lang_new.php', $fileContent);
                    }
                }
            }
        }

        return [
            'movedToModels' => $movedToModels,
            'movedToWorkflows' => $movedToWorkflows
        ];
    }
}
