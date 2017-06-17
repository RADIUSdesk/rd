<?php
namespace App\Shell\Task;

use Cake\Console\Shell;

class MigrateTask extends Shell{

    private $aro_ap_id = 3116;
    
    private $acos_entries_rename  = [
        'Ssids' => [
            ['old' => 'index_ap', 'new' => 'indexAp']
        ],
        'AccessProviders' => [
            ['old' => 'change_password',    'new' => 'changePassword'],
            ['old' => 'export_csv',         'new' => 'exportCsv'],
            ['old' => 'note_index',         'new' => 'noteIndex'],
            ['old' => 'note_add',           'new' => 'noteAdd'],
            ['old' => 'note_del',           'new' => 'noteDel'],
            ['old' => 'enable_disable',     'new' => 'enableDisable']
        ],
        'Tags' => [
            ['old' => 'index_for_filter',   'new' => 'indexForFilter'],
            ['old' => 'export_csv',         'new' => 'exportCsv'],
            ['old' => 'note_index',         'new' => 'noteIndex'],
            ['old' => 'note_add',           'new' => 'noteAdd'],
            ['old' => 'note_del',           'new' => 'noteDel']
        ],
        'Realms' => [
            ['old' => 'index_for_filter',   'new' => 'indexForFilter'],
            ['old' => 'export_csv',         'new' => 'exportCsv'],
            ['old' => 'note_index',         'new' => 'noteIndex'],
            ['old' => 'note_add',           'new' => 'noteAdd'],
            ['old' => 'note_del',           'new' => 'noteDel'],
            ['old' => 'index_ap',           'new' => 'indexAp'],
            ['old' => 'update_na_realm',    'new' => 'updateNaRealm'],
            //Not listed here is uploadLogo
        ],
        'DynamicDetails' => [
            ['old' => 'upload_logo',        'new' => 'uploadLogo'],
            ['old' => 'index_photo ',       'new' => 'indexPhoto '],
            ['old' => 'upload_photo ',      'new' => 'uploadPhoto '],
            ['old' => 'delete_photo ',      'new' => 'deletePhoto'],
            ['old' => 'edit_photo',         'new' => 'editPhoto'],       
            ['old' => 'index_page',         'new' => 'indexPage'],
            ['old' => 'add_page',           'new' => 'addPage'],
            ['old' => 'edit_page',          'new' => 'editPage'],
            ['old' => 'delete_page',        'new' => 'deletePage'],         
            ['old' => 'index_pair',         'new' => 'indexPair'],
            ['old' => 'add_pair',           'new' => 'addPair'],
            ['old' => 'edit_pair',          'new' => 'editPair'],
            ['old' => 'delete_pair',        'new' => 'deletePair'],
            ['old' => 'note_index',         'new' => 'noteIndex'],
            ['old' => 'note_add',           'new' => 'noteAdd'],
            ['old' => 'note_del',           'new' => 'noteDel'],   
            ['old' => 'edit_settings',      'new' => 'editSettings'],
            ['old' => 'view_social_login',  'new' => 'viewSocialLogin'],
            ['old' => 'edit_social_login',  'new' => 'editSocialLogin'],    
        ],
        'Profiles' => [
            ['old' => 'note_index',         'new' => 'noteIndex'],
            ['old' => 'note_add',           'new' => 'noteAdd'],
            ['old' => 'note_del',           'new' => 'noteDel'],
            ['old' => 'index_ap',           'new' => 'indexAp'],
            ['old' => 'manage_components',  'new' => 'manageComponents'],
        ],
        'PermanentUsers' => [
            ['old' => 'view_basic_info',    'new' => 'viewBasicInfo'],
            ['old' => 'edit_basic_info',    'new' => 'editBasicInfo'],
            ['old' => 'view_personal_info', 'new' => 'viewPersonalInfo'],
            ['old' => 'edit_personal_info', 'new' => 'editPersonalInfo'],
            ['old' => 'private_attr_index', 'new' => 'privateAttrIndex'],
            ['old' => 'private_attr_add',   'new' => 'privateAttrAdd'],
            ['old' => 'private_attr_edit',  'new' => 'privateAttrEdit'],
            ['old' => 'private_attr_delete','new' => 'privateAttrDelete'],
            ['old' => 'change_password',    'new' => 'changePassword'],
            ['old' => 'enable_disable',     'new' => 'enableDisable'],
            ['old' => 'export_csv',         'new' => 'exportCsv'],
            ['old' => 'restrict_list_of_devices','new' => 'restrictListOfDevices'],
            ['old' => 'note_index',         'new' => 'noteIndex'],
            ['old' => 'note_add',           'new' => 'noteAdd'],
            ['old' => 'note_del',           'new' => 'noteDel'],
            ['old' => 'auto_mac_on_off',    'new' => 'autoMacOnOff'],
            ['old' => 'view_password',      'new' => 'viewPassword']
        ],
        'Vouchers'      => [
            ['old' => 'view_basic_info',    'new' => 'viewBasicInfo'],
            ['old' => 'edit_basic_info',    'new' => 'editBasicInfo'],
            ['old' => 'private_attr_index', 'new' => 'privateAttrIndex'],
            ['old' => 'private_attr_add',   'new' => 'privateAttrAdd'],
            ['old' => 'private_attr_edit',  'new' => 'privateAttrEdit'],
            ['old' => 'private_attr_delete','new' => 'privateAttrDelete'],
            ['old' => 'change_password',    'new' => 'changePassword'],
            ['old' => 'export_csv',         'new' => 'exportCsv'],
            ['old' => 'export_pdf',         'new' => 'exportPdf'],
            ['old' => 'email_voucher_details', 'new' => 'emailVoucherDetails'],  
        ],
        'Devices'       => [
            ['old' => 'view_basic_info',    'new' => 'viewBasicInfo'],
            ['old' => 'edit_basic_info',    'new' => 'editBasicInfo'],
            ['old' => 'private_attr_index', 'new' => 'privateAttrIndex'],
            ['old' => 'private_attr_add',   'new' => 'privateAttrAdd'],
            ['old' => 'private_attr_edit',  'new' => 'privateAttrEdit'],
            ['old' => 'private_attr_delete','new' => 'privateAttrDelete'],
            ['old' => 'enable_disable',     'new' => 'enableDisable'],
            ['old' => 'export_csv',         'new' => 'exportCsv'],
            ['old' => 'note_index',         'new' => 'noteIndex'],
            ['old' => 'note_add',           'new' => 'noteAdd'],
            ['old' => 'note_del',           'new' => 'noteDel']   
        ],
        'AcosRights'    => [
            ['old' => 'index_ap',   'new' => 'indexAp'],
            ['old' => 'edit_ap',    'new' => 'editAp'],  
        ]     
    ];

