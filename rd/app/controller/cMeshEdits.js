Ext.define('Rd.controller.cMeshEdits', {
    extend: 'Ext.app.Controller',
    views:  [
        'meshes.pnlMeshEdit',
        'meshes.gridMeshEntries',   'meshes.winMeshAddEntry',   'meshes.cmbEncryptionOptions',
        'meshes.winMeshEditEntry',  'meshes.pnlMeshSettings',   'meshes.gridMeshExits',
        'meshes.winMeshAddExit',    'meshes.cmbMeshEntryPoints','meshes.winMeshEditExit',
        'meshes.pnlNodeCommonSettings', 'meshes.gridNodes',     'meshes.winMeshAddNode',
        'meshes.cmbHardwareOptions', 'meshes.cmbStaticEntries', 'meshes.cmbStaticExits',
        'meshes.winMeshEditNode',	'meshes.pnlMeshEditGMap',	'meshes.winMeshMapPreferences',
		'meshes.winMeshMapNodeAdd',	'meshes.cmbEthBridgeOptions',
		'components.cmbFiveGigChannels',
        'components.cmbTimezones',      
        'components.cmbCountries',
        'components.cmbRealm'
    ],
    stores      : [	
		'sMeshEntries', 'sMeshExits', 	'sMeshEntryPoints',	'sNodes','sRealms'
    ],
    models      : [ 
		'mMeshEntry',  	'mMeshExit', 	'mMeshEntryPoint',  'mNode', 'mRealm','mDynamicDetail', 'mPermanentUser'
    ],
    config      : {  
        urlAddEntry:        '/cake2/rd_cake/meshes/mesh_entry_add.json',
        urlViewEntry:       '/cake2/rd_cake/meshes/mesh_entry_view.json',
        urlEditEntry:       '/cake2/rd_cake/meshes/mesh_entry_edit.json',
        urlViewMeshSettings:'/cake2/rd_cake/meshes/mesh_settings_view.json',
        urlEditMeshSettings:'/cake2/rd_cake/meshes/mesh_settings_edit.json',
        urlExitAddDefaults :'/cake2/rd_cake/meshes/mesh_exit_add_defaults.json',
        urlAddExit:         '/cake2/rd_cake/meshes/mesh_exit_add.json',
        urlViewExit:        '/cake2/rd_cake/meshes/mesh_exit_view.json',
        urlEditExit:        '/cake2/rd_cake/meshes/mesh_exit_edit.json',
        urlViewNodeCommonSettings:'/cake2/rd_cake/meshes/node_common_settings_view.json',
        urlEditNodeCommonSettings:'/cake2/rd_cake/meshes/node_common_settings_edit.json',
        urlAddNode:         '/cake2/rd_cake/meshes/mesh_node_add.json',
        urlViewNode:        '/cake2/rd_cake/meshes/mesh_node_view.json',
        urlEditNode:        '/cake2/rd_cake/meshes/mesh_node_edit.json',
		urlMapPrefView		: '/cake2/rd_cake/meshes/map_pref_view.json',
		urlMapPrefEdit		: '/cake2/rd_cake/meshes/map_pref_edit.json',
		urlMapSave			: '/cake2/rd_cake/meshes/map_node_save.json',
		urlMapDelete		: '/cake2/rd_cake/meshes/map_node_delete.json',
		urlMeshNodes		: '/cake2/rd_cake/meshes/mesh_nodes_index.json',
		urlBlueMark 		: 'resources/images/map_markers/blue-dot.png',
        urlAdvancedSettingsForModel : '/cake2/rd_cake/meshes/advanced_settings_for_model.json'
    },
    refs: [
    	{  ref: 'editEntryWin', 	selector: 'winMeshEditEntry'},
        {  ref: 'editExitWin',  	selector: 'winMeshEditExit'},
        {  ref: 'tabMeshes',        selector: '#tabMeshes'     }   
    ],
    init: function() {
        var me = this;
        if (me.inited) {
            return;
        }
        me.inited = true;

        me.control({
            'gridMeshEntries' : {
                activate: me.gridActivate
            },
			'gridMeshEntries #reload': {
                click:  me.reloadEntry
            },
            'gridMeshEntries #add': {
                click:  me.addEntry
            },
            'gridMeshEntries #edit': {
                click:  me.editEntry
            },
            'winMeshAddEntry cmbEncryptionOptions': {
                change: me.cmbEncryptionChange
            },
            'winMeshAddEntry #chk_maxassoc': {
                change: me.chkMaxassocChange
            },
            'winMeshAddEntry cmbMacFilter': {
                change: me.cmbMacFilterChange
            },
            'winMeshAddEntry #save': {
                click: me.btnAddEntrySave
            },
            'gridMeshEntries #delete': {
                click: me.delEntry
            },
            'winMeshEditEntry': {
                beforeshow:      me.loadEntry
            },
             'winMeshEditEntry cmbEncryptionOptions': {
                change: me.cmbEncryptionChange
            },
            'winMeshEditEntry #chk_maxassoc': {
                change: me.chkMaxassocChange
            },
            'winMeshEditEntry cmbMacFilter': {
                change: me.cmbMacFilterChange
            },
            'winMeshEditEntry #save': {
                click: me.btnEditEntrySave
            },
            'pnlMeshEdit #tabMeshSettings' : {
                activate:      me.frmMeshSettingsLoad
            },
            'pnlMeshSettings  #con_mesh_point' : {
                change :    me.radioMeshPointChange    
            }, 
            'pnlMeshSettings  #encryption':{
                change  : me.chkMeshEncryptionChange
            },
            'pnlMeshSettings #save': {
                click:  me.btnMeshSettingsSave
            },
            'gridMeshExits' : {
                activate: me.gridActivate
            },
            'gridMeshExits #reload': {
                click:  me.reloadExit
            },
            'gridMeshExits #add': {
                click:  me.addExit
            },
            'winMeshAddExit': {
                beforeshow:      me.loadAddExit
            },
            'winMeshAddExit #btnTypeNext' : {
                click:  me.btnExitTypeNext
            },
            'winMeshAddExit #btnDataPrev' : {
                click:  me.btnExitDataPrev
            },
            'winMeshAddExit #save' : {
                click:  me.btnAddExitSave
            },
            'gridMeshExits #delete': {
                click: me.delExit
            },
            'gridMeshExits #edit': {
                click:  me.editExit
            },
            'winMeshEditExit': {
                beforeshow:      me.loadExit
            },
            'winMeshEditExit #save': {
                click: me.btnEditExitSave
            },//Common node settings
            //Enable the CoovaChilli transparent proxy settings
            '#chkProxyEnable' : {
                change:  me.chkProxyEnableChange
            },
            'pnlMeshEdit #tabNodeCommonSettings' : {
                activate:      me.frmNodeCommonSettingsLoad
            },
            'pnlNodeCommonSettings #save': {
                click:  me.btnNodeCommonSettingsSave
            },
			'pnlNodeCommonSettings  #eth_br_chk' : {
                change:  me.chkEthBrChange
            },
            //Here nodes start
            'gridNodes #reload': {
                click:  me.reloadNodes
            },
            'gridNodes #add': {
                click:  me.addNode
            },
            '#winMeshAddNodeEdit #save' : {
                click:  me.btnAddNodeSave
            },
            'gridNodes #delete': {
                click: me.delNode
            },
            'gridNodes #edit': {
                click:  me.editNode
            },
			'gridNodes #map' : {
                click: 	me.mapLoadApi
            },
            '#winMeshEditNodeEdit': {
                beforeshow:      me.loadNode
            },
            '#winMeshEditNodeEdit #save': {
                click: me.btnEditNodeSave
            },
            
			//---- MAP Starts here..... -----
			'pnlMeshEdit #mapTab'		: {
				activate: function(pnl){
					me.reloadMap(pnl);
				}
			},
			'pnlMeshEditGMap #reload'	: {
				click:	function(b){
					var me = this;
					me.reloadMap(b.up('pnlMeshEditGMap'));
				}
			},
			'pnlMeshEditGMap #preferences': {
                click: me.mapPreferences
            },
			'winMeshMapPreferences #snapshot': {
                click:      me.mapPreferencesSnapshot
            },
            'winMeshMapPreferences #save': {
                click:      me.mapPreferencesSave
            },
            'pnlMeshEditGMap #add': {
                click: me.mapNodeAdd
            },
           'winMeshMapNodeAdd #save': {
                click: me.meshMapNodeAddSubmit
            },
            'pnlMeshEditGMap #edit': {
                click:  function(){
                    Ext.Msg.alert(
                        i18n('sEdit_a_marker'), 
                        i18n('sSimply_drag_a_marker_to_a_different_postition_and_click_the_save_button_in_the_info_window')
                    );
                }
            },
            'pnlMeshEditGMap #delete': {
                click:  function(){
                    Ext.Msg.alert(
                        i18n('sDelete_a_marker'), 
                        i18n('sSimply_drag_a_marker_to_a_different_postition_and_click_the_delete_button_in_the_info_window')
                    );
                }
            },
            '#pnlMapsEdit #cancel': {
                click: me.btnMapCancel
            },
            '#pnlMapsEdit #delete': {
                click: me.btnMapDelete
            },
            '#pnlMapsEdit #save': {
                click: me.btnMapSave
            },
            'winMeshAddExit #chkNasClient' : {
				change	: me.chkNasClientChange
			}, 
            'winMeshAddExit #chkLoginPage' : {
				change	: me.chkLoginPageChange
			}
            
        });
    },
    actionIndex: function(mesh_id,name){
        var me      = this; 
         
        var id		= 'tabMeshEdit'+ mesh_id;
        var tabMeshes = me.getTabMeshes();
        var newTab  = tabMeshes.items.findBy(
            function (tab){
                return tab.getItemId() === id;
            });
         
        if (!newTab){
            newTab = tabMeshes.add({
                glyph   : Rd.config.icnEdit, 
                //title   : i18n('sEdit')+' '+name,
                title   : name,
                closable: true,
                layout  : 'fit',
                xtype   : 'pnlMeshEdit',
                itemId  : id,
                border  : false,
                meshId  : mesh_id,
                meshName: name
            });
        }    
        tabMeshes.setActiveTab(newTab);
    },
    gridActivate: function(grid){
        var me = this;
        grid.getStore().reload();
    },
	reloadEntry: function(button){
        var me      = this;
        var win     = button.up("pnlMeshEdit");
        var entGrid = win.down("gridMeshEntries");
        entGrid.getStore().reload();
    },
    addEntry: function(button){
        var me      = this;
        var tabEdit = button.up("pnlMeshEdit");
        var store   = tabEdit.down("gridMeshEntries").getStore();
        if(!Ext.WindowManager.get('winMeshAddEntryId')){
            var w = Ext.widget('winMeshAddEntry',
            {
                id          :'winMeshAddEntryId',
                store       : store,
                meshId      : tabEdit.meshId
            });
            w.show();        
        }
    },
    cmbEncryptionChange: function(cmb){
        var me      = this;
        var form    = cmb.up('form');
        var key     = form.down('#key');
        var srv     = form.down('#auth_server');
        var scrt    = form.down('#auth_secret'); 
        var val     = cmb.getValue();
        if(val == 'none'){
            key.setVisible(false);
            key.setDisabled(true); 
            srv.setVisible(false);
            srv.setDisabled(true);
            scrt.setVisible(false);
            scrt.setDisabled(true);  
        }

        if((val == 'wep')|(val == 'psk')|(val =='psk2')){
            key.setVisible(true);
            key.setDisabled(false); 
            srv.setVisible(false);
            srv.setDisabled(true);
            scrt.setVisible(false);
            scrt.setDisabled(true);  
        }

        if((val == 'wpa')|(val == 'wpa2')){
            key.setVisible(false);
            key.setDisabled(true); 
            srv.setVisible(true);
            srv.setDisabled(false);
            scrt.setVisible(true);
            scrt.setDisabled(false);  
        }

    },
    chkMaxassocChange: function(chk){
        var me      = this;
        var form    = chk.up('form');
        var num     = form.down('#maxassoc');    
        var val     = chk.getValue();
        if(val){
            num.setVisible(true);
            num.setDisabled(false); 
        }else{
            num.setVisible(false);
            num.setDisabled(true);
        }
    },
    cmbMacFilterChange:function(cmb){
        var me      = this;
        var form    = cmb.up('form');
        var pu      = form.down('cmbPermanentUser');
        var val     = cmb.getValue();
        
        if(val == 'disable'){
            pu.setVisible(false);
            pu.setDisabled(true); 
        }else{
            pu.setVisible(true);
            pu.setDisabled(false); 
        }
    },
    btnAddEntrySave:  function(button){
        var me      = this;
        var win     = button.up("winMeshAddEntry");
        var form    = win.down('form');
        form.submit({
            clientValidation: true,
            url: me.getUrlAddEntry(),
            success: function(form, action) {
                win.close();
                win.store.load();
                Ext.ux.Toaster.msg(
                    i18n('sNew_mesh_entry_point_added'),
                    i18n('sNew_mesh_enty_point_created_fine'),
                    Ext.ux.Constants.clsInfo,
                    Ext.ux.Constants.msgInfo
                );
            },
            failure: Ext.ux.formFail
        });
    },
    editEntry: function(button){
        var me      = this;
        var win     = button.up("pnlMeshEdit");
        var store   = win.down("gridMeshEntries").getStore();

        if(win.down("gridMeshEntries").getSelectionModel().getCount() == 0){
             Ext.ux.Toaster.msg(
                        i18n('sSelect_an_item'),
                        i18n('sFirst_select_an_item'),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
            );
        }else{
            var sr      = win.down("gridMeshEntries").getSelectionModel().getLastSelected();
            var id      = sr.getId();
            if(!Ext.WindowManager.get('winMeshEditEntryId')){
                var w = Ext.widget('winMeshEditEntry',
                {
                    id          :'winMeshEditEntryId',
                    store       : store,
                    entryId     : id
                });
                w.show();         
            }else{
                var w       = me.getEditEntryWin();
                w.entryId   = id; 
                me.loadEntry(w)
            } 
        }     
    },
    loadEntry: function(win){
        var me      = this; 
        var form    = win.down('form');
        var entryId = win.entryId;     
        form.load({
            url         :me.getUrlViewEntry(), 
            method      :'GET',
            params      :{entry_id:entryId},
            success     : function(a,b,c){
                var mf     = form.down("cmbMacFilter");
                var mf_val = mf.getValue();
                if(mf_val != 'disable'){
                    var cmb     = form.down("cmbPermanentUser");
                    var rec     = Ext.create('Rd.model.mPermanentUser', {username: b.result.data.username, id: b.result.data.permanent_user_id});
                    cmb.getStore().loadData([rec],false);
                    cmb.setValue(b.result.data.permanent_user_id);
                }
            }
        });  
        
    },
    btnEditEntrySave:  function(button){
        var me      = this;
        var win     = button.up("winMeshEditEntry");
        var form    = win.down('form');
        form.submit({
            clientValidation: true,
            url: me.getUrlEditEntry(),
            success: function(form, action) {
                win.close();
                win.store.load();
                Ext.ux.Toaster.msg(
                    i18n('sItem_updated'),
                    i18n('sItem_updated_fine'),
                    Ext.ux.Constants.clsInfo,
                    Ext.ux.Constants.msgInfo
                );
            },
            failure: Ext.ux.formFail
        });
    },
    delEntry:   function(btn){
        var me      = this;
        var tabEdit = btn.up("pnlMeshEdit");
        var grid    = tabEdit.down("gridMeshEntries");
    
        //Find out if there was something selected
        if(grid.getSelectionModel().getCount() == 0){
             Ext.ux.Toaster.msg(
                        i18n('sSelect_an_item'),
                        i18n('sFirst_select_an_item_to_delete'),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
            );
        }else{
            Ext.MessageBox.confirm(i18n('sConfirm'), i18n('sAre_you_sure_you_want_to_do_that_qm'), function(val){
                if(val== 'yes'){
                    grid.getStore().remove(grid.getSelectionModel().getSelection());
                    grid.getStore().sync({
                        success: function(batch,options){
                            Ext.ux.Toaster.msg(
                                i18n('sItem_deleted'),
                                i18n('sItem_deleted_fine'),
                                Ext.ux.Constants.clsInfo,
                                Ext.ux.Constants.msgInfo
                            );  
                        },
                        failure: function(batch,options,c,d){
                            Ext.ux.Toaster.msg(
                                i18n('sProblems_deleting_item'),
                                batch.proxy.getReader().rawData.message.message,
                                Ext.ux.Constants.clsWarn,
                                Ext.ux.Constants.msgWarn
                            );
                            grid.getStore().load(); //Reload from server since the sync was not good
                        }
                    });
                }
            });
        }
    },
    frmMeshSettingsLoad: function(tab){
        var me      = this;
        var form    = tab.down('form');
        var meshId  = tab.meshId;
        form.load({url:me.getUrlViewMeshSettings(), method:'GET',params:{mesh_id:meshId}});
    },
    radioMeshPointChange : function(rbtn){
        var me      = this;
        
        var form    = rbtn.up('form');
        var enc     = form.down('#encryption');
        var enc_key = form.down('#encryption_key');
        
        if(rbtn.getValue()){
            enc.setVisible(true);
            enc.setDisabled(false); 
            if(enc.getValue()){
                enc_key.setVisible(true);
                enc_key.setDisabled(false);
            }else{
                enc_key.setVisible(false);
                enc_key.setDisabled(true);
            } 
        }else{
            enc.setVisible(false);
            enc.setDisabled(true);
            enc_key.setVisible(false);
            enc_key.setDisabled(true);
        }
    }, 
    chkMeshEncryptionChange : function(chk){
        var me      = this;
        var form    = chk.up('form');
        var enc_key = form.down('#encryption_key');
        if(chk.getValue()){
            enc_key.setVisible(true);
            enc_key.setDisabled(false);
        }else{
            enc_key.setVisible(false);
            enc_key.setDisabled(true);
        }    
    },  
    btnMeshSettingsSave: function(button){
        var me      = this;
        var form    = button.up('form');
        var tab     = button.up('#tabMeshSettings');
        var meshId  = tab.meshId;
        form.submit({
            clientValidation    : true,
            url                 : me.getUrlEditMeshSettings(),
            params              : {mesh_id: meshId},
            success: function(form, action) {
                Ext.ux.Toaster.msg(
                    i18n('sItem_updated'),
                    i18n('sItem_updated_fine'),
                    Ext.ux.Constants.clsInfo,
                    Ext.ux.Constants.msgInfo
                );
            },
            failure: Ext.ux.formFail
        });
    },
    
    chkNasClientChange: function(chk){
        var me          = this;
        var form        = chk.up('form');
        var win         = chk.up('window');
        var nas_id      = win.down('#radius_nasid');
                  
        var cmb_realm    = form.down('#cmbRealm');
        if(chk.getValue()){	
            nas_id.setVisible(false);
            nas_id.setDisabled(true);
			cmb_realm.setVisible(true);
			cmb_realm.setDisabled(false);
		}else{
		    nas_id.setVisible(true);
            nas_id.setDisabled(false);
			cmb_realm.setVisible(false);
			cmb_realm.setDisabled(true);		
		}
    },
    chkLoginPageChange: function(chk){
        var me          = this;
        var form        = chk.up('form');
        var cmb_page    = form.down('#cmbDynamicDetail');
        if(chk.getValue()){	
			cmb_page.setVisible(true);
			cmb_page.setDisabled(false);
		}else{
			cmb_page.setVisible(false);
			cmb_page.setDisabled(true);		
		}
    },
    
    reloadExit: function(button){
        var me      = this;
        var win     = button.up("pnlMeshEdit");
        var exit    = win.down("gridMeshExits");
        exit.getStore().reload();
    },
    loadAddExit: function(win){
        var me      = this; 
        var form    = win.down('#scrnData');
        form.load({url:me.getUrlExitAddDefaults(), method:'GET'});
    },
    addExit: function(button){
        var me      = this;

        var tabEdit = button.up("pnlMeshEdit");

        //If there are NO entry points defined; we will NOT pop up this window.
        var entries_count   = tabEdit.down("gridMeshEntries").getStore().count();
        if(entries_count == 0){
            Ext.ux.Toaster.msg(
                i18n('sNo_entry_points_defined'),
                i18n('sDefine_some_entry_points_first'),
                Ext.ux.Constants.clsWarn,
                Ext.ux.Constants.msgWarn
            );
            return;
        }
        
        //Entry points present; continue 
        var store   = tabEdit.down("gridMeshExits").getStore();
        if(!Ext.WindowManager.get('winMeshAddExitId')){
            var w = Ext.widget('winMeshAddExit',
            {
                id          :'winMeshAddExitId',
                store       : store,
                meshId      : tabEdit.meshId
            });
            w.show();         
        }
    },
    btnExitTypeNext: function(button){
        var me      = this;
        var win     = button.up('winMeshAddExit');
        var type    = win.down('radiogroup').getValue().exit_type;
        var vlan    = win.down('#vlan');
        var tab_capt= win.down('#tabCaptivePortal');
        var sel_type= win.down('#type');
        var vpn     = win.down('#cmbOpenVpnServers')       
        var a_nas   = win.down('#chkNasClient');
        var cmb_realm = win.down('#cmbRealm');
        var a_page  = win.down('#chkLoginPage');
        var cmb_page= win.down('cmbDynamicDetail');
        
        sel_type.setValue(type);
 
        if(type == 'openvpn_bridge'){
            vpn.setVisible(true);
            vpn.setDisabled(false);
        }else{
            vpn.setVisible(false);
            vpn.setDisabled(true);
        }
        
       if(type == 'tagged_bridge'){
            vlan.setVisible(true);
            vlan.setDisabled(false);
        }else{
            vlan.setVisible(false);
            vlan.setDisabled(true);
        }

       if(type == 'captive_portal'){
            tab_capt.setDisabled(false);
			tab_capt.tab.show();					
			a_nas.setVisible(true);
			a_nas.setDisabled(false);
			a_page.setVisible(true);
			a_page.setDisabled(false);
			cmb_page.setVisible(true);
			cmb_page.setDisabled(false);
			cmb_realm.setVisible(true);
			cmb_realm.setDisabled(false);
			
        }else{
            tab_capt.setDisabled(true);
			tab_capt.tab.hide();		
			a_nas.setVisible(false);
			a_nas.setDisabled(true);
			a_page.setVisible(false);
			a_page.setDisabled(true);
			cmb_page.setVisible(false);
			cmb_page.setDisabled(true);
			cmb_realm.setVisible(false);
			cmb_realm.setDisabled(true);			
        }   
        win.getLayout().setActiveItem('scrnData');
    },
    btnExitDataPrev: function(button){
        var me      = this;
        var win     = button.up('winMeshAddExit');
        win.getLayout().setActiveItem('scrnType');
    },
    btnAddExitSave: function(button){
        var me      = this;
        var win     = button.up("winMeshAddExit");
        var form    = win.down('#scrnData');
        form.submit({
            clientValidation: true,
            url: me.getUrlAddExit(),
            success: function(form, action) {
                win.close();
                win.store.load();
                Ext.ux.Toaster.msg(
                    i18n('sItem_added'),
                    i18n('sItem_added_fine'),
                    Ext.ux.Constants.clsInfo,
                    Ext.ux.Constants.msgInfo
                );
            },
            failure: Ext.ux.formFail
        });
    },
    delExit:   function(btn){
        var me      = this;
        var tabEdit = btn.up("pnlMeshEdit");
        var grid    = tabEdit.down("gridMeshExits");
    
        //Find out if there was something selected
        if(grid.getSelectionModel().getCount() == 0){
             Ext.ux.Toaster.msg(
                        i18n('sSelect_an_item'),
                        i18n('sFirst_select_an_item_to_delete'),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
            );
        }else{
            Ext.MessageBox.confirm(i18n('sConfirm'), i18n('sAre_you_sure_you_want_to_do_that_qm'), function(val){
                if(val== 'yes'){
                    grid.getStore().remove(grid.getSelectionModel().getSelection());
                    grid.getStore().sync({
                        success: function(batch,options){
                            Ext.ux.Toaster.msg(
                                i18n('sItem_deleted'),
                                i18n('sItem_deleted_fine'),
                                Ext.ux.Constants.clsInfo,
                                Ext.ux.Constants.msgInfo
                            );  
                        },
                        failure: function(batch,options,c,d){
                            Ext.ux.Toaster.msg(
                                i18n('sProblems_deleting_item'),
                                batch.proxy.getReader().rawData.message.message,
                                Ext.ux.Constants.clsWarn,
                                Ext.ux.Constants.msgWarn
                            );
                            grid.getStore().load(); //Reload from server since the sync was not good
                        }
                    });
                }
            });
        }
    },
    editExit: function(button){
        var me      = this;
        var win     = button.up("pnlMeshEdit");
        var store   = win.down("gridMeshExits").getStore();

        if(win.down("gridMeshExits").getSelectionModel().getCount() == 0){
             Ext.ux.Toaster.msg(
                        i18n('sSelect_an_item'),
                        i18n('sFirst_select_an_item'),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
            );
        }else{
            var sr      = win.down("gridMeshExits").getSelectionModel().getLastSelected();
            var id      = sr.getId();
            var meshId  = sr.get('mesh_id');
            var type    = sr.get('type');
            if(!Ext.WindowManager.get('winMeshEditExitId')){
                var w = Ext.widget('winMeshEditExit',
                {
                    id          :'winMeshEditExitId',
                    store       : store,
                    exitId      : id,
                    meshId      : meshId,
                    type        : type
                });
                w.show();        
            }else{
                var w       = me.getEditExitWin();
                var vlan    = w.down('#vlan');
                var vpn     = win.down('#cmbOpenVpnServers'); 
                var tab_capt= w.down('#tabCaptivePortal');
                w.exitId    = id;
                w.meshId    = meshId;
                
                if(type == 'openvpn_bridge'){
                    vpn.setVisible(true);
                    vpn.setDisabled(false);
                    
                    vlan.setVisible(false);
                    vlan.setDisabled(true);
                    
                }else{
                    vpn.setVisible(false);
                    vpn.setDisabled(true);
                }

                if(type == 'tagged_bridge'){
                    vlan.setVisible(true);
                    vlan.setDisabled(false);
                    
                    vpn.setVisible(false);
                    vpn.setDisabled(true);
                    
                }else{
                    vlan.setVisible(false);
                    vlan.setDisabled(true);
                }

                if(type == 'captive_portal'){
                    tab_capt.setDisabled(false);
					tab_capt.tab.show();
                }else{
                    tab_capt.setDisabled(true);
					tab_capt.tab.hide(); 
                }
                me.loadExit(w)
            } 
        }     
    },
    loadExit: function(win){
        var me      = this; 
        var form    = win.down('form');
        var exitId = win.exitId;
        form.load({
            url         :me.getUrlViewExit(), 
            method      :'GET',
            params      :{exit_id:exitId},
            success     : function(a,b,c){
                var t     = form.down("#type");
                var t_val = t.getValue();
                var vlan  = form.down('#vlan');
                var vpn   = form.down('#cmbOpenVpnServers') 
                
                if(t_val == 'openvpn_bridge'){
                    vpn.setVisible(true);
                    vpn.setDisabled(false);
                    
                    vlan.setVisible(false);
                    vlan.setDisabled(true);
                }else{
                    vpn.setVisible(false);
                    vpn.setDisabled(true);
                }
                  
                if(t_val == 'tagged_bridge'){
                    vlan.setVisible(true);
                    vlan.setDisabled(false);
                    
                    vpn.setVisible(false);
                    vpn.setDisabled(true);
                }else{
                    vlan.setVisible(false);
                    vlan.setDisabled(true);
                }
                var ent  = form.down("cmbMeshEntryPoints");
                ent.setValue(b.result.data.entry_points);
                if(b.result.data.type == 'captive_portal'){
                    if((b.result.data.auto_login_page == true)&&
                    (b.result.data.dynamic_detail != null)){
                        var cmb     = form.down("cmbDynamicDetail");
                        var rec     = Ext.create('Rd.model.mDynamicDetail', {name: b.result.data.dynamic_detail, id: b.result.data.dynamic_detail_id});
                        cmb.getStore().loadData([rec],false);
                        cmb.setValue( b.result.data.dynamic_detail_id );
                    }else{
                        //FIXME PLEASE CHECK WHAT MUST HAPPEN HERE
                        //form.down("cmbDynamicDetail").setVisible(false);
                        //form.down("cmbDynamicDetail").setDisabled(true);
                    }
                }  
            }
        });
    },
    btnEditExitSave:  function(button){
        var me      = this;
        var win     = button.up("winMeshEditExit");
        var form    = win.down('form');
        form.submit({
            clientValidation: true,
            url: me.getUrlEditExit(),
            success: function(form, action) {
                win.close();
                win.store.load();
                Ext.ux.Toaster.msg(
                    i18n('sItem_updated'),
                    i18n('sItem_updated_fine'),
                    Ext.ux.Constants.clsInfo,
                    Ext.ux.Constants.msgInfo
                );
            },
            failure: Ext.ux.formFail
        });
    },//Common node settings
    frmNodeCommonSettingsLoad: function(tab){
        var me      = this;
        var form    = tab.down('form');
        var meshId  = tab.meshId;
        form.load({url:me.getUrlViewNodeCommonSettings(), method:'GET',params:{mesh_id:meshId}});
    },

    chkProxyEnableChange: function(chk){
        var me      = this;
        var panel   = chk.up('panel');
        var items   = Ext.ComponentQuery.query("textfield", panel);
        if(chk.getValue()){
            Ext.Array.each(items, function(item, index, itemsItSelf) {
                item.setDisabled(false);
                 item.setVisible(true);
            });
        }else{
            Ext.Array.each(items, function(item, index, itemsItSelf) {
                item.setDisabled(true);
                 item.setVisible(false);
            });
        }
    },

	chkEthBrChange: function(chk){
		var me 		= this;
		var form	= chk.up('form');
		var cmbBr	= form.down('#eth_br_with');
		var chkAll	= form.down('#eth_br_for_all');
		if(chk.getValue()){
			cmbBr.setDisabled(false);
			chkAll.setDisabled(false);
		}else{
			cmbBr.setDisabled(true);
			chkAll.setDisabled(true);
		}
	},
    btnNodeCommonSettingsSave: function(button){
        var me      = this;
        var form    = button.up('form');
        var tab     = button.up('#tabNodeCommonSettings');
        var meshId  = tab.meshId;
        form.submit({
            clientValidation    : true,
            url                 : me.getUrlEditNodeCommonSettings(),
            params              : {mesh_id: meshId},
            success: function(form, action) {
                Ext.ux.Toaster.msg(
                    i18n('sItem_updated'),
                    i18n('sItem_updated_fine'),
                    Ext.ux.Constants.clsInfo,
                    Ext.ux.Constants.msgInfo
                );
            },
            failure: Ext.ux.formFail
        });
    },//Nodes related
    reloadNodes: function(button){
        var me      = this;
        var win     = button.up("pnlMeshEdit");
        var nodes   = win.down("gridNodes");
        nodes.getStore().reload();
    },
    addNode: function(button){
        var me          = this;
        var tabEdit     = button.up("pnlMeshEdit");
        
        //Entry points present; continue 
        var store   	= tabEdit.down("gridNodes").getStore();
        if(!Ext.WindowManager.get('winMeshAddNodeId')){
            var w = Ext.widget('winMeshAddNode',
            {
                id          :'winMeshAddNodeId',
                store       : store,
                meshId      : tabEdit.meshId,
				meshName	: tabEdit.meshName,
                itemId      : 'winMeshAddNodeEdit'	
            });
            w.show();        
        }
    },
    btnAddNodeSave: function(button){
        var me      = this;
        var win     = button.up("winMeshAddNode");
        var form    = win.down('form');
        form.submit({
            clientValidation: true,
            url: me.getUrlAddNode(),
            success: function(form, action) {
                win.close();
                win.store.load();
                Ext.ux.Toaster.msg(
                    i18n('sItem_added'),
                    i18n('sItem_added_fine'),
                    Ext.ux.Constants.clsInfo,
                    Ext.ux.Constants.msgInfo
                );
            },
            failure: Ext.ux.formFail
        });
    },
    delNode:   function(btn){
        var me      = this;
        var tabEdit = btn.up("pnlMeshEdit");
        var grid    = tabEdit.down("gridNodes");
    
        //Find out if there was something selected
        if(grid.getSelectionModel().getCount() == 0){
             Ext.ux.Toaster.msg(
                        i18n('sSelect_an_item'),
                        i18n('sFirst_select_an_item_to_delete'),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
            );
        }else{
            Ext.MessageBox.confirm(i18n('sConfirm'), i18n('sAre_you_sure_you_want_to_do_that_qm'), function(val){
                if(val== 'yes'){
                    grid.getStore().remove(grid.getSelectionModel().getSelection());
                    grid.getStore().sync({
                        success: function(batch,options){
                            Ext.ux.Toaster.msg(
                                i18n('sItem_deleted'),
                                i18n('sItem_deleted_fine'),
                                Ext.ux.Constants.clsInfo,
                                Ext.ux.Constants.msgInfo
                            );  
                        },
                        failure: function(batch,options,c,d){
                            Ext.ux.Toaster.msg(
                                i18n('sProblems_deleting_item'),
                                batch.proxy.getReader().rawData.message.message,
                                Ext.ux.Constants.clsWarn,
                                Ext.ux.Constants.msgWarn
                            );
                            grid.getStore().load(); //Reload from server since the sync was not good
                        }
                    });
                }
            });
        }
    },
    editNode: function(button){
        var me      = this;
        var tabEdit = button.up("pnlMeshEdit");
        var store   = tabEdit.down("gridNodes").getStore();
        if(tabEdit.down("gridNodes").getSelectionModel().getCount() == 0){
             Ext.ux.Toaster.msg(
                        i18n('sSelect_an_item'),
                        i18n('sFirst_select_an_item'),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
            );
        }else{
            var sr      = tabEdit.down("gridNodes").getSelectionModel().getLastSelected();
            var id      = sr.getId();
            var meshId  = sr.get('mesh_id');
            if(!Ext.WindowManager.get('winMeshEditNodeId')){
                var w = Ext.widget('winMeshEditNode',
                {
                    id          :'winMeshEditNodeId',
                    store       : store,
                    nodeId      : id,
                    meshId      : tabEdit.meshId,
					meshName	: tabEdit.meshName,
                    itemId      : 'winMeshEditNodeEdit'
                });
                w.show();         
            }
        }
    },
    loadNode: function(win){
        var me      = this; 
        var form    = win.down('form');
        var nodeId  = win.nodeId;
        form.load({url:me.getUrlViewNode(), method:'GET',params:{node_id:nodeId}});

        //We have to disable this and hide it upon initial loading
        var tabAdvRadio1 = form.down('#tabAdvWifiRadio1');
        tabAdvRadio1.setDisabled(true);
        tabAdvRadio1.tab.hide();
    },
    btnEditNodeSave:  function(button){
        var me      = this;
        var win     = button.up("winMeshEditNode");
        var form    = win.down('form');
        form.submit({
            clientValidation: true,
            url: me.getUrlEditNode(),
            success: function(form, action) {
                win.close();
                win.store.load();
                Ext.ux.Toaster.msg(
                    i18n('sItem_updated'),
                    i18n('sItem_updated_fine'),
                    Ext.ux.Constants.clsInfo,
                    Ext.ux.Constants.msgInfo
                );
            },
            failure: Ext.ux.formFail
        });
    },

	//____ MAP ____

    mapLoadApi:   function(button){
        var me 	= this;
		Ext.ux.Toaster.msg(
	        'Loading Google Maps API',
	        'Please be patient....',
	        Ext.ux.Constants.clsInfo,
	        Ext.ux.Constants.msgInfo
	    );
	    
	    Ext.Loader.loadScript({
            url: 'https://www.google.com/jsapi',                    // URL of script
            scope: this,                   // scope of callbacks
            onLoad: function() {           // callback fn when script is loaded
                google.load("maps", "3", {
                    other_params:"sensor=false",
                    callback : function(){
                    // Google Maps are loaded. Place your code here
                        me.mapCreatePanel(button);
                }
            });
            },
            onError: function() {          // callback fn if load fails 
                console.log("Error loading Google script");
            } 
        });
    },
    mapCreatePanel : function(button){
        var me          = this
        var tp          = button.up('tabpanel');
        var map_tab_id  = 'mapTab';
        var nt          = tp.down('#'+map_tab_id);
        if(nt){
            tp.setActiveTab(map_tab_id); //Set focus on  Tab
            return;
        }

        var map_tab_name    = i18n("sGoogle_Maps");
		var tabEdit 		= tp.up('pnlMeshEdit');
		var mesh_id		    = tp.meshId;

        //We need to fetch the Preferences for this user's Google Maps map
        Ext.Ajax.request({
            url		: me.getUrlMapPrefView(),
            method	: 'GET',
			params	: {
				mesh_id	: mesh_id
			},
            success: function(response){
                var jsonData    = Ext.JSON.decode(response.responseText);
                if(jsonData.success){     
                   	//console.log(jsonData);
					//___Build this tab based on the preferences returned___
                    tp.add({ 
                        title 		: map_tab_name,
                        itemId		: map_tab_id,
                        closable	: true,
                        glyph		: Rd.config.icnMap, 
                        layout		: 'fit', 
                        xtype		: 'pnlMeshEditGMap',
                        mapOptions	: {zoom: jsonData.data.zoom, mapTypeId: google.maps.MapTypeId[jsonData.data.type] },	//Required for map
                       	centerLatLng: {lat:jsonData.data.lat,lng:jsonData.data.lng},										//Required for map
                       	markers		: [],
						meshId		: mesh_id
                    });
                    tp.setActiveTab(map_tab_id); //Set focus on Add Tab
                    //____________________________________________________   
                }   
            },
			failure: function(batch,options){
                Ext.ux.Toaster.msg(
                    'Problems getting the map preferences',
                    'Map preferences could not be fetched',
                    Ext.ux.Constants.clsWarn,
                    Ext.ux.Constants.msgWarn
                );
            },
			scope: me
        });
    },
    dragStart: function(node_id,map_panel,sel_marker){
        var me = this;
        me.lastMovedMarker  = sel_marker;
        me.lastOrigPosition = sel_marker.getPosition();
        me.editWindow 		= map_panel.editwindow;
    },
    dragEnd: function(node_id,map_panel,sel_marker){
        var me = this;
        var l_l = sel_marker.getPosition();
        map_panel.new_lng = l_l.lng();
        map_panel.new_lat = l_l.lat();
        map_panel.editwindow.open(map_panel.gmap, sel_marker);
        me.lastLng    = l_l.lng();
        me.lastLat    = l_l.lat();
        me.lastDragId = node_id;
    },
    btnMapCancel: function(button){
        var me = this;
        me.editWindow.close();
        me.lastMovedMarker.setPosition(me.lastOrigPosition);
    },
    btnMapDelete: function(button){
        var me 		= this;
		var pnl		= button.up('#pnlMapsEdit');
		var map_pnl = pnl.mapPanel;
        Ext.Ajax.request({
            url: me.getUrlMapDelete(),
            method: 'GET',
            params: {
                id: me.lastDragId
            },
            success: function(response){
                var jsonData    = Ext.JSON.decode(response.responseText);
                if(jsonData.success){     
                    me.editWindow.close();
                    Ext.ux.Toaster.msg(
                        i18n('sItem_deleted'),
                        i18n('sItem_deleted_fine'),
                        Ext.ux.Constants.clsInfo,
                        Ext.ux.Constants.msgInfo
                    );
					me.reloadMap(map_pnl);
                }   
            },
            scope: me
        });
    },
    btnMapSave: function(button){
        var me 		= this;
		var pnl		= button.up('#pnlMapsEdit');
		var map_pnl = pnl.mapPanel;
        Ext.Ajax.request({
            url: me.getUrlMapSave(),
            method: 'GET',
            params: {
                id: me.lastDragId,
                lat: me.lastLat,
                lon: me.lastLng
            },
            success: function(response){
                var jsonData    = Ext.JSON.decode(response.responseText);
                if(jsonData.success){     
                    me.editWindow.close();
                    Ext.ux.Toaster.msg(
                        i18n('sItem_updated'),
                        i18n('sItem_updated_fine'),
                        Ext.ux.Constants.clsInfo,
                        Ext.ux.Constants.msgInfo
                    );
					me.reloadMap(map_pnl);
                }   
            },
            scope: me
        });
    },
	mapPreferences: function(button){
       	var me 		= this;
		var tabEdit = button.up('pnlMeshEdit');
		var mesh_id	= tabEdit.meshId;
		var pref_id = 'winMeshMapPreferences_'+mesh_id;
		var map_p	= tabEdit.down('pnlMeshEditGMap');

        if(!Ext.WindowManager.get(pref_id)){
            var w = Ext.widget('winMeshMapPreferences',{id:pref_id,mapPanel: map_p,meshId: mesh_id});
            w.show();
            //We need to load this widget's form with the latest data:
            w.down('form').load({
				url		: me.getUrlMapPrefView(),
            	method	: 'GET',
				params	: {
					mesh_id	: mesh_id
				}
			});
       }   
    },
   	mapNodeAdd: function(button){
        var me 		= this;
		var tabEdit = button.up('pnlMeshEdit');
		var mesh_id	= tabEdit.meshId;
		var add_id  = 'winMeshMapNodeAdd_'+mesh_id;
		var map_p	= tabEdit.down('pnlMeshEditGMap');

        if(!Ext.WindowManager.get(add_id)){
            var w = Ext.widget('winMeshMapNodeAdd',{id: add_id,mapPanel: map_p,meshId:mesh_id});
            w.show()     
       }   
    },
    meshMapNodeAddSubmit: function(button){
        var me      = this;
        var win     = button.up('winMeshMapNodeAdd');
        var node    = win.down('cmbMeshAddMapNodes');
        var id      = node.getValue();
		var pnl		= win.mapPanel
        win.close();
        var m_center 	= pnl.gmap.getCenter();
        var sel_marker 	= pnl.addMarker({
            lat: m_center.lat(), 
            lng: m_center.lng(),
            icon: "resources/images/map_markers/yellow-dot.png",
            draggable: true, 
            title: "New Marker",
            listeners: {
                dragend: function(){
                    me.dragEnd(id,pnl,sel_marker);
                },
                dragstart: function(){
                    pnl.addwindow.close();
                    me.dragStart(id,pnl,sel_marker);
                }
            }
        });
		//Show the add infowinfow on the pnl's gmap at the marker
		pnl.addwindow.open(pnl.gmap, sel_marker);
    },
    mapPreferencesSnapshot: function(button){

        var me      = this;
        var form    = button.up('form');
		var w		= button.up('winMeshMapPreferences');
        var pnl     = w.mapPanel;
        var zoom    = pnl.gmap.getZoom();
        var type    = pnl.gmap.getMapTypeId();
        var ll      = pnl.gmap.getCenter();
        var lat     = ll.lat();
        var lng     = ll.lng();

        form.down('#lat').setValue(lat);
        form.down('#lng').setValue(lng);
        form.down('#zoom').setValue(zoom);
        form.down('#type').setValue(type.toUpperCase());
        //console.log(" zoom "+zoom+" type "+type+ " lat "+lat+" lng "+lng);
    },
    mapPreferencesSave: function(button){

        var me      = this;
        var form    = button.up('form');
        var win     = button.up('winMeshMapPreferences');
		var mesh_id = win.meshId;
       
        form.submit({
            clientValidation: true,
            url: me.getUrlMapPrefEdit(),
			params: {
				mesh_id: mesh_id
			},
            success: function(form, action) {
                win.close();
                Ext.ux.Toaster.msg(
                    i18n('sItem_updated'),
                    i18n('sItem_updated_fine'),
                    Ext.ux.Constants.clsInfo,
                    Ext.ux.Constants.msgInfo
                );
            },
            failure: Ext.ux.formFail
        });
    },
	reloadMap: function(map_panel){
		var me = this;
		//console.log("Reload markers");
		map_panel.setLoading(true);
		map_panel.clearMarkers();
		var mesh_id = map_panel.meshId;

		Ext.Ajax.request({
            url: me.getUrlMeshNodes(),
            method: 'GET',
			params: {
				mesh_id: mesh_id
			},
            success: function(response){
                var jsonData    = Ext.JSON.decode(response.responseText);
                if(jsonData.success){
					Ext.each(jsonData.items, function(i){
						var icon = me.getUrlBlueMark();
						var sel_marker = map_panel.addMarker({
		                    lat			: i.lat, 
		                    lng			: i.lng,
		                    icon		: icon,
		                    draggable	: true, 
		                    title		: i.name,
		                    listeners: {
		                        dragend: function(){
		                            me.dragEnd(i.id,map_panel,sel_marker);
		                        },
		                        dragstart: function(){
		                            me.dragStart(i.id,map_panel,sel_marker);
		                        }
		                    }
		                })
					});
					map_panel.setLoading(false);
                }   
            },
            scope: me
        });
	}

});
