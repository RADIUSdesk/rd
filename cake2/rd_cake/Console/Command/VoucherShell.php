<?php
class VoucherShell extends AppShell {

    //This shell runs at longer intervals (15 min) to check for tw things.
    //It checks all the new and used vouchers and then see if:
   //It has **Rd-Voucher** attribute it will mark it as depleted if the time is up
   //It has **Expiration** attribute it will mark the voucher as expired if it is passed the expiration date

    public $uses    = array('Radcheck','Radacct','Voucher','Radusergroup','Radgroupcheck');
    public $tasks   = array('Counters','Usage');

    public function main() {
        $qr = $this->Voucher->find('all',
            array('conditions' => 
                array('OR'=> 
                    array(array('Voucher.status' => 'new'),array('Voucher.status' => 'used'))
                )
            ));

        foreach($qr as $i){
            $this->process_voucher($i['Voucher']['name']);
        }
    }

    private function process_voucher($name){

        $this->out("<info>Voucher => $name</info>");

        //Test for depleted
		$ret_val 				= $this->Usage->time_left_from_login($name);

		$time_left_from_login 	= $ret_val[0];
		$time_avail 			= $ret_val[1];

        if($time_left_from_login){
            if($time_left_from_login == 'depleted'){
                //Mark time usage as 100% and voucher as depleted
                $q_r = $this->Voucher->findByName($name);
                if($q_r){
                    $this->Voucher->id              = $q_r['Voucher']['id'];
                    $d['Voucher']['id']             = $q_r['Voucher']['id'];
                    $d['Voucher']['precede']        = '';
                    $d['Voucher']['perc_time_used'] = 100;
                    $d['Voucher']['status']         = 'depleted';
					if($time_avail){
						$d['Voucher']['time_cap']       = $time_avail;
						$d['Voucher']['time_used']      = $time_avail; //Make them equal
					}
                    $this->Voucher->save($d);
                }
            }else{
				if($time_avail){
					$time_used 	= $time_avail - $time_left_from_login;
					$q_r 		= $this->Voucher->findByName($name);
				    if($q_r){
				        $this->Voucher->id              = $q_r['Voucher']['id'];
				        $d['Voucher']['id']             = $q_r['Voucher']['id'];
						$d['Voucher']['time_cap']       = $time_avail;
						$d['Voucher']['time_used']      = $time_used; //Make them equal
				        $this->Voucher->save($d);
				    }
				}
			}
        }

        //Test for expired
         $time_left_from_expire = $this->Usage->time_left_from_expire($name);
        if($time_left_from_expire){
            if($time_left_from_expire == 'expired'){
                //Mark time usage as 100% and voucher as expired
                $q_r = $this->Voucher->findByName($name);
                if($q_r){
                    $this->Voucher->id              = $q_r['Voucher']['id'];
                    $d['Voucher']['id']             = $q_r['Voucher']['id'];
                    $d['Voucher']['precede']        = '';
                    $d['Voucher']['perc_time_used'] = 100;
                    $d['Voucher']['status']         = 'expired';
                    $this->Voucher->save($d);
                }
            }
        }
    }
}

?>
