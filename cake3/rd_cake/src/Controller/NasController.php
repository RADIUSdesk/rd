<?php

namespace App\Controller;
use App\Controller\AppController;

use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

use Cake\Utility\Inflector;

class NasController extends AppController{
  
    protected $base         = "Access Providers/Controllers/Nas/";   
    protected $owner_tree   = array();
    protected $main_model   = 'Nas';
  
    public function initialize(){  
        parent::initialize();
        $this->loadModel('Nas'); 
        $this->loadModel('Users');
                 
        $this->loadComponent('Aa');
        $this->loadComponent('GridButtons');
        $this->loadComponent('CommonQuery', [ //Very important to specify the Model
            'model'     => 'Nas',
            'sort_by'   => 'Nas.nasname'
        ]);
        
        $this->loadComponent('Notes', [
            'model'     => 'NaNotes',
            'condition' => 'na_id'
        ]);
        
        $this->loadComponent('JsonErrors'); 
        $this->loadComponent('TimeCalculations');         
    }
    
    public function exportCsv(){

        $this->autoRender   = false;

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $query = $this->{$this->main_model}->find();
        $this->CommonQuery->build_common_query($query,$user,[
            'Users',
            'NaRealms' => ['Realms'],
            'NaTags' => ['Tags'],
            'NaNotes' => ['Notes']
        ]);
        
        $q_r  = $query->all();

        //Create file
        $this->ensureTmp();     
        $tmpFilename    = TMP . $this->tmpDir . DS .  strtolower( Inflector::pluralize($this->modelClass) ) . '-' . date('Ymd-Hms') . '.csv';
        $fp             = fopen($tmpFilename, 'w');

        //Headings
        $heading_line   = array();
        if(isset($this->request->query['columns'])){
            $columns = json_decode($this->request->query['columns']);
            foreach($columns as $c){
                array_push($heading_line,$c->name);
            }
        }
        fputcsv($fp, $heading_line,';','"');
        foreach($q_r as $i){

            //FIXME ADD Status; Realms; Tags
            $columns    = array();
            $csv_line   = array();
            if(isset($this->request->query['columns'])){
                $columns = json_decode($this->request->query['columns']);
                foreach($columns as $c){
                    $column_name = $c->name;
                    if($column_name == 'notes'){
                        $notes   = '';
                        foreach($i->na_notes as $un){
                            if(!$this->Aa->test_for_private_parent($un->note,$user)){
                                $notes = $notes.'['.$un->note->note.']';    
                            }
                        }
                        array_push($csv_line,$notes);
                    }elseif($column_name =='owner'){
                        $owner_id       = $i->user_id;
                        $owner_tree     = $this->Users->find_parents($owner_id);
                        array_push($csv_line,$owner_tree); 
                    }else{
                        array_push($csv_line,$i->{$column_name});  
                    }
                }
                fputcsv($fp, $csv_line,';','"');
            }
        }

        //Return results
        fclose($fp);
        $data = file_get_contents( $tmpFilename );
        $this->cleanupTmp( $tmpFilename );
        $this->RequestHandler->respondAs('csv');
        $this->response->download( strtolower( Inflector::pluralize( $this->modelClass ) ) . '.csv' );
        $this->response->body($data);
    } 
      
    public function index(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
                
        $query = $this->{$this->main_model}->find();

        //FIXME Add NaState

        $this->CommonQuery->build_common_query($query,$user,[
            'Users',
            'NaRealms' => ['Realms'],
            'NaTags' => ['Tags'],
            'NaNotes' => ['Notes']
        ]);
 
        $limit  = 50;
        $page   = 1;
        $offset = 0;
        if(isset($this->request->query['limit'])){
            $limit  = $this->request->query['limit'];
            $page   = $this->request->query['page'];
            $offset = $this->request->query['start'];
        }
        
        $query->page($page);
        $query->limit($limit);
        $query->offset($offset);

        $total  = $query->count();       
        $q_r    = $query->all();
        $items  = array();

        foreach($q_r as $i){
              
            $owner_id   = $i->user_id;
            if(!array_key_exists($owner_id,$this->owner_tree)){
                $owner_tree     = $this->Users->find_parents($owner_id);
            }else{
                $owner_tree = $this->owner_tree[$owner_id];
            }
            
            $action_flags   = $this->Aa->get_action_flags($owner_id,$user);
            
            $notes_flag     = false;
            foreach($i->na_notes as $un){
                if(!$this->Aa->test_for_private_parent($un->note,$user)){
                    $notes_flag = true;
                    break;
                }
            }
            
            $row        = array();
            $fields    = $this->{$this->main_model}->schema()->columns();
            foreach($fields as $field){
                $row["$field"]= $i->{"$field"};
                
                if($field == 'created'){
                    $row['created_in_words'] = $this->TimeCalculations->time_elapsed_string($i->{"$field"});
                }
                if($field == 'modified'){
                    $row['modified_in_words'] = $this->TimeCalculations->time_elapsed_string($i->{"$field"});
                }
            }
            
            $row['tags'] = [];
            foreach($i->na_tags as $t){
                if(!$this->Aa->test_for_private_parent($t->tag,$user)){
                    array_push($row['tags'], 
                    [
                        'id'                    => $t->tag->id,
                        'name'                  => $t->tag->name,
                        'available_to_siblings' => $t->tag->available_to_siblings
                    ]);
                }
            }
            
            $row['realms'] = [];
            foreach($i->na_realms as $t){
                if(!$this->Aa->test_for_private_parent($t->realm,$user)){
                    array_push($row['realms'], 
                    [
                        'id'                    => $t->realm->id,
                        'name'                  => $t->realm->name,
                        'available_to_siblings' => $t->realm->available_to_siblings
                    ]);
                }
            }
                
            $row['user']	= $i->user->username;
            $row['owner']   = $owner_tree;
            $row['notes']   = $notes_flag;
			$row['update']	= $action_flags['update'];
			$row['delete']	= $action_flags['delete']; 
            array_push($items,$row);      
        }
       
        $this->set(array(
            'items'         => $items,
            'success'       => true,
            'totalCount'    => $total,
            '_serialize'    => array('items','success','totalCount')
        ));
    }
    
    
    
    public function noteIndex(){
        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $items = $this->Notes->index($user); 
    }
    
    public function noteAdd(){
        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }   
        $this->Notes->add($user);
    }
    
    public function noteDel(){  
        if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $this->Notes->del($user);
    }
    
}
