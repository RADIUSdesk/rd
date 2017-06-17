Ext.define('Rd.controller.cMeshEdits', {
    extend: 'Ext.app.Controller',
    views:  [
        'components.pnlBanner',  	'meshes.winMeshEdit',
        'meshes.gridMeshEntries',   'meshes.winMeshAddEntry',   'meshes.cmbEncryptionOptions',
        'meshes.winMeshEditEntry',  'meshes.pnlMeshSettings',   'meshes.gridMeshExits',
        'meshes.winMeshAddExit',    'meshes.cmbMeshEntryPoints','meshes.winMeshEditExit',
        'meshes.pnlNodeCommonSettings', 'meshes.gridNodes',     'meshes.winMeshAddNode',
        'meshes.cmbHardwareOptions', 'meshes.cmbStaticEntries', 'meshes.cmbStaticExits',
        'meshes.winMeshEditNode',	'meshes.pnlMeshEditGMap',	'meshes.winMeshMapPreferences',
		'meshes.winMeshMapNodeAdd',	'meshes.cmbEthBridgeOptions',
		'components.cmbFiveGigChannels',
        'meshes.cmbTimezones',      'meshes.cmbCountries'
    ],
    stores      : [	
		'sMeshEntries', 'sMeshExits', 	'sMeshEntryPoints',	'sNodes'
    ],
    models      : [ 
		'mMeshEntry',  	'mMeshExit', 	'mMeshEntryPoint',  'mNode'
    ],
    config      : {  
        urlAddEntry:        '/cake2/rd_cake/meshes/mesh_entry_add.json',
        urlViewEntry:       '/cake2/rd_cake/meshes/mesh_entry_view.json',
        urlEditEntry:       '/cake2/rd_cake/meshes/mesh_entry_edit.json',
        urlViewMeshSettings:'/cake2/rd_cake/meshes/mesh_settings_view.json',
        urlEditMeshSettings:'/cake2/rd_cake/meshes/mesh_settings_edit.json',
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
        {  ref: 'editExitWin',  	selector: 'winMeshEditExit'}  
    ],
    init: function() {
        var me = this;
        if (me.inited) {
            return;
        }
        me.inited = true;

        me.control({
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
            'winMeshEditEntry #save': {
                click: me.btnEditEntrySave
            },
            'winMeshEdit #tabMeshSettings' : {
                activate:      me.frmMeshSettingsLoad
            },
            'pnlMeshSettings #save': {
                click:  me.btnMeshSettingsSave
            },
            'gridMeshExits #reload': {
                click:  me.reloadExit
            },
            'gridMeshExits #add': {
                click:  me.addExit
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
            'winMeshEdit #tabNodeCommonSettings' : {
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
            '#winMeshAddNodeEdit' : {
                beforeshow:  me.loadAdvancedWifiSettings
            },
            '#winMeshAddNodeEdit #save' : {
                click:  me.btnAddNodeSave
            },
			'#winMeshAddNodeEdit cmbHardwareOptions': {
                change: me.cmbHardwareOptionsChange
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
			'#winMeshEditNodeEdit cmbHardwareOptions': {
                change: me.cmbHardwareOptionsChange
            },

			//Dual RADIO Choices
            //Add
            '#winMeshAddNodeEdit #chkRadio0Enable'	: {
				change	: me.chkRadioEnableChange
			},
			'#winMeshAddNodeEdit #chkRadio1Enable' : {
				change	: me.chkRadioEnableChange
			},
			'#winMeshAddNodeEdit #chkRadio0Mesh' : {
				change	: me.chkRadioMeshChange
			},
			'#winMeshAddNodeEdit #chkRadio1Mesh' : {
				change	: me.chkRadioMeshChange
			},
            '#winMeshAddNodeEdit radio[name=radio0_band]' : {
                change  : me.radio_0_BandChange  
            },
            '#winMeshAddNodeEdit radio[name=radio1_band]' : {
                change  : me.radio_1_BandChange  
            },
            
            //Edit
			'#winMeshEditNodeEdit #chkRadio0Enable'	: {
				change	: me.chkRadioEnableChange
			},
			'#winMeshEditNodeEdit #chkRadio1Enable' : {
				change	: me.chkRadioEnableChange
			},
			'#winMeshEditNodeEdit #chkRadio0Mesh' : {
				change	: me.chkRadioMeshChange
			},
			'#winMeshEditNodeEdit #chkRadio1Mesh' : {
				change	: me.chkRadioMeshChange
			},
            '#winMeshEditNodeEdit radio[name=radio0_band]' : {
                change  : me.radio_0_BandChange  
            },
            '#winMeshEditNodeEdit radio[name=radio1_band]' : {
                change  : me.radio_1_BandChange  
            },

			//VOIP Choices
            //Add
            '#winMeshAddNodeEdit #chkSip'	: {
				change	: me.chkSipChange
			},
			'#winMeshAddNodeEdit #chkAsterisk' : {
				change	: me.chkAsteriskChange
			},
            //Edit
			'#winMeshEditNodeEdit #chkSip'	: {
				change	: me.chkSipChange
			},
			'#winMeshEditNodeEdit #chkAsterisk' : {
				change	: me.chkAsteriskChange
			},
            
			//---- MAP Starts here..... -----
			'winMeshEdit #mapTab'		: {
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
            }
        });
    },
    actionIndex: function(mesh_id,name){
        var me      = this; 
		var id		= 'winMeshEdit'+ mesh_id;
        if(!me.application.runAction('cDesktop','AlreadyExist',id)){
			var w = Ext.widget('winMeshEdit',{id:id, name:name, stateId:id,title: 'MESHdesk edit '+name, meshId :mesh_id, meshName: name});;
            me.application.runAction('cDesktop','Add',w);      
        }
    },
	reloadEntry: function(button){
        var me      = this;
        var win     = button.up("winMeshEdit");
        var entGrid = win.down("gridMeshEntries");
        entGrid.getStore().reload();
    },
    addEntry: function(button){
        var me      = this;
        var win     = button.up("winMeshEdit");
        var store   = win.down("gridMeshEntries").getStore();
        if(!me.application.runAction('cDesktop','AlreadyExist','winMeshAddEntryId')){
            var w = Ext.widget('winMeshAddEntry',
            {
                id          :'winMeshAddEntryId',
                store       : store,
                meshId      : win.getItemId()
            });
            me.application.runAction('cDesktop','Add',w);         
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
        var win     = button.up("winMeshEdit");
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
            if(!me.application.runAction('cDesktop','AlreadyExist','winMeshEditEntryId')){
                var w = Ext.widget('winMeshEditEntry',
                {
                    id          :'winMeshEditEntryId',
                    store       : store,
                    entryId     : id
                });
                me.application.runAction('cDesktop','Add',w);         
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
        form.load({url:me.getUrlViewEntry(), method:'GET',params:{entry_id:entryId}});
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
        var win     = btn.up("window");
        var grid    = win.down("gridMeshEntries");
    
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
    reloadExit: function(button){
        var me      = this;
        var win     = button.up("winMeshEdit");
        var exit    = win.down("gridMeshExits");
        exit.getStore().reload();
    },
    addExit: function(button){
        var me      = this;

        var win             = button.up("winMeshEdit");

        //If there are NO entry points defined; we will NOT pop up this window.
        var entries_count   = win.down("gridMeshEntries").getStore().count();
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
        var store   = win.down("gridMeshExits").getStore();
        if(!me.application.runAction('cDesktop','AlreadyExist','winMeshAddExitId')){
            var w = Ext.widget('winMeshAddExit',
            {
                id          :'winMeshAddExitId',
                store       : store,
                meshId      : win.getItemId()
            });
            me.application.runAction('cDesktop','Add',w);         
        }
    },
    btnExitTypeNext: function(button){
        var me      = this;
        var win     = button.up('winMeshAddExit');
        var type    = win.down('radiogroup').getValue().exit_type;
        var vlan    = win.down('#vlan');
        var tab_capt= win.down('#tabCaptivePortal');
        var sel_type= win.down('#type');
        sel_type.setValue(type);
 
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
        }else{
            tab_capt.setDisabled(true);
			tab_capt.tab.hide();
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
        var win     = btn.up("window");
        var grid    = win.down("gridMeshExits");
    
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
        var win     = button.up("winMeshEdit");
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
            if(!me.application.runAction('cDesktop','AlreadyExist','winMeshEditExitId')){
                var w = Ext.widget('winMeshEditExit',
                {
                    id          :'winMeshEditExitId',
                    store       : store,
                    exitId      : id,
                    meshId      : meshId,
                    type        : type
                });
                me.application.runAction('cDesktop','Add',w);         
            }else{
                var w       = me.getEditExitWin();
                var vlan    = w.down('#vlan');
                var tab_capt= w.down('#tabCaptivePortal');
                w.exitId    = id;
                w.meshId    = meshId;

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
                if(t_val == 'tagged_bridge'){
                    vlan.setVisible(true);
                    vlan.setDisabled(false);
                }else{
                    vlan.setVisible(false);
                    vlan.setDisabled(true);
                }
                var ent  = form.down("cmbMeshEntryPoints");
                ent.setValue(b.result.data.entry_points);
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
        var win     = button.up("winMeshEdit");
        var nodes   = win.down("gridNodes");
        nodes.getStore().reload();
    },

    //Initial load of the Advanced settings
    loadAdvancedWifiSettings: function(win){
        var me      = this;
        var form    = win.down('form');
        var hw      = form.down('cmbHardwareOptions');
        var val     = hw.getValue();

        //We have to disable this and hide it upon initial loading
        var tabAdvRadio1 = form.down('#tabAdvWifiRadio1');
        tabAdvRadio1.setDisabled(true);
        tabAdvRadio1.tab.hide();

        form.load({url:me.getUrlAdvancedSettingsForModel(), method:'GET',params:{model:val}});
    },

	cmbHardwareOptionsChange: function(cmb){
		var me      = this;
        var form    = cmb.up('form');
        var key     = form.down('#key');
        var voip    = form.down('#tabVoip');
        var adv     = form.down('#tabVoipAdvanced');
		var radio	= form.down('#tabRadio');
        var val     = cmb.getValue();
        var tabAdvRadio1 = form.down('#tabAdvWifiRadio1');
        var window  = cmb.up('window');

        if(window.getItemId() != 'winMeshEditNodeEdit'){
            //Load the advanced settings for this hardware...
            form.load({url:me.getUrlAdvancedSettingsForModel(), method:'GET',params:{model:val}});
        }else{
            //Include the node_id
            form.load({url:me.getUrlAdvancedSettingsForModel(), method:'GET',params:{model:val,node_id:window.nodeId}});
        }

		if((val == 'mp2_basic')||(val == 'mp2_phone')){
			voip.setDisabled(false);
			adv.setDisabled(false);
			adv.tab.show();
			voip.tab.show();
		}else{
			voip.setDisabled(true);
			adv.setDisabled(true);
			adv.tab.hide();
			voip.tab.hide();
		}

		if(
			(val == 'tl_wdr3500')||
            (val == 'tl_wdr3600')||
			(val == 'alix3d2')||
			(val == 'unifiappro')||
			(val == 'gentworadio')||
            (val == 'rb433')
		){
			radio.setDisabled(false);	
			radio.tab.show();
            tabAdvRadio1.setDisabled(false);
            tabAdvRadio1.tab.show();
		}else{
			radio.setDisabled(true);
			radio.tab.hide();
            tabAdvRadio1.setDisabled(true);
            tabAdvRadio1.tab.hide();
		}
	},
    addNode: function(button){
        var me      = this;
        var win     = button.up("winMeshEdit");
        
        //Entry points present; continue 
        var store   	= win.down("gridNodes").getStore();
        if(!me.application.runAction('cDesktop','AlreadyExist','winMeshAddNodeId')){
            var w = Ext.widget('winMeshAddNode',
            {
                id          :'winMeshAddNodeId',
                store       : store,
                meshId      : win.getItemId(),
				meshName	: win.meshName,
                itemId      : 'winMeshAddNodeEdit'	
            });
            me.application.runAction('cDesktop','Add',w);         
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
        var win     = btn.up("window");
        var grid    = win.down("gridNodes");
    
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
        var win     = button.up("winMeshEdit");
        var store   = win.down("gridNodes").getStore();
        if(win.down("gridNodes").getSelectionModel().getCount() == 0){
             Ext.ux.Toaster.msg(
                        i18n('sSelect_an_item'),
                        i18n('sFirst_select_an_item'),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
            );
        }else{
            var sr      = win.down("gridNodes").getSelectionModel().getLastSelected();
            var id      = sr.getId();
            var meshId  = sr.get('mesh_id');

            if(!me.application.runAction('cDesktop','AlreadyExist','winMeshEditNodeId')){
                var w = Ext.widget('winMeshEditNode',
                {
                    id          :'winMeshEditNodeId',
                    store       : store,
                    nodeId      : id,
                    meshId      : win.getItemId(),
					meshName	: win.meshName,
                    itemId      : 'winMeshEditNodeEdit'
                });
                me.application.runAction('cDesktop','Add',w);         
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

	//___ Dual RADIO _____
	chkRadioEnableChange: function(chk){
		var me 		= this;
		var fs    	= chk.up('panel');//fs
        var value   = chk.getValue();
		var fields_voip = Ext.ComponentQuery.query('field',fs);
		Ext.Array.forEach(fields_voip,function(item){
			if(item != chk){
				item.setDisabled(!value);
			}
		});
	},
	chkRadioMeshChange: function(chk){
		var me 		= this;
		var fs    	= chk.up('panel');//fs
		var t_band	= fs.down('#radio24');
		var n_t		= fs.down('#numRadioTwoChan');
		var n_v		= fs.down('#numRadioFiveChan');

		if(chk.getValue() == false){
			if(t_band.getValue()){	//2.4 selected... show it
				n_t.setVisible(true);
				n_t.setDisabled(false);
				n_v.setVisible(false);
				n_v.setDisabled(true);
			}else{
				n_t.setVisible(false);
				n_t.setDisabled(true);
				n_v.setVisible(true);
				n_v.setDisabled(false);
			}
		}else{
			//hide and disable both
			n_t.setVisible(false);
			n_t.setDisabled(true);
			n_v.setVisible(false);
			n_v.setDisabled(true);
		}		
	},

    radio_0_BandChange: function(rb){
        var me      = this;
        var band    = rb.getValue();      
        var fs      = rb.up('panel');//fs   
        var mesh    = fs.down('#chkRadio0Mesh');
        var t_band	= fs.down('#radio24');
        var n_t		= fs.down('#numRadioTwoChan');
		var n_v		= fs.down('#numRadioFiveChan');
        if(mesh.getValue() == false){
            if(t_band.getValue()){	//2.4 selected... show it
				n_t.setVisible(true);
				n_t.setDisabled(false);
				n_v.setVisible(false);
				n_v.setDisabled(true);
			}else{
				n_t.setVisible(false);
				n_t.setDisabled(true);
				n_v.setVisible(true);
				n_v.setDisabled(false);
			}
        }
    },
    radio_1_BandChange: function(rb){
        var me      = this;
        var band    = rb.getValue();      
        var fs      = rb.up('panel');//fs    
        var mesh    = fs.down('#chkRadio1Mesh');
        var t_band	= fs.down('#radio24');
        var n_t		= fs.down('#numRadioTwoChan');
		var n_v		= fs.down('#numRadioFiveChan');

        if(mesh.getValue() == false){
            if(t_band.getValue()){	//2.4 selected... show it
				n_t.setVisible(true);
				n_t.setDisabled(false);
				n_v.setVisible(false);
				n_v.setDisabled(true);
			}else{
				n_t.setVisible(false);
				n_t.setDisabled(true);
				n_v.setVisible(true);
				n_v.setDisabled(false);
			}
        }
    },

	//____ VOIP _____
	chkSipChange: function(chk){
		var me 		= this;
		var voip    = chk.up('#tabVoip');
        var value   = chk.getValue();
		var fields_voip = Ext.ComponentQuery.query('field',voip);
		Ext.Array.forEach(fields_voip,function(item){
			if(item != chk){
				item.setDisabled(!value);
			}
		});
	},
	chkAsteriskChange: function(chk){
		var me 		= this;
		var voipA   = chk.up('#tabVoipAdvanced');
        var value   = chk.getValue();
		var fields_voip = Ext.ComponentQuery.query('field',voipA);
		Ext.Array.forEach(fields_voip,function(item){
			if(item != chk){
				item.setDisabled(!value);
			}
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
        var me = this
        var tp          = button.up('tabpanel');
        var map_tab_id  = 'mapTab';
        var nt          = tp.down('#'+map_tab_id);
        if(nt){
            tp.setActiveTab(map_tab_id); //Set focus on  Tab
            return;
        }

        var map_tab_name    = i18n("sGoogle_Maps");
		var win 		    = tp.up('winMeshEdit');
		var mesh_id		    = win.meshId;

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
		var win		= button.up('winMeshEdit');
		var mesh_id	= win.getItemId();
		var pref_id = 'winMeshMapPreferences_'+mesh_id;
		var map_p	= win.down('pnlMeshEditGMap');

       	if(!me.application.runAction('cDesktop','AlreadyExist',pref_id)){
            var w = Ext.widget('winMeshMapPreferences',{id:pref_id,mapPanel: map_p,meshId: mesh_id});
            me.application.runAction('cDesktop','Add',w);
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
		var win		= button.up('winMeshEdit');
		var mesh_id	= win.getItemId();
		var add_id  = 'winMeshMapNodeAdd_'+mesh_id;
		var map_p	= win.down('pnlMeshEditGMap');

        if(!me.application.runAction('cDesktop','AlreadyExist',add_id)){
            var w = Ext.widget('winMeshMapNodeAdd',{id: add_id,mapPanel: map_p,meshId:mesh_id});
            me.application.runAction('cDesktop','Add',w);       
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
