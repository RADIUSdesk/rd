<?php
App::uses('AppController', 'Controller');

class IpPoolsController extends AppController {

    public $name        = 'IpPools';
    public $components  = array('Aa','GridFilter');
    public $uses        = array('IpPool','PermanentUser','Device');
    protected $base     = "Access Providers/Controllers/IpPools/";

    protected $fields  = array(
        'id',      				'pool_name',    		'framedipaddress',  'nasipaddress',
        'calledstationid',  	'callingstationid',
        'expiry_time',       	'username',            	'pool_key',
		'nasidentifier',		'extra_name',			'extra_value',
		'active',				'permanent_user_id',	'created',
		'modified'
    );

//------------------------------------------------------------------------


    //____ BASIC CRUD Manager ________
    public function index(){

        if(!$this->Aa->admin_check($this)){   //Only for admin users!
            return;
        }
 
        $c = $this->_build_common_query(); 

        //===== PAGING (MUST BE LAST) ======
        $limit  = 50;   //Defaults
        $page   = 1;
        $offset = 0;
        if(isset($this->request->query['limit'])){
            $limit  = $this->request->query['limit'];
            $page   = $this->request->query['page'];
            $offset = $this->request->query['start'];
        }

        $c_page             = $c;
        $c_page['page']     = $page;
        $c_page['limit']    = $limit;
        $c_page['offset']   = $offset;

        $total  = $this->{$this->modelClass}->find('count',$c);       
        $q_r    = $this->{$this->modelClass}->find('all',$c_page);
        $items  = array();

        foreach($q_r as $i){
            $row = array();
            foreach($this->fields as $field){
                if(array_key_exists($field,$i['IpPool'])){
                    $row["$field"]= $i['IpPool']["$field"];
                }
            } 
            array_push($items,$row);
        }
       
        //___ FINAL PART ___
        $this->set(array(
            'items' => $items,
            'success' => true,
            'totalCount' => $total,
            '_serialize' => array('items','success','totalCount')
        ));
    }

	public function list_of_pools(){

		if(!$this->Aa->admin_check($this)){   //Only for admin users!
            return;
        }
		$items 	= array();
		$this->IpPool->contain();
		$q_r 	= $this->IpPool->find('all',array('fields' => array('DISTINCT IpPool.pool_name')));

		foreach($q_r as $i){
			$data 			= array();
			$data['name']	= $i['IpPool']['pool_name'];
			$data['id']		= $i['IpPool']['pool_name'];
			array_push($items,$data);
		}

		$this->set(array(
            'items' 		=> $items,
            'success' 		=> true,
            '_serialize' => array('items','success')
        ));
	}
 
    public function add_pool() {

        if(!$this->Aa->admin_check($this)){   //Only for admin users!
            return;
        }

		$count  = 0;
		$junk_trigger = 300; //We limit the trigger to 300 to prevent the user from creating havoc

		//print_r($this->request->data);
		

		$this->{$this->modelClass}->create();

		//Start with the first IP
		$name 		= $this->request->data['name'];
		$current_ip	= $this->request->data['pool_start'];
		$end_ip		= $this->request->data['pool_end'];
		$next_ip	= $this->_get_next_ip($current_ip);

		while($current_ip != $end_ip){
			$count++;
			if($count > $junk_trigger){
				$this->set(array(
		            'success'   => false,
		            'message'   => array('message' => "Could not add pool - Recheck range"),
		            '_serialize' => array('success','message')
		        ));
				return;
			}

			$data 						= array();
			$data['pool_name'] 			= $name;
			$data['framedipaddress'] 	= $current_ip;

			$count 	= $this->{$this->modelClass}->find('count', 
				array('conditions' => array('IpPool.pool_name' => $name, 'IpPool.framedipaddress' => $current_ip))
			);
			if($count ==0){ //If already there we silently ignore it...
				$this->{$this->modelClass}->save($data);
				$this->{$this->modelClass}->id = null;
			}
			$current_ip = $next_ip;
			$next_ip	= $this->_get_next_ip($current_ip);
		}

		//Last one in the range
		if($current_ip == $end_ip){
			$data 						= array();
			$data['pool_name'] 			= $name;
			$data['framedipaddress'] 	= $current_ip;

			$count 	= $this->{$this->modelClass}->find('count', 
				array('conditions' => array('IpPool.pool_name' => $name, 'IpPool.framedipaddress' => $current_ip))
			);
			if($count ==0){ //If already there we silently ignore it...
				$this->{$this->modelClass}->save($data);
				$this->{$this->modelClass}->id = null;
			}
		}

		$this->set(array(
            'success' => true,
            '_serialize' => array('success')
        ));
	}

