<?php
class DailyPermanentUserNotificationShell extends AppShell {

    public $uses    = array('PermanentUserNotification','PermanentUser');

    public function main() {
        $this->out("<comment>Email of Permanent  Users with notification type **daily**".APP."</comment>");
        //For now we only support email
        $q_r = $this->PermanentUserNotification->find('all',array('conditions' => 
            array(
                'PermanentUserNotification.type'    =>'daily',
                'PermanentUserNotification.method'  =>'email'
            )));
        if($q_r){
            foreach($q_r as $i){
                $this->_prepare_email($i);  
            }
        }
    }

    private function _prepare_email($i){
        print_r($i);
        //We need to check if the email addresses are valid
        $email_1 = $i['PermanentUserNotification']['address_1'];
        $email_2 = $i['PermanentUserNotification']['address_2'];
        $pu_data = $i['PermanentUser'];

      
        if (!filter_var($email_1, FILTER_VALIDATE_EMAIL) === false) {
            $this->out("<info>$email_1 is a valid email address</info>");
            $this->_send_out_email($pu_data,$email_1);    
        } else {
            $this->out("<info>$email_1 is not a valid email address</info>");
        }

        if($email_2 != ''){
            if (!filter_var($email_2, FILTER_VALIDATE_EMAIL) === false) {
                $this->out("<info>$email_2 is a valid email address</info>");
                $this->_send_out_email($pu_data,$email_2);
            } else {
                $this->out("<info>$email_2 is not a valid email address</info>");
            }
        }
    }

    private function _send_out_email($pu_data,$to){

        $username       = $pu_data['username'];
        $perc_time_used = $pu_data['perc_time_used'];
        $perc_data_used = $pu_data['perc_data_used'];


        $email_server = Configure::read('EmailServer');
        App::uses('CakeEmail', 'Network/Email');
        $Email = new CakeEmail();
        $Email->config($email_server);
        $Email->subject('Your daily usage report');
        $Email->to($to);
        $Email->viewVars(compact( 'username','perc_time_used','perc_data_used'));
        $Email->template('daily_permanent_user_notification', 'daily_permanent_user_notification');
        $Email->emailFormat('html');
      //  $Email->send();
    }
}

?>
