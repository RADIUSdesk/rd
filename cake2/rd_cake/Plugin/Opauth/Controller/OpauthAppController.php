<?php
/**
 * CakePHP plugin for Opauth
 * 
 * @copyright    Copyright © 2012-2013 U-Zyn Chua (http://uzyn.com)
 * @link         http://opauth.org
 * @license      MIT License
 */
class OpauthAppController extends AppController {

	//We will query the database to determine the Application ID and Secret
	public $uses = array('DynamicDetail');
	
	/**
	 * Opauth instance
	 */
	public $Opauth;
	
	/**
	 * {@inheritDoc}
	 */
	public function __construct($request = null, $response = null) {
		parent::__construct($request, $response);
		
		$this->autoRender = false;
	}
	
	/**
	 * Catch all for Opauth
	 */
	public function index($which_one){
	//	$this->_loadOpauth();
	//	$this->Opauth->run();

		//We need to try and determine which Dynamic Login Page was used by checking the query string
		//We only store the query string if it contains social_login=1
		$qs = $_SERVER['QUERY_STRING'];
        if(preg_match('/social_login=1/',$qs)){	
        	CakeSession::write('rd.qs',$qs );
			CakeSession::write('rd.strategy',$which_one);
        }

		//Set up strategy....
		if($this->_set_up_strategy()){
			$this->_loadOpauth();
			$this->Opauth->run();
		}
	}
	
	/**
	 * Receives auth response and does validation
	 */
	public function callback(){
		$response = null;

		//Set up strategy....
		if(!$this->_set_up_strategy()){
			echo '<strong style="color: red;">Error: </strong>Problems with the session data stored for the strategy .'."<br>\n";
			return;
		}
		
		/**
		* Fetch auth response, based on transport configuration for callback
		*/
		switch(Configure::read('Opauth.callback_transport')){	
			case 'session':
				if (!session_id()){
					session_start();
				}
				
				if(isset($_SESSION['opauth'])) {
					$response = $_SESSION['opauth'];
					unset($_SESSION['opauth']);
				}
				break;
			case 'post':
				$response = unserialize(base64_decode( $_POST['opauth'] ));
				break;
			case 'get':
				$response = unserialize(base64_decode( $_GET['opauth'] ));
				break;
			default:
				echo '<strong style="color: red;">Error: </strong>Unsupported callback_transport.'."<br>\n";
				break;
		}
		
		/**
		 * Check if it's an error callback
		 */
		if (isset($response) && is_array($response) && array_key_exists('error', $response)) {
			// Error
			$response['validated'] = false;
		}

		/**
		 * Auth response validation
		 * 
		 * To validate that the auth response received is unaltered, especially auth response that 
		 * is sent through GET or POST.
		 */
		else{
			$this->_loadOpauth();
			
			if (empty($response['auth']) || empty($response['timestamp']) || empty($response['signature']) || empty($response['auth']['provider']) || empty($response['auth']['uid'])){
				$response['error'] = array(
					'provider' => $response['auth']['provider'],
					'code' => 'invalid_auth_missing_components',
					'message' => 'Invalid auth response: Missing key auth response components.'
				);
				$response['validated'] = false;
			}
			elseif (!($this->Opauth->validate(sha1(print_r($response['auth'], true)), $response['timestamp'], $response['signature'], $reason))){
				$response['error'] = array(
					'provider' => $response['auth']['provider'],
					'code' => 'invalid_auth_failed_validation',
					'message' => 'Invalid auth response: '.$reason
				);
				$response['validated'] = false;
			}
			else{
				$response['validated'] = true;
			}
		}
		
		/**
		 * Redirect user to /opauth-complete
		 * with validated response data available as POST data
		 * retrievable at $this->data at your app's controller
		 */
		$completeUrl = Configure::read('Opauth._cakephp_plugin_complete_url');
		if (empty($completeUrl)) $completeUrl = Router::url('/opauth-complete');
		
		
		$CakeRequest = new CakeRequest('/opauth-complete');
		$CakeRequest->data = $response;
		
		$Dispatcher = new Dispatcher();
		$Dispatcher->dispatch( $CakeRequest, new CakeResponse() );
		exit();
	}
	
