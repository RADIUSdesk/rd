<?php
// app/Controller/PhraseValuesController.php
class PhraseValuesController extends AppController {

    //Replace later with config values
    var $engCountry_iso  = 'GB';
    var $engLanguage_iso = 'en';


    //Used to 'prime' the application with all the localized strings
    function get_language_strings(){

        if((isset($this->request->query['language']))&&($this->request->query['language'] != '')){
            $selLanguage = $this->request->query['language'];
        }else{
            $selLanguage = Configure::read('language.default'); //Get the default language from the config file
        };

        $languages  = $this->PhraseValue->list_languages();                 //Give a list of available languages
        $phrases    = $this->PhraseValue->getLanguageStrings($selLanguage); //We get all the phrases for the selected language
        //--- Language related --------

        $this->set(array(
            'data'          => array(
                                'phrases'       => $phrases, 
                                'languages'     => $languages, 
                                'selLanguage'   => $selLanguage
                            ),
            'success'       => true,
            '_serialize' => array('data','success')
        ));

    }

    public function l_languages(){

        //We must see if we were called with a certain language in mind / if not give default
        if((isset($this->request->query['language']))&&($this->request->query['language'] != '')){
            $selLanguage = $this->request->query['language'];
        }else{
            $selLanguage = '1_1';
        };
        $languages  = $this->PhraseValue->list_languages();
        $this->set(array(
            'items'     => $languages,
            'selLanguage'   => $selLanguage,
            'success'       => true,
            '_serialize' => array('items','selLanguage','success')
        ));
    }

    function add_language(){

        //See if we do not already have this language present
        $l_name = $this->request->data['name'];
        $l_iso  = $this->request->data['iso_code'];
        $q_r = $this->PhraseValue->Language->find('first',array('conditions' => array('Language.name' => $l_name, 'Language.iso_code' => $l_iso)));

        $new_lang_id = false;
        if($q_r){
           $new_lang_id = $q_r['Language']['id']; 
        }else{
            if ($this->PhraseValue->Language->save($this->request->data)) {
                $new_lang_id = $this->PhraseValue->Language->id;
            }
        }

        if ($new_lang_id != false) {

            //Now we need to add phrases for this
            $country_id  = $this->data['country_id'];

            //Get the country's name
            $this->PhraseValue->Country->contain();
            $c      = $this->PhraseValue->Country->findById($country_id);
            $c_name = $c['Country']['name'];
            $c_iso  = $c['Country']['iso_code'];

            //Get tha language's name
            $this->PhraseValue->Language->contain();
            $l      = $this->PhraseValue->Language->findById($new_lang_id);
            $l_name = $l['Language']['name'];
            $l_iso  = $l['Language']['iso_code'];

            $this->PhraseValue->PhraseKey->contain();
            $q = $this->PhraseValue->PhraseKey->find('all');
            foreach($q as $i){
                $key_id     = $i['PhraseKey']['id'];
                $key_name   = $i['PhraseKey']['name'];
                $phrase     = '(modify me)';
                if($key_name == 'spclCountry'){
                    $phrase = $c_name;
                }
                if($key_name == 'spclLanguage'){
                    $phrase = $l_name;
                }
                $this->PhraseValue->create();
                $this->PhraseValue->save(array(
                    'name'              => $phrase,
                    'phrase_key_id'     => $key_id,
                    'country_id'        => $country_id,
                    'language_id'       => $new_lang_id
                ));
            }

            //Check if the yfi_cake/Locale/$l_iso _ $c_iso/LC_MESSAGES/default.po file exists; if not create and copy
            $locale     = "$l_iso".'_'."$c_iso";
            $file       = APP."Locale/$locale/LC_MESSAGES/default.po";
            $source     = APP."Locale/en_GB/LC_MESSAGES/default.po";
            if(!file_exists($file)){
                $dir    = APP."Locale/$locale/LC_MESSAGES";
                if(!is_dir($dir)){
                    mkdir($dir, 0755, true);
                    copy($source,$file);
                }
            }

            $this->set(array(
                'success' => true,
                '_serialize' => array('success')
            ));
        } else {
            $message = 'Error';
            $this->set(array(
                'errors' => $this->PhraseValue->Language->validationErrors,
                'success' => false,
                '_serialize' => array('errors','success')
            ));
        }
    }

    function add_key(){

        if ($this->PhraseValue->PhraseKey->save($this->request->data)) {
            //Add this key to each distinct Country / Language combination in the PhraseValues table
            $new_key_id = $this->PhraseValue->PhraseKey->id;
            $q = $this->PhraseValue->find('all', 
                array('fields' => array('DISTINCT PhraseValue.language_id, PhraseValue.country_id')));
            foreach($q as $i){
                $this->PhraseValue->create();
                $this->PhraseValue->save(array(
                    'name'              => "(new addition)",
                    'phrase_key_id'     => $new_key_id,
                    'country_id'        => $i['PhraseValue']['country_id'],
                    'language_id'       => $i['PhraseValue']['language_id']
                ));
            }
            //Reply
            $this->set(array(
                'id'        => $new_key_id,
                'success'   => true,
                '_serialize' => array('success')
            ));
        } else {
            $message = 'Error';
            $this->set(array(
                'errors' => $this->PhraseValue->PhraseKey->validationErrors,
                'success' => false,
                'message'   => array('message' => __('Could not create key')),
                '_serialize' => array('errors','success','message')
            ));
        }
    }

