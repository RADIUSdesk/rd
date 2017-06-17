<?php
App::uses('AppController', 'Controller');

class UnknownApsController extends AppController {

    public $name        = 'UnknownAps';
    public $components  = array('Aa','TimeCalculations');
    public $uses        = array('UnknownAp');
    protected $base     = "Access Providers/Controllers/UnknownAps/";

//------------------------------------------------------------------------

	public function index(){
		$items 	= array();
		$q_r  	= $this->UnknownAp->find('all');
		
		App::uses('GeoIpLocation', 'GeoIp.Model');
        $GeoIpLocation = new GeoIpLocation();
		

		foreach($q_r as $i){
		
		    $location = $GeoIpLocation->find($i['UnknownAp']['last_contact_from_ip']);
                   
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
		    
		    $i['UnknownAp']['country_code']          = $country_code;
            $i['UnknownAp']['country_name']          = $country_name;
            $i['UnknownAp']['city']                  = $city;
            $i['UnknownAp']['postal_code']           = $postal_code;

		    $i['UnknownAp']['last_contact_human']     = $this->TimeCalculations->time_elapsed_string($i['UnknownAp']['last_contact']);
			array_push($items,$i['UnknownAp']);
		}
			
		$this->set(array(
            'items'         => $items,
            'success'       => true,
            '_serialize'    => array('items','success')
        ));
	}

	public function delete(){

       	if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}

	    if(isset($this->data['id'])){   //Single item delete
            $message = "Single item ".$this->data['id']; 
            $this->UnknownAp->id = $this->data['id'];
            $this->UnknownAp->delete($this->UnknownAp->id, true);
        }else{                          //Assume multiple item delete
            foreach($this->data as $d){
                    $this->UnknownAp->id = $d['id'];
                    $this->UnknownAp->delete($this->UnknownAp->id, true);
            }
        } 
 
        $this->set(array(
            'success' => true,
            '_serialize' => array('success')
        ));
    }

    //----- Menus ------------------------
	public function menu_for_grid(){
		$menu = array();
		$menu = array(
                array('xtype' => 'buttongroup', 'items' => array(
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
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnAttach'), 'scale' => 'large', 'itemId' => 'attach',      'tooltip'=> __('Attach')),
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnDelete'), 'scale' => 'large', 'itemId' => 'delete',   'tooltip'=> __('Delete')),
                    array('xtype' => 'button', 'glyph'     => Configure::read('icnRedirect'), 'scale' => 'large', 'itemId' => 'redirect',   'tooltip'=> __('Redirect')),
                    
                )),
            );

		$this->set(array(
            'items'         => $menu,
            'success'       => true,
            '_serialize'    => array('items','success')
        ));
	}

}
