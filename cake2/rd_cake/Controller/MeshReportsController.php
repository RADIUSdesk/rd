<?php
class MeshReportsController extends AppController {

    public  $name    	= 'MeshReports';
	public $components  = array('Aa','TimeCalculations');
    public  $uses    	= array(
		'Node',					'NodeLoad',		'NodeStation',	'NodeSystem','MeshEntry',
		'NodeIbssConnection',	'NodeSetting',	'NodeNeighbor',	'NodeAction', 'MeshExit',
		'OpenvpnServerClient'
	);
	private $blue	 	= '#627dde';
	private $l_red   	= '#fb6002';
	private $d_red   	= '#dc1a1a';
	private $l_green 	= '#4bd765';
	private $d_green 	= "#117c25";
	private	$green	 	= '#01823d';	//Green until dead time then grey
	private	$dark_blue	= '#0a3cf6';	//Blue until dead time then grey
	private	$grey	 	= '#505351';	//Green until dead time then grey
	private	$blue_grey	= '#adbcf6';	//Blue until dead time then blue_grey
	private $yellow		= '#f8d908'; 
	private $thickness  = 9;
	private $gw_size	= 20;
	private $node_size	= 10;

	
    public function submit_report(){

        //Source the vendors file and keep in memory
        $vendor_file        = APP.DS."Setup".DS."Scripts".DS."mac_lookup.txt";
        $this->vendor_list  = file($vendor_file);

        $this->log('Got a new report submission', 'debug');
        $fb = $this->_new_report();

		//Handy for debug to see what has been submitted
        file_put_contents('/tmp/mesh_report.txt', print_r($this->request->data, true));
        $this->set(array(
           // 'items' => $this->request->data,
            'items'   => $fb,
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }

	public function overview(){

		$user = $this->Aa->user_for_token($this);
        if(!$user){   //If not a valid user
            return;
        }

		if(!isset($this->request->query['mesh_id'])){
			$this->set(array(
		        'message'	=> array("message"	=>"Mesh ID (mesh_id) missing"),
		        'success' => false,
		        '_serialize' => array('success','message')
		    ));
			return;
		}

		//Create a hardware lookup for proper names of hardware
	    $hardware = $this->_make_hardware_lookup();    
	
		$items 		= array();
		$mesh_id 	= $this->request->query['mesh_id'];

		//Get the 'dead_after' value
		$dead_after = $this->_get_dead_after($mesh_id);

		//Find all the nodes for this mesh with their Neighbors
		$this->Node->contain('NodeNeighbor');
		$q_r 		= $this->Node->find('all',array('conditions' => array('Node.mesh_id' => $mesh_id)));
	
		$grey_list		= array(); //List of nodes with no neighbors
		//===No Nodes found===
		if(!($q_r)){
			$items = array(
				array(
					'id' 	=> "empty1",
					'name' 	=> "Please add nodes.....",
					'data'	=> array(
						'$color' 	=> $this->yellow,
						'$type'		=> "star",
						'$dim'		=> 30
					)
				)
			);

			array_push($grey_list,array( 'nodeTo' => "empty1",'data' => array('$alpha'	=> 0.0)));
		}

		//Some defaults for the spiderweb
		$opacity		= 1;	//The older a line is the more opacity it will have (tend to zero)
		$cut_off		= 3 * $dead_after;//Three times ater it will turn red
		$no_neighbors  	= true; 	//If none of the nodes has neighbor entries this will stay true
		

		foreach($q_r as $i){

			$node_id    = $i['Node']['id'];
            $node_name  = $i['Node']['name'];
            $l_contact  = $i['Node']['last_contact'];
			$hw_id 		= $i['Node']['hardware'];
			$hw_human	= $hardware["$hw_id"]; 	//Human name for Hardware
			$type		= 'node';

			//=== Determine if this node is a gataway node or not === 
			$gw			= 'yes'; 				//Make it by default a gateway node (if there are only one node)
			if(count($i['NodeNeighbor'])>0){
				$gw = $i['NodeNeighbor'][0]['gateway']; //Check if this is a gateway ('yes' or 'no')
			}

			//===Determine when last did we saw this node (never / up / down) ====
			if($l_contact == null){
                $state = 'never';
                $i['Node']['last_contact_human'] = null;
            }else{
                $i['Node']['last_contact_human'] = $this->TimeCalculations->time_elapsed_string($l_contact);
                $last_timestamp = strtotime($l_contact);
                if($last_timestamp+$dead_after <= time()){
                    $state = 'down';
                }else{
                    $state = 'up';
                }
            }
			
			//===Specify the color based on the state + gw type
			if($state == 'never'){
				$color	= $this->blue;	//Default
				$size	= $this->node_size;
			}

			if(($state == 'down')&($gw == 'no')){
				$color 	= $this->l_red;
				$size	= $this->node_size;
			}

			if(($state == 'up')&($gw == 'no')){
				$color = $this->l_green;
				$size  = $this->node_size;
			}

			if(($state == 'down')&($gw == 'yes')){
				$color 	= $this->d_red;
				$size   = $this->gw_size;
				$type	= 'gateway';
			}

			if(($state == 'up')&($gw == 'yes')){
				$color 	= $this->d_green;
				$size   = $this->gw_size;
				$type	= 'gateway';
			}

			//if($state == 'up'){
			//	$color = $this->l_green;
			//}

			//=== add extra info to node data ===
			$i['Node']['state'] 	= $state;
			$i['Node']['hw_human'] 	= $hw_human;
			$i['Node']['gateway']	= $gw;
			$node_data				= $i['Node'];

			
			if(count($i['NodeNeighbor']) == 0){	//We handle nodes without any entries as blue nodes

				$specific_data = array(
					'$color'		=> "$color",
					'$type'			=> "circle",
					'$dim'			=> $size,
					'type'			=> 'no_neighbors'
				);

				$this_data = array_merge((array)$node_data,(array)$specific_data);
				array_push($items,array('id'=> $node_id,'name'=> $node_name,'data' => $this_data));
				array_push($grey_list,array( 'nodeTo' => $i['Node']['id'],'data' => array('$alpha'	=> 0.0)));
			}else{
				$no_neighbors 	= false; //Set this trigger for us to know once loop is done
				$adjacencies 	= array();

				//=== Loop the neighbors ===
				foreach($i['NodeNeighbor'] as $n){
					//We need to determine the 1.)Thickness 2.)Color and 3.) Opacity
					$metric = $n['metric'];
					$last	= strtotime($n['modified']);
					$now	= time();
					$weight	= round((1/$metric*$this->thickness),2);


					$green_cut	= $now - $dead_after;
					$grey_cut	= $now - $cut_off;
					
					if($last >= $green_cut){
						$c = $this->green;

						//5G we make blue
						if(($n['hwmode'] == '11a')||($n['hwmode'] == '11na')){
							$c = $this->dark_blue;
						}

						//How clear the line must be
						$green_range 	= $now - $green_cut;
						$green_percent	= ($last- $green_cut)/$green_range;
						$o_val			= ($green_percent * 0.5)+0.5;
						$o_val			= round($o_val,2);	
					}elseif(($last >= $grey_cut)&&($last <= $green_cut)){

						//How clear the line must be
						$c				= $this->grey; //Default

						//5G we make blue
						if(($n['hwmode'] == '11a')||($n['hwmode'] == '11na')){
							$c = $this->blue_grey;
						}

						$grey_range 	= $green_cut - $grey_cut;
						$grey_percent	= ($last- $grey_cut)/$grey_range;
						$o_val			= ($grey_percent * 0.5)+0.5;
						$o_val			= round($o_val,2);	
					}else{
						$weight			= 0;
						$o_val			= 0;
						$c				= $this->grey; //Default
					}

					array_push($adjacencies,array( 
						'nodeTo' 	=> $n['neighbor_id'],
						'data' 		=> array(
							'$color'	=> $c,
							'$lineWidth'=> $weight,
							'$alpha'	=> $o_val
						)
					));
				}
				//=== End loop neighbors ===

				$specific_data = array(
					'$color'		=> $color,
					'$type'			=> "circle",
					'$dim'			=> $size,
					'type'			=> $type,
				);
				$this_data = array_merge((array)$node_data,(array)$specific_data);
				array_push($items,array('id'=> $this_data['id'],'name'=> $this_data['name'], 'data' => $this_data,'adjacencies'=> $adjacencies));
			}
		}
		//End the loop of nodes

		if($no_neighbors){
			//Add a 'ghost' node as the center
			array_push($items,array(
				'id'	=> 'center',
				'name'	=> '',
				'data'	=> array(
					'$color'	=> "grey",
					'$type'		=> "circle",
					'$dim'		=> 0
				),
				'adjacencies'	=> $grey_list
			));
		}else{
			//Attach those to the list of adjacencies of the gateway node
			$count = 0;
			foreach($items as $item){
				//Attach to the first one
				if(isset($item['gateway'])&&($item['gateway']=='yes')){
					if(isset($item['adjacencies'])){
						$items[$count]['adjacencies'] = array_merge((array)$item['adjacencies'],(array)$grey_list);

					}else{
						$items[$count]['adjacencies'] = $grey_list;
					}
					break;
				}
				$count ++;
			}
		}

		$this->set(array(
	        'data' => $items,
	        'success' => true,
	        '_serialize' => array('data','success')
	    ));
	}
	public function overview_google_map(){

		$user = $this->Aa->user_for_token($this);
        if(!$user){   //If not a valid user
            return;
        }

		if(!isset($this->request->query['mesh_id'])){
			$this->set(array(
		        'message'	=> array("message"	=>"Mesh ID (mesh_id) missing"),
		        'success' => false,
		        '_serialize' => array('success','message')
		    ));
			return;
		}

		$items 			= array();
		$connections	= array();

		//Create a hardware lookup for proper names of hardware
	    $hardware = $this->_make_hardware_lookup();        
	   
        //Find all the nodes for this mesh
        $mesh_id = $this->request->query['mesh_id'];

        $this->Node->contain('NodeNeighbor');
        $q_r = $this->Node->find('all',array(
			'conditions'=> array('Node.mesh_id'      => $mesh_id)
		));

		//Get the 'dead_after' value
		$dead_after = $this->_get_dead_after($mesh_id);


		//Build a hash of nodes with their detail for lookup
		$neighbor_hash = array();
		foreach($q_r as $i){
			if($i['Node']['lat'] != null){	//Only those with co-ordinates
				$id = $i['Node']['id'];
				$neighbor_hash[$id]=$i['Node'];
			}
		}

		//Some defaults for the spiderweb
		$thickness 	= 9;	//The bigger the metric; ther thinner this line
		$opacity	= 1;	//The older a line is the more opacity it will have (tend to zero)
		$cut_off	= 3 * $dead_after;

		foreach($q_r as $i){
			//print_r($i);
            $node_id    = $i['Node']['id'];
            $node_name  = $i['Node']['name'];
            $l_contact  = $i['Node']['last_contact'];
			$hw_id 		= $i['Node']['hardware'];
			$hw_human	= $hardware["$hw_id"];
			$gw			= false;
			if(count($i['NodeNeighbor'])>0){
				$gw = $i['NodeNeighbor'][0]['gateway'];

				foreach($i['NodeNeighbor'] as $n){
					$n_id 	= $n['neighbor_id'];
					$metric = $n['metric'];
					$last	= strtotime($n['modified']);
					$now	= time();

					//It might be so old that we do not bother drawing it
					if($last <= ($now - $cut_off)){
						continue;
					}

					//print_r($n);
					if((array_key_exists($n_id,$neighbor_hash))&&
						(array_key_exists($node_id,$neighbor_hash))
					){
						$from_lat 	= $neighbor_hash[$node_id]['lat'];
						$from_lng 	= $neighbor_hash[$node_id]['lon'];
						$to_lat 	= $neighbor_hash[$n_id]['lat'];
						$to_lng 	= $neighbor_hash[$n_id]['lon'];
						$metric		= $n['metric'];
						$weight		= round((1/$metric*$this->thickness),2);

						//What color the line must be
						$green_cut	= $now - $dead_after;
						$grey_cut	= $now - $cut_off;
						
						if($last >= $green_cut){

							$c = $this->green;
							if(($n['hwmode'] == '11a')||($n['hwmode'] == '11na')){
								$c = $this->dark_blue;
							}

							//How clear the line must be
							$green_range 	= $now - $green_cut;
							$green_percent	= ($last- $green_cut)/$green_range;
							$o_val			= ($green_percent * 0.5)+0.5;
							$o_val			= round($o_val,2);	
						}else{
							//How clear the line must be
							$c 				= $this->grey;
							if(($n['hwmode'] == '11a')||($n['hwmode'] == '11na')){
								$c = $this->blue_grey;
							}
							$grey_range 	= $green_cut - $grey_cut;
							$grey_percent	= ($last- $grey_cut)/$grey_range;
							$o_val			= ($grey_percent * 0.5)+0.5;
							$o_val			= round($o_val,2);	
						}
						
						array_push($connections,array(
							'from' 	=> array(
								'lat'	=> $from_lat,
								'lng'	=> $from_lng
							),
							'to'	=> array(
								'lat'	=> $to_lat,
								'lng'	=> $to_lng
							),
							'weight'	=> $weight,
							'color'		=> $c,
							'opacity'	=> $o_val,
							'metric'	=> $metric
						));

					}
				}
			}

            //Find the dead time (only once)
            if($l_contact == null){
                $l_contact_human = null;
                $state = 'never';
            }else{
                $last_timestamp = strtotime($l_contact);
                $l_contact_human = $this->TimeCalculations->time_elapsed_string($l_contact);
                if($last_timestamp+$dead_after <= time()){
                    $state = 'down';
                }else{
                    $state = 'up';
                }
            }

            $i['Node']['l_contact_human' ] = $l_contact_human;
            
			$i['Node']['state'] 	= $state;
			$i['Node']['hw_human'] 	= $hw_human;
			$i['Node']['lng']		= $i['Node']['lon'];
			$i['Node']['gateway']	= $gw;

			array_push($items,$i['Node']);
		}

		$this->set(array(
	        'items' 		=> $items,
			'connections'	=> $connections,
	        'success' 		=> true,
	        '_serialize' => array('items', 'connections','success')
	    ));
	}

    public function view_entries(){

		$user = $this->Aa->user_for_token($this);
        if(!$user){   //If not a valid user
            return;
        }

		if(!isset($this->request->query['mesh_id'])){
			$this->set(array(
		        'message'	=> array("message"	=>"Mesh ID (mesh_id) missing"),
		        'success' => false,
		        '_serialize' => array('success','message')
		    ));
			return;
		}

        $items  	= array();
        $id     	= 1;
		$modified 	= $this->_get_timespan();


       
        //Find all the entries for this mesh
        $mesh_id = $this->request->query['mesh_id'];
        $this->MeshEntry->contain();
        $q_r = $this->MeshEntry->find('all',array('conditions' => array(
            'MeshEntry.mesh_id' => $mesh_id
        )));

        //Create a lookup of all the nodes for this mesh
		$this->Node->contain();
        $q_nodes = $this->Node->find('all',array('conditions' => array(
            'Node.mesh_id'      => $mesh_id
        )));
        $this->node_lookup = array();
        foreach($q_nodes as $n){
            $n_id   = $n['Node']['id'];
            $n_name = $n['Node']['name'];               
            $this->node_lookup[$n_id] = $n_name;
        }
    

        //Find all the distinct MACs for this Mesh entry...
        foreach($q_r as $i){
            $mesh_entry_id  = $i['MeshEntry']['id'];
            $entry_name     = $i['MeshEntry']['name'];
			$this->NodeStation->contain();
            $q_s = $this->NodeStation->find('all',array(
                'conditions'    => array(
                    'NodeStation.mesh_entry_id' => $mesh_entry_id,
                    'NodeStation.modified >='   => $modified
                ),
                'fields'        => array(
                    'DISTINCT(mac)'
                )
            ));

            if($q_s){

                foreach($q_s as $j){
                    $mac = $j['NodeStation']['mac'];
                    //Get the sum of Bytes and avg of signal
					$this->NodeStation->contain();
                    $q_t = $this->NodeStation->find('first', array(
                        'conditions'    => array(
                            'NodeStation.mac'           => $mac,
                            'NodeStation.mesh_entry_id' => $mesh_entry_id,
                            'NodeStation.modified >='   => $modified
                        ),
                        'fields'    => array(
                            'SUM(NodeStation.tx_bytes) as tx_bytes',
                            'SUM(NodeStation.rx_bytes)as rx_bytes',
                            'AVG(NodeStation.signal_avg)as signal_avg',
                        )
                    ));
                   // print_r($q_t);
                    $t_bytes    = $q_t[0]['tx_bytes'];
                    $r_bytes    = $q_t[0]['rx_bytes'];
                    $signal_avg = round($q_t[0]['signal_avg']); 
                    if($signal_avg < -95){
                        $signal_avg_bar = 0.01;
                    }
                    if(($signal_avg >= -95)&($signal_avg <= -35)){
                            $p_val = 95-(abs($signal_avg));
                            $signal_avg_bar = round($p_val/60,1);
                    }
                    if($signal_avg > -35){
                        $signal_avg_bar = 1;
                    }

                    //Get the latest entry
					$this->NodeStation->contain();
                    $lastCreated = $this->NodeStation->find('first', array(
                        'conditions'    => array(
                            'NodeStation.mac'           => $mac,
                            'NodeStation.mesh_entry_id' => $mesh_entry_id
                        ),
                        'order' => array('NodeStation.created' => 'desc')
                    ));

                   // print_r($lastCreated);

                    $signal = $lastCreated['NodeStation']['signal'];

                    if($signal < -95){
                        $signal_bar = 0.01;
                    }
                    if(($signal >= -95)&($signal <= -35)){
                            $p_val = 95-(abs($signal));
                            $signal_bar = round($p_val/60,1);
                    }
                    if($signal > -35){
                        $signal_bar = 1;
                    }
                    
                    $last_node_id = $lastCreated['NodeStation']['node_id'];

                    array_push($items,array(
                        'id'                => $id,
                        'name'              => $entry_name, 
                        'mesh_entry_id'     => $mesh_entry_id, 
                        'mac'               => $mac,
                        'vendor'            => $lastCreated['NodeStation']['vendor'],
                        'tx_bytes'          => $t_bytes,
                        'rx_bytes'          => $r_bytes, 
                        'signal_avg'        => $signal_avg ,
                        'signal_avg_bar'    => $signal_avg_bar,
                        'signal_bar'        => $signal_bar,
                        'signal'            => $signal,
                        'l_tx_bitrate'      => $lastCreated['NodeStation']['tx_bitrate'],
                        'l_rx_bitrate'      => $lastCreated['NodeStation']['rx_bitrate'],
                        'l_signal'          => $lastCreated['NodeStation']['signal'],
                        'l_signal_avg'      => $lastCreated['NodeStation']['signal_avg'],
                        'l_MFP'             => $lastCreated['NodeStation']['MFP'],
                        'l_tx_failed'       => $lastCreated['NodeStation']['tx_failed'],
                        'l_tx_retries'      => $lastCreated['NodeStation']['tx_retries'],
                        'l_modified'        => $lastCreated['NodeStation']['modified'],
                        'l_modified_human'  => $this->TimeCalculations->time_elapsed_string($lastCreated['NodeStation']['modified']),
                        'l_authenticated'   => $lastCreated['NodeStation']['authenticated'],
                        'l_authorized'      => $lastCreated['NodeStation']['authorized'],
                        'l_tx_bytes'        => $lastCreated['NodeStation']['tx_bytes'],
                        'l_rx_bytes'        => $lastCreated['NodeStation']['rx_bytes'],
                        'l_node'            => $this->node_lookup[$last_node_id]
                    ));
                    $id++;
                }
            }else{
                 array_push($items,array(
                        'id'                => $id,
                        'name'              => $entry_name, 
                        'mesh_entry_id'     => $mesh_entry_id, 
                        'mac'               => 'N/A',
                        'tx_bytes'          => 0,
                        'rx_bytes'          => 0, 
                        'signal_avg'        => null ,
                        'signal_bar'        => 'N/A' ,
                        'signal_avg_bar'    => 'N/A',
                        'signal_bar'        => 'N/A',
                        'signal'            => null,
                        'tx_bitrate'        => 0,
                        'rx_bitrate'        => 0,
                        'vendor'            => 'N/A'
                    ));
                    $id++;


            }            
        }

        $this->set(array(
            'items' => $items,
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }


     public function view_nodes(){

		$user = $this->Aa->user_for_token($this);
        if(!$user){   //If not a valid user
            return;
        }

		if(!isset($this->request->query['mesh_id'])){
			$this->set(array(
		        'message'	=> array("message"	=>"Mesh ID (mesh_id) missing"),
		        'success' => false,
		        '_serialize' => array('success','message')
		    ));
			return;
		}

        $items  	= array();
        $id     	= 1;
        $modified 	= $this->_get_timespan();

        //Find all the nodes for this mesh
        $mesh_id = $this->request->query['mesh_id'];

        $this->Node->contain();
        $q_r = $this->Node->find('all',array('conditions' => array(
            'Node.mesh_id'      => $mesh_id
        )));

		//Get the 'dead_after' value
		$dead_after = $this->_get_dead_after($mesh_id);
       
        //Create a lookup of all the entries for this mesh 
        $this->MeshEntry->contain();
        $q_entries = $this->MeshEntry->find('all',array('conditions' => array(
            'MeshEntry.mesh_id' => $mesh_id
        )));

        $this->entry_lookup = array();
        foreach($q_entries as $e){
            $e_id   = $e['MeshEntry']['id'];
            $e_name = $e['MeshEntry']['name'];               
            $this->entry_lookup[$e_id] = $e_name;
        }

        
        //Find all the distinct MACs for this Mesh node...
        foreach($q_r as $i){
            $node_id    = $i['Node']['id'];
            $node_name  = $i['Node']['name'];
            $l_contact  = $i['Node']['last_contact'];

            if($l_contact == null){
                $l_contact_human = null;
                $state = 'never';
            }else{
            
                $l_contact_human = $this->TimeCalculations->time_elapsed_string($l_contact);
                
                $last_timestamp = strtotime($l_contact);
                if($last_timestamp+$dead_after <= time()){
                    $state = 'down';
                }else{
                    $state = 'up';
                }
            }

            $this->NodeStation->contain();
            $q_s = $this->NodeStation->find('all',array(
                'conditions'    => array(
                    'NodeStation.node_id'       => $node_id,
                    'NodeStation.modified >='   => $modified
                ),
                'fields'        => array(
                    'DISTINCT(mac)'
                )
            ));

            if($q_s){
                foreach($q_s as $j){
                    //print_r($j);
                    $mac = $j['NodeStation']['mac'];
                    //Get the sum of Bytes and avg of signal
					$this->NodeStation->contain();
                    $q_t = $this->NodeStation->find('first', array(
                        'conditions'    => array(
                            'NodeStation.mac'           => $mac,
                            'NodeStation.node_id'       => $node_id,
                            'NodeStation.modified >='   => $modified
                        ),
                        'fields'    => array(
                            'SUM(NodeStation.tx_bytes) as tx_bytes',
                            'SUM(NodeStation.rx_bytes)as rx_bytes',
                            'AVG(NodeStation.signal_avg)as signal_avg',
                        )
                    ));
                   // print_r($q_t);
                    $t_bytes    = $q_t[0]['tx_bytes'];
                    $r_bytes    = $q_t[0]['rx_bytes'];
                    $signal_avg = round($q_t[0]['signal_avg']); 
                    if($signal_avg < -95){
                        $signal_avg_bar = 0.01;
                    }
                    if(($signal_avg >= -95)&($signal_avg <= -35)){
                            $p_val = 95-(abs($signal_avg));
                            $signal_avg_bar = round($p_val/60,1);
                    }
                    if($signal_avg > -35){
                        $signal_avg_bar = 1;
                    }

                    //Get the latest entry
					$this->NodeStation->contain();
                    $lastCreated = $this->NodeStation->find('first', array(
                        'conditions'    => array(
                            'NodeStation.mac'       => $mac,
                            'NodeStation.node_id'   => $node_id
                        ),
                        'order' => array('NodeStation.created' => 'desc')
                    ));

                   // print_r($lastCreated);

                    $signal = $lastCreated['NodeStation']['signal'];

                    if($signal < -95){
                        $signal_bar = 0.01;
                    }
                    if(($signal >= -95)&($signal <= -35)){
                            $p_val = 95-(abs($signal));
                            $signal_bar = round($p_val/60,1);
                    }
                    if($signal > -35){
                        $signal_bar = 1;
                    }
                    
                    $last_mesh_entry_id = $lastCreated['NodeStation']['mesh_entry_id'];

                    array_push($items,array(
                        'id'                => $id,
                        'name'              => $node_name, 
                        'node_id'           => $node_id, 
                        'mac'               => $mac,
                        'vendor'            => $lastCreated['NodeStation']['vendor'],
                        'tx_bytes'          => $t_bytes,
                        'rx_bytes'          => $r_bytes, 
                        'signal_avg'        => $signal_avg ,
                        'signal_avg_bar'    => $signal_avg_bar,
                        'signal_bar'        => $signal_bar,
                        'signal'            => $signal,
                        'l_tx_bitrate'      => $lastCreated['NodeStation']['tx_bitrate'],
                        'l_rx_bitrate'      => $lastCreated['NodeStation']['rx_bitrate'],
                        'l_signal'          => $lastCreated['NodeStation']['signal'],
                        'l_signal_avg'      => $lastCreated['NodeStation']['signal_avg'],
                        'l_MFP'             => $lastCreated['NodeStation']['MFP'],
                        'l_tx_failed'       => $lastCreated['NodeStation']['tx_failed'],
                        'l_tx_retries'      => $lastCreated['NodeStation']['tx_retries'],
                        'l_modified'        => $lastCreated['NodeStation']['modified'],
                        'l_modified_human'  => $this->TimeCalculations->time_elapsed_string($lastCreated['NodeStation']['modified']),
                        'l_authenticated'   => $lastCreated['NodeStation']['authenticated'],
                        'l_authorized'      => $lastCreated['NodeStation']['authorized'],
                        'l_tx_bytes'        => $lastCreated['NodeStation']['tx_bytes'],
                        'l_rx_bytes'        => $lastCreated['NodeStation']['rx_bytes'],
                        'l_entry'           => $this->entry_lookup[$last_mesh_entry_id],
                        'l_contact'         => $l_contact,
                        'l_contact_human'   => $l_contact_human,
                        'state'             => $state
                    ));
                    $id++;
                }
            }else{
                 array_push($items,array(
                        'id'                => $id,
                        'name'              => $node_name, 
                        'mesh_entry_id'     => $node_id, 
                        'mac'               => 'N/A',
                        'tx_bytes'          => 0,
                        'rx_bytes'          => 0, 
                        'signal_avg'        => null ,
                        'signal_bar'        => 'N/A' ,
                        'signal_avg_bar'    => 'N/A',
                        'signal_bar'        => 'N/A',
                        'signal'            => null,
                        'tx_bitrate'        => 0,
                        'rx_bitrate'        => 0,
                        'vendor'            => 'N/A',
                        'l_contact'         => $l_contact,
                        'l_contact_human'   => $l_contact_human,
                        'state'             => $state
                    ));
                    $id++;
            }            
        }

        $this->set(array(
            'items' => $items,
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }

	 public function view_node_nodes(){

		$user = $this->Aa->user_for_token($this);
        if(!$user){   //If not a valid user
            return;
        }

		if(!isset($this->request->query['mesh_id'])){
			$this->set(array(
		        'message'	=> array("message"	=>"Mesh ID (mesh_id) missing"),
		        'success' => false,
		        '_serialize' => array('success','message')
		    ));
			return;
		}

        $items  		= array();
        $id     		= 1;
        $modified 		= $this->_get_timespan();
		$node_lookup 	= array();


        //Find all the nodes for this mesh
        $mesh_id = $this->request->query['mesh_id'];

        $this->Node->contain();
        $q_r = $this->Node->find('all',array('conditions' => array(
            'Node.mesh_id'      => $mesh_id
        )));

		//Get the 'dead_after' value
		$dead_after = $this->_get_dead_after($mesh_id);


		//Build a quick lookup
		foreach($q_r as $k){
			$node_id    = $k['Node']['id'];
            $node_name  = $k['Node']['name'];
			$node_lookup[$node_id]=$node_name;
		}
        
        //Find all the distinct MACs for this Mesh node...
        foreach($q_r as $i){
            $node_id    = $i['Node']['id'];
            $node_name  = $i['Node']['name'];
            $l_contact  = $i['Node']['last_contact'];

            //Find the dead time (only once)
            if($l_contact == null){
                $l_contact_human = null;
                $state = 'never';
            }else{
                $l_contact_human = $this->TimeCalculations->time_elapsed_string($l_contact);
                
				$this->NodeSetting->contain();
                $last_timestamp = strtotime($l_contact);
                if($last_timestamp+$dead_after <= time()){
                    $state = 'down';
                }else{
                    $state = 'up';
                }
            }

			//--Get a list of all the other nodes to which this one connected within specified time
			$this->NodeIbssConnection->contain();
			$q_s = $this->NodeIbssConnection->find('all',array(
                'conditions'    => array(
                    'NodeIbssConnection.node_id'    => $node_id,
                    'NodeIbssConnection.modified >='   	=> $modified
                ),
                'fields'        => array(
                    'DISTINCT(mac)'
                )
            ));

			//----- Found ANY? --------------

			if($q_s){
                foreach($q_s as $j){

                    $mac = $j['NodeIbssConnection']['mac'];
                    //Get the sum of Bytes and avg of signal
					$this->NodeIbssConnection->contain();
                    $q_t = $this->NodeIbssConnection->find('first', array(
                        'conditions'    => array(
                            'NodeIbssConnection.mac'           => $mac,
                            'NodeIbssConnection.node_id'       => $node_id,
                            'NodeIbssConnection.modified >='   => $modified
                        ),
                        'fields'    => array(
                            'SUM(NodeIbssConnection.tx_bytes) as tx_bytes',
                            'SUM(NodeIbssConnection.rx_bytes)as rx_bytes',
                            'AVG(NodeIbssConnection.signal_avg)as signal_avg',
                        )
                    ));
                   // print_r($q_t);
                    $t_bytes    = $q_t[0]['tx_bytes'];
                    $r_bytes    = $q_t[0]['rx_bytes'];
                    $signal_avg = round($q_t[0]['signal_avg']); 
                    if($signal_avg < -95){
                        $signal_avg_bar = 0.01;
                    }
                    if(($signal_avg >= -95)&($signal_avg <= -35)){
                            $p_val = 95-(abs($signal_avg));
                            $signal_avg_bar = round($p_val/60,1);
                    }
                    if($signal_avg > -35){
                        $signal_avg_bar = 1;
                    }

                    //Get the latest entry
					$this->NodeIbssConnection->contain();
                    $lastCreated = $this->NodeIbssConnection->find('first', array(
                        'conditions'    => array(
                            'NodeIbssConnection.mac'       => $mac,
                            'NodeIbssConnection.node_id'   => $node_id
                        ),
                        'order' => array('NodeIbssConnection.created' => 'desc')
                    ));

                   // print_r($lastCreated);

                    $signal = $lastCreated['NodeIbssConnection']['signal'];

                    if($signal < -95){
                        $signal_bar = 0.01;
                    }
                    if(($signal >= -95)&($signal <= -35)){
                            $p_val = 95-(abs($signal));
                            $signal_bar = round($p_val/60,1);
                    }
                    if($signal > -35){
                        $signal_bar = 1;
                    }
                   
                    array_push($items,array(
                        'id'                => $id,
                        'name'              => $node_name, 
                        'node_id'           => $node_id, 
                        'mac'               => $mac,
                        'tx_bytes'          => $t_bytes,
                        'rx_bytes'          => $r_bytes, 
                        'signal_avg'        => $signal_avg ,
                        'signal_avg_bar'    => $signal_avg_bar,
                        'signal_bar'        => $signal_bar,
                        'signal'            => $signal,
                        'l_tx_bitrate'      => $lastCreated['NodeIbssConnection']['tx_bitrate'],
                        'l_rx_bitrate'      => $lastCreated['NodeIbssConnection']['rx_bitrate'],
                        'l_signal'          => $lastCreated['NodeIbssConnection']['signal'],
                        'l_signal_avg'      => $lastCreated['NodeIbssConnection']['signal_avg'],
                        'l_MFP'             => $lastCreated['NodeIbssConnection']['MFP'],
                        'l_tx_failed'       => $lastCreated['NodeIbssConnection']['tx_failed'],
                        'l_tx_retries'      => $lastCreated['NodeIbssConnection']['tx_retries'],
                        'l_modified'        => $lastCreated['NodeIbssConnection']['modified'],
                        'l_modified_human'  => $this->TimeCalculations->time_elapsed_string($lastCreated['NodeIbssConnection']['modified']),
                        'l_authenticated'   => $lastCreated['NodeIbssConnection']['authenticated'],
                        'l_authorized'      => $lastCreated['NodeIbssConnection']['authorized'],
                        'l_tx_bytes'        => $lastCreated['NodeIbssConnection']['tx_bytes'],
                        'l_rx_bytes'        => $lastCreated['NodeIbssConnection']['rx_bytes'],
                        'l_contact'         => $l_contact,
                        'l_contact_human'   => $l_contact_human,
                        'state'             => $state
                    ));
                    $id++;
                }

            }else{	//---NOT FOUND ANY???----

                 array_push($items,array(
                        'id'                => $id,
                        'name'              => $node_name, 
                        'node_id'     		=> $node_id, 
                        'mac'               => 'N/A',
                        'tx_bytes'          => 0,
                        'rx_bytes'          => 0, 
                        'signal_avg'        => null ,
                        'signal_bar'        => 'N/A' ,
                        'signal_avg_bar'    => 'N/A',
                        'signal_bar'        => 'N/A',
                        'signal'            => null,
                        'tx_bitrate'        => 0,
                        'rx_bitrate'        => 0,
                        'vendor'            => 'N/A',
                        'l_contact'         => $l_contact,
                        'l_contact_human'   => $l_contact_human,
                        'state'             => $state
                    ));
                    $id++;
            }            

			//--- END FOUND ANY?---

        }


        $this->set(array(
            'items' => $items,
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }


	public function view_node_details(){

		$user = $this->Aa->user_for_token($this);
        if(!$user){   //If not a valid user
            return;
        }

		if(!isset($this->request->query['mesh_id'])){
			$this->set(array(
		        'message'	=> array("message"	=>"Mesh ID (mesh_id) missing"),
		        'success' => false,
		        '_serialize' => array('success','message')
		    ));
			return;
		}

		$items 		= array();
		$mesh_id 	= $this->request->query['mesh_id'];
		
		$do_gateway = true;

		//Get the 'dead_after' value
		$dead_after = $this->_get_dead_after($mesh_id);

		$this->Node->contain('NodeSystem', 'NodeLoad','NodeAction','NodeNeighbor');
		$q_r 		= $this->Node->find('all', array('conditions' => array('Node.mesh_id' => $mesh_id )));

		//Create a hardware lookup for proper names of hardware
	    $hardware 	= $this->_make_hardware_lookup(); 
		
		foreach($q_r as $i){

			$l_contact  = $i['Node']['last_contact'];
			$hw_id 		= $i['Node']['hardware'];
			$hw_human	= $hardware["$hw_id"]; 	//Human name for Hardware

			//===Determine when last did we saw this node (never / up / down) ====
			if($l_contact == null){
                $state = 'never';  
                $i['Node']['last_contact_human'] = $l_contact;
                
            }else{
                $last_timestamp = strtotime($l_contact);
                if($last_timestamp+$dead_after <= time()){
                    $state = 'down';
                }else{
                    $state = 'up';
                }
                
                //Make it easy for us to understand
                $i['Node']['last_contact_human'] = $this->TimeCalculations->time_elapsed_string($l_contact);
                
            }

			//=== add extra info to node data ===
			$i['Node']['state'] 	= $state;
			$i['Node']['hw_human'] 	= $hw_human;
			$node_data				= $i['Node'];
			unset($i['NodeLoad']['id']);		//Else the node's ID is just wrong!
			$load_data				= $i['NodeLoad'];
			$this_data 				= array_merge((array)$node_data,(array)$load_data);
		
			$system_data			= array();
			foreach($i['NodeSystem'] as $ns){
				$group 	= $ns['group'];
				$name	= $ns['name'];
				$value	= $ns['value'];
				$k 		= array('name' => $name, 'value' => $value);

				if(!array_key_exists($group,$system_data)){
					$system_data[$group] = array();
				}
				array_push($system_data[$group],$k);

			}	
			$this_data 	= array_merge((array)$this_data,(array)$system_data);

			//Merge the last command (if present)
			if(count($i['NodeAction'])>0){
				$last_action = $i['NodeAction'][0];
				//Add it to the list....
				$this_data['last_cmd'] 			= $last_action['command'];
				$this_data['last_cmd_status'] 	= $last_action['status'];
			}
			
			$gateway = 'yes';
			if(count($i['NodeNeighbor'])>0){
			    $gateway = $i['NodeNeighbor'][0]['gateway'];
			}
			$this_data['gateway'] = $gateway;
					
			if($gateway == 'yes'){
			    //See if there are any Openvpn connections
			    $this->OpenvpnServerClient->contain('OpenvpnServer');
			    $q_vpn = $this->OpenvpnServerClient->find('all',array('conditions' => array('OpenvpnServerClient.mesh_id' => $mesh_id)));
			    if($q_vpn){
			        if($do_gateway == true){ //This will ensure we only to it once per mesh :-)
			            $this_data['openvpn_list'] = array();
			            foreach($q_vpn as $vpn){
			                $vpn_name           = $vpn['OpenvpnServer']['name']; 
			                $vpn_description    = $vpn['OpenvpnServer']['description'];
			                $last_contact_to_server  = $vpn['OpenvpnServerClient']['last_contact_to_server'];
			                if($last_contact_to_server != null){
			                    $lc_human           = $this->TimeCalculations->time_elapsed_string($last_contact_to_server);
			                }else{
			                    $lc_human = 'never';
			                }
			                $vpn_state              = $vpn['OpenvpnServerClient']['state'];
			                array_push($this_data['openvpn_list'], array(
			                    'name'          => $vpn_name,
			                    'description'   => $vpn_description,
			                    'lc_human'      => $lc_human,
			                    'state'         => $vpn_state
			                ));
			            }
			            //print_r($q_vpn);
			            $do_gateway = false;
			        }
			    }
			}
			
			

			array_push($items,$this_data);
		}
	
		$this->set(array(
            'items' => $items,
            'success' => true,
            '_serialize' => array('items','success')
        ));
	}

	public function restart_nodes(){

		$user = $this->Aa->user_for_token($this);
        if(!$user){   //If not a valid user
            return;
        }
		//Loop through the nodes and make sure there is not already one pending before adding one
		foreach($this->request->data['nodes'] as $n){
			$node_id	= $n['id'];
			$this->NodeAction->contain();
			$already = $this->NodeAction->find('count', array('conditions' => 
				array('NodeAction.command' => 'reboot','NodeAction.node_id' => $node_id, 'NodeAction.status' => 'awaiting' )
			));

			if($already == 0){ //Nothing waiting - Create a new one
				$d	= array('node_id' => $node_id,'command' => 'reboot');
				$this->NodeAction->create();
				$this->NodeAction->save($d);
				$this->NodeAction->id = null;
			}
		}

		$items = array();
		$this->set(array(
            'items' => $items,
            'success' => true,
            '_serialize' => array('items','success')
        ));
	}

    //---------- Private Functions --------------

    private function _new_report(){

        //--- Check if the 'network_info' array is in the data ----
        $this->log('Checking for network_info in log', 'debug');
        if(array_key_exists('network_info',$this->request->data)){
            $this->log('Found network_info', 'debug');
            foreach($this->request->data['network_info'] as $ni){
                $id = $this->_format_mac($ni['eth0']);
                $this->log('Locating the node with MAC '.$id, 'debug');
                $this->Node->contain();
                $q_r = $this->Node->findByMac($id);
                if($q_r){
                    $node_id = $q_r['Node']['id'];
                    $mesh_id    = $q_r['Node']['mesh_id'];
                    $this->log('The node id of '.$id.' is '.$node_id, 'debug');
                    $rad_zero_int = $ni['radios'][0]['interfaces'];
                    $this->_do_radio_interfaces($mesh_id,$node_id,$rad_zero_int);

					//If it is a dual radio --- report on it also ----
					if(array_key_exists(1,$ni['radios'])){
						$this->log('Second RADIO reported for '.$id.' is '.$node_id, 'debug');
						$rad_one_int = $ni['radios'][1]['interfaces'];
						$this->_do_radio_interfaces($mesh_id,$node_id,$rad_one_int);
					}
                }else{
                    $this->log('Node with MAC '.$id.' was not found', 'debug');
                }
            }
        }
        
        //--- Check if the 'vpn_info' array is in the data ----
        $this->log('MESH: Checking for vpn_info in log', 'debug');
        if(array_key_exists('vpn_info',$this->request->data)){
            $this->log('MESH: Found vpn_info', 'debug');    
            $openvpn_server_client = ClassRegistry::init('OpenvpnServerClient');  
            foreach($this->request->data['vpn_info'] as $vpn_i){
                $vpn_gw_list = $vpn_i['vpn_gateways'];
                foreach($vpn_gw_list as $gw){
                    $vpn_client_id  = $gw['vpn_client_id'];
                    $vpn_state      = $gw['state'];
                    $timestamp      = $gw['timestamp'];
                    $date           = date('Y-m-d H:i:s',$timestamp);
                    
                    $d              = array();
                    $d['id']        = $vpn_client_id;
                    $d['last_contact_to_server'] =  $date;
                    $d['state']     = $vpn_state;
                    $openvpn_server_client->save($d); 
                }    
            }  
        }

       
        //--- Check if the 'system_info' array is in the data ----
        $this->log('Checking for system_info in log', 'debug');
	$m_id = false;
        if(array_key_exists('system_info',$this->request->data)){
            $this->log('Found system_info', 'debug');
            $mesh_id = false;
            foreach($this->request->data['system_info'] as $si){
                $id = $this->_format_mac($si['eth0']);
                $this->log('Locating the node with MAC '.$id, 'debug');
                $this->Node->contain();
                $q_r = $this->Node->findByMac($id);
                if($q_r){ 
                    $node_id    = $q_r['Node']['id'];
                    $m_id    	= $q_r['Node']['mesh_id'];
                    $this->log('The node id of '.$id.' is '.$node_id, 'debug');
                    $this->_do_node_system_info($node_id,$si['sys']);
                    $this->_do_node_load($node_id,$si['sys']);
                    $this->_update_last_contact($node_id);
                }else{
                    $this->log('Node with MAC '.$id.' was not found', 'debug');
                }
            }  
        }
        
        //See if there are any heartbeats associated with the Mesh these nodes belong to (For the captive portals)
        if($m_id){ 
            $this->_update_any_nas_heartbeats($m_id);
        }
        

		//----- Check if the 'vis' array is in the data ----
		$this->log('Checking for vis info in log', 'debug');
		if(array_key_exists('vis',$this->request->data)){

			$this->log('Found vis', 'debug');
			//Create a lookup hash:
			$mac_lookup = array();
            foreach($this->request->data['vis'] as $vis){
              	$mac = $vis['eth0'];
				$gw	 = $vis['gateway'];
                $this->Node->contain();
                $q_r = $this->Node->findByMac($mac);
                if($q_r){
                   	$node_id    = $q_r['Node']['id'];
					$mac_lookup[$mac] = $node_id;
					$gw_val = 'no';
					if($gw == 1){
						$gw_val = 'yes';
					}
                    $this->log("Found VIS node $mac", 'debug');
					//Check if there are any entries for this node + neighbor combination
					foreach($vis['neighbors'] as $n){
						$neighbor_mac = $n['eth0'];
						$neighbor_id  = false;
						$metric		  = $n['metric'];
						$hwmode 	  = 'g';
						if(array_key_exists('hwmode',$n)){
							$hwmode		  = $n['hwmode'];
						}
						if(!array_key_exists($neighbor_mac,$mac_lookup)){
							//Find the ID of the neighbor
							$q_n = $this->Node->findByMac($neighbor_mac);
							if($q_n){
								$this->log("FOUND $neighbor_mac", 'debug');
								$mac_lookup[$neighbor_mac] = $q_n['Node']['id'];
								$neighbor_id = 	$q_n['Node']['id'];
							}
						}else{
							$neighbor_id = 	$mac_lookup[$neighbor_mac];
						}
						if($neighbor_id){
							$d = array();
							$previous = $this->NodeNeighbor->find('first',
								array('conditions' =>array(
									'NodeNeighbor.node_id' 		=> $node_id,
									'NodeNeighbor.neighbor_id'  => $neighbor_id,
								))
							);
							if($previous) {
								$d['id'] = $previous[ 'NodeNeighbor' ][ 'id' ];
							}
							$d['node_id']		= $node_id;
							$d['neighbor_id']	= $neighbor_id;
							$d['metric']	    = $metric;
							$d['gateway']	    = $gw_val;
							$d['hwmode']		= $hwmode;
							$this->NodeNeighbor->saveAll($d);

						}
					}
                }
            }  
		}

		//--- Finally we may have some commands waiting for the nodes----
		//--- We assume $this->request->data['network_info'][0]['eth0'] will contain one of the nodes of the mesh
		$items = false;
		if(array_key_exists('network_info',$this->request->data)){
            $this->log('Looking for commands waiting for this mesh', 'debug');

			$id 	= $this->_format_mac($this->request->data['network_info'][0]['eth0']);
			$this->Node->contain();
		    $q_r 	= $this->Node->findByMac($id);
		    if($q_r){
				$items	 = array();
		        $node_id = $q_r['Node']['id'];
		        $mesh_id = $q_r['Node']['mesh_id'];
				$this->NodeAction->contain('Node');
				$q_r = $this->NodeAction->find('all', 
					array('conditions' => array('Node.mesh_id' => $mesh_id,'NodeAction.status' => 'awaiting')
				)); //Only awaiting actions
				foreach($q_r as $i){
					$mac 		= strtoupper($i['Node']['mac']);
					$action_id	= $i['NodeAction']['id'];
					if(array_key_exists($mac,$items)){
						array_push($items[$mac],$action_id);
					}else{
						$items[$mac] = array($action_id); //First one
					}
				}
		    }else{
                $this->log('Node with MAC '.$id.' was not found', 'debug');
            }
		}

		return $items;       
    }

    private function _do_radio_interfaces($mesh_id,$node_id,$interfaces){

        foreach($interfaces as $i){
            if(count($i['stations']) > 0){
                //Try to find (if type=AP)the Entry ID of the Mesh
                if($i['type'] == 'AP'){
                    $this->MeshEntry->contain();
                    $q_r = $this->MeshEntry->find('first', array(
                        'conditions'    => array(
                            'MeshEntry.name'    => $i['ssid'],
                            'MeshEntry.mesh_id' => $mesh_id
                        )
                    ));

                    if($q_r){
                        $entry_id = $q_r['MeshEntry']['id'];
                        foreach($i['stations'] as $s){
                            $data = $this->_prep_station_data($s);
                            $data['mesh_entry_id']  = $entry_id;
                            $data['node_id']        = $node_id;
                            //--Check the last entry for this MAC
                            $q_mac = $this->NodeStation->find('first',array(
                                'conditions'    => array(
                                    'NodeStation.mesh_entry_id' => $entry_id,
                                    'NodeStation.node_id'       => $node_id,
                                    'NodeStation.mac'           => $data['mac'],
                                ),
                                'order' => array('NodeStation.created' => 'desc')
                            ));
                            $new_flag = true;
                            if($q_mac){
                                $old_tx = $q_mac['NodeStation']['tx_bytes'];
                                $old_rx = $q_mac['NodeStation']['rx_bytes'];
                                if(($data['tx_bytes'] >= $old_tx)&($data['rx_bytes'] >= $old_rx)){
                                    $data['id'] =  $q_mac['NodeStation']['id'];
                                    $new_flag = false;   
                                }
                            }
                            if($new_flag){
                                $this->NodeStation->create();
                            }   
                            $this->NodeStation->save($data);
                        }
                    }
                }

                //If the type is IBSS we will try to determine which nodes are connected
                //April 2016 - We now also include support for mesh node (802.11s)
                if(($i['type'] == 'IBSS')||($i['type'] == 'mesh point')){
                     foreach($i['stations'] as $s){
                            $data = $this->_prep_station_data($s);
                            $data['node_id']    = $node_id;

                            //--Check the last entry for this MAC
                            $q_mac = $this->NodeIbssConnection->find('first',array(
                                'conditions'    => array(
                                    'NodeIbssConnection.node_id'    => $node_id,
                                    'NodeIbssConnection.mac'        => $data['mac'],
                                ),
                                'order' => array('NodeIbssConnection.created' => 'desc')
                            ));
                            $new_flag = true;
                            if($q_mac){
                                $old_tx = $q_mac['NodeIbssConnection']['tx_bytes'];
                                $old_rx = $q_mac['NodeIbssConnection']['rx_bytes'];
                                if(($data['tx_bytes'] >= $old_tx)&($data['rx_bytes'] >= $old_rx)){
                                    $data['id'] =  $q_mac['NodeIbssConnection']['id'];
                                    $new_flag = false;   
                                }
                            }
                            if($new_flag){
                                $this->NodeIbssConnection->create(); 
                            }    
                            $this->NodeIbssConnection->save($data);
                     }
                }
                    
            }
        }
    }

    private function _do_node_load($node_id,$info){
        $this->log('====Doing the node load info for===: '.$node_id, 'debug');
        $mem_total  = $this->_mem_kb_to_bytes($info['memory']['total']);
        $mem_free   = $this->_mem_kb_to_bytes($info['memory']['free']);
        $u          = $info['uptime'];
        $time       = preg_replace('/\s+up.*/', "", $u);
        $load       = preg_replace('/.*.\s+load average:\s+/', "", $u);
        $loads      = explode(", ",$load);
        $up         = preg_replace('/.*\s+up\s+/', "", $u);
        $up         = preg_replace('/,\s*.*/', "", $up);
        $data       = array();
        $data['mem_total']  = $mem_total;
        $data['mem_free']   = $mem_free;
        $data['uptime']     = $up;
        $data['system_time']= $time;
        $data['load_1']     = $loads[0];
        $data['load_2']     = $loads[1];
        $data['load_3']     = $loads[2];
        $data['node_id']    = $node_id;


        $n_l = $this->NodeLoad->find('first',array(
            'conditions'    => array(
                'NodeLoad.node_id' 	=> $node_id
            )
        ));

        $new_flag = true;
        if($n_l){  
		    $data['id'] =  $n_l['NodeLoad']['id'];
		    $new_flag 	= false;   
        }
        if($new_flag){
            $this->NodeLoad->create();
        }   
        $this->NodeLoad->save($data);
    }

    private function _do_node_system_info($node_id,$info){
        $this->log('Doing the system info for node id: '.$node_id, 'debug');

        $q_r = $this->NodeSystem->findByNodeId($node_id);
        if(!$q_r){
            $this->log('EMPTY NodeSystem - Add first one', 'debug');
            $this->_new_node_system($node_id,$info);

        }else{
            $this->log('NodeSystem info exists - Update if needed', 'debug');
            //We will check the value of DISTRIB_REVISION
            $dist_rev = false;
            if(array_key_exists('release',$info)){ 
                $release_array = explode("\n",$info['release']);
                foreach($release_array as $r){  
                    $this->log("There are ".$r, 'debug'); 
                    $r_entry    = explode('=',$r);
                    $elements   = count($r_entry);
                    if($elements == 2){
                        $value          = preg_replace('/"|\'/', "", $r_entry[1]);
                        if(preg_match('/DISTRIB_REVISION/',$r_entry[0])){
                            $dist_rev = $value;
                            $this->log('Submitted DISTRIB_REVISION '.$dist_rev, 'debug');
                            break;
                        }
                        
                    }
                }
            }

            //Find the current  DISTRIB_REVISION
            $q_r = $this->NodeSystem->find('first', array('conditions' => 
                        array(
                            'NodeSystem.node_id'    => $node_id,
                            'NodeSystem.name'       => 'DISTRIB_REVISION'
            )));        
            if($q_r){
                $current = $q_r['NodeSystem']['value'];

                $this->log('Current DISTRIB_REVISION '.$dist_rev, 'debug');
                if($current !== $dist_rev){
                    $this->log('Change in DISTRIB_REVISION -> renew', 'debug');
                    $this->NodeSystem->deleteAll(array('NodeSystem.node_id' => $node_id), false);
                    $this->_new_node_system($node_id,$info);
                }else{
                    $this->log('DISTRIB_REVISION unchanged', 'debug');
                }
            }
        }
    }

    private function _new_node_system($node_id,$info){
        //--CPU Info--
        if(array_key_exists('cpu',$info)){
             $this->log('Adding  CPU info', 'debug');
            foreach(array_keys($info['cpu']) as $key){
              //  $this->log('Adding first CPU info '.$key, 'debug');
                $this->NodeSystem->create();
                $d['group']     = 'cpu';
                $d['name']      = $key;
                $d['value']     = $info['cpu']["$key"];
                $d['node_id']   = $node_id;
                $this->NodeSystem->save($d);
            }
        }

        //--
        if(array_key_exists('release',$info)){ 

            $release_array = explode("\n",$info['release']);
            foreach($release_array as $r){  
               // $this->log("There are ".$r, 'debug'); 
                $r_entry    = explode('=',$r);
                $elements   = count($r_entry);
                if($elements == 2){
                   // $this->log('Adding  Release info '.$r, 'debug');
                    $value          = preg_replace('/"|\'/', "", $r_entry[1]);
                    $this->NodeSystem->create();
                    $d['group']     = 'release';
                    $d['name']      = $r_entry[0];
                    $d['value']     = $value;
                    $d['node_id']   = $node_id;
                    $this->NodeSystem->save($d);
                }
            }
        }           
    }
      
    private function _update_any_nas_heartbeats($mesh_id){
        $this->MeshExit->contain('MeshExitCaptivePortal');
        //Only captive portal types
        $q_r = $this->MeshExit->find('all', array('conditions' => array('MeshExit.mesh_id' => $mesh_id, 'MeshExit.type' => 'captive_portal')));
        
        $this->log("**Updating hearbeats on the NAS for mesh $mesh_id**", 'debug');
        if($q_r){
            $na = ClassRegistry::init('Na');
            $na->contain();
            foreach($q_r as $i){
        	$this->log('Found a captive portal on the mesh', 'debug');
                if(array_key_exists('radius_nasid',$i['MeshExitCaptivePortal'] )){
                    $nas_id = $i['MeshExitCaptivePortal']['radius_nasid'];
                    $n_q    = $na->find('first', 
                        array('conditions' => 
                            array(
                                'Na.nasidentifier'  => $nas_id,
                                'Na.type'           => 'CoovaChilli-Heartbeat',
                                'Na.monitor'        => 'heartbeat'
                            )
                        ));
                    if($n_q){
                        $na->id = $n_q['Na']['id'];
                        $na->saveField('last_contact', date('Y-m-d H:i:s'));
                    }  
                }    
            } 
        }
    }

    private function _update_last_contact($node_id){
        $this->Node->id = $node_id;
        if($this->Node->id){
            $this->Node->saveField('last_contact', date("Y-m-d H:i:s", time()));
        }
    }

    private function _format_mac($mac){
        return preg_replace('/:/', '-', $mac);
    }

    private function _mem_kb_to_bytes($kb_val){
        $kb = preg_replace('/\s*kb/i', "", $kb_val);
        return($kb * 1024);
    }

    private function _prep_station_data($station_info){
        $data       = array();
        $tx_proc    = $station_info['tx bitrate'];
        $tx_bitrate = preg_replace('/\s+.*/','',$tx_proc);
        $tx_extra   = preg_replace('/.*\s+/','',$tx_proc);
        $rx_proc    = $station_info['rx bitrate'];
        $rx_bitrate = preg_replace('/\s+.*/','',$rx_proc);
        $rx_extra   = preg_replace('/.*\s+/','',$rx_proc);
        $incative   = preg_replace('/\s+ms.*/','',$station_info['inactive time']);
        $s          = preg_replace('/\s+\[.*/','',$station_info['signal']);
        $a          = preg_replace('/\s+\[.*/','',$station_info['avg']);

        $data['vendor']        = $this->_lookup_vendor($station_info['mac']);
        $data['mac']           = $station_info['mac'];
        $data['tx_bytes']      = $station_info['tx bytes'];
        $data['rx_bytes']      = $station_info['rx bytes'];
        $data['tx_packets']    = $station_info['tx packets'];
        $data['rx_packets']    = $station_info['rx packets'];
        $data['tx_bitrate']    = $tx_bitrate;
        $data['rx_bitrate']    = $rx_bitrate;
        $data['tx_extra_info'] = $tx_extra;
        $data['rx_extra_info'] = $rx_extra;
        $data['authorized']    = $station_info['authorized'];
        $data['authenticated'] = $station_info['authenticated'];
        $data['tdls_peer']     = $station_info['TDLS peer'];
        $data['preamble']      = $station_info['preamble'];
        $data['tx_failed']     = $station_info['tx failed'];
        $data['tx_failed']     = $station_info['tx failed'];
        $data['inactive_time'] = $incative;
        $data['WMM_WME']       = $station_info['WMM/WME'];
        $data['tx_retries']    = $station_info['tx retries'];
        $data['MFP']           = $station_info['MFP'];
        $data['signal']        = $s;
        $data['signal_avg']    = $a;
        return $data;
    }

    private function _lookup_vendor($mac){
        //Convert the MAC to be in the same format as the file 
        $mac    = strtoupper($mac);
        $pieces = explode(":", $mac);

        $big_match      = $pieces[0].":".$pieces[1].":".$pieces[2].":".$pieces[3].":".$pieces[4];
        $small_match    = $pieces[0].":".$pieces[1].":".$pieces[2];
        $lines          = $this->vendor_list;

        $big_match_found = false;
        foreach($lines as $i){
            if(preg_match("/^$big_match/",$i)){
                $big_match_found = true;
                //Transform this line
                $vendor = preg_replace("/$big_match\s?/","",$i);
                $vendor = preg_replace( "{[ \t]+}", ' ', $vendor );
                $vendor = rtrim($vendor);
                return $vendor;   
            }
        }
       
        if(!$big_match_found){
            foreach($lines as $i){
                if(preg_match("/^$small_match/",$i)){
                    //Transform this line
                    $vendor = preg_replace("/$small_match\s?/","",$i);
                    $vendor = preg_replace( "{[ \t]+}", ' ', $vendor );
                    $vendor = rtrim($vendor);
                    return $vendor;
                }
            }
        }
        $vendor = "Unkown";
    }

	private function _get_dead_after($mesh_id){
		Configure::load('MESHdesk');
		$data 		= Configure::read('common_node_settings'); //Read the defaults
		$dead_after	= $data['heartbeat_dead_after'];
		$n_s = $this->NodeSetting->find('first',array(
            'conditions'    => array(
                'NodeSetting.mesh_id' => $mesh_id
            )
        )); 
        if($n_s){
            $dead_after = $n_s['NodeSetting']['heartbeat_dead_after'];
        }
		return $dead_after;
	}

	private function _make_hardware_lookup(){
		$hardware = array();
		Configure::load('MESHdesk');        
	    $hw   = Configure::read('hardware');
	    foreach($hw as $h){
	        $id     = $h['id'];
	        $name   = $h['name']; 
	        $hardware["$id"]= $name;
	    }
		return $hardware;
	}

	private function _get_timespan(){

		$hour   = (60*60);
        $day    = $hour*24;
        $week   = $day*7;

		$timespan = 'hour';  //Default
        if(isset($this->request->query['timespan'])){
            $timespan = $this->request->query['timespan'];
        }

        if($timespan == 'hour'){
            //Get entries created modified during the past hour
            $modified = date("Y-m-d H:i:s", time()-$hour);
        }

        if($timespan == 'day'){
            //Get entries created modified during the past hour
            $modified = date("Y-m-d H:i:s", time()-$day);
        }

        if($timespan == 'week'){
            //Get entries created modified during the past hour
            $modified = date("Y-m-d H:i:s", time()-$week);
        }
		return $modified;
	}
}
?>
