<?php
App::uses('AppController', 'Controller');

class PhpPhrasesController extends AppController {

    public $name       = 'PhpPhrases';
    public $components = array('Aa');
    
    //-------- BASIC CRUD -------------------------------
    public function index(){

        if(!$this->Aa->admin_check($this)){   //Only for admin users!
            return;
        }

        $file_contents  = $this->_get_po_file_contents(true);
        if($file_contents != ''){
            $raw_items  = $this->_get_items($file_contents);
            $items      = array();
            if($raw_items != ''){
                foreach($raw_items as $item_num => $i){
                    $comment_string = '';
                    foreach($i['comments'] as $num => $c){
                    if($num > 0){
                            $comment_string = $comment_string."<br>\n$c";
                        }else{
                            $comment_string = $c;
                        }
                    }
                    array_push($items,array('id' => ($item_num+1),'msgid' => $i['msgid'],'msgstr' => $i['msgstr'], 'comment' => $comment_string));
                }

                $this->set(array(
                    'items'         => $items,
                    'total'         => count($items),
                    'success'       => true,
                    '_serialize'    => array('items','success','total')
                ));       
            }
        }
    }

    public function add(){

        if(!$this->Aa->admin_check($this)){   //Only for admin users!
            return;
        }

        $msgid  = $this->request->data['msgid'];
        $msgstr = $this->request->data['msgstr'];
        $comment= $this->request->data['comment'];
        $lines  = $this->_get_po_file_contents(true);
        $this->_add_msgid($lines,$msgid,$msgstr,$comment);
        $this->set(array(
            'items'         => array(),
            'success'       => true,
            '_serialize'    => array('items','success')
        ));
    }

     public function comment(){

        if(!$this->Aa->admin_check($this)){   //Only for admin users!
            return;
        }

        $remove_existing = false;
        if(isset($this->request->data['remove_existing'])){
            $remove_existing = true;  
        }
        $msgid  = $this->request->data['msgid'];
        $comment= $this->request->data['comment'];
        $lines  = $this->_get_po_file_contents(true);
        $this->_add_comment($lines,$msgid,$comment,$remove_existing);

        $this->set(array(
            'items'         => array(),
            'success'       => true,
            '_serialize'    => array('items','success')
        ));
    }

