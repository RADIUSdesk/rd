<?php
class CountriesController extends AppController {

    //--Read (the whole lot)
    public function index() {

        $this->{$this->modelClass}->contain();
        $q = $this->{$this->modelClass}->find('all');
        $items = array();
        foreach($q as $i){
            array_push($items,$i[$this->modelClass]);
        }
        $this->set(array(
            'items' => $items,
            'success' => true,
            '_serialize' => array('items','success')
        ));
    }

    //--Edit and Create--
    public function add($id = null){ //(Add and edit are one and detected how it has been called)
    //This is a deviation from the standard JSON serialize view since extjs requires a html type reply when files
    //are posted to the server.
        $this->layout = 'ext_file_upload';

        $path_parts     = pathinfo($_FILES['icon']['name']);
        $extension      = $_FILES['icon']['name'];
        $dest           = IMAGES."flags/".$this->data['iso_code'].'.'.$path_parts['extension'];
        $dest_www       = "/cake2/rd_cake/webroot/img/flags/".$this->data['iso_code'].'.'.$path_parts['extension'];

        //Now add....
        $data['name']       = $this->data['name'];
        $data['iso_code']   = $this->data['iso_code'];
        $data['icon_file']  = $dest_www;
        if($id == null){ //New
            $this->{$this->modelClass}->create();
        }else{          //Existing
            $this->{$this->modelClass}->id = $id;
        }
        if($this->{$this->modelClass}->save($data)){

            //Move the file to flags directory:
            move_uploaded_file ($_FILES['icon']['tmp_name'] , $dest);
            //End of file move
            $json_return['id']          = $this->{$this->modelClass}->id;
            $json_return['success']     = true;
        }else{
            $json_return['errors']      = $this->{$this->modelClass}->validationErrors;
            $json_return['message']     = array("message"   => __('Problem adding country'));
            $json_return['success']     = false;
        }
        $this->set('json_return',$json_return);
    }

    //--DELETE-- 
    public function delete() {
        if ($this->{$this->modelClass}->delete($this->data['id'],true)) {
            $success = true;
        } else {
            $success = false;
        }
        $this->set(array(
            'success' => $success,
            '_serialize' => array('success')
        ));
    }
}
?>
