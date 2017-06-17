<?php
App::uses('AppController', 'Controller');

class UnknownDynamicClientsController extends AppController {

    public $name        = 'UnknownDynamicClients';
    public $components  = array('Aa','GridFilter','TimeCalculations');
    public $uses        = array('UnknownDynamicClient','User');
    protected $base     = "Access Providers/Controllers/UnknownDynamicClients/";

//------------------------------------------------------------------------

    //____ BASIC CRUD Manager ________
    public function index(){

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

        $total  = $this->{$this->modelClass}->find('count',$c);       
        $q_r    = $this->{$this->modelClass}->find('all',$c_page);

        $items      = array();
        
        App::uses('GeoIpLocation', 'GeoIp.Model');
        $GeoIpLocation = new GeoIpLocation();

        foreach($q_r as $i){
            $location = $GeoIpLocation->find($i['UnknownDynamicClient']['last_contact_ip']);
                   
            //Some defaults:
            $country_code = '';
            $country_name = '';
            $city         = '';
            $postal_code  = '';
            
            if(array_key_exists('GeoIpLocation',$location)){
                if($location['GeoIpLocation']['country_code'] != ''){
                    $country_code = utf8_encode($location['GeoIpLocation']['country_code']);
                }
                if($location['GeoIpLocation']['country_name'] != ''){
                    $country_name = utf8_encode($location['GeoIpLocation']['country_name']);
                }
                if($location['GeoIpLocation']['city'] != ''){
                    $city = utf8_encode($location['GeoIpLocation']['city']);
                }
                if($location['GeoIpLocation']['postal_code'] != ''){
                    $postal_code = utf8_encode($location['GeoIpLocation']['postal_code']);
                }
            }

            array_push($items,array(
                'id'                    => $i['UnknownDynamicClient']['id'],
                'nasidentifier'         => $i['UnknownDynamicClient']['nasidentifier'],
                'calledstationid'       => $i['UnknownDynamicClient']['calledstationid'],
                'last_contact'          => $i['UnknownDynamicClient']['last_contact'], 
                'last_contact_ip'       => $i['UnknownDynamicClient']['last_contact_ip'],
                'last_contact_human'    => $this->TimeCalculations->time_elapsed_string($i['UnknownDynamicClient']['last_contact']), 
                'country_code'          => $country_code,
                'country_name'          => $country_name,
                'city'                  => $city,
                'postal_code'           => $postal_code
            ));
        }
       
        //___ FINAL PART ___
        $this->set(array(
            'items' => $items,
            'success' => true,
            'totalCount' => $total,
            '_serialize' => array('items','success','totalCount')
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

        $user_id    = $user['id'];
        $fail_flag = false;

	    if(isset($this->data['id'])){   //Single item delete    
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

		$user = $this->_ap_right_check();
        if(!$user){
            return;
        }

        if ($this->request->is('post')) {

            //Unfortunately there are many check items which means they will not be in the POST if unchecked
            //so we have to check for them
            $check_items = array(
				'available_to_siblings'
			);
            foreach($check_items as $i){
                if(isset($this->request->data[$i])){
                    $this->request->data[$i] = 1;
                }else{
                    $this->request->data[$i] = 0;
                }
            }

            if ($this->{$this->modelClass}->save($this->request->data)) {
                   $this->set(array(
                    'success' => true,
                    '_serialize' => array('success')
                ));
            }
        }
    }

    //----- Menus ------------------------
    public function menu_for_grid(){

        $user = $this->Aa->user_for_token($this);
        if(!$user){   //If not a valid user
            return;
        }

		$menu = array(
                array('xtype' => 'buttongroup','title' => __('Action'), 'items' => array(
                     array( 
                        'xtype'     =>  'splitbutton',  
                        'iconCls'   => 'b-reload',
                        'glyph'     => Configure::read('icnReload'),   
                        'scale'     => 'large', 
                        'itemId'    => 'reload',   
                        'tooltip'   => __('Reload'),
                            'menu'  => array( 
                                'items' => array( 
                                    '<b class="menu-title">'.__('Reload every').':</b>',
                                    array( 'text'  => __('30 seconds'),      'itemId'    => 'mnuRefresh30s', 'group' => 'refresh','checked' => false ),
                                    array( 'text'  => __('1 minute'),        'itemId'    => 'mnuRefresh1m', 'group' => 'refresh' ,'checked' => false),
                                    array( 'text'  => __('5 minutes'),       'itemId'    => 'mnuRefresh5m', 'group' => 'refresh', 'checked' => false ),
                                    array( 'text'  => __('Stop auto reload'),'itemId'    => 'mnuRefreshCancel', 'group' => 'refresh', 'checked' => true )
                                   
                                )
                            )
                    ),
                    array(
                        'xtype' => 'button', 
                        'glyph'     => Configure::read('icnAttach'), 
                        'scale' => 'large', 
                        'itemId' => 'attach',      
                        'tooltip'=> __('Attach')
                    ),
                    array(
                        'xtype' => 'button', 
                        'glyph'     => Configure::read('icnDelete'), 
                        'scale' => 'large', 'itemId' => 'delete',   
                        'tooltip'=> __('Delete')
                    ),    
                )),
            );

		$this->set(array(
            'items'         => $menu,
            'success'       => true,
            '_serialize'    => array('items','success')
        ));
    }

    function _build_common_query($user){

        //Empty to start with
        $c                  = array();
        $c['joins']         = array(); 
        $c['conditions']    = array();

       
        //===== SORT =====
        //Default values for sort and dir
        $sort   = 'UnknownDynamicClient.last_contact';
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
        //====== END REQUEST FILTER =====  
        return $c;
    }
}