    public function initialize(){
        parent::initialize();
        $this->loadModel('Acos');
        $this->loadModel('ArosAcos');
    }
    
    public function main(){
    
        $this->_rename_acos_entries();
        $this->_clean_up_acos();     
        $this->_addTopUps();
        $this->_addTopUpTransactions();
        $this->_addMisc();    
    }
    
    private function _addMisc(){
        $this->out("Adding Misc Items");   
        $q_ap   = $this->Acos->find()->where(['alias' => 'Access Providers'])->first();
        $ap_id  = $q_ap->id;
        $this->out("AccessProviders id is ".$ap_id);
        $q_ap_c = $this->Acos->find()->where(['alias' => 'Controllers','parent_id' => $ap_id])->first();
        $c_id   = $q_ap_c->id;
        $this->out("Controllers ID is ".$c_id);

        $q_a = $this->Acos->find()->where(['alias' => 'DynamicDetails','parent_id' => $c_id])->first();
        $dd_id = $q_a->id;

        $q_b = $this->Acos->find()->where(['alias' => 'shufflePhoto','parent_id' => $dd_id])->first();

        if($q_b){
            $this->out("shufflePhoto is already present");
        }else{
            $this->out("$i is NOT present");
            $output = shell_exec("bin/cake acl create aco $dd_id shufflePhoto");
            print($output);
        }
         
        $q_c        = $this->Acos->find()->where(['alias' => 'shufflePhoto','parent_id' => $dd_id ])->first();
        $sp_id      = $q_c->id;
        $aros_id    = $this->aro_ap_id;
        $output     = shell_exec("bin/cake acl grant $aros_id $sp_id");
        print($output);
    }
    
