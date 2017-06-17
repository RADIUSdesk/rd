<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class NasTable extends Table
{
    public function initialize(array $config)
    {
        $this->addBehavior('Timestamp');
        $this->belongsTo('Users');
        
        $this->hasMany('NaRealms',['dependent' => false]); //Do not delete Realms when deleting NAS 
        $this->hasMany('NaTags',['dependent' => false]); //Do not delete Tags wen deleting NAS
         
        $this->hasMany('NaNotes',['dependent' => true]);
        $this->table('nas');
    }
    
    public function validationDefault(Validator $validator){
        $validator = new Validator();
        $validator
            ->notEmpty('nasname', 'A name is required')
            ->add('nasname', [ 
                'nameUnique' => [
                    'message' => 'The nasname you provided is already taken. Please provide another one.',
                    'rule' => 'validateUnique', 
                    'provider' => 'table'
                ]
            ]);
        return $validator;
    }
       
}
