<?php

namespace Waka\DevTools\Classes\Traits;

use Waka\Cloudis\Classes\YamlParserRelation;
use \Waka\Cloudis\Models\Settings as CloudisSettings;

trait WakaConsoleHelperTrait
{
    private $mode;
    private $finalTraits = [];
    private $finalInterfaces = [];
    
    private $chooseMode = [
        'complete(relation,ds,prod,workf,reord,s_tab)', 
        'noworkflow(relation,ds,prod,reord,s_tab)', 
        'basic(relation,ds,prod,s_tab)', 
        'basic2(ds,prod,s_tab)', 
        'settings(ds,prod,in_set)', 
        'settingsRelation(relationds,prod,in_set)', 
        'settings_reorder(ds,prod,reord,in_set)', 
        'vide',
        'select',
    ];
    private $traitOptions = [
        'RIEN',
        'has_relation',
        'has_ds',
        'has_productor',
        'has_workflow',
        'has_reorder',
    ];
    private $interfacesOptions = [
        'RIEN',
        'has_secondary_tab',
        'in_settings',
    ];


    public function getChoices() {
        $this->mode = $this->option('mode') ?? $this->choice('mode', $this->chooseMode , 8, null, false);
        if($this->mode == $this->chooseMode[0]) {
            $this->finalTraits = $this->traitOptions;
            $this->finalInterfaces = ['has_secondary_tab'];
        }
        else if($this->mode == $this->chooseMode[1]) {
            $this->finalTraits = [
                'has_relation',
                'has_ds',
                'has_productor',
                'has_reorder',
            ];
            $this->finalInterfaces = ['has_secondary_tab'];
        }
        else if($this->mode == $this->chooseMode[2]) {
            $this->finalTraits = [
                'has_relation',
                'has_ds',
                'has_productor',
            ];
            $this->finalInterfaces = ['has_secondary_tab'];
        }
        else if($this->mode == $this->chooseMode[3]) {
            $this->finalTraits = [
                'has_relation',
                'has_ds',
                'has_productor',
            ];
            $this->finalInterfaces = ['in_settings'];
        }
        else if($this->mode == $this->chooseMode[4]) {
            $this->finalTraits = [
                'has_ds',
                'has_productor',
            ];
            $this->finalInterfaces = ['in_settings'];
        }
        else if($this->mode == $this->chooseMode[5]) {
            $this->finalTraits = [
                'has_ds',
                'has_productor',
                'has_reorder',
            ];
            $this->finalInterfaces = ['in_settings'];
        }
         else if($this->mode == $this->chooseMode[6]) {
            $this->finalTraits = [
                'has_ds',
                'has_productor',
                'has_reorder',
            ];
            $this->finalInterfaces = ['in_settings'];
        }
        else if($this->mode == $this->chooseMode[7]) {
            $this->finalTraits = [];
            $this->finalInterfaces = [];
        }
        else  {
             if($this->option('finalTraits'))  {
                $this->finalTraits = explode(',',$this->option('finalTraits'));
            } else {
                $this->finalTraits = $this->choice('Ajouter des traits ? ', $this->traitOptions, 0, null, true);
            }
            if($this->option('finalInterfaces'))  {
                $this->finalInterfaces = explode(',',$this->option('finalInterfaces'));
            } else {
                $this->finalInterfaces = $this->choice('Ajouter des opt d interfaces ? ', $this->interfacesOptions, 0, null, true);
            }
        }
    }

    

}