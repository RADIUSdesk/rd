<?php
App::uses('AppController', 'Controller');

class RadacctsController extends AppController {

    public $name       = 'Radaccts';
    public $components = array('Aa','Kicker', 'Counters','GridFilter','TimeCalculations');
    public $uses       = array('Radacct','User','PermanentUser');
    protected $base    = "Access Providers/Controllers/Radaccts/";


	//---- Return the usage for a user/MAC combination
	public function get_usage(){
		if(
			(isset($this->request->query['username']))&&
			(isset($this->request->query['mac']))
		){

			//Some defaults 
			$data_used	= null;
			$data_cap	= null;
			$time_used	= null;
			$time_cap	= null;

//			$new_entry = true;

			//We need a civilized way to tell the query if there are NO accountig data yet BUT there is a CAP (time_cap &| data_cap)! 

			//$data_used	= 10000;
			//$data_cap	= 50000;
			//$time_used	= 100;
			//$time_cap	= 200;

			$username 	= $this->request->query['username'];
			$mac		= $this->request->query['mac'];
			
			$this->MacUsage = ClassRegistry::init('MacUsage');
			$q_m_u	= $this->MacUsage->find('first', array(
				'conditions'	=> array('MacUsage.username' => $username, 'MacUsage.mac'=> $mac)
			));

			if($q_m_u){
				$data_used	= $q_m_u['MacUsage']['data_used'];
				$data_cap	= $q_m_u['MacUsage']['data_cap'];
				$time_used	= $q_m_u['MacUsage']['time_used'];
				$time_cap	= $q_m_u['MacUsage']['time_cap'];
				$new_entry 	= false;
			}else{
				//Check what type of user it is since there was no record under MacUsage table....

				$this->Radcheck = ClassRegistry::init('Radcheck');
				$type 			= 'unknown';
				$q_r 			= $this->Radcheck->find('first',
					array('conditions' => array('Radcheck.username' => $username,'Radcheck.attribute' => 'Rd-User-Type'))
				);
				if($q_r){
				    $type = $q_r['Radcheck']['value'];
				}

				$new_entry = false;

				if($type == 'user'){
					$this->PermanentUser->contain();
					$q_u 		= $this->PermanentUser->find('first',
						array('conditions' => array('PermanentUser.username' => $username))
					);
					if($q_u){
						$data_used	= $q_u['PermanentUser']['data_used'];
						$data_cap	= $q_u['PermanentUser']['data_cap'];
						$time_used	= $q_u['PermanentUser']['time_used'];
						$time_cap	= $q_u['PermanentUser']['time_cap'];
						if(($time_cap == null)&&($data_cap == null)){
							$new_entry = true;
						}
					}
				}

				if($type == 'voucher'){
					$this->Voucher = ClassRegistry::init('Voucher');
					$this->Voucher->contain();
					$q_v 		= $this->Voucher->find('first',
						array('conditions' => array('Voucher.name' => $username))
					);
					if($q_v){
						$data_used	= $q_v['Voucher']['data_used'];
						$data_cap	= $q_v['Voucher']['data_cap'];
						$time_used	= $q_v['Voucher']['time_used'];
						$time_cap	= $q_v['Voucher']['time_cap'];
						if(($time_cap == null)&&($data_cap == null)){
							$new_entry = true;
						}
					}
				}

				if($type == 'device'){
					$this->Device = ClassRegistry::init('Device');
					$this->Device->contain();
					$q_v 		= $this->Device->find('first',
						array('conditions' => array('Device.name' => $username))
					);
					if($q_v){
						$data_used	= $q_v['Device']['data_used'];
						$data_cap	= $q_v['Device']['data_cap'];
						$time_used	= $q_v['Device']['time_used'];
						$time_cap	= $q_v['Device']['time_cap'];
						if(($time_cap == null)&&($data_cap == null)){
							$new_entry = true;
						}
					}
				}
			}

			//If we don't have any data yet for this user ..we just specify its cap and 0 used....
			if($new_entry){
				$profile = $this->_find_user_profile($username);
            	if($profile){
					$counters = $this->Counters->return_counter_data($profile,$type);
					if(array_key_exists('time', $counters)){
						$time_cap = $counters['time']['value'];
						$time_used= 0;
					}
					if(array_key_exists('data', $counters)){
						$data_cap = $counters['data']['value'];
						$data_used= 0;
					}
				}
			}

			$data = array('data_used' => $data_used, 'data_cap' => $data_cap, 'time_used' => $time_used, 'time_cap' => $time_cap);
      
			$this->set(array(
                'success'   => true,
                'data'      => $data,
                '_serialize' => array('success','data')
            ));

		}else{
			$this->set(array(
                'success'   => false,
                'message'   => array('message' => "Require a valid MAC address and username in the query string"),
                '_serialize' => array('success','message')
            ));
		}
	}


