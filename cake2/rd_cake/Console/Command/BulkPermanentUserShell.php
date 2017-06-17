<?php
class BulkPermanentUserShell extends AppShell {

    public $uses    = array('User','Profile','Realm');
    public $tasks   = array();

    public function main() {
        $this->out('<info>======================================</info>');
        $this->out('<info>===Bulk import of permanent users ====</info>');
        $this->out('<info>======================================</info>');

        //Fetch the file with usernames
        $file = $this->in('Specify the CSV file with list of users', null, '/tmp/users.csv');
        if(file_exists($file)){
            $contents = file($file);
            $this->add_users($contents);
        }else{
          $this->out("<warning>Could not open $file. Check if it exists!</warning>");  
        }
    }


    private function add_users($contents){

        //===Choose an owner====
        $this->User->contain('Group');
        $owners  = $this->User->find('all',
                array(
                    'fields'        => array('User.token','User.username'),
                    'conditions'    => array('OR' => 
                                        array(
                                            'Group.name' => array(Configure::read('group.ap'),Configure::read('group.admin'))
                                            )
                                        )
                                    )
                    );
        $owner_options = array();
        foreach($owners as $o){
            $username   = $o['User']['username'];
            $token      = $o['User']['token'];
            $owner_options["$username"] = $token;
        }
        $owner      = $this->in('Select the creator of the permanent users', array_keys($owner_options), 'root');
        $use_token  = $owner_options[$owner];

        //===Choose a Profile====
        $this->Profile->contain();
        $profiles  = $this->Profile->find('all');
        $profile_options = array();
        $default_profile = '';
        foreach($profiles as $p){
            $name   = $p['Profile']['name'];
            $id     = $p['Profile']['id'];
            $profile_options["$name"] = $id;
            $default_profile = $name;
        }
        $profile        = $this->in('Select the profile these users must belong to', array_keys($profile_options), $default_profile);
        $profile_id    = $profile_options[$profile];


        //===Choose a Realm======
        $this->Realm->contain();
        $realms  = $this->Realm->find('all');
        $realm_options = array();
        $default_realm = '';
        foreach($realms as $r){
            $name   = $r['Realm']['name'];
            $id     = $r['Realm']['id'];
            $realm_options["$name"] = $id;
            $default_realm = $name;
        }
        $realm        = $this->in('Select the realm these users must belong to', array_keys($realm_options), $default_realm);
        $realm_id     = $realm_options[$realm];

        foreach($contents as $i){
            $items      = explode(',',$i);
            $username   = $items[0];
            $password   = $items[1];
            $this->out("<info>Add===== $username / $password</info>");
            $this->out("<info>Owner=== $owner ($use_token)</info>");
            $this->out("<info>Profile= $profile ($profile_id)</info>");
            $this->out("<info>Realm=== $realm ($realm_id)</info>");

            //===== From Wiki==========
            $active         = 'active';
            $cap_data       = 'soft';
            $language       = '4_4';
            $parent_id      = 0;        
            $url            = 'http://127.0.0.1/cake2/rd_cake/permanent_users/add.json';
             
            // The data to send to the API
            $postData = array(
                'active'        => $active,
                'cap_data'      => $cap_data,
                'language'      => $language,
                'parent_id'     => $parent_id,
                'profile_id'    => $profile_id,
                'realm_id'      => $realm_id,
                'token'         => $use_token,
                'username'      => $username,
                'password'      => $password
            );
             
            // Setup cURL
            $ch = curl_init($url);
            curl_setopt_array($ch, array(
             
                CURLOPT_POST            => TRUE,
                CURLOPT_RETURNTRANSFER  => TRUE,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
                CURLOPT_POSTFIELDS => json_encode($postData)
            ));
             
            // Send the request
            $response = curl_exec($ch);
             
            // Check for errors
            if($response === FALSE){
                die(curl_error($ch));
            }
             
            // Decode the response
            $responseData = json_decode($response, TRUE);
            print_r($responseData);
            ///=====END From Wiki===

        }
    }
}

?>