    function list_phrases_for(){

        if((array_key_exists('language',$this->request->query))&&($this->request->query['language'] != '')){
            $language = $this->request->query['language'];
        }else{
            //Simply return empty since nothing is selected
            $this->set(array(
                'items'         => array(),
                'success'       => true,
            '_serialize' => array('items','success')
            ));
            return; 
        }

        $eng_flag = false;

        $this->PhraseValue->Country->contain();
        $eng_country    = $this->PhraseValue->Country->findByIsoCode($this->engCountry_iso,'Country.id');
        $eng_country    = $eng_country['Country']['id'];
        
        $this->PhraseValue->Language->contain();
        $eng_language   = $this->PhraseValue->Language->findByIsoCode($this->engLanguage_iso,'Language.id');
        $eng_language   = $eng_language['Language']['id'];

        
        $country_language   = explode( '_', $language );
        $country            = $country_language[0];
        $language           = $country_language[1];

        //Check if english default is selected
        if(($eng_language == $language)&&($eng_country == $country)){
            $eng_flag = true;
        }
        //Depending if English default is selected or another language we will behave differently
        if($eng_flag == true){
            $q = $this->PhraseValue->PhraseKey->find('all', array(
                'contain' => array(
                    'PhraseValue' => array(
                        'conditions' => array(
                            'PhraseValue.country_id'  => "$eng_country",
                            'PhraseValue.language_id' => "$eng_language",
                        )
                    )
            )));
        }else{
            $q = $this->PhraseValue->PhraseKey->find('all', array(
                'contain' => array(
                    'PhraseValue' => array(
                        'conditions' => array(
                            'OR' =>array(
                                array(
                                    'AND' => array(
                                        'PhraseValue.country_id'  => "$eng_country",
                                        'PhraseValue.language_id' => "$eng_language"
                                    )
                                ),
                                array(
                                    'AND' => array(
                                        'PhraseValue.country_id'  => "$country",
                                        'PhraseValue.language_id' => "$language"
                                    )
                                )
                            )
                            
                        )
                    )
            )));
        }

        $return_items = array();
        //Loop through them and make sure there are PhraseValue's for each one for this language
        $phrase_flag = false; //Test to see if there are a phrase for this key in this language
        foreach($q as $item){
            $key_name       = $item['PhraseKey']['name'];
            $key_comment    = $item['PhraseKey']['comment'];
            $key_id         = $item['PhraseKey']['id'];
            $phrase_id      = false;
            $eng_phrase     = '';
            $trans_phrase   = '';
            foreach($item['PhraseValue'] as $pv){
                $l_id   = $pv['language_id'];
                $c_id   = $pv['country_id'];
                if($eng_flag == true){
                    $phrase_id      = $pv['id'];
                    $eng_phrase     = $pv['name'];
                    $trans_phrase   = $pv['name'];
                }else{
                    if(($l_id == $eng_language)&&($c_id == $eng_country)){
                        $eng_phrase = $pv['name'];
                    }
                    if(($l_id == $language)&&($c_id == $country)){
                        $trans_phrase   = $pv['name'];
                        $phrase_id      = $pv['id'];
                    }
                }
            }

            //FIXME
            if($phrase_id == false){
                //Add a blank phrase for this key for this language
                print("Blank value");
            }else{
                //push to array and clear the blank flag
                array_push($return_items, 
                    array(  'id'        => $phrase_id,  'key'       => $key_name,
                            'comment'   => $key_comment, 'english'  => $eng_phrase,
                            'translated'=> $trans_phrase, 'key_id'  => $key_id
                     ));
                $phrase_id = false;  
            }
        }

        $this->set(array(
            'items'         => $return_items,
            'success'       => true,
            '_serialize' => array('items','success')
        )); 
    }

    function update_phrase($id){

        $success = true;
        $data = array('id' => $this->data['id'], 'name' => $this->data['translated']);
        if($this->PhraseValue->save($data) == false){
            $success = false;
        }
        $this->set(array(
            'success'       => $success,
            '_serialize' => array('success')
        ));
    }

    function copy_phrases(){
        $this->PhraseValue->copy_phrases($this->data);
        $this->set(array(
            'data'          => $this->data,
            'success'       => true,
            '_serialize' => array('data','success')
        ));
    }

    //TODO get a easy way for definign the default language
    function list_languages(){
        //We must see if we were called with a certain language in mind / if not give default
        if((isset($this->request->query['language']))&&($this->request->query['language'] != '')){
            $selLanguage = $this->request->query['language'];
        }else{
            $selLanguage = Configure::read('language.default'); //Get the default language from the config file
        };

        $languages  = $this->PhraseValue->list_languages();
        $phrases    = $this->PhraseValue->getLanguageStrings($selLanguage);

        $this->set(array(
            'phrases'       => $phrases,
            'languages'     => $languages,
            'selLanguage'   => $selLanguage,
            'success'       => true,
            '_serialize' => array('phrases','languages','selLanguage','success')
        ));
    }

    //This will give the id of a phrase_value. We need to get the phrase key id for that value and delete all the entries of that thing
    public function delete_keys($id){

        $success = false;
        $q = $this->PhraseValue->findById($id);
        if(isset($q['PhraseValue']['phrase_key_id'])){
            $key_id = $q['PhraseValue']['phrase_key_id'];
            if ($this->PhraseValue->PhraseKey->delete($key_id,true)) {
                $success = true;
            } else {
                $success = false;
            }
        }

        $this->set(array(
            'success' => $success,
            '_serialize' => array('success')
        ));
    }
}

?>