    //-------- BASIC CRUD -------------------------------
    public function export_csv(){

        $this->autoRender   = false;

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        //Build query
        $user_id    = $user['id'];
        $c          = $this->_build_common_query($user);
        $q_r        = $this->{$this->modelClass}->find('all',$c);

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

        //Results
        foreach($q_r as $i){

            $columns    = array();
            $csv_line   = array();
            if(isset($this->request->query['columns'])){
                $columns = json_decode($this->request->query['columns']);
                foreach($columns as $c){
                    $column_name = $c->name;
                    if($column_name == 'user_type'){
                        $user_type = 'unknown'; 
                        //Find device type
                       /* if(count($i['Radcheck']) > 0){
                            foreach($i['Radcheck'] as $rc){
                                if($rc['attribute'] == 'Rd-User-Type'){
                                    $user_type = $rc['value'];   
                                }
                            }
                        }*/
                        array_push($csv_line,$user_type);
                    }else{
                        array_push($csv_line,$i['Radacct']["$column_name"]);
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
        //-- Required query attributes: token;
        //-- Optional query attribute: sel_language (for i18n error messages)
        //-- also LIMIT: limit, page, start (optional - use sane defaults)
        //-- FILTER <- This will need fine tunning!!!!
        //-- AND SORT ORDER <- This will need fine tunning!!!!

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }
        $user_id    = $user['id'];
        $c = $this->_build_common_query($user); 

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

        

        $total  = $this->{$this->modelClass}->find('count'  , $c); 

        //Get some totals to display
        $c['fields']        = array(
                                'sum(Radacct.acctinputoctets)  as total_in',
                                'sum(Radacct.acctoutputoctets) as total_out',
                                'sum(Radacct.acctoutputoctets)+ sum(Radacct.acctinputoctets) as total',
                            );
        $this->{$this->modelClass}->contain();
        $t_q  = $this->{$this->modelClass}->find('first'  , $c);
        $q_r  = $this->{$this->modelClass}->find('all'    , $c_page);
        
        $items  = array();
        foreach($q_r as $i){
              
            $user_type      = 'unknown';
            $online_human   = '';

            if($i['Radacct']['acctstoptime'] == null){
                $online_time    = time()-strtotime($i['Radacct']['acctstarttime']);
                $active         = true; 
                $online_human   = $this->TimeCalculations->time_elapsed_string($i['Radacct']['acctstarttime'],false,true);
            }else{
                $online_time    = $i['Radacct']['acctstoptime'];
                $active         = false;
            }

            array_push($items,
                array(
                    'id'                => $i['Radacct']['radacctid'], 
                    'acctsessionid'     => $i['Radacct']['acctsessionid'],
                    'acctuniqueid'      => $i['Radacct']['acctuniqueid'],
                    'username'          => $i['Radacct']['username'],
                    'groupname'         => $i['Radacct']['groupname'],
                    'realm'             => $i['Radacct']['realm'],
                    'nasipaddress'      => $i['Radacct']['nasipaddress'],
                    'nasidentifier'     => $i['Radacct']['nasidentifier'],
                    'nasportid'         => $i['Radacct']['nasportid'],
                    'nasporttype'       => $i['Radacct']['nasporttype'],
                    'acctstarttime'     => $i['Radacct']['acctstarttime'],
                    'acctstoptime'      => $online_time,
                    'acctsessiontime'   => $i['Radacct']['acctsessiontime'],
                    'acctauthentic'     => $i['Radacct']['acctauthentic'],
                    'connectinfo_start' => $i['Radacct']['connectinfo_start'],
                    'connectinfo_stop'  => $i['Radacct']['connectinfo_stop'],
                    'acctinputoctets'   => $i['Radacct']['acctinputoctets'],
                    'acctoutputoctets'  => $i['Radacct']['acctoutputoctets'],
                    'calledstationid'   => $i['Radacct']['calledstationid'],
                    'callingstationid'  => $i['Radacct']['callingstationid'],
                    'acctterminatecause'=> $i['Radacct']['acctterminatecause'],
                    'servicetype'       => $i['Radacct']['servicetype'],
                    'framedprotocol'    => $i['Radacct']['framedprotocol'],
                    'framedipaddress'   => $i['Radacct']['framedipaddress'],
                    'acctstartdelay'    => $i['Radacct']['acctstartdelay'],
                    'acctstopdelay'     => $i['Radacct']['acctstopdelay'],
                    'xascendsessionsvrkey' => $i['Radacct']['xascendsessionsvrkey'],
                    'user_type'         => $user_type,
                    'active'            => $active,
                    'online_human'      => $online_human
                )
            );
        }                
        $this->set(array(
            'items'         => $items,
            'success'       => true,
            'totalCount'    => $total,
            'totalIn'       => $t_q[0]['total_in'],
            'totalOut'      => $t_q[0]['total_out'],
            'totalInOut'    => $t_q[0]['total'],
            '_serialize'    => array('items','success','totalCount','totalIn','totalOut','totalInOut')
        ));
    }

    public function delete($id = null) {
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        //FIXME We need to find a creative wat to determine if the Access Provider can delete this accounting data!!!
	    if(isset($this->data['id'])){   //Single item delete
            $this->_voucher_status_check($this->data['id']);        
            $this->{$this->modelClass}->id = $this->data['id'];
            $this->{$this->modelClass}->delete($this->{$this->modelClass}->id, true);
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){ 
                $this->_voucher_status_check($d['id']);   
                $this->{$this->modelClass}->id = $d['id'];
                $this->{$this->modelClass}->delete($this->{$this->modelClass}->id,true);
            }         
        }

        $fail_flag = false;
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

    public function kick_active(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        foreach(array_keys($this->request->query) as $key){
            if(preg_match('/^\d+/',$key)){
                $qr = $this->{$this->modelClass}->find('first',array('conditions' => array('Radacct.radacctid' => $key)));
                if($qr){
                    $this->Kicker->kick($qr['Radacct']);
                }  
            }
        }

        $this->set(array(
            'success' => true,
            '_serialize' => array('success')
        ));

    }

    public function close_open(){

        //__ Authentication + Authorization __
        $user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        foreach(array_keys($this->request->query) as $key){
            if(preg_match('/^\d+/',$key)){
                $qr = $this->{$this->modelClass}->find('first',array('conditions' => array('Radacct.radacctid' => $key)));
                if($qr){
                    if($qr['Radacct']['acctstoptime'] == null){
                        $now = date('Y-m-d h:i:s');
                        $this->{$this->modelClass}->id = $key;
                        $this->{$this->modelClass}->saveField('acctstoptime', $now);
                    }
                }  
            }
        }

        $this->set(array(
            'success' => true,
            '_serialize' => array('success')
        ));
    }

    //--------- END BASIC CRUD ---------------------------

    //----- Menus ------------------------
    public function menu_for_grid(){

        $user = $this->Aa->user_for_token($this);
        if(!$user){   //If not a valid user
            return;
        }

        //Empty by default
        $menu = array();

        //Admin => all power
        if($user['group_name'] == Configure::read('group.admin')){  //Admin

            $menu = array(
                    array('xtype' => 'buttongroup','title' => __('Action'), 'items' => array(
                        array( 'xtype' =>  'splitbutton',  'iconCls' => 'b-reload',   'glyph'     => Configure::read('icnReload'), 'scale'   => 'large', 'itemId'    => 'reload',   'tooltip'    => __('Reload'),
                            'menu'  => array( 
                                'items' => array( 
                                    '<b class="menu-title">'.__('Reload every').':</b>',
                                  //  array( 'text'   => _('Cancel auto reload'),   'itemId' => 'mnuRefreshCancel', 'group' => 'refresh', 'checked' => true ),
                                    array( 'text'  => __('30 seconds'),      'itemId'    => 'mnuRefresh30s', 'group' => 'refresh','checked' => false ),
                                    array( 'text'  => __('1 minute'),        'itemId'    => 'mnuRefresh1m', 'group' => 'refresh' ,'checked' => false),
                                    array( 'text'  => __('5 minutes'),       'itemId'    => 'mnuRefresh5m', 'group' => 'refresh', 'checked' => false ),
                                    array( 'text'  => __('Stop auto reload'),'itemId'    => 'mnuRefreshCancel', 'group' => 'refresh', 'checked' => true )
                                   
                                )
                            )
                    ),
                    array(
                            'xtype'         => 'button', 
                            'iconCls'       => 'b-connect',
                            'glyph'         => Configure::read('icnConnect'),      
                            'scale'         => 'large',
                            'itemId'        => 'connected',
                            'enableToggle'  => true,
                            'pressed'       => true,    
                            'tooltip'       => __('Show only currently connected')
                    )     
                )),
                array('xtype' => 'buttongroup','title' => __('Document'), 'items' => array(
                    array('xtype' => 'button', 'iconCls' => 'b-csv',     'glyph'     => Configure::read('icnCsv'), 'scale' => 'large', 'itemId' => 'csv',      'tooltip'=> __('Export CSV')),
                    array('xtype' => 'button', 'iconCls' => 'b-graph',   'glyph'     => Configure::read('icnGraph'), 'scale' => 'large', 'itemId' => 'graph',    'tooltip'=> __('Usage graph')),
                )),
                array('xtype' => 'buttongroup','title' => __('Extra actions'), 'items' => array(
                    array('xtype' => 'button', 'iconCls' => 'b-kick', 'glyph'     => Configure::read('icnKick'),'scale' => 'large', 'itemId' => 'kick', 'tooltip'=> __('Kick user off')),
                    array('xtype' => 'button', 'iconCls' => 'b-close', 'glyph'     => Configure::read('icnClose'),'scale' => 'large', 'itemId' => 'close','tooltip'=> __('Close session')),
                )) 
               
            );
        }

        //AP depend on rights
        if($user['group_name'] == Configure::read('group.ap')){ //AP (with overrides)

            $id             = $user['id'];
            $action_group   = array();
            $document_group = array();
            $specific_group = array();

            //Reload
            array_push($action_group,array( 
                'xtype'     =>  'splitbutton',  
                'iconCls'   => 'b-reload',
                'glyph'     => Configure::read('icnReload'),   
                'scale'     => 'large', 
                'itemId'    => 'reload',   
                'tooltip'   => __('Reload'),
                'menu'      => array(             
                    'items'     => array( 
                                    '<b class="menu-title">'.__('Reload every').':</b>',            
                    array( 'text'  => __('30 seconds'),      'itemId'    => 'mnuRefresh30s', 'group' => 'refresh','checked' => false ),
                    array( 'text'  => __('1 minute'),        'itemId'    => 'mnuRefresh1m', 'group' => 'refresh' ,'checked' => false),
                    array( 'text'  => __('5 minutes'),       'itemId'    => 'mnuRefresh5m', 'group' => 'refresh', 'checked' => false ),
                    array( 'text'  => __('Stop auto reload'),'itemId'    => 'mnuRefreshCancel', 'group' => 'refresh', 'checked' => true )                                  
                ))));

            array_push($action_group,array(
                'xtype'         => 'button', 
                'iconCls'       => 'b-connect',
                'glyph'         => Configure::read('icnConnect'),     
                'scale'         => 'large',
                'itemId'        => 'connected',
                'enableToggle'  => true,
                'pressed'       => true,    
                'tooltip'       => __('Show only currently connected')
            ));    


            if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->base.'export_csv')){ 
                array_push($document_group,array(
                    'xtype'     => 'button', 
                    'iconCls'   => 'b-csv',
                    'glyph'     => Configure::read('icnCsv'),     
                    'scale'     => 'large', 
                    'itemId'    => 'csv',      
                    'tooltip'   => __('Export CSV')));
            }

          array_push($document_group,array(
                'xtype'     => 'button', 
                'iconCls'   => 'b-graph',
                'glyph'     => Configure::read('icnGraph'),     
                'scale'     => 'large', 
                'itemId'    => 'graph',      
                'tooltip'   => __('Usage graph')));


           if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->base.'kick_active')){ 
                array_push($specific_group, array(
                    'xtype'     => 'button', 
                    'iconCls'   => 'b-kick',
                    'glyph'     => Configure::read('icnKick'), 
                    'scale'     => 'large', 
                    'itemId'    => 'kick', 
                    'tooltip'   => __('Kick user off')));
            }

            if($this->Acl->check(array('model' => 'Users', 'foreign_key' => $id), $this->base.'close_open')){ 
                array_push($specific_group, array(
                    'xtype'     => 'button', 
                    'iconCls'   => 'b-close',
                    'glyph'     => Configure::read('icnClose'), 
                    'scale'     => 'large', 
                    'itemId'    => 'close', 
                    'tooltip'   => __('Close session')));
            }


            $menu = array(
                        array('xtype' => 'buttongroup','title' => __('Action'),                 'items' => $action_group),
                        array('xtype' => 'buttongroup','title' => __('Document'),               'items' => $document_group),
                        array('xtype' => 'buttongroup','title' => __('Extra actions'),          'items' => $specific_group)
                    );
        }
        $this->set(array(
            'items'         => $menu,
            'success'       => true,
            '_serialize'    => array('items','success')
        ));
    }

    //______ END EXT JS UI functions ________


     function _build_common_query($user){

        //Empty to start with
        $c                  = array();
        $c['joins']         = array(); 
        $c['conditions']    = array();

        //What should we include....
        $c['contain']   = array(
        //                    'Radcheck'   //This makes it slow
                        );
                        
                        
        //====== Only_connectd filter ==========
        $only_connected = false;
        if(isset($this->request->query['only_connected'])){
            if($this->request->query['only_connected'] == 'true'){
                $only_connected = true;
                array_push($c['conditions'],array($this->modelClass.".acctstoptime" => null));
            }
        }                  

        //===== SORT =====
        //Default values for sort and dir
        $sort   = 'Radacct.username';
        $dir    = 'DESC';

        if(isset($this->request->query['sort'])){
            $sort = $this->modelClass.'.'.$this->request->query['sort'];
            //Here we do a trick if we onlt list active connections since we can't order by null
            if(($sort == 'Radacct.acctstoptime')&&($only_connected)){
                $sort = 'Radacct.acctstarttime';
            }
            $dir  = $this->request->query['dir'];
        } 

        $c['order'] = array("$sort $dir");
        //==== END SORT ===

       

        //======= For a specified username filter *Usually on the edit of user / voucher ======
        if(isset($this->request->query['username'])){
            $un = $this->request->query['username'];
            array_push($c['conditions'],array($this->modelClass.".username" => $un));
        }

        //======= For a specified callingstationid filter *Usually on the edit of device ======
        if(isset($this->request->query['callingstationid'])){
            $cs_id = $this->request->query['callingstationid'];
            array_push($c['conditions'],array($this->modelClass.".callingstationid" => $cs_id));
        }



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
                   
                }
                //Date
                if($f->type == 'date'){
                    //date we want it in "2013-03-12"
                    $col = $this->modelClass.'.'.$f->field;
                    if($f->comparison == 'eq'){
                        array_push($c['conditions'],array("DATE($col)" => $f->value));
                    }

                    if($f->comparison == 'lt'){
                        array_push($c['conditions'],array("DATE($col) <" => $f->value));
                    }
                    if($f->comparison == 'gt'){
                        array_push($c['conditions'],array("DATE($col) >" => $f->value));
                    }
                }
                //Lists
                if($f->type == 'list'){
                    if($f->field == 'user_type'){
                        $list_array = array();
                        foreach($f->value as $filter_list){
                            array_push($list_array,array("Radcheck.attribute" => "Rd-User-Type", "Radcheck.value" => $filter_list));
                        }
                        array_push($c['joins'],array(
                            'table'         => 'radcheck',
                            'alias'         => 'Radcheck',
                            'type'          => 'LEFT',
                            'conditions'    => array('(Radcheck.username = Radacct.callingstationid) OR (Radcheck.username = Radacct.username)')
                        )); 
                        array_push($c['conditions'],array('OR' => $list_array));
                    }
                }
            }
        }
        //====== END REQUEST FILTER =====

        //====== AP FILTER =====
        if($user['group_name'] == Configure::read('group.ap')){  //AP               
        
            $this->Realm    = ClassRegistry::init('Realm');
            $q_r            = $this->User->getPath($user['id']); //Get all the parents up to the root
            $ap_clause      = array();
            $ap_id          = $user['id'];
            
            //** ALL the AP's children **
            $tree_array_children    = array();
            $this->children         = $this->User->find_access_provider_children($user['id']);
            if($this->children){   //Only if the AP has any children...
                foreach($this->children as $i){
                    $id = $i['id'];
                    array_push($tree_array_children,array('Realm.user_id' => $id));
                }       
            } 

            $this->Realm->contain();
            $r_children = $this->Realm->find('all',array('conditions' => array('OR' => $tree_array_children)));
            foreach($r_children as $r_c){
                $name   = $r_c['Realm']['name'];
                array_push($ap_clause,array($this->modelClass.'.realm' => $name));
            }
            
            //** ALL the AP's Parents **
            $tree_array_parents     = array();
            $this->parents          = $this->User->getPath($user['id'],'User.id');
            foreach($this->parents as $i){
                $i_id = $i['User']['id'];
                if($i_id != $user['id']){ //upstream
                    array_push($tree_array_parents,array('Realm.user_id' => $i_id,'Realm.available_to_siblings' => true));
                }
            }
            
            $this->Realm->contain();
            $r_parents = $this->Realm->find('all',array('conditions' => array('OR' => $tree_array_parents)));
            foreach($r_parents as $r_p){
                $id     = $r_p['Realm']['id'];
                $name   = $r_p['Realm']['name'];
                $read   = $this->Acl->check(
                                array('model' => 'Users', 'foreign_key' => $user['id']), 
                                array('model' => 'Realms','foreign_key' => $id), 'read');
                if($read == true){
                    array_push($ap_clause,array($this->modelClass.'.realm' => $name));
                }                  
            }
            
            //Add it as an OR clause
            array_push($c['conditions'],array('OR' => $ap_clause)); 
        }
        //====== END AP FILTER =====

        return $c;
    }

   
    private function _voucher_status_check($id){

        //Find the count of this username; if zero check if voucher; if voucher change status to 'new';
        $q_r = $this->{$this->modelClass}->findByRadacctid($id);
        if($q_r){
            $user_type = 'unknown';
            $un = $q_r['Radacct']['username'];
            //Get the user type
            if(count($q_r['Radcheck']) > 0){
                foreach($q_r['Radcheck'] as $rc){
                    if($rc['attribute'] == 'Rd-User-Type'){
                        $user_type = $rc['value'];   
                    }
                }
            }
            //Check if voucher
            if($user_type == 'voucher'){
                $count = $this->{$this->modelClass}->find('count', array('conditions' => array('Radacct.username' => $un)));
                if($count == 1){
                    $this->Voucher = ClassRegistry::init('Voucher');
                    $this->Voucher->contain();
                    $qr = $this->Voucher->find('first', array('conditions' => array('Voucher.name' => $un)));
                    if($qr){
                        $this->Voucher->id = $qr['Voucher']['id'];
                        $this->Voucher->saveField('status', 'new');
                        $this->Voucher->saveField('perc_data_used', null);
                        $this->Voucher->saveField('perc_time_used', null);
                    }                           
                }
            }
        }
    }

	private function _find_user_profile($username){
        $profile = false;
        $q_r = $this->Radcheck->find('first',array('conditions' => array('Radcheck.username' => $username,'Radcheck.attribute' => 'User-Profile')));
        if($q_r){
            $profile = $q_r['Radcheck']['value'];
        }
        return $profile;
    }


}
