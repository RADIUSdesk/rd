<?php
// app/Model/PhraseValue.php
class PhraseValue extends AppModel {

    public $name    = 'PhraseValue';
    public $actsAs  = array('Containable');

    public $belongsTo = array(
        'PhraseKey' => array(
            'className'    => 'PhraseKey',
            'foreignKey'   => 'phrase_key_id'
        ),
        'Country' => array(
            'className'    => 'Country',
            'foreignKey'   => 'country_id'
        ),
        'Language' => array(
            'className'    => 'Language',
            'foreignKey'   => 'language_id'
        )
    );

    public $validate = array(
        'name' => array(
            'required' => array(
                'rule' => array('notBlank'),
                'message' => 'Value is required'
            )
        )
    );


    public function getLanguageStrings($language = '1'){

        $country_language   = explode( '_', $language );
        $country            = $country_language[0];
        $language           = $country_language[1];
        $q                  = $this->PhraseKey->find('all');
        $language_strings   = array();

        foreach($q as $i){
            foreach($i['PhraseValue'] as $j){
                if(($j['country_id'] == $country)&&($j['language_id'] == $language)){
                    $language_strings[$i['PhraseKey']['name']] = $j['name'];
                    break; //Once we have our phrase we don't care for the rest
                }
            }
        }
        return $language_strings;
    }

    public function getLanguageForString($language, $string){

        $country_language   = explode( '_', $language );
        $country            = $country_language[0];
        $language           = $country_language[1];

        $q                  =   $this->find('first',
                                    array('conditions' => 
                                        array('PhraseValue.country_id'    => $country,
                                        'PhraseValue.language_id'         => $language,
                                        'PhraseKey.name'                  => $string  
                                )));
        $string = $q['PhraseValue']['name'];
        return $string;
    }

    function list_languages(){
        $q = $this->find('all',
                array('fields' => 
                    array('DISTINCT PhraseValue.language_id, PhraseValue.country_id','Country.icon_file','Language.rtl')
                )
            );
        $languages = array();
        foreach($q as $i){
            $l_id       = $i['PhraseValue']['country_id'].'_'.$i['PhraseValue']['language_id'];
            $country    = $this->getLanguageForString($l_id,'spclCountry');
            $language   = $this->getLanguageForString($l_id,'spclLanguage');
            $icon_file  = $i['Country']['icon_file'];
            array_push($languages,
                array(  'id'        => $l_id,
                        'country'   => $country, 
                        'language'  => $language,
                        'text'      => "$country -> $language",
                        'rtl'       => $i['Language']['rtl'],
                        'icon_file' => $icon_file)
            );   
        }
        return $languages;
    }

    function copy_phrases($data){
        $s_language = $data['source_id'];
        $d_language = $data['destination_id'];

        $s_country_language   = explode( '_', $s_language );
        $s_country            = $s_country_language[0];
        $s_language           = $s_country_language[1];

        $d_country_language   = explode( '_', $d_language );
        $d_country            = $d_country_language[0];
        $d_language           = $d_country_language[1];

        $this->Country->contain();
        $q_s = $this->find('all',array('conditions' =>
            array('PhraseValue.country_id' => $s_country,'PhraseValue.language_id' => $s_language)
        ));
        foreach($q_s as $i){
            //Find the item for the destination language and replace the name field
            $phrase_key = $i['PhraseValue']['phrase_key_id'];
            $phrase_val = $i['PhraseValue']['name'];
            $q_d = $this->find('first',array('conditions' =>
                array(
                    'PhraseValue.country_id'    => $d_country,
                    'PhraseValue.language_id'   => $d_language,
                    'PhraseValue.phrase_key_id' => $phrase_key
                )
            ));
            $q_d['PhraseValue']['name'] = $phrase_val;
            $this->id = $q_d['PhraseValue']['id'];
            $this->save($q_d);
        }
    }

}
?>
