<?php
namespace App\Shell;

use Cake\Console\Shell;

class MigrateRadiusdeskShell extends Shell{

     public $tasks = ['Migrate'];

     public function initialize(){
        parent::initialize();
        
    }

    public function main(){
        $this->Migrate->main();
    }
}