    private function _addTopUps(){
    
        $this->out("Adding Rights for TopUps");
        //Find the id of 'Access Providers'
        $q_ap   = $this->Acos->find()->where(['alias' => 'Access Providers'])->first();
        $ap_id  = $q_ap->id;
        $this->out("AccessProviders id is ".$ap_id);
        $q_ap_c = $this->Acos->find()->where(['alias' => 'Controllers','parent_id' => $ap_id])->first();
        $c_id   = $q_ap_c->id;
        $this->out("Controllers ID is ".$c_id);
        
        //Check if it exists perhaps already
        $q_a = $this->Acos->find()->where(['alias' => 'TopUps','parent_id' => $c_id])->first();
        if($q_a){
           $this->out("TopUps already added it has an ID of ".$q_a->id); 
        
        }else{
            $this->out("TopUps NOT added YET adding it");
            //$this->dispatchShell("acl create aco 31 TopUps");
            $output = shell_exec("bin/cake acl create aco $c_id TopUps");
            print($output);
        }
        
        //Now we can loop through the items and see if they are not created
        $tu_methods = [
            'exportCsv',
            'index',
            'add',
            'edit',
            'delete'
        ];
        
        //Get the topups id
        $this->out("Finding the ID of the top-up entry");
        $q_a = $this->Acos->find()->where(['alias' => 'TopUps','parent_id' => $c_id])->first();
        $top_up_id = $q_a->id;
        foreach($tu_methods as $i){
            $this->out("Checking and / or adding $i");
            $q_b = $this->Acos->find()->where(['alias' => $i,'parent_id' => $top_up_id])->first();
            if($q_b){
                $this->out("$i is already present");
            }else{
                $this->out("$i is NOT present");
                $output = shell_exec("bin/cake acl create aco $top_up_id $i");
                print($output);
            }
        }
        
        //Set the topup's righs
        foreach($tu_methods as $i){
            $this->out("Setting TopUp right for $i");
            $q_b        = $this->Acos->find()->where(['alias' => $i,'parent_id' => $top_up_id])->first();
            $m_id       = $q_b->id;
            $aros_id    = $this->aro_ap_id;
            $output     = shell_exec("bin/cake acl grant $aros_id $m_id");
            print($output);
        }    
    }
    
