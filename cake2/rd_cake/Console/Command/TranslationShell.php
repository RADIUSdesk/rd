<?php
class TranslationShell extends AppShell {

    public $uses    = array('Country','Language','PhraseValue');
    public $tasks   = array();

    public function main() {
        $this->out('<info>=========================================</info>');
        $this->out('<info>===Export and import of translations ====</info>');
        $this->out('<info>=========================================</info>');


        $e_i = $this->in('Do you want to export or import phrases?', array('export','import'), 'import');

        if($e_i == 'import'){

            $this->out('<warning>CSV file should be in the format [phrase_key_id]|[translated_phrase]|</warning>');
            $this->out('<warning>Note that we use the "|" character to seperate the three things</warning>');
            $file = $this->in('Specify the CSV file with translated phrases', null, '/tmp/translated.csv');
            if(file_exists($file)){
                $contents = file($file);
                $this->import_translation($contents);
            }else{
              $this->out("<warning>Could not open $file. Check if it exists!</warning>");  
            }
        }

        if($e_i == 'export'){
            $this->PhraseValue->contain('Language','Country');
            $q_r    = $this->PhraseValue->find('all',array('conditions' =>array('Language.name' => 'English','Country.name' => 'United Kingdom')));
            $file   = fopen('/tmp/phrases_export.csv', 'w');
            foreach ($q_r as $i){
                $line = $i['PhraseValue']['phrase_key_id']."|".$i['PhraseValue']['name'];
                fwrite($file, "$line\n");
            }
            fclose($file);
        }
    }


    private function import_translation($contents){

        //===Choose the Language====
        $this->Language->contain();
        $la  = $this->Language->find('all');
        $l_options = array();

        foreach($la as $l){
            $name   = $l['Language']['name'];
            $id     = $l['Language']['id'];
            $l_options["$name"] = $id;
        }
        $language   = $this->in('Select the Language of these phrases', array_keys($l_options), 'English');
        $l_id       = $l_options[$language];

        //===Choose the Country====
        $this->Country->contain();
        $co  = $this->Country->find('all');
        $c_options = array();
        $default_c = '';
        foreach($co as $c){
            $name   = $c['Country']['name'];
            $id     = $c['Country']['id'];
            $c_options["$name"] = $id;
            $default_c = $name;
        }
        $country = $this->in('Select the Country of these phrases', array_keys($c_options), $default_c);
        $c_id    = $c_options[$country];

        foreach($contents as $i){
            $items          = explode('|',$i);
            $phrase_key_id  = $items[0];
            $phrase         = rtrim($items[2]);
            print("Finding $phrase_key_id $l_id $c_id");
            //See if there is already an enry for this
            $this->PhraseValue->contain();
            $q_r = $this->PhraseValue->find('first', array('conditions' =>
                array(
                    'PhraseValue.phrase_key_id' => $phrase_key_id,
                    'PhraseValue.language_id'   => $l_id,
                    'PhraseValue.country_id'    => $c_id,
                )
            ));

            if($q_r){
                $id   = $q_r['PhraseValue']['id'];
                $data = array( 
                    'id'            => $id,
                    'language_id'   => $l_id,
                    'country_id'    => $c_id,
                    'name'          => $phrase,
                    'phrase_key_id' => $phrase_key_id
                );
                $this->PhraseValue->create();
                $this->PhraseValue->save($data);
                $this->out("<info>Replace========= $phrase</info>");
                $this->out("<info>Language======== $language ($l_id)</info>");
                $this->out("<info>Country========= $country  ($c_id)</info>");
                $this->out("<info>Phrase_key_id=== $phrase_key_id </info>");

            }else{
                 $data = array(
                    'language_id'   => $l_id,
                    'country_id'    => $c_id,
                    'name'          => $phrase,
                    'phrase_key_id' => $phrase_key_id
                );

                $this->PhraseValue->save($data);
                $this->out("<info>====Add=========</info>");
                $this->out("<info>Language======== $language ($l_id)</info>");
                $this->out("<info>Country========= $country  ($c_id)</info>");
                $this->out("<info>Phrase_key_id=== $phrase_key_id </info>");
            }
            sleep(1);
        }
    }
}

?>