	public function add_ip() {

        if(!$this->Aa->admin_check($this)){   //Only for admin users!
            return;
        }

		//First check so we don't add doubles
		$name  	= $this->request->data['name'];
		$ip		= $this->request->data['ip'];
		

		$count 	= $this->{$this->modelClass}->find('count', array('conditions' => array('IpPool.pool_name' => $name, 'IpPool.framedipaddress' => $ip)));
		if($count > 0){
			$this->set(array(
	            'success'   => false,
	            'message'   => array('message' => "IP Already listed"),
	            '_serialize' => array('success','message')
	        ));	

		}else{

			$data 						= array();
			$data['pool_name'] 			= $name;
			$data['framedipaddress'] 	= $ip;
			$this->{$this->modelClass}->save($data);

			$this->set(array(
		        'success' => true,
		        '_serialize' => array('success')
		    ));
		} 
	}

    public function delete($id = null) {

		if(!$this->Aa->admin_check($this)){   //Only for admin users!
            return;
       	}
 
       	if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}

        $fail_flag = false;

	    if(isset($this->data['id'])){   //Single item delete
            $message = "Single item ".$this->data['id'];
            $this->{$this->modelClass}->id = $this->data['id'];
            $this->{$this->modelClass}->delete($this->{$this->modelClass}->id, true);
   
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){
                $this->{$this->modelClass}->id = $d['id'];
                $this->{$this->modelClass}->delete($this->{$this->modelClass}->id, true);
            }
        }

        if($fail_flag == true){
            $this->set(array(
                'success'   => false,
                'message'   => array('message' => __('Could not delete some items')),
                '_serialize' => array('success','message')
            ));
        }else{
            $this->set(array(
                'success' => true,
                '_serialize' => array('success')
            ));
        }
	}

    public function edit(){

		if(!$this->Aa->admin_check($this)){   //Only for admin users!
            return;
       	}
		
		if ($this->request->is('post')) {
		 	//Unfortunately there are many check items which means they will not be in the POST if unchecked
            //so we have to check for them
            $check_items = array(
				'active', 'clean_up'
			);
            foreach($check_items as $i){
                if(isset($this->request->data[$i])){
                    $this->request->data[$i] = 1;
                }else{
                    $this->request->data[$i] = 0;
                }
            }

			if($this->request->data['clean_up'] == 1){
				$this->request->data['nasipaddress'] 		= '';
				$this->request->data['calledstationid'] 	= '';
				$this->request->data['callingstationid'] 	= '';
				$this->request->data['expiry_time'] 		= '';
				$this->request->data['pool_key'] 			= '';
				$this->request->data['nasidentifier'] 		= '';
			}

			//Check if there was a user attached that we perhaps need to remove first...
			$q_r = $this->{$this->modelClass}->findById($this->request->data['id']);
			if($q_r){
				if($q_r['IpPool']['permanent_user_id'] != ''){
					$d 				= array();
					$d['id']		= $q_r['IpPool']['permanent_user_id'];
					$d['static_ip'] = '';
					$this->PermanentUser->save($d);
				}
			}


			//Find the username assiciated with the permanent_user_id
			$permanent_user_id = false;
			if($this->request->data['permanent_user_id'] != ''){
				$this->PermanentUser->contain();
				$q_r = $this->PermanentUser->findById($this->request->data['permanent_user_id']);
				if($q_r){
					$this->request->data['username'] 	= $q_r['PermanentUser']['username'];
					$permanent_user_id 					= $q_r['PermanentUser']['id'];
				}else{
					$this->request->data['username'] = '';
				}
			}else{
				$this->request->data['username'] = '';
			}



			//We are only allowing to attach one permanent user / mac at a time to an IP Address
			if($this->request->data['username'] != ''){
				$username = $this->request->data['username'];
				$entry_id = $this->request->data['id'];
				
				$q_r = $this->{$this->modelClass}->find('all', array('conditions' => array('IpPool.username' => $username)));

				if($q_r){
					foreach($q_r as $i){
						if($i['IpPool']['id'] != $entry_id){
							$fip = $i['IpPool']['framedipaddress'];
							$this->set(array(
								'success'   => false,
								'message'   => array('message' => "User $username already attached to $fip"),
								'_serialize' => array('success','message')
							));
							return;
						}					
					}
				}
			}

			if($this->request->data['callingstationid'] != ''){
				$callingstationid 	= $this->request->data['callingstationid'];
				$entry_id 			= $this->request->data['id'];
				
				$q_r = $this->{$this->modelClass}->find('all', array('conditions' => array('IpPool.callingstationid' => $callingstationid)));

				if($q_r){
					foreach($q_r as $i){
						if($i['IpPool']['id'] != $entry_id){
							$fip = $i['IpPool']['framedipaddress'];
							$this->set(array(
								'success'   => false,
								'message'   => array('message' => "MAC $callingstationid already attached to $fip"),
								'_serialize' => array('success','message')
							));
							return;
						}					
					}
				}
			}
			

            if ($this->{$this->modelClass}->save($this->request->data)) {
				
				//If there is a user attached, we need to also add this IP back to the user
				if($permanent_user_id){
					$d = array();
					$d['id'] 		= $permanent_user_id;
					$d['static_ip'] = $this->request->data['framedipaddress'];
					$this->PermanentUser->save($d);
				}	
               	$this->set(array(
                    'success' => true,
                    '_serialize' => array('success')
                ));
            }
        }
    }

	public function get_ip_for_user(){
		if(isset($this->request->query['username'])){
			$username 	= $this->request->query['username'];

			//Test to see if username is not perhaps a BYOD device
			$pattern = '/^([0-9A-F]{2}[:-]){5}([0-9A-F]{2})$/i';
			if(preg_match($pattern, $username)){
				$this->Device->contain();
				$q_r = $this->Device->find('first', array('conditions' => array('Device.name' => $username)));
				if($q_r){
					$permanent_user_id = $q_r['Device']['permanent_user_id'];
					$q_s = $this->{$this->modelClass}->find('first', array('conditions' => array('IpPool.permanent_user_id' => $permanent_user_id)));
					if($q_s){
						$data 		= array();
						$data['ip'] = $q_s['IpPool']['framedipaddress'];
						$this->set(array(
							'data' 		=> $data,
							'success'   => true,
							'_serialize' => array('success','data')
						));
						return;
					}
				}
			}else{	
				$q_r		= $this->{$this->modelClass}->find('first', array('conditions' => array('IpPool.username' => $username)));
				if($q_r){
					$data 		= array();
					$data['ip'] = $q_r['IpPool']['framedipaddress'];
					$this->set(array(
						'data' 		=> $data,
						'success'   => true,
						'_serialize' => array('success','data')
					));
					return;
				}
			}
		}
		$this->set(array(
		    'success' => false,
		    '_serialize' => array('success')
		));
	}

    //----- Menus ------------------------
    public function menu_for_grid(){

        if(!$this->Aa->admin_check($this)){   //Only for admin users!
            return;
        }

            $menu = array(
                array('xtype' => 'buttongroup','title' => __('Action'), 'items' => array(
					array(
                        'xtype'     => 'button', 
                        'glyph'     => Configure::read('icnReload'), 
                    'scale'     => 'large', 
                    'itemId'    => 'reload',      
                    'tooltip'   => __('Reload')
                ),
                array(
                    'xtype'     => 'button', 
                    'glyph'     => Configure::read('icnAdd'), 
                    'scale'     => 'large', 
                    'itemId'    => 'add',      
                    'tooltip'   => __('Add')
                ),
                array(
                    'xtype'     => 'button', 
                    'glyph'     => Configure::read('icnDelete'), 
                    'scale'     => 'large', 
                    'itemId'    => 'delete',   
                    'tooltip'   => __('Delete')
                ),
                array(
                    'xtype'     => 'button', 
                    'glyph'     => Configure::read('icnEdit'), 
                    'scale'     => 'large', 
                    'itemId'    => 'edit',     
                    'tooltip'   => __('Edit')
                )
            )),
            array('xtype' => 'buttongroup','title' => __('Document'), 'width' => 100, 'items' => array(  
                array(
                    'xtype'     => 'button',
                    'glyph'     => Configure::read('icnCsv'), 
                    'scale'     => 'large', 
                    'itemId'    => 'csv',      
                    'tooltip'   => __('Export CSV')
                ),
            ))   
        );

        $this->set(array(
            'items'         => $menu,
            'success'       => true,
            '_serialize'    => array('items','success')
        ));
    }

    function _build_common_query(){

        //Empty to start with
        $c                  = array();
        $c['joins']         = array(); 
        $c['conditions']    = array();

        //What should we include....
        $c['contain']   = array();

        //===== SORT =====
        //Default values for sort and dir
        $sort   = 'IpPool.expiry_time';
        $dir    = 'DESC';

        if(isset($this->request->query['sort'])){   
            $sort = $this->modelClass.'.'.$this->request->query['sort'];
            $dir  = $this->request->query['dir'];
        } 
        $c['order'] = array("$sort $dir");
        //==== END SORT ===


        //====== REQUEST FILTER =====
        if(isset($this->request->query['filter'])){
            $filter = json_decode($this->request->query['filter']);
            foreach($filter as $f){

                $f = $this->GridFilter->xformFilter($f);

                //Strings
                if($f->type == 'string'){
                    $col = $this->modelClass.'.'.$f->field;
                    array_push($c['conditions'],array("$col LIKE" => '%'.$f->value.'%'));
                }
                //Bools
                if($f->type == 'boolean'){
                     $col = $this->modelClass.'.'.$f->field;
                     array_push($c['conditions'],array("$col" => $f->value));
                }
            }
        }
        //====== END REQUEST FILTER ====    
        return $c;
	}

	private function _get_next_ip($ip){

        $pieces     = explode('.',$ip);
        $octet_1    = $pieces[0];
        $octet_2    = $pieces[1];
        $octet_3    = $pieces[2];
        $octet_4    = $pieces[3];

        if($octet_4 >= 254){
            $octet_4 = 1;
            $octet_3 = $octet_3 +1;
        }else{

            $octet_4 = $octet_4 +1;
        }
        $next_ip = $octet_1.'.'.$octet_2.'.'.$octet_3.'.'.$octet_4;
        return $next_ip;
    }
    
}
