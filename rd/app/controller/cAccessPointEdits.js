Ext.define('Rd.controller.cAccessPointEdits', {
    extend: 'Ext.app.Controller',
    views:  [
        'aps.pnlAccessPointEdit',
        'aps.gridAccessPointEntries', 
        'aps.gridAccessPointExits',
        'aps.pnlAccessPointCommonSettings',    
        'aps.gridAccessPointAps',
        
        'aps.winAccessPointAddEntry',   
        'components.cmbEncryptionOptions',
        'aps.winAccessPointEditEntry',
             
        'aps.winAccessPointAddExit',    
        'aps.tagAccessPointEntryPoints',
        'aps.winAccessPointEditExit',
        
        'aps.cmbApHardwareModels',
        'aps.winAccessPointAddAp',
        'aps.winAccessPointEditAp',
        
        'components.cmbTimezones',      
        'components.cmbCountries',
        'components.cmbFiveGigChannels',
        'components.cmbRealm',
        'components.cmbMacFilter',
        'components.cmbPermanentUser'
    ],
    stores      : [	
		'sAccessPointEntries', 'sAccessPointExits', 	'sAps', 'sAccessPointEntryPoints', 'sRealms'
    ],
    models      : [ 
		'mAccessPointEntry',  	'mAccessPointExit', 	'mAp',  'mAccessPointEntryPoint', 'mRealm', 'mPermanentUser'
    ],
    config      : {  
        urlAddEntry:        '/cake2/rd_cake/ap_profiles/ap_profile_entry_add.json',
        urlViewEntry:       '/cake2/rd_cake/ap_profiles/ap_profile_entry_view.json',
        urlEditEntry:       '/cake2/rd_cake/ap_profiles/ap_profile_entry_edit.json',
        
       
        urlExitAddDefaults: '/cake2/rd_cake/ap_profiles/ap_profile_exit_add_defaults.json',
        urlAddExit:         '/cake2/rd_cake/ap_profiles/ap_profile_exit_add.json',
        urlViewExit:        '/cake2/rd_cake/ap_profiles/ap_profile_exit_view.json',
        urlEditExit:        '/cake2/rd_cake/ap_profiles/ap_profile_exit_edit.json',
        
        
        urlViewApCommonSettings:'/cake2/rd_cake/ap_profiles/ap_common_settings_view.json',
        urlEditApCommonSettings:'/cake2/rd_cake/ap_profiles/ap_common_settings_edit.json',
        
        urlAddAp            : '/cake2/rd_cake/ap_profiles/ap_profile_ap_add.json',  
        urlViewAp           : '/cake2/rd_cake/ap_profiles/ap_profile_ap_view.json',
        urlEditAp           : '/cake2/rd_cake/ap_profiles/ap_profile_ap_edit.json',
        
        
		urlMapPrefView		: '/cake2/rd_cake/meshes/map_pref_view.json',
		urlMapPrefEdit		: '/cake2/rd_cake/meshes/map_pref_edit.json',
		urlMapSave			: '/cake2/rd_cake/meshes/map_node_save.json',
		urlMapDelete		: '/cake2/rd_cake/meshes/map_node_delete.json',
		urlMeshNodes		: '/cake2/rd_cake/meshes/mesh_nodes_index.json',
		urlBlueMark 		: 'resources/images/map_markers/blue-dot.png',
		
		urlAdvancedSettingsForModel : '/cake2/rd_cake/ap_profiles/advanced_settings_for_model.json'
    },
    refs: [
    	{  ref: 'editEntryWin', 	selector: 'winAccessPointEditEntry'},
        {  ref: 'editExitWin',  	selector: 'winAccessPointEditExit' },
        {  ref: 'tabAccessPoints',  selector: '#tabAccessPoints'      } 
    ],
    init: function() {
        var me = this;
        
        if (me.inited) {
            return;
        }
        me.inited = true;
        
        
        me.control({
			'gridAccessPointEntries #reload': {
                click:  me.reloadEntry
            },
            'gridAccessPointEntries #add': {
                click:  me.addEntry
            },
            'gridAccessPointEntries #edit': {
                click:  me.editEntry
            },
            'winAccessPointAddEntry cmbEncryptionOptions': {
                change: me.cmbEncryptionChange
            },
            'winAccessPointAddEntry #chk_maxassoc': {
                change: me.chkMaxassocChange
            },
            'winAccessPointAddEntry cmbMacFilter': {
                change: me.cmbMacFilterChange
            },
            'winAccessPointAddEntry #save': {
                click: me.btnAddEntrySave
            },
            'gridAccessPointEntries #delete': {
                click: me.delEntry
            },
            'winAccessPointEditEntry': {
                beforeshow:      me.loadEntry
            },
             'winAccessPointEditEntry cmbEncryptionOptions': {
                change: me.cmbEncryptionChange
            },
            'winAccessPointEditEntry #chk_maxassoc': {
                change: me.chkMaxassocChange
            },
            'winAccessPointEditEntry cmbMacFilter': {
                change: me.cmbMacFilterChange
            },
            'winAccessPointEditEntry #save': {
                click: me.btnEditEntrySave
            },  
            'gridAccessPointExits #reload': {
                click:  me.reloadExit
            },
            'gridAccessPointExits #add': {
                click:  me.addExit
            },
            'winAccessPointAddExit': {
                beforeshow:      me.loadAddExit
            },
            'winAccessPointAddExit #btnTypeNext' : {
                click:  me.btnExitTypeNext
            },
            'winAccessPointAddExit #btnDataPrev' : {
                click:  me.btnExitDataPrev
            },
            'winAccessPointAddExit #save' : {
                click:  me.btnAddExitSave
            },
            'gridAccessPointExits #delete': {
                click: me.delExit
            },
            'gridAccessPointExits #edit': {
                click:  me.editExit
            },
            'winAccessPointEditExit': {
                beforeshow:      me.loadExit
            },
            'winAccessPointEditExit #save': {
                click: me.btnEditExitSave
            },//Common node settings
             //Enable the CoovaChilli transparent proxy settings
            '#chkProxyEnable' : {
                change:  me.chkProxyEnableChange
            },
            'pnlAccessPointEdit #tabAccessPointCommonSettings' : {
                activate:      me.frmApCommonSettingsLoad
            },
            'pnlAccessPointCommonSettings #save': {
                click:  me.btnApCommonSettingsSave
            },
			
            //Here nodes start
            'gridAccessPointAps #reload': {
                click:  me.reloadAps
            },
            'gridAccessPointAps #add': {
                click:  me.addAp
            }, 
            '#winAccessPointAddApEdit #save' : {
                click:  me.btnAddApSave
            },
            'gridAccessPointAps #delete': {
                click: me.delAp
            },
            'gridAccessPointAps #edit': {
                click:  me.editAp
            },
            '#winAccessPointEditApEdit': {
                beforeshow:      me.loadAp
            },
            '#winAccessPointEditApEdit #save': {
                click: me.btnEditApSave
            },
			//RADIOs Choices
            //Add
            '#winAccessPointAddApEdit #chkRadio0Enable'	: {
				change	: me.chkRadioEnableChange
			},
			'#winAccessPointAddApEdit #chkRadio1Enable' : {
				change	: me.chkRadioEnableChange
			},
            '#winAccessPointAddApEdit radio[name=radio0_band]' : {
                change  : me.radio_0_BandChange  
            },
            '#winAccessPointAddApEdit radio[name=radio1_band]' : {
                change  : me.radio_1_BandChange  
            },
            '#winAccessPointAddApEdit cmbApHardwareModels': {
                change: me.cmbApHardwareModelsChange
            },
            '#winAccessPointAddApEdit' : {
                beforeshow:  me.loadAdvancedWifiSettings
            },
            
            //Edit
			'#winAccessPointEditApEdit #chkRadio0Enable'	: {
				change	: me.chkRadioEnableChange
			},
			'#winAccessPointEditApEdit #chkRadio1Enable' : {
				change	: me.chkRadioEnableChange
			},
            '#winAccessPointEditApEdit radio[name=radio0_band]' : {
                change  : me.radio_0_BandChange  
            },
            '#winAccessPointEditApEdit radio[name=radio1_band]' : {
                change  : me.radio_1_BandChange  
            },
            '#winAccessPointEditApEdit cmbApHardwareModels': {
                change: me.cmbApHardwareModelsChange
            },
            
            'winAccessPointAddExit #chkNasClient' : {
				change	: me.chkNasClientChange
			},
			'winAccessPointEditExit #chkNasClient' : {
				change	: me.chkNasClientChange
			},  
            'winAccessPointAddExit #chkLoginPage' : {
				change	: me.chkLoginPageChange
			},
			'winAccessPointEditExit #chkLoginPage' : {
				change	: me.chkLoginPageChange
			}
        });
    },
    actionIndex: function(ap_profile_id,name){
        var me              = this;
        var id		        = 'tabAccessPoint'+ ap_profile_id;
        var tabAccessPoints = me.getTabAccessPoints();
        var newTab  = tabAccessPoints.items.findBy(
            function (tab){
                return tab.getItemId() === id;
            });
         
        if (!newTab){
            newTab = tabAccessPoints.add({
                glyph   : Rd.config.icnEdit, 
                title   : name,
                closable: true,
                layout  : 'fit',
                xtype   : 'pnlAccessPointEdit',
                itemId  : id,
                ap_profile_id : ap_profile_id
            });
        }    
        tabAccessPoints.setActiveTab(newTab);
    },
	reloadEntry: function(button){
        var me      = this;
        var pnl     = button.up("pnlAccessPointEdit");//
        var entGrid = pnl.down("gridAccessPointEntries");
        entGrid.getStore().reload();
    },
    addEntry: function(button){
        var me      = this;
        var pnl     = button.up("pnlAccessPointEdit");
        var store   = pnl.down("gridAccessPointEntries").getStore();
       
        if(!Ext.WindowManager.get('winAccessPointAddEntryId')){
            var w = Ext.widget('winAccessPointAddEntry',
            {
                id          :'winAccessPointAddEntryId',
                store       : store,
                apProfileId : pnl.ap_profile_id
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
        var win     = button.up("winAccessPointAddEntry");
        var form    = win.down('form');
        form.submit({
            clientValidation: true,
            url: me.getUrlAddEntry(),
            success: function(form, action) {
                win.store.load();
                win.close();
                Ext.ux.Toaster.msg(
                        i18n("sItem_added_fine"),
                        i18n('New Access Point SSID created fine'),
                        Ext.ux.Constants.clsInfo,
                        Ext.ux.Constants.msgInfo
                );
            },
            scope       : me,
            failure     : 'formFailure'
        });
    },
    editEntry: function(button){  
        var me      = this;
        var pnl     = button.up("pnlAccessPointEdit");
        var store   = pnl.down("gridAccessPointEntries").getStore();  
        if(pnl.down("gridAccessPointEntries").getSelectionModel().getCount() == 0){
            Ext.ux.Toaster.msg(
                        i18n('sSelect_an_item'),
                        i18n('sFirst_select_an_item'),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
            );    
        }else{
            var sr      = pnl.down("gridAccessPointEntries").getSelectionModel().getLastSelected();
            var id      = sr.getId();
            
            if(!Ext.WindowManager.get('winAccessPointEditEntryId')){
                var w = Ext.widget('winAccessPointEditEntry',
                {
                    id          :'winAccessPointEditEntryId',
                    store       : store,
                    entryId     : id
                });
                w.show();         
            }else{
                var w       = Ext.WindowManager.get('winAccessPointEditEntryId');
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
        var win     = button.up("winAccessPointEditEntry");
        var form    = win.down('form');
        form.submit({
            clientValidation: true,
            url: me.getUrlEditEntry(),
            success: function(form, action) {
                win.close();
                win.store.load();
                Ext.ux.Toaster.msg(
                        i18n("sItem_updated_fine"),
                        i18n("sItem_updated_fine"),
                        Ext.ux.Constants.clsInfo,
                        Ext.ux.Constants.msgInfo
                );
            },
            scope       : me,
            failure     : Ext.ux.formFail
        });
    },
    delEntry:   function(button){
        var me      = this;
        var pnl     = button.up("pnlAccessPointEdit");
        var grid    = pnl.down("gridAccessPointEntries");
    
        //Find out if there was something selected
        if(grid.getSelectionModel().getCount() == 0){
            Ext.ux.Toaster.msg(
                        i18n('sSelect_an_item'),
                        i18n('sFirst_select_an_item'),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
            );   
        }else{     
            Ext.Msg.show({
                 title      : i18n("sConfirm"),
                 msg        : i18n("sAre_you_sure_you_want_to_do_that_qm"),
                 buttons    : Ext.Msg.YESNO,
                 icon       : Ext.Msg.QUESTION,
                 callback   :function(btn) {
                    if('yes' === btn) {
                        grid.getStore().remove(grid.getSelectionModel().getSelection());
                        grid.getStore().sync({
                            success: function(batch,options){
                                Ext.ux.Toaster.msg(
                                        i18n("sItem_deleted_fine"),
                                        i18n("sItem_deleted_fine"),
                                        Ext.ux.Constants.clsInfo,
                                        Ext.ux.Constants.msgInfo
                                );
                            },
                            failure: function(batch,options,c,d){
                                Ext.ux.Toaster.msg(
                                            i18n('sError_encountered'),
                                            batch.proxy.getReader().rawData.message.message,
                                            Ext.ux.Constants.clsWarn,
                                            Ext.ux.Constants.msgWarn
                                );
                                grid.getStore().load(); //Reload from server since the sync was not good
                            }
                        });
                    }
                }
            });
        }
    },
    
    chkNasClientChange: function(chk){
        var me          = this;
        var form        = chk.up('form');
        var cmb_realm   = form.down('#cmbRealm');
        if(chk.getValue()){	
			cmb_realm.setVisible(true);
			cmb_realm.setDisabled(false);
		}else{
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
        var pnl     = button.up("pnlAccessPointEdit");
        var exit    = pnl.down("gridAccessPointExits");
        exit.getStore().reload();
    },
    loadAddExit: function(win){
        var me      = this; 
        var form    = win.down('#scrnData');
        form.load({url:me.getUrlExitAddDefaults(), method:'GET'});
    },
    addExit: function(button){
        var me      = this;
        var pnl     = button.up("pnlAccessPointEdit");
        //If there are NO entry points defined; we will NOT pop up this window.
        var entries_count   = pnl.down("gridAccessPointEntries").getStore().count();
        if(entries_count == 0){
            Ext.ux.Toaster.msg(
                        i18n("sDefine_some_entry_points_first"),
                        i18n("sDefine_some_entry_points_first"),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
            );     
            return;
        }
        
        //Entry points present; continue 
        var store   = pnl.down("gridAccessPointExits").getStore();
        if(!Ext.WindowManager.get('winAccessPointAddExitId')){
            var w = Ext.widget('winAccessPointAddExit',
            {
                id              :'winAccessPointAddExitId',
                store           : store,
                apProfileId     : pnl.ap_profile_id
            });
            w.show();        
        }
    },
    btnExitTypeNext: function(button){
        var me      = this;
        var win     = button.up('winAccessPointAddExit');
        var type    = win.down('#rgrpExitType').getValue().exit_type;
        var vlan    = win.down('#vlan');
        var tab_capt= win.down('#tabCaptivePortal');
        var sel_type= win.down('#type');
        var vpn     = win.down('#cmbOpenVpnServers') 
        var a_nas   = win.down('#chkNasClient');
        var cmb_realm = win.down('#cmbRealm');
        var a_page  = win.down('#chkLoginPage');
        var cmb_page= win.down('cmbDynamicDetail');
        
        //#rgrpProtocol #txtIpaddr #txtNetmask #txtGateway #txtDns1 #txtDns2
        var rgrpProtocol= win.down('#rgrpProtocol');
        var txtIpaddr   = win.down('#txtIpaddr');
        var txtNetmask  = win.down('#txtNetmask');
        var txtGateway  = win.down('#txtGateway');
        var txtDns1     = win.down('#txtDns1');
        var txtDns2     = win.down('#txtDns2');
        var tagConWith  = win.down('tagAccessPointEntryPoints');
        
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
        
        if(type == 'tagged_bridge_l3'){
            vlan.setVisible(true);
            vlan.setDisabled(false);
            rgrpProtocol.setVisible(true);
            rgrpProtocol.setDisabled(false);
            
            if(rgrpProtocol.getValue().proto == 'static'){         
                txtIpaddr.setVisible(true);
			    txtIpaddr.setDisabled(false);
                txtNetmask.setVisible(true);
                txtNetmask.setDisabled(false);  
                txtGateway.setVisible(true);
                txtGateway.setDisabled(false);     
                txtDns1.setVisible(true);
                txtDns1.setDisabled(false);
                txtDns2.setVisible(true);  
                txtDns2.setDisabled(false);
            }else{
                txtIpaddr.setVisible(false);
			    txtIpaddr.setDisabled(true);
                txtNetmask.setVisible(false);
                txtNetmask.setDisabled(true);  
                txtGateway.setVisible(false);
                txtGateway.setDisabled(true);     
                txtDns1.setVisible(false);
                txtDns1.setDisabled(true);
                txtDns2.setVisible(false);  
                txtDns2.setDisabled(true);
            }
            tagConWith.setVisible(false);
            tagConWith.setDisabled(true);
            
        }else{
            //vlan.setVisible(false);
            //vlan.setDisabled(true);
            rgrpProtocol.setVisible(false);
            rgrpProtocol.setDisabled(true);
            txtIpaddr.setVisible(false);
			txtIpaddr.setDisabled(true);
            txtNetmask.setVisible(false);
            txtNetmask.setDisabled(true);  
            txtGateway.setVisible(false);
            txtGateway.setDisabled(true);     
            txtDns1.setVisible(false);
            txtDns1.setDisabled(true);
            txtDns2.setVisible(false);  
            txtDns2.setDisabled(true);
            
            tagConWith.setVisible(true);
            tagConWith.setDisabled(false);
        } 
        win.getLayout().setActiveItem('scrnData');
    },
    
    btnExitDataPrev: function(button){
        var me      = this;
        var win     = button.up('winAccessPointAddExit');
        win.getLayout().setActiveItem('scrnType');
    },
    btnAddExitSave: function(button){
        var me      = this;
        var win     = button.up("winAccessPointAddExit");
        var form    = win.down('#scrnData');
        form.submit({
            clientValidation: true,
            url: me.getUrlAddExit(),
            submitEmptyText: false,
            success: function(form, action) {
                win.close();
                win.store.load();
                Ext.ux.Toaster.msg(
                        i18n("sItem_added_fine"),
                        i18n("sItem_added_fine"),
                        Ext.ux.Constants.clsInfo,
                        Ext.ux.Constants.msgInfo
                );
                
            },
            scope       : me,
            failure     : Ext.ux.formFail
        });
    },
    delExit:   function(button){
        var me      = this;
        var pnl     = button.up("pnlAccessPointEdit");
        var grid    = pnl.down("gridAccessPointExits");
    
        //Find out if there was something selected
        if(grid.getSelectionModel().getCount() == 0){
            Ext.ux.Toaster.msg(
                        i18n('sSelect_an_item'),
                        i18n('sFirst_select_an_item'),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
            );         
        }else{   
            Ext.Msg.show({
                 title      : i18n("sConfirm"),
                 msg        : i18n("sAre_you_sure_you_want_to_do_that_qm"),
                 buttons    : Ext.Msg.YESNO,
                 icon       : Ext.Msg.QUESTION,
                 callback   :function(btn) {
                    if('yes' === btn) {
                        grid.getStore().remove(grid.getSelectionModel().getSelection());
                        grid.getStore().sync({
                            success: function(batch,options){                             
                                Ext.ux.Toaster.msg(
                                        i18n("sItem_deleted_fine"),
                                        i18n("sItem_deleted_fine"),
                                        Ext.ux.Constants.clsInfo,
                                        Ext.ux.Constants.msgInfo
                                );
                            },
                            failure: function(batch,options,c,d){
                                Ext.ux.Toaster.msg(
                                            i18n('sError_encountered'),
                                            batch.proxy.getReader().rawData.message.message,
                                            Ext.ux.Constants.clsWarn,
                                            Ext.ux.Constants.msgWarn
                                );
                                grid.getStore().load(); //Reload from server since the sync was not good
                            }
                        });

                    }
                }
            });
        }
    },
    editExit: function(button){
        var me      = this;
        var pnl     = button.up("pnlAccessPointEdit");
        var store   = pnl.down("gridAccessPointExits").getStore();
        
        if(pnl.down("gridAccessPointExits").getSelectionModel().getCount() == 0){
            Ext.ux.Toaster.msg(
                        i18n('sSelect_an_item'),
                        i18n('sFirst_select_an_item'),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
            );     
        }else{
            var sr          = pnl.down("gridAccessPointExits").getSelectionModel().getLastSelected();
            var id          = sr.getId();
            var apProfileId = sr.get('ap_profile_id');
            var type        = sr.get('type');
            
            
            if(!Ext.WindowManager.get('winAccessPointEditExitId')){
                var w = Ext.widget('winAccessPointEditExit',
                {
                    id          :'winAccessPointEditExitId',
                    store       : store,
                    exitId      : id,
                    apProfileId : apProfileId,
                    type        : type
                });
                w.show(); 
                        
            }else{
                var w           = Ext.WindowManager.get('winAccessPointEditExitId');
                var vlan        = w.down('#vlan');
                var tab_capt    = w.down('#tabCaptivePortal');
                w.exitId        = id;
                w.apProfileId   = apProfileId;
                var vpn         = w.down('#cmbOpenVpnServers'); 
                
                var a_nas       = w.down('#chkNasClient');
                var a_page      = w.down('#chkLoginPage');
                var cmb_page    = w.down('cmbDynamicDetail');
                
                              
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
					
                }else{
                    tab_capt.setDisabled(true);
					tab_capt.tab.hide(); 
					
					a_nas.setVisible(false);
			        a_nas.setDisabled(true);
			        a_page.setVisible(false);
			        a_page.setDisabled(true);
			        cmb_page.setVisible(false);
			        cmb_page.setDisabled(true);	
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
            
                var t           = form.down("#type");
                var t_val       = t.getValue();
                var vlan        = form.down('#vlan');  
                var vpn         = form.down('#cmbOpenVpnServers') 
                 
                var rgrpProtocol= form.down('#rgrpProtocol');
                var txtIpaddr   = form.down('#txtIpaddr');
                var txtNetmask  = form.down('#txtNetmask');
                var txtGateway  = form.down('#txtGateway');
                var txtDns1     = form.down('#txtDns1');
                var txtDns2     = form.down('#txtDns2');
                var tagConWith  = form.down('tagAccessPointEntryPoints');
                
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
                }else{
                    vlan.setVisible(false);
                    vlan.setDisabled(true);
                }
                var ent  = form.down("tagAccessPointEntryPoints");
                ent.setValue(b.result.data.entry_points);
                if(b.result.data.type == 'captive_portal'){
                    //Login Page (Dynamic Detail)
                    if((b.result.data.auto_login_page == true)&&
                    (b.result.data.dynamic_detail != null)){
                        var cmb     = form.down("cmbDynamicDetail");
                        var rec     = Ext.create('Rd.model.mDynamicDetail', {name: b.result.data.dynamic_detail, id: b.result.data.dynamic_detail_id});
                        cmb.getStore().loadData([rec],false);
                        cmb.setValue( b.result.data.dynamic_detail_id );
                    }else{
                        form.down("cmbDynamicDetail").setVisible(false);
                        form.down("cmbDynamicDetail").setDisabled(true);
                    }
                    //Realms for Dynamic Client (auto_dynamic_client)
                    if((b.result.data.auto_dynamic_client == true)&&
                    (b.result.data.realm_records != null)){    
                        var cmb_r     = form.down("cmbRealm");
                        var record_list = [];
                        Ext.Array.forEach(b.result.data.realm_records,function(r){
                            var rec = Ext.create('Rd.model.mRealm', {name: r.name, id: r.id});
			                Ext.Array.push(record_list,rec);
		                });
                        cmb_r.getStore().loadData(record_list,false);
                        cmb_r.setValue(b.result.data.realm_ids);
                    }else{
                        form.down("cmbRealm").setVisible(false);
                        form.down("cmbRealm").setDisabled(true);
                    }    
                }
                
                if(b.result.data.type == 'tagged_bridge_l3'){
                
                    vlan.setVisible(true);
                    vlan.setDisabled(false);
                    rgrpProtocol.setVisible(true);
                    rgrpProtocol.setDisabled(false);
                    
                    if(rgrpProtocol.getValue().proto == 'static'){         
                        txtIpaddr.setVisible(true);
			            txtIpaddr.setDisabled(false);
                        txtNetmask.setVisible(true);
                        txtNetmask.setDisabled(false);  
                        txtGateway.setVisible(true);
                        txtGateway.setDisabled(false);     
                        txtDns1.setVisible(true);
                        txtDns1.setDisabled(false);
                        txtDns2.setVisible(true);  
                        txtDns2.setDisabled(false);
                    }else{
                        txtIpaddr.setVisible(false);
			            txtIpaddr.setDisabled(true);
                        txtNetmask.setVisible(false);
                        txtNetmask.setDisabled(true);  
                        txtGateway.setVisible(false);
                        txtGateway.setDisabled(true);     
                        txtDns1.setVisible(false);
                        txtDns1.setDisabled(true);
                        txtDns2.setVisible(false);  
                        txtDns2.setDisabled(true);
                    }
                    tagConWith.setVisible(false);
                    tagConWith.setDisabled(true);
                    
                }else{
                
                    rgrpProtocol.setVisible(false);
                    rgrpProtocol.setDisabled(true);
                    txtIpaddr.setVisible(false);
			        txtIpaddr.setDisabled(true);
                    txtNetmask.setVisible(false);
                    txtNetmask.setDisabled(true);  
                    txtGateway.setVisible(false);
                    txtGateway.setDisabled(true);     
                    txtDns1.setVisible(false);
                    txtDns1.setDisabled(true);
                    txtDns2.setVisible(false);  
                    txtDns2.setDisabled(true);
                    
                    tagConWith.setVisible(true);
                    tagConWith.setDisabled(false);
                }    
            }
        });
    },
    btnEditExitSave:  function(button){
        var me      = this;
        var win     = button.up("winAccessPointEditExit");
        var form    = win.down('form');
        form.submit({
            clientValidation: true,
            url: me.getUrlEditExit(),
            submitEmptyText: false,
            success: function(form, action) {
                win.close();
                win.store.load();
                Ext.ux.Toaster.msg(
                        i18n("sItem_updated_fine"),
                        i18n("sItem_updated_fine"),
                        Ext.ux.Constants.clsInfo,
                        Ext.ux.Constants.msgInfo
                );
            },
            scope       : me,
            failure     : Ext.ux.formFail
        });
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
    //Common ap settings
    frmApCommonSettingsLoad: function(tab){
        var me          = this;
        var form        = tab.down('form');
        var apProfileId = tab.apProfileId;
        form.load({url:me.getUrlViewApCommonSettings(), method:'GET',params:{ap_profile_id:apProfileId}});
    },
    btnApCommonSettingsSave: function(button){
        var me          = this;
        var form        = button.up('form');
        var tab         = button.up('#tabAccessPointCommonSettings');
        var apProfileId = tab.apProfileId;
        form.submit({
            clientValidation    : true,
            url                 : me.getUrlEditApCommonSettings(),
            params              : {ap_profile_id: apProfileId},
            success: function(form, action) {
                Ext.ux.Toaster.msg(
                        i18n("sItem_updated_fine"),
                        i18n("sItem_updated_fine"),
                        Ext.ux.Constants.clsInfo,
                        Ext.ux.Constants.msgInfo
                );
            },
            scope       : me,
            failure     : Ext.ux.formFail
        });
    },
    
    
    //Aps related
   reloadAps: function(button){
        var me      = this;
        var pnl     = button.up("pnlAccessPointEdit");
        var aps     = pnl.down("gridAccessPointAps");
        aps.getStore().reload();
    },
    
	cmbApHardwareModelsChange: function(cmb){
		var me              = this;
        var form            = cmb.up('form');
        var val             = cmb.getValue();
		 
        var r_count         = 1;  
        var record          = cmb.getSelection();
        if(record != null){
            r_count =record.get('radios');
        }
        
        if(r_count == 0){
            form.down('#tabAdvanced').setDisabled(true);
            form.down('#tabAdvanced').tab.hide();
            form.down('#tabRadio').setDisabled(true);
            form.down('#tabRadio').tab.hide();
            return;
        }else{
            form.down('#tabAdvanced').setDisabled(false);
            form.down('#tabAdvanced').tab.show();
            form.down('#tabRadio').setDisabled(false);
            form.down('#tabRadio').tab.show();
        }
        
        var tabRadiosRadio1	= form.down('#tabRadiosRadio1');
        var tabAdvRadio1    = form.down('#tabAdvWifiRadio1');
        var window          = cmb.up('window');
        
        if(window.getItemId() != 'winAccessPointEditApEdit'){
            //Load the advanced settings for this hardware...
            form.load({url:me.getUrlAdvancedSettingsForModel(), method:'GET',params:{model:val}});
        }else{
            //Include the ap_id
            form.load({url:me.getUrlAdvancedSettingsForModel(), method:'GET',params:{model:val,ap_id:window.apId}});
        }
        
		if(r_count == 2){
			tabRadiosRadio1.setDisabled(false);	
			tabRadiosRadio1.tab.show();
			tabAdvRadio1.setDisabled(false);
            tabAdvRadio1.tab.show();
		}else{
			tabRadiosRadio1.setDisabled(true);
			tabRadiosRadio1.tab.hide();
			tabAdvRadio1.setDisabled(true);
            tabAdvRadio1.tab.hide();
		}
	},
	
	 //Initial load of the Advanced settings
    loadAdvancedWifiSettings: function(win){
        var me      = this;
        var form    = win.down('form');
        var hw      = form.down('cmbApHardwareModels');
        var val     = hw.getValue();

        //We have to disable this and hide it upon initial loading
        var tabAdvRadio1 = form.down('#tabAdvWifiRadio1');
        tabAdvRadio1.setDisabled(true);
        tabAdvRadio1.tab.hide(); 
        
        var tabRadiosRadio1	= form.down('#tabRadiosRadio1');
        tabRadiosRadio1.setDisabled(true);
        tabRadiosRadio1.tab.hide();    
            
        form.load({url:me.getUrlAdvancedSettingsForModel(), method:'GET',params:{model:val}});
    },
	
    addAp: function(button){
        var me      = this;
        var pnl     = button.up("pnlAccessPointEdit");
        
        //Entry points present; continue 
        var store   	= pnl.down("gridAccessPointAps").getStore();
        
        if(!Ext.WindowManager.get('winAccessPointAddApId')){
            var w = Ext.widget('winAccessPointAddAp',
            {
                id              :'winAccessPointAddApId',
                store           : store,
                apProfileId     : pnl.ap_profile_id,
				apProfileName	: pnl.title,
                itemId          : 'winAccessPointAddApEdit'	
            });
            w.show();         
        }
    },
    btnAddApSave: function(button){
        var me      = this;
        var win     = button.up("winAccessPointAddAp");
        var form    = win.down('form');
        form.submit({
            clientValidation: true,
            url: me.getUrlAddAp(),
            success: function(form, action) {
                win.close();
                win.store.load();
                Ext.ux.Toaster.msg(
                        i18n("sItem_added_fine"),
                        i18n("sItem_added_fine"),
                        Ext.ux.Constants.clsInfo,
                        Ext.ux.Constants.msgInfo
                );
            },
            scope       : me,
            failure     : Ext.ux.formFail
        });
    },
    delAp:   function(button){
        var me      = this;
        var pnl     = button.up("pnlAccessPointEdit");
        var grid    = pnl.down("gridAccessPointAps");
    
        //Find out if there was something selected
        if(grid.getSelectionModel().getCount() == 0){
            Ext.ux.Toaster.msg(
                        i18n('sSelect_an_item'),
                        i18n('sFirst_select_an_item'),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
            );   
        }else{
            Ext.Msg.show({
                 title      : i18n("sConfirm"),
                 msg        : i18n("sAre_you_sure_you_want_to_do_that_qm"),
                 buttons    : Ext.Msg.YESNO,
                 icon       : Ext.Msg.QUESTION,
                 callback   :function(btn) {
                    if('yes' === btn) {

                        grid.getStore().remove(grid.getSelectionModel().getSelection());
                        grid.getStore().sync({
                            success: function(batch,options){
                                Ext.ux.Toaster.msg(
                                        i18n("sItem_deleted_fine"),
                                        i18n("sItem_deleted_fine"),
                                        Ext.ux.Constants.clsInfo,
                                        Ext.ux.Constants.msgInfo
                                );
                            },
                            failure: function(batch,options,c,d){
                                Ext.ux.Toaster.msg(
                                            i18n('sError_encountered'),
                                            batch.proxy.getReader().rawData.message.message,
                                            Ext.ux.Constants.clsWarn,
                                            Ext.ux.Constants.msgWarn
                                );
                                grid.getStore().load(); //Reload from server since the sync was not good
                            }
                        });

                    }
                }
            });
        }
    },
    editAp: function(button){
        var me      = this;
        var pnl     = button.up("pnlAccessPointEdit");
        var store   = pnl.down("gridAccessPointAps").getStore();
        if(pnl.down("gridAccessPointAps").getSelectionModel().getCount() == 0){
            Ext.ux.Toaster.msg(
                        i18n('sSelect_an_item'),
                        i18n('sFirst_select_an_item'),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
            );   
            
        }else{
            var sr          = pnl.down("gridAccessPointAps").getSelectionModel().getLastSelected();
            var id          = sr.getId();
            var apProfileId = sr.get('ap_profile_id');

            if(!Ext.WindowManager.get('winAccessPointEditApId')){
                var w = Ext.widget('winAccessPointEditAp',
                {
                    id              :'winAccessPointEditApId',
                    store           : store,
                    apId            : id,
                    apProfileId     : pnl.ap_profile_id,
					apProfileName	: pnl.title,
                    itemId          : 'winAccessPointEditApEdit'
                });
                w.show();          
            }
        }
    },
    loadAp: function(win){
        var me      = this; 
        var form    = win.down('form');
        var apId    = win.apId;
        form.load({url:me.getUrlViewAp(), method:'GET',params:{ap_id:apId}});
        
        //We have to disable this and hide it upon initial loading
        var tabAdvRadio1 = form.down('#tabAdvWifiRadio1');
        tabAdvRadio1.setDisabled(true);
        tabAdvRadio1.tab.hide();
        
        var tabRadiosRadio1	= form.down('#tabRadiosRadio1');
        tabRadiosRadio1.setDisabled(true);
        tabRadiosRadio1.tab.hide();    
        
    },
    btnEditApSave:  function(button){
        var me      = this;
        var win     = button.up("winAccessPointEditAp");
        var form    = win.down('form');
        form.submit({
            clientValidation: true,
            url: me.getUrlEditAp(),
            success: function(form, action) {
                win.close();
                win.store.load();
                Ext.ux.Toaster.msg(
                        i18n("sItem_updated_fine"),
                        i18n("sItem_updated_fine"),
                        Ext.ux.Constants.clsInfo,
                        Ext.ux.Constants.msgInfo
                );
                
            },
            scope       : me,
            failure     : Ext.ux.formFail
        });
    },

	//___ RADIO settings _____
	chkRadioEnableChange: function(chk){
		var me 		= this;
		var fs    	= chk.up('panel');//fs
        var value   = chk.getValue();
		var fields = Ext.ComponentQuery.query('field',fs);
		Ext.Array.forEach(fields,function(item){
			if(item != chk){
				item.setDisabled(!value);
			}
		});
	},
	
    radio_0_BandChange: function(rb){
        var me      = this;
        var band    = rb.getValue();      
        var fs      = rb.up('panel');//fs   
        var t_band	= fs.down('#radio24');
        var n_t		= fs.down('#numRadioTwoChan');
		var n_v		= fs.down('#numRadioFiveChan');
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
    },
    radio_1_BandChange: function(rb){
        var me      = this;
        var band    = rb.getValue();      
        var fs      = rb.up('panel');//fs    
        var t_band	= fs.down('#radio24');
        var n_t		= fs.down('#numRadioTwoChan');
		var n_v		= fs.down('#numRadioFiveChan');
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
    },
    
	//____ MAP ____

    mapLoadApi:   function(button){
        var me 	= this;
        Ext.ux.Toaster.msg(
                        i18n("sLoading_Google_Maps_API"),
                        i18n("sLoading_Google_Maps_API"),
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
        var pnl         = button.up('pnlAccessPointEdit');
        var map_tab_id  = 'mapTab';
        var nt          = pnl.down('#'+map_tab_id);
        if(nt){
            pnl.setActiveTab(map_tab_id); //Set focus on  Tab
            return;
        }

        var map_tab_name    = i18n("sGoogle_Maps");
		var mesh_id		    = pnl.mesh_id

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
                    pnl.add({ 
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
                    pnl.setActiveTab(map_tab_id); //Set focus on Add Tab
                    //____________________________________________________   
                }   
            },
			failure: function(batch,options){
			    Ext.ux.Toaster.msg(
                        i18n('sError_encountered'),
                        i18n('sMap_preferences_could_not_be_fetched'),
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
                            i18n("sItem_deleted_fine"),
                            i18n("sItem_deleted_fine"),
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
                            i18n("sItem_updated_fine"),
                            i18n("sItem_updated_fine"),
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
		var pnl		= button.up('pnlMeshEdit');
		var mesh_id	= pnl.mesh_id;
		var pref_id = 'winMeshMapPreferences_'+mesh_id;
		var map_p	= pnl.down('pnlMeshEditGMap');

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
		var pnl		= button.up('pnlMeshEdit');
		var mesh_id	= pnl.mesh_id;
		var add_id  = 'winMeshMapNodeAdd_'+mesh_id;
		var map_p	= pnl.down('pnlMeshEditGMap');

        if(!Ext.WindowManager.get(add_id)){
            var w = Ext.widget('winMeshMapNodeAdd',{id: add_id,mapPanel: map_p,meshId:mesh_id});
            w.show();     
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
            title: i18n("sNew_marker"),
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
                            i18n("sItem_updated_fine"),
                            i18n("sItem_updated_fine"),
                            Ext.ux.Constants.clsInfo,
                            Ext.ux.Constants.msgInfo
                    );
            },
            scope       : me,
            failure     : Ext.ux.formFail
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