    public function update_msgid(){

        if(!$this->Aa->admin_check($this)){   //Only for admin users!
            return;
        }
        $msgid      = $this->request->data['msgid'];
        $old_msgid  = $this->request->data['old_msgid'];
        $lines      = $this->_get_po_file_contents();
   
        $look_for = "msgid \"$old_msgid\"";
        //Find the msgid with the following value:
        foreach($lines as $line_num => $line){
            $line = rtrim($line);
            if($line == $look_for){ 
                break;
            }
        }

        if($line_num != ''){
            $lines[$line_num] = "msgid \"$msgid\"\n";
        }

        file_put_contents($this->_get_file_name(),$lines);

        $this->set(array(
            'items' => array(),
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }


    public function edit(){

        if(!$this->Aa->admin_check($this)){   //Only for admin users!
            return;
        }
        $msgid  = $this->request->data['msgid'];
        $msgstr = $this->request->data['msgstr'];
        $lines  = $this->_get_po_file_contents(true);
   
        $look_for = 'msgid "'.$msgid.'"';

        //Find the msgid with the following value:
        foreach($lines as $line_num => $line){
            $line = rtrim($line);
            if($line == $look_for){ //Use this instead of preg_match since "funny characters cause trouble e.g. ( )
                break;
            }
        }

        //Found it now we change it
        $try_max = 5;
        $try_now = 1;
        if($line_num != ''){
            //Try and find the first line starting with "msgstr "
            while($try_now < $try_max){
                $next_line  = $line_num + $try_now;
                $line       = $lines[$next_line];
                $look_for   = "msgstr \"";
                if(preg_match("/^$look_for/", $line)){
                    $lines[$next_line] = "msgstr \"$msgstr\"\n";
                    break;
                }else{
                    $try_now= $try_now+1;
                }
            }           
        }

        file_put_contents($this->_get_file_name(),$lines);
        $this->set(array(
            'items' => array(),
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }


    public function view(){

    }

    
    public function delete(){

        if(!$this->Aa->admin_check($this)){   //Only for admin users!
            return;
        } 

        foreach(array_keys($this->data) as $i){
            if(preg_match("/item_/",$i)){   //Only items to delete will start with 'item_'
               $this->_delete_msgid($this->data[$i]);
            }
        }
        $this->set(array(
            'items' => array(),
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }


    public function copy(){

        if(!$this->Aa->admin_check($this)){   //Only for admin users!
            return;
        } 

        $source         = $this->data['source'];
        $destination    = $this->data['destination'];

        $source_file    = $this->_get_file_name($source);
        $dest_file      = $this->_get_file_name($destination);

        $maintain_existing = false;
        if(isset($this->request->data['maintain_existing'])){
            $maintain_existing = true; 
        }

        if($maintain_existing){
            $msgcat = Configure::read('commands.msgcat');
            exec("$msgcat $source_file $dest_file  -o $dest_file");
            $this->_clear_meta_data($destination); //msgcat adds som junk in the meta data section - if it is there clear it
        }else{
            copy($source_file,$dest_file);
        }

        $this->set(array(
            'items' => array(),
            'success' => true,
            '_serialize' => array('items','success')
        ));

    }


    public function get_metadata(){

        if(!$this->Aa->admin_check($this)){   //Only for admin users!
            return;
        } 
        $this->request->query['language'] = $this->request->query['id'];

        $lines      = $this->_get_po_file_contents();
        $meta_data  = $this->_get_meta_data($lines);

        $this->set(array(
            'items' =>$meta_data,
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }


     public function save_meta(){

        if(!$this->Aa->admin_check($this)){   //Only for admin users!
            return;
        } 

        foreach(array_keys($this->data) as $key){
            $lines      = $this->_get_po_file_contents();
            //print_r("$key ".$this->data[$key]."");
            $this->_set_meta_data($lines,$key,$this->data[$key]);
        }
   
        $this->set(array(
            'items' =>array(),
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }


    //--------- END BASIC CRUD ---------------------------


    private function _get_items($lines){
     
        $items = array();
        $count = 0;
        //The fist part of the file is the meta data our interest only starts after the first #: characters
        $record_flag = false;
        foreach($lines as $line){
            if(preg_match("/^#: /", $line)){
                $record_flag = true;
                //If it is the first one create a new array
                if(!array_key_exists($count,$items)){
                    $items[$count] = array();
                }
            }

            if($record_flag == true){
                //Try to determine what the item is
                
                if(preg_match("/^#: /", $line)) { //Comment
                    $line = preg_replace('/^#: /', "", $line);
                    $line = chop($line);
                    if(array_key_exists('comments',$items[$count])){
                        array_push($items[$count]['comments'],$line);
                    }else{
                        $items[$count]['comments'] = array();
                        array_push($items[$count]['comments'],$line);
                    }
                }

                if(preg_match("/^msgid /", $line)) { //Key
                    $line = preg_replace('/^msgid /', "", $line);
                    $line = preg_replace('/"/', "", $line);
                    $line = chop($line);
                    $items[$count]['msgid'] = $line;
                }

                if(preg_match("/^msgstr /", $line)) { //Key
                    $line = preg_replace('/^msgstr /', "", $line);
                    $line = preg_replace('/"/', "", $line);
                    $line = chop($line);
                    $items[$count]['msgstr'] = $line;
                }
            }

            if((preg_match("/^$/", $line))&&($record_flag == true)){
                $record_flag = false;
                $count++;
               // print_r($count);
            }
        }
        return $items;
    }

    private function _get_file_name($language = false){

        if($language == false){
            if((isset($this->request->query['language']))&&($this->request->query['language'] != '')){
                $language = $this->request->query['language'];
            }
        }

        if($language != false){
            $country_language   = explode( '_', $language );

            $country            = $country_language[0];
            $language           = $country_language[1];
            $this->Language     = ClassRegistry::init('Language');
            $this->Country      = ClassRegistry::init('Country');

            $this->Country->contain();
            $qr         = $this->Country->findById($country);
            $c_iso      = $qr['Country']['iso_code'];

            $this->Language->contain();
            $qr         = $this->Language->findById($language);
            $l_iso      = $qr['Language']['iso_code'];

            $locale = "$l_iso".'_'."$c_iso";
            $file = APP."Locale/$locale/LC_MESSAGES/default.po";

        }
        return $file;
    }

    private function _get_po_file_contents($soft_fail = false){ //Softfail will not return an error

         if((isset($this->request->query['language']))&&($this->request->query['language'] != '')){

            $language = $this->request->query['language'];
            $country_language   = explode( '_', $language );

            $country            = $country_language[0];
            $language           = $country_language[1];
            $this->Language     = ClassRegistry::init('Language');
            $this->Country      = ClassRegistry::init('Country');

            $this->Country->contain();
            $qr         = $this->Country->findById($country);
            $c_iso      = $qr['Country']['iso_code'];

            $this->Language->contain();
            $qr         = $this->Language->findById($language);
            $l_iso      = $qr['Language']['iso_code'];

            $locale = "$l_iso".'_'."$c_iso";
            $file = APP."Locale/$locale/LC_MESSAGES/default.po";

            //We need to try and determine if there are a folder and .po file for this locale
            if(!file_exists($file)){
                $this->set(array(
                    'items' => array(),
                    'success'   => false,
                    'message'   => array('message' => __("The file default.po is missing for")." ".$locale),
                    '_serialize' => array('items','success','message')
                ));
                return;
            }else{
                return file($file);
            }
        }else{

            if($soft_fail){
                $this->set(array(
                    'items' => array(),
                    'success'   => true,
                    '_serialize' => array('items','success')
                ));
            }else{

                $this->set(array(
                    'items' => array(),
                    'success'   => false,
                     'message'   => array('message' => __("No language specified")),
                    '_serialize' => array('items','success','message')
                ));
            }
            return;
        }
    }

    private function _delete_msgid($msgid){

        //Get the file contents
        $lines      = $this->_get_po_file_contents();
        $filename   = $this->_get_file_name();

        $look_for = "msgid \"$msgid\"";
        //Find the msgid with the following value:
        foreach($lines as $line_num => $line){
            $line = rtrim($line);
            if($line == $look_for){ 
                break;
            }
        }

        if($line_num != ''){

            //Unset upwards until we get a blank line
            while($line_num > 0){
                $line_num = $line_num -1;
                $line = $lines[$line_num];
                if(preg_match("/^$/",$line)){//Blank line
                    unset($lines[$line_num]); //Remove the top blank line
                    break;
                }elseif(preg_match("/^#: /",$line)){//Comment
                    unset($lines[$line_num]);
                }
            }
            $lines = array_values($lines);

            //Unset downwards until we get the msgstr
            while($line_num < count($lines)){
                $line = $lines[$line_num];
               // print($line);
                unset($lines[$line_num]);
                $line_num = $line_num +1; 
                if(preg_match("/^msgstr \"/",$line)){//msgstr (assume one liner)   
                    break;   
                }
            }
            $lines = array_values($lines);
        }
        file_put_contents($filename,$lines);    //Commit the change
    }

    private function _add_msgid($lines, $msgid, $msgstr="", $comment="" ){
        if($comment == ""){
            $comment = 'Manual add';
        }
        $comment = "#: $comment";
        array_push($lines,"$comment\n");
        array_push($lines,"msgid \"$msgid\"\n");
        array_push($lines,"msgstr \"$msgstr\"\n\n");

        $filename = $this->_get_file_name();
        file_put_contents($filename,$lines);    //Commit the change
    }

    private function _add_comment($lines,$msgid,$comment,$remove_existing = false){

        $look_for = "msgid \"$msgid\"";

        //Find the msgid with the following value:
        foreach($lines as $line_num => $line){
            $line = rtrim($line);
            if($line == $look_for){ 
                break;
            }
        }

        //Remove all existing comments:
        if(($remove_existing == true)&&($line_num != '')){
            //Unset upwards until we get a blank line
            while($line_num > 0){
                $line_num = $line_num -1;
                $line = $lines[$line_num];
                if(preg_match("/^$/",$line)){//Blank line
                    unset($lines[$line_num]); //Remove the top blank line
                    break;
                }elseif(preg_match("/^#: /",$line)){//Comment
                    unset($lines[$line_num]);
                }
            }
            $lines = array_values($lines);
            $comment = "\n#: $comment\n";
            
        }else{
            $comment = "#: $comment\n";
        }

        if($line_num != ''){
            array_splice($lines, $line_num, 0, $comment);
        }

        $filename = $this->_get_file_name();
        file_put_contents($filename,$lines);    //Commit the change
    }

    private function _get_meta_data($lines){
        $meta_data = array();      
        $record_flag = false;

        foreach ($lines as $line) {
            //Stop at the first file comment
            if(preg_match("/^#:/", $line)) {
                $record_flag = false;
                break;
            }  

            //while the recording flag is set
            if($record_flag == true){
                $line = preg_replace('/\\\n"$/', "", $line);
                $line = preg_replace('/"/', "", $line);
                $line = chop($line);
                if(strlen($line)> 10){
                    list($item, $value) = explode(": ", $line);
                    $meta_data["$item"] = $value;
                }
            }
            //After the first msgstr follows the meta data
            if(preg_match("/msgstr/", $line)) {
                $record_flag = true;
            }   
        }
        return $meta_data; 
    }

    private function _set_meta_data($lines,$item,$value){
        $record_flag = false;     
        foreach ($lines as $line_num => $line) {
            //Stop at the first file comment
            if(preg_match("/^#:/", $line)) {
                $record_flag = false;
                break;
            }  

            //while the recording flag is set
            if($record_flag == true){
                if(preg_match("/$item/", $line)) {
                   $lines[$line_num] = '"'.$item.": ".$value.'\n"'."\n";
                    break;
                } 
            }

            //After the first msgstr follows the meta data
            if(preg_match("/msgstr/", $line)) {
                $record_flag = true;
            }   
        }

        $filename = $this->_get_file_name();
        file_put_contents($filename,$lines);    //Commit the change
    }

    private function _clear_meta_data($dest_lang){

        //Get the file contents
        $this->request->query['language'] = $dest_lang;
        $lines      = $this->_get_po_file_contents();
        $record = false;
        foreach($lines as $line_num => $line){
            if(preg_match("/^\"#-#-#-#-# /", $line)){   //Trigger point
                $record = !($record);
            }
            if($record == true){
                unset($lines[$line_num]);
            }

            //For the last one
            if(preg_match("/^\"#-#-#-#-# /", $line)){   //Trigger point
                unset($lines[$line_num]);
            }
        }
        $lines = array_values($lines);
        $filename = $this->_get_file_name();
        file_put_contents($filename,$lines);    //Commit the change
    } 

}