	/**
	 * Instantiate Opauth
	 * 
	 * @param array $config User configuration
	 * @param boolean $run Whether Opauth should auto run after initialization.
	 */
	protected function _loadOpauth($config = null, $run = false){
		// Update dependent config in case the dependency is overwritten at app-level
		if (Configure::read('Opauth.callback_url') == '/auth/callback') {
			Configure::write('Opauth.callback_url', Configure::read('Opauth.path').'callback');
		}
		
		if (is_null($config)){
			$config = Configure::read('Opauth');
		}
		
		App::import('Vendor', 'Opauth.Opauth/lib/Opauth/Opauth');
		$this->Opauth = new Opauth( $config, $run );
	}


	//RADIUSdesk add ons
	private function _queryToArray($qry){

		//Take the query string and make in an Array

		$result = array();
		//string must contain at least one = and cannot be in first position
		if(strpos($qry,'=')) {

		if(strpos($qry,'?')!==false) {
		$q = parse_url($qry);
		$qry = $q['query'];
		}
		}else {
			return false;
		}
		foreach (explode('&', $qry) as $couple) {
			list ($key, $val) = explode('=', $couple);
			$result[$key] = $val;
		}
		return empty($result) ? false : $result;
	}

	private function _set_up_strategy(){

		$qs 		= CakeSession::read('rd.qs');
		$which_one	= CakeSession::read('rd.strategy');

		if($qs){	
			//Build the URL	
			$query = $this->_queryToArray($qs);
			$conditions = array("OR" =>array());
      
		    foreach(array_keys($query) as $key){
		        array_push($conditions["OR"],
		            array("DynamicPair.name" => $key, "DynamicPair.value" =>  $query[$key])
		        ); //OR query all the keys
		    }

		    $this->DynamicDetail->DynamicPair->contain(
				array('DynamicDetail' => array('DynamicDetailSocialLogin'))
			);

		    $q_r = $this->DynamicDetail->DynamicPair->find('first', 
		        array('conditions' => $conditions, 'order' => 'DynamicPair.priority DESC')); //Return the one with the highest priority

			//No match - return rathe
			if(!$q_r){
				echo '<strong style="color: red;">Error: </strong>No Dynamic Info for query string '."$qs<br>\n";
				return false;
			}

			//Loop through all the DynamicDetailSocialLogin entries and see if one compares with 
			//$this->data[auth][provider]
			$social_login_info = array();
			foreach($q_r['DynamicDetail']['DynamicDetailSocialLogin'] as $i){
				if(strtolower($i['name']) == $which_one){
					$social_login_info = $i;
					break; //No need to go on
				}
			}

			if(!$social_login_info){
				echo '<strong style="color: red;">Error: </strong>No configuration for '."$which_one<br>\n";
				return false;
			}else{

				$strategy 	= $social_login_info['name'];
				$s_key		= $social_login_info['special_key'];
				$s_secret	= $social_login_info['secret'];

				if($strategy == 'Facebook'){ //Facebook = app_id and app_secret
					Configure::write('Opauth.Strategy.'.$strategy, array(
					   'app_id' 	=> "$s_key",
					   'app_secret' => "$s_secret"
					));
				}

				if($strategy == 'Google'){ //Google = client_id and client_secret
					Configure::write('Opauth.Strategy.'.$strategy, array(
					   'client_id' 	=> "$s_key",
					   'client_secret' => "$s_secret"
					));
				}

				if($strategy == 'Twitter'){ //Twitter = key and secret
					Configure::write('Opauth.Strategy.'.$strategy, array(
					   'key' 	=> "$s_key",
					   'secret' => "$s_secret"
					));
				}
				return true;	
			}
		}
	}
}