     private function _addTopUpTransactions(){
    
        $this->out("Adding Rights for TopUpTransactions");
        //Find the id of 'Access Providers'
        $q_ap   = $this->Acos->find()->where(['alias' => 'Access Providers'])->first();
        $ap_id  = $q_ap->id;
        $this->out("AccessProviders id is ".$ap_id);
        $q_ap_c = $this->Acos->find()->where(['alias' => 'Controllers','parent_id' => $ap_id])->first();
        $c_id   = $q_ap_c->id;
        $this->out("Controllers ID is ".$c_id);
        
        //Check if it exists perhaps already
        $q_a = $this->Acos->find()->where(['alias' => 'TopUpTransactions','parent_id' => $c_id])->first();
        if($q_a){
           $this->out("TopUpTransactions already added it has an ID of ".$q_a->id); 
        
        }else{
            $this->out("TopUpTransactionss NOT added YET adding it");
            //$this->dispatchShell("acl create aco 31 TopUps");
            $output = shell_exec("bin/cake acl create aco $c_id TopUpTransactions");
            print($output);
        }
        
        //Now we can loop through the items and see if they are not created
        $tu_methods = [        
            'index'
        ];
        
        //Get the topups id
        $this->out("Finding the ID of the top-up entry");
        $q_a = $this->Acos->find()->where(['alias' => 'TopUpTransactions','parent_id' => $c_id])->first();
        $top_up_id = $q_a->id;
        foreach($tu_methods as $i){
            $this->out("Checking and / or adding $i");
            $q_b = $this->Acos->find()->where(['alias' => $i,'parent_id' => $top_up_id])->first();
            if($q_b){
                $this->out("$i is already present");
            }else{
                $this->out("$i is NOT present");
                $output = shell_exec("bin/cake acl create aco $top_up_id $i");
                print($output);
            }
        }
        
        //Set the topup's righs
        foreach($tu_methods as $i){
            $this->out("Setting TopUpTransactions right for $i");
            $q_b        = $this->Acos->find()->where(['alias' => $i,'parent_id' => $top_up_id])->first();
            $m_id       = $q_b->id;
            $aros_id    = $this->aro_ap_id;
            $output     = shell_exec("bin/cake acl grant $aros_id $m_id");
            print($output);
        }    
    }
    
    private function _clean_up_acos(){
        //Somehow the table got littered with junk all having a parent_id of 35
        
        //Confirm id 35 is "Realms"
        $entity = $this->Acos->find()->where(['id' => 35])->first();
        if($entity){
            if($entity->alias == 'Realms'){
                $this->out("Found Junk entity called Realms on ID 35 go on and delete ID 35 related entries");
                $this->Acos->deleteAll(['parent_id' => 35]);
                $this->Acos->deleteAll(['id' => 35]);
            }
        }
        
        //We can also remove some ocos items
        //Vouchers has note_add and note_del double(255 and 256)
        
        $entity = $this->Acos->find()->where(['id' => 255])->first();
        if($entity){
            if($entity->alias == 'note_add'){
                $this->out("Removing double note_add entry");
                $this->Acos->delete($entity);
                $this->ArosAcos->deleteAll(['aco_id' => 255]);
            }
        }
        
        $entity = $this->Acos->find()->where(['id' => 256])->first();
        if($entity){
            if($entity->alias == 'note_del'){
                $this->out("Removing double note_del entry");
                $this->Acos->delete($entity);
                $this->ArosAcos->deleteAll(['aco_id' => 256]);
            }
        }   
    }
    
    private function _rename_acos_entries(){
        $this->hr();
        $this->out("Renaming ACOS entries");
        //Find the id of 'Access Providers'
        $q_ap   = $this->Acos->find()->where(['alias' => 'Access Providers'])->first();
        $ap_id  = $q_ap->id;
        $this->out("AccessProviders id is ".$ap_id);
        $q_ap_c = $this->Acos->find()->where(['alias' => 'Controllers','parent_id' => $ap_id])->first();
        $c_id   = $q_ap_c->id;
        $this->out("Controllers ID is ".$c_id);
             
        foreach(array_keys($this->acos_entries_rename) as $a){
        
            $this->hr();
            $this->out("Finding the ID of ".$a);
            $this->hr();
            //We put parent id  of 
            $q_a = $this->Acos->find()->where(['alias' => $a,'parent_id' => $c_id])->first();
            if($q_a){
                $parent_id = $q_a->id;
                $this->out($a." was found to have an id of ".$parent_id);
                foreach($this->acos_entries_rename[$a] as $b){
                    $old = $b['old'];
                    $new = $b['new'];
                    $q_b = $this->Acos->find()->where(['alias' => $old,'parent_id' => $parent_id])->first();
                    if($q_b){
                        $this->out("Updating $old on $a to $new");
                        $q_b->alias = $new;
                        $this->Acos->save($q_b);
                    }else{
                        $this->out("Could not find $old on $a assume it is already updated");
                    } 
                }
            }
        }   
        $this->hr();
    }
}
