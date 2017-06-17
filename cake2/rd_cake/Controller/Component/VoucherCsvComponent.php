<?php
class VoucherCsvComponent extends Component {

    private $default_email  = "dirk@gmail.com";
    private $pwd_length     = 3;
	
   	public function generateVoucherList($temp_file,$batch,$message_id){
        $voucher_list   = array();
        $file           = fopen("$temp_file","r");
        while(! feof($file)){
            //This convention is so that we need to create vouchers for all the $line_array[1] that starts with CM
            //;CMA12A;KOOS NEL;koos@nel.com;
            $line_array = fgetcsv($file,0,';'); //We need to specify ";"
            if(count($line_array) > 2){
                $unit_name = $line_array[1];
                if(preg_match("/^(CM)/",$unit_name)){
                    //See if there is an email
                    if(count($line_array) > 4){
                        $email = $line_array[3];
                        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
                        if (!filter_var($email, FILTER_VALIDATE_EMAIL) === true) {
                            $email = $this->default_email;
                        } 
                    }else{
                        $email = $this->default_email;
                    }
                    //See if there is a email_message id
                    $extra_name = 'mail_not_sent';
                    if($message_id){
                        $extra_name = 'mail_not_sent'.'_'.$message_id;
                    }
                    
                    $batch  = $batch;
                    $name   = "$batch"."-".$unit_name;
                    $pwd    = $this->_random_alpha_numeric($this->pwd_length);
                    array_push($voucher_list,array('name' => $name, 'password' => $pwd, 'extra_name' => $extra_name ,'extra_value' => $email));
                }
            }
        }
        fclose($file);     
        return $voucher_list;
    }

    private function _random_alpha_numeric($length = 6){
        // start with a blank password
        $v_value = "";
        // define possible characters
       // $possible = "!#$%^&*()+=?0123456789bBcCdDfFgGhHjJkmnNpPqQrRstTvwxyz";
        $possible = "0123456789bBcCdDfFgGhHjJkmnNpPqQrRstTvwxyz";
        // set up a counter
        $i = 0; 
        // add random characters to $password until $length is reached
        while ($i < $length) { 
            // pick a random character from the possible ones
            $char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
            // we don't want this character if it's already in the password
            if (!strstr($v_value, $char)) { 
                $v_value .= $char;
                $i++;
            }
        }
        return $v_value;
    }
}
?>
