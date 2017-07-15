Ext.define('Rd.controller.cAccessPoints', {
    extend: 'Ext.app.Controller',
    actionIndex: function(pnl){
        var me      = this; 
    
        if (me.populated) {
            return; 
        }     
        pnl.add({
            xtype   : 'tabpanel',
            border  : true,
            itemId  : 'tabAccessPoints',
            items   : [
                {   
                    xtype       : 'gridApProfiles',
                    title       : i18n('sAccess_Point_Profiles'), 
                    glyph       : Rd.config.icnProfile   
                },
                { 
                    xtype       : 'gridApLists',  
                    title       : i18n('sAttached_Devices'),
                    glyph       : Rd.config.icnChain
                },
                {  
                    xtype       : 'gridUnknownAps', 
                    title       : i18n('sDetached_Devices'),
                    glyph       : Rd.config.icnChainBroken
                }	
            ]
        });
        me.populated = true;
    },

    views:  [
        'aps.gridApProfiles', 
        'aps.gridApLists', 
        'aps.gridUnknownAps', 
        'aps.winAccessPointAttachAp',
        'aps.cmbApHardwareModels',
        'aps.winApProfileAddWizard',
        'aps.winAccessPointEditAp', 
        'aps.winAccessPointAddAp', 
        'components.cmbDynamicDetail',
        'components.winHardwareAddAction',
        'aps.winApUnknownRedirect'
    ],
    stores: ['sAccessProvidersTree', 'sUnknownAps', 'sApProfiles', 'sApLists'  ],
    models: ['mAccessProviderTree',  'mUnknownAp',  'mApProfile',  'mApList', 'mDynamicDetail' ],
    selectedRecord: null,
    config: {
        urlApChildCheck : '/cake3/rd_cake/access-providers/child-check.json',
        urlAdd          : '/cake2/rd_cake/ap_profiles/add.json',
        urlDelete       : '/cake2/rd_cake/ap_profiles/delete.json',
        
        urlAddAp        : '/cake2/rd_cake/ap_profiles/ap_profile_ap_add.json',
        urlViewAp       : '/cake2/rd_cake/ap_profiles/ap_profile_ap_view.json',
        urlEditAp       : '/cake2/rd_cake/ap_profiles/ap_profile_ap_edit.json',
        urlAdvancedSettingsForModel : '/cake2/rd_cake/ap_profiles/advanced_settings_for_model.json',
        urlNoteAdd      : '/cake2/rd_cake/ap_profiles/note_add.json',
        urlApProfileAddApAction :  '/cake2/rd_cake/ap_actions/add.json',
        urlRestartAps   : '/cake2/rd_cake/ap_actions/restart_aps.json',
        urlRedirectAp   : '/cake2/rd_cake/aps/redirect_unknown.json'
    },
    refs: [
        {  ref: 'grid',         selector: 'gridApProfiles'},
        {  ref: 'gridApLists',  selector: 'gridApLists'}      
    ],
    init: function() {
        var me = this;
        
        if (me.inited) {
            return;
        }
        me.inited = true;
        
        me.control({
            '#tabAccessPoints'    : {
                destroy   :      me.appClose
            },
			'#tabAccessPoints gridApProfiles' : {
				activate	: me.gridActivate
			},
			'#tabAccessPoints gridApLists' : {
				activate	: me.gridActivate
			},
            '#tabAccessPoints gridUnknownAps' : {
				activate	: me.gridActivate
			},
            'gridApProfiles #reload': {
                click:      me.reload
            },
            'gridApProfiles #reload menuitem[group=refresh]'   : {
                click:      me.reloadOptionClick
            },
            'gridApProfiles #add'   : {
                click:      me.add
            },
            'gridApProfiles #delete'   : {
                click:      me.del
            },
            'gridApProfiles #edit'   : {
                click:      me.edit
            },
            'gridApProfiles'  : {
                select:      me.select
            },
            
            'winApProfileAddWizard #btnTreeNext' : {
                click:  me.btnTreeNext
            },
            'winApProfileAddWizard #btnDataPrev' : {
                click:  me.btnDataPrev
            },
            'winApProfileAddWizard #btnDataNext' : {
                click:  me.btnDataNext
            },
            
            
			'gridUnknownAps #reload': {
                click:      me.gridUnknownApsReload
            },
            'gridUnknownAps #reload menuitem[group=refresh]'   : {
                click:      me.reloadUnknownApsOptionClick
            },  
			'gridUnknownAps #attach': {
                click:  me.attachAp
            },
			'winAccessPointAttachAp cmbHardwareOptions': {
                change: me.cmbHardwareOptionsChange
            },
			'winAccessPointAttachAp #saveAttach' : {
				click: me.btnAttachApSave
			},
			'gridUnknownAps #delete': {
                click: me.delUnknownAp
            },
            'gridUnknownAps #redirect' : {
                click: me.redirectAp
            },
            'winApUnknownRedirect #save' : {
				click: me.btnRedirectApSave
			},
            
            //Known aps
			'gridApLists #reload': {
                click:      me.gridApListsReload
            },
            'gridApLists #reload menuitem[group=refresh]'   : {
                click:      me.reloadApListsOptionClick
            }, 
			'gridApLists #add': {
                click:  me.addAp
            },        
            '#winAccessPointAddApMain #save' : {
                click:  me.btnAddApSave
            },
            'gridApLists #delete': {
                click: me.delAp
            },
            'gridApLists #edit': {
                click:  me.editAp
            },
            '#winAccessPointEditApMain': {
                beforeshow:      me.loadAp
            },
            '#winAccessPointEditApMain #save': {
                click: me.btnEditApSave
            },
            'gridApLists #view' : {
				click	: me.viewAp
			},
            'gridApLists #execute' : {
				click	: me.execute
			},
            '#winHardwareAddActionMain #save' : {
				click	: me.commitExecute
			},
			'gridApLists #restart' : {
				click	: me.restart
			},
            
            //RADIOs Choices
            
            //Attach
            'winAccessPointAttachAp #chkRadio0Enable'	: {
				change	: me.chkRadioEnableChange
			},
			'winAccessPointAttachAp #chkRadio1Enable' : {
				change	: me.chkRadioEnableChange
			},
            'winAccessPointAttachAp radio[name=radio0_band]' : {
                change  : me.radio_0_BandChange  
            },
            'winAccessPointAttachAp radio[name=radio1_band]' : {
                change  : me.radio_1_BandChange  
            },
            'winAccessPointAttachAp cmbApHardwareModels': {
                change: me.cmbApHardwareModelsChange
            },
            'winAccessPointAttachAp' : {
                beforeshow:  me.loadAdvancedWifiSettings
            },
            
            //Add
            '#winAccessPointAddApMain #chkRadio0Enable'	: {
				change	: me.chkRadioEnableChange
			},
			'#winAccessPointAddApMain #chkRadio1Enable' : {
				change	: me.chkRadioEnableChange
			},
            '#winAccessPointAddApMain radio[name=radio0_band]' : {
                change  : me.radio_0_BandChange  
            },
            '#winAccessPointAddApMainradio[name=radio1_band]' : {
                change  : me.radio_1_BandChange  
            },
            '#winAccessPointAddApMain cmbApHardwareModels': {
                change: me.cmbApHardwareModelsChange
            },
            '#winAccessPointAddApMain' : {
                beforeshow:  me.loadAdvancedWifiSettings
            },
            
            //Edit
			'#winAccessPointEditApMain #chkRadio0Enable'	: {
				change	: me.chkRadioEnableChange
			},
			'#winAccessPointEditApMain #chkRadio1Enable' : {
				change	: me.chkRadioEnableChange
			},
            '#winAccessPointEditApMain radio[name=radio0_band]' : {
                change  : me.radio_0_BandChange  
            },
            '#winAccessPointEditApMain radio[name=radio1_band]' : {
                change  : me.radio_1_BandChange  
            },
            '#winAccessPointEditApMain cmbApHardwareModels': {
                change: me.cmbApHardwareModelsChange
            },
            //Notes
            'gridApProfiles #note'   : {
                click:      me.note
            },
            'gridNote[noteForGrid=ap_profiles] #reload' : {
                click:  me.noteReload
            },
            'gridNote[noteForGrid=ap_profiles] #add' : {
                click:  me.noteAdd
            },
            'gridNote[noteForGrid=ap_profiles] #delete' : {
                click:  me.noteDelete
            },
            'gridNote[noteForGrid=ap_profiles]' : {
                itemclick: me.gridNoteClick
            },
            'winNoteAdd[noteForGrid=ap_profiles] #btnTreeNext' : {
                click:  me.btnNoteTreeNext
            },
            'winNoteAdd[noteForGrid=ap_profiles] #btnNoteAddPrev'  : {   
                click: me.btnNoteAddPrev
            },
            'winNoteAdd[noteForGrid=ap_profiles] #btnNoteAddNext'  : {   
                click: me.btnNoteAddNext
            }
        });
    },
    appClose:   function(){
        var me          = this;
        me.populated    = false;
        
        if(me.autoReload != undefined){
            clearInterval(me.autoReload);   //Always clear
        }
        
        if(me.autoReloadApLists != undefined){
            clearInterval(me.autoReloadApLists);
        }
        
        if(me.autoReloadUnknownAps != undefined){
            clearInterval(me.autoReloadUnknownAps);
        }      
    },
	reload: function(){
        var me =this;
        me.getGrid().getSelectionModel().deselectAll(true);
        me.getGrid().getStore().load();
    },
    reloadOptionClick: function(menu_item){
        var me      = this;
        var n       = menu_item.getItemId();
        var b       = menu_item.up('button'); 
        var interval= 30000; //default
        clearInterval(me.autoReload);   //Always clear
        b.setGlyph(Rd.config.icnTime);

        if(n == 'mnuRefreshCancel'){
            b.setGlyph(Rd.config.icnReload);
            return;
        }
        
        if(n == 'mnuRefresh1m'){
           interval = 60000
        }

        if(n == 'mnuRefresh5m'){
           interval = 360000
        }
        me.autoReload = setInterval(function(){        
            me.reload();
        },  interval);  
    },
    gridActivate: function(g){
        var me = this;
        g.getStore().load();
    },
    gridApListsReload: function(button){
        var me  = this;
        var g = button.up('gridApLists');
        g.getStore().load();
    },
    reloadApListsOptionClick: function(menu_item){
        var me      = this;
        var n       = menu_item.getItemId();
        var b       = menu_item.up('button'); 
        var interval= 30000; //default
        clearInterval(me.autoReloadApLists);   //Always clear
        b.setGlyph(Rd.config.icnTime);

        if(n == 'mnuRefreshCancel'){
            b.setGlyph(Rd.config.icnReload);
            return;
        }
        
        if(n == 'mnuRefresh1m'){
           interval = 60000
        }

        if(n == 'mnuRefresh5m'){
           interval = 360000
        }
        me.autoReloadApLists = setInterval(function(){        
            me.gridApListsReload(b);
        },  interval);  
    },
    gridUnknownApsReload: function(button){
        var me  = this;
        var g = button.up('gridUnknownAps');
        g.getStore().load();
    },
    reloadUnknownApsOptionClick: function(menu_item){
        var me      = this;
        var n       = menu_item.getItemId();
        var b       = menu_item.up('button'); 
        var interval= 30000; //default
        clearInterval(me.autoReloadUnknownAps);   //Always clear
        b.setGlyph(Rd.config.icnTime);

        if(n == 'mnuRefreshCancel'){
            b.setGlyph(Rd.config.icnReload);
            return;
        }
        
        if(n == 'mnuRefresh1m'){
           interval = 60000
        }

        if(n == 'mnuRefresh5m'){
           interval = 360000
        }
        me.autoReloadUnknownAps = setInterval(function(){        
            me.gridUnknownApsReload(b);
        },  interval);  
    },
    
    select: function(grid,record){
        var me = this;
        //Adjust the Edit and Delete buttons accordingly...

        //Dynamically update the top toolbar
        tb = me.getGrid().down('toolbar[dock=top]');

        var edit = record.get('update');
        if(edit == true){
            if(tb.down('#edit') != null){
                tb.down('#edit').setDisabled(false);
            }
        }else{
            if(tb.down('#edit') != null){
                tb.down('#edit').setDisabled(true);
            }
        }

        var del = record.get('delete');
        if(del == true){
            if(tb.down('#delete') != null){
                tb.down('#delete').setDisabled(false);
            }
        }else{
            if(tb.down('#delete') != null){
                tb.down('#delete').setDisabled(true);
            }
        }

        var view = record.get('view');
        if(view == true){
            if(tb.down('#view') != null){
                tb.down('#view').setDisabled(false);
            }
        }else{
            if(tb.down('#view') != null){
                tb.down('#view').setDisabled(true);
            }
        }
    },
    
    //_______ Known APs ________
    addAp: function(button){
        var me      = this;
        var tab     = button.up("#tabAccessPoints"); 
        var store   = tab.down("gridApLists").getStore();
        
        if(!Ext.WindowManager.get('winAccessPointAddApId')){
            var w = Ext.widget('winAccessPointAddAp',
            {
                id              :'winAccessPointAddApId',
                store           : store,
                apProfileId     : '',
				apProfileName	: '',
                itemId          : 'winAccessPointAddApMain'	
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
    delAp:   function(btn){
        var me      = this;
       // var win     = btn.up("window");
        var grid    = btn.up("gridApLists");
    
        //Find out if there was something selected
        if(grid.getSelectionModel().getCount() == 0){
            Ext.ux.Toaster.msg(
                        i18n('sSelect_an_item'),
                        i18n('sFirst_select_an_item'),
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
    editAp: function(button){
        var me      = this;
        
        var tab     = button.up("#tabAccessPoints"); 
        var store   = tab.down("gridApLists").getStore();
        if(tab.down("gridApLists").getSelectionModel().getCount() == 0){
             Ext.ux.Toaster.msg(
                        i18n('sSelect_an_item'),
                        i18n('sFirst_select_an_item'),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
            );
            
        }else{
            var sr          = tab.down("gridApLists").getSelectionModel().getLastSelected();
            var id          = sr.getId();
            var apProfileId = sr.get('ap_profile_id');
            var apProfile   = sr.get('ap_profile');

            if(!Ext.WindowManager.get('winAccessPointEditApId')){
                var w = Ext.widget('winAccessPointEditAp',
                {
                    id              :'winAccessPointEditApId',
                    store           : store,
                    apId            : id,
                    apProfileId     : apProfileId,
                    apProfileName	: apProfile,
                    itemId          : 'winAccessPointEditApMain'
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
                    i18n('sItem_updated'),
                    i18n('sItem_updated_fine'),
                    Ext.ux.Constants.clsInfo,
                    Ext.ux.Constants.msgInfo
                );  
            },
            scope       : me,
            failure     : Ext.ux.formFail
        });
    },
    
    execute:   function(button){
        var me      = this;
        
        var tab     = button.up("#tabAccessPoints"); 
        var grid    = tab.down("gridApLists");
         
        //Find out if there was something selected
        if(grid.getSelectionModel().getCount() == 0){
            Ext.ux.Toaster.msg(
                        i18n('sSelect_an_item'),
                        i18n("sSelect_an_item_on_which_to_execute_the_command"),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
            );
        }else{
        	//console.log("Show window for command content")
        	if(!Ext.WindowManager.get('winHardwareAddActionMain')){
                var w = Ext.widget('winHardwareAddAction',{id:'winHardwareAddActionMain',grid : grid});
                w.show();       
            }
        }
    },
	commitExecute:  function(button){
        var me      = this;
        var win     = button.up('#winHardwareAddActionMain');
        var form    = win.down('form');

		var selected    = win.grid.getSelectionModel().getSelection();
		var list        = [];
        Ext.Array.forEach(selected,function(item){
            var id = item.getId();
            Ext.Array.push(list,{'id' : id});
        });

        form.submit({
            clientValidation	: true,
            url					: me.getUrlApProfileAddApAction(),
			params				: list,
            success: function(form, action) {       
                win.grid.getStore().reload();
				win.close();
				Ext.ux.Toaster.msg(
                    i18n("sItem_created"),
                    i18n("sItem_created_fine"),
                    Ext.ux.Constants.clsInfo,
                    Ext.ux.Constants.msgInfo
                );
            },
            scope       : me,
            failure     : Ext.ux.formFail
        });
    },
    
    restart:   function(button){
        var me      = this; 
        
        var tab     = button.up("#tabAccessPoints"); 
        var grid    = tab.down("gridApLists");
		
    
        //Find out if there was something selected
        if(grid.getSelectionModel().getCount() == 0){
             Ext.ux.Toaster.msg(
                        i18n('sSelect_an_item'),
                        i18n('sFirst_select_an_item_to_restart'),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
            );
        }else{
        
            //This is
            Ext.Msg.show({
                 title      : i18n("sConfirm"),
                 msg        : i18n("sAre_you_sure_you_want_to_do_that_qm"),
                 buttons    : Ext.Msg.YESNO,
                 icon       : Ext.Msg.QUESTION,
                 callback   :function(btn) {
                    if('yes' === btn) {
                        var selected    = grid.getSelectionModel().getSelection();
                        var list        = [];
                        Ext.Array.forEach(selected,function(item){
                            var id = item.getId();
                            Ext.Array.push(list,{'id' : id});
                        });

                        Ext.Ajax.request({
                            url: me.getUrlRestartAps(),
                            method: 'POST',          
                            jsonData: {aps: list},
                            success: function(batch,options){
                                Ext.ux.Toaster.msg(
                                            i18n('sCommand_queued'),
                                            i18n('sCommand_queued_for_execution'),
                                            Ext.ux.Constants.clsInfo,
                                            Ext.ux.Constants.msgInfo
                                );
                                grid.getStore().reload();
                            },                                    
                            failure: function(batch,options){
                                Ext.ux.Toaster.msg(
                                            i18n('sError_encountered'),
                                            batch.proxy.getReader().rawData.message.message,
                                            Ext.ux.Constants.clsWarn,
                                            Ext.ux.Constants.msgWarn
                                );
                                grid.getStore().reload();
                            }
                        });
                    }
                }
            });
        }
    },
    
    viewAp: function(button){
        var me      = this;   
        //Find out if there was something selected
        var selCount = me.getGridApLists().getSelectionModel().getCount();
        if(selCount == 0){
            Ext.ux.Toaster.msg(
                i18n('sSelect_an_item'),
                i18n('sFirst_select_an_item'),
                Ext.ux.Constants.clsWarn,
                Ext.ux.Constants.msgWarn
            );
        }else{
            if(selCount > 1){
                Ext.ux.Toaster.msg(
                    i18n('sSelect_one_only'),
                    i18n('sSelection_limited_to_one'),
                    Ext.ux.Constants.clsWarn,
                    Ext.ux.Constants.msgWarn
                );
            }else{
                var sr      = me.getGridApLists().getSelectionModel().getLastSelected();
                var id      = sr.getId();
                var name    = sr.get('name'); 
                //var cont    = Rd.app.createController('cAccessPointViews');
                //cont.actionIndex(id,name);
                me.application.runAction('cAccessPointViews','Index',id,name);
            }
        }
    },
    //_______ Unknown Aps ______
	attachAp: function(button){
        var me      = this;
        var tab     = button.up("#tabAccessPoints");
        var store   = tab.down("gridUnknownAps").getStore();
        if(tab.down("gridUnknownAps").getSelectionModel().getCount() == 0){
            Ext.ux.Toaster.msg(
                i18n('sSelect_an_item'),
                i18n('sFirst_select_an_item'),
                Ext.ux.Constants.clsWarn,
                Ext.ux.Constants.msgWarn
            );  
        }else{
            var sr              = tab.down("gridUnknownAps").getSelectionModel().getLastSelected();
            var id              = sr.getId();
			var mac		        = sr.get('mac');

            if(!Ext.WindowManager.get('winAccessPointAttachApId')){
                var w = Ext.widget('winAccessPointAttachAp',
                {
                    id          :'winAccessPointAttachApId',
					mac			: mac,
					store       : store
                });
                w.show();        
            }
        }
    },
	btnAttachApSave: function(button){
        var me      = this;
        var win     = button.up("winAccessPointAttachAp");        
        var form    = win.down('form');
        form.submit({
            clientValidation: true,
            url: me.getUrlAddAp(),
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
            scope       : me,
            failure     : Ext.ux.formFail
        });
    },
	delUnknownAp:   function(btn){
        var me      = this;
        var grid    = btn.up("gridUnknownAps");
        //Find out if there was something selected
        if(grid.getSelectionModel().getCount() == 0){
            Ext.ux.Toaster.msg(
                i18n('sFirst_select_an_item'),
                i18n('sFirst_select_an_item_to_delete'),
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
                                    i18n('sItem_deleted'),
                                    i18n('sItem_deleted_fine'),
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
    
    //___ RADIO settings _____
	chkRadioEnableChange: function(chk){
		var me 		= this;
		var fs    	= chk.up('panel');//fs
        var value   = chk.getValue();
        
        fs.down('fieldcontainer').setVisible(value);
        
		var fields = Ext.ComponentQuery.query('field',fs);
		Ext.Array.forEach(fields,function(item){
			if(item != chk){
			    if(!(item.isDisabled())){
				    item.setVisible(value);
		        }
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
            return
        }else{
            form.down('#tabAdvanced').setDisabled(false);
            form.down('#tabAdvanced').tab.show();
            form.down('#tabRadio').setDisabled(false);
            form.down('#tabRadio').tab.show();
        }
        
        var tabRadiosRadio1	= form.down('#tabRadiosRadio1');
        var tabAdvRadio1    = form.down('#tabAdvWifiRadio1');
        var window          = cmb.up('window');
        
        if(window.getItemId() != 'winAccessPointEditApMain'){
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
    
    add: function(button){
        var me = this;
        Ext.Ajax.request({
            url: me.getUrlApChildCheck(),
            method: 'GET',
            success: function(response){
                var jsonData    = Ext.JSON.decode(response.responseText);
                if(jsonData.success){
                        
                    if(jsonData.items.tree == true){
                        if(!Ext.WindowManager.get('winApProfileAddWizardId')){
                            var w = Ext.widget('winApProfileAddWizard',{id:'winApProfileAddWizardId'});
                            w.show();         
                        }
                    }else{
                        if(!Ext.WindowManager.get('winApProfileAddWizardId')){
                            var w = Ext.widget('winApProfileAddWizard',
                                {id:'winApProfileAddWizardId',startScreen: 'scrnData',user_id:'0',owner: i18n("sLogged_in_user"), no_tree: true}
                            );
                            w.show()          
                        }
                    }
                }   
            },
            scope: me
        });

    },
    btnTreeNext: function(button){
        var me = this;
        var tree = button.up('treepanel');
        //Get selection:
        var sr = tree.getSelectionModel().getLastSelected();
        if(sr){    
            var win = button.up('winApProfileAddWizard');
            win.down('#owner').setValue(sr.get('username'));
            win.down('#user_id').setValue(sr.getId());
            win.getLayout().setActiveItem('scrnData');
        }else{
            Ext.ux.Toaster.msg(
                i18n('sSelect'),
                i18n('sFirst_select_an_Access_Provider_who_will_be_the_owner'),
                Ext.ux.Constants.clsWarn,
                Ext.ux.Constants.msgWarn
            );            
        }
    },
    btnDataPrev:  function(button){
        var me      = this;
        var win     = button.up('winApProfileAddWizard');
        win.getLayout().setActiveItem('scrnApTree');
    },
    btnDataNext:  function(button){
        var me      = this;
        var win     = button.up('window');
        var form    = win.down('form');
        form.submit({
            clientValidation: true,
            url: me.getUrlAdd(),
            success: function(form, action) {
                win.close();
                me.getStore('sApProfiles').load();
                Ext.ux.Toaster.msg(
                    i18n('sItem_created'),
                    i18n('sItem_created_fine'),
                    Ext.ux.Constants.clsInfo,
                    Ext.ux.Constants.msgInfo
                );
            },
            scope       : me,
            failure     : Ext.ux.formFail
        });
    },
    del:   function(){
        var me      = this;     
        //Find out if there was something selected
        if(me.getGrid().getSelectionModel().getCount() == 0){
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

                        var selected    = me.getGrid().getSelectionModel().getSelection();
                        var list        = [];
                        Ext.Array.forEach(selected,function(item){
                            var id = item.getId();
                            Ext.Array.push(list,{'id' : id});
                        });

                        Ext.Ajax.request({
                            url: me.getUrlDelete(),
                            method: 'POST',          
                            jsonData: list,
                            success: function(batch,options){
                                Ext.ux.Toaster.msg(
                                    i18n('sItem_deleted'),
                                    i18n('sItem_deleted_fine'),
                                    Ext.ux.Constants.clsInfo,
                                    Ext.ux.Constants.msgInfo
                                );
                                me.reload(); //Reload from server
                            },                                    
                            failure: function(batch,options){
                                Ext.ux.Toaster.msg(
                                    i18n('sProblems_deleting_item'),
                                    batch.proxy.getReader().rawData.message.message,
                                    Ext.ux.Constants.clsWarn,
                                    Ext.ux.Constants.msgWarn
                                );                               
                                me.reload(); //Reload from server
                            }
                        });

                    }
                }
            });
        }
    },
    
    edit: function(button){
        var me      = this;   
        //Find out if there was something selected
        var selCount = me.getGrid().getSelectionModel().getCount();
        if(selCount == 0){
            Ext.ux.Toaster.msg(
                i18n('sSelect_an_item'),
                i18n('sFirst_select_an_item'),
                Ext.ux.Constants.clsWarn,
                Ext.ux.Constants.msgWarn
            );
        }else{
            if(selCount > 1){
                Ext.ux.Toaster.msg(
                    i18n('sSelect_one_only'),
                    i18n('sSelection_limited_to_one'),
                    Ext.ux.Constants.clsWarn,
                    Ext.ux.Constants.msgWarn
                );
            }else{
                var sr      = me.getGrid().getSelectionModel().getLastSelected();
                var id      = sr.getId();
                var name    = sr.get('name');
                me.application.runAction('cAccessPointEdits','Index',id,name); 
            }
        }
    },
    
    //Notes for ap_profiles
    note: function(button,format) {
        var me      = this;    
        //Find out if there was something selected
        var sel_count = me.getGrid().getSelectionModel().getCount();
        if(sel_count == 0){
            Ext.ux.Toaster.msg(
                i18n('sSelect_an_item'),
                i18n('sFirst_select_an_item'),
                Ext.ux.Constants.clsWarn,
                Ext.ux.Constants.msgWarn
            );
        }else{
            if(sel_count > 1){
                Ext.ux.Toaster.msg(
                    i18n('sSelect_one_only'),
                    i18n('sSelection_limited_to_one'),
                    Ext.ux.Constants.clsWarn,
                    Ext.ux.Constants.msgWarn
                );
            }else{

                //Determine the selected record:
                var sr = me.getGrid().getSelectionModel().getLastSelected();
                
                if(!Ext.WindowManager.get('winNoteApProfiles'+sr.getId())){
                    var w = Ext.widget('winNote',
                        {
                            id          : 'winNoteApProfiles'+sr.getId(),
                            noteForId   : sr.getId(),
                            noteForGrid : 'ap_profiles',
                            noteForName : sr.get('name')
                        });
                    w.show();       
                }
            }    
        }
    },
    noteReload: function(button){
        var me      = this;
        var grid    = button.up('gridNote');
        grid.getStore().load();
    },
    noteAdd: function(button){
        var me      = this;
        var grid    = button.up('gridNote');

        //See how the wizard should be displayed:
        Ext.Ajax.request({
            url: me.getUrlApChildCheck(),
            method: 'GET',
            success: function(response){
                var jsonData    = Ext.JSON.decode(response.responseText);
                if(jsonData.success){                      
                    if(jsonData.items.tree == true){
                        if(!Ext.WindowManager.get('winNoteApProfilesAdd'+grid.noteForId)){
                            var w   = Ext.widget('winNoteAdd',
                            {
                                id          : 'winNoteApProfilesAdd'+grid.noteForId,
                                noteForId   : grid.noteForId,
                                noteForGrid : grid.noteForGrid,
                                refreshGrid : grid
                            });
                            w.show()        
                        }
                    }else{
                        if(!Ext.WindowManager.get('winNoteApProfilesAdd'+grid.noteForId)){
                            var w   = Ext.widget('winNoteAdd',
                            {
                                id          : 'winNoteApProfilesAdd'+grid.noteForId,
                                noteForId   : grid.noteForId,
                                noteForGrid : grid.noteForGrid,
                                refreshGrid : grid,
                                startScreen : 'scrnNote',
                                user_id     : '0',
                                owner       : i18n("sLogged_in_user"),
                                no_tree     : true
                            });
                            w.show()        
                        }
                    }
                }   
            },
            scope: me
        });
    },
    gridNoteClick: function(item,record){
        var me = this;
        //Dynamically update the top toolbar
        grid    = item.up('gridNote');
        tb      = grid.down('toolbar[dock=top]');
        var del = record.get('delete');
        if(del == true){
            if(tb.down('#delete') != null){
                tb.down('#delete').setDisabled(false);
            }
        }else{
            if(tb.down('#delete') != null){
                tb.down('#delete').setDisabled(true);
            }
        }
    },
    btnNoteTreeNext: function(button){
        var me = this;
        var tree = button.up('treepanel');
        //Get selection:
        var sr = tree.getSelectionModel().getLastSelected();
        if(sr){    
            var win = button.up('winNoteAdd');
            win.down('#owner').setValue(sr.get('username'));
            win.down('#user_id').setValue(sr.getId());
            win.getLayout().setActiveItem('scrnNote');
        }else{
            Ext.ux.Toaster.msg(
                i18n('sSelect'),
                i18n('sFirst_select_an_Access_Provider_who_will_be_the_owner'),
                Ext.ux.Constants.clsWarn,
                Ext.ux.Constants.msgWarn
            ); 
        }
    },
    btnNoteAddPrev: function(button){
        var me = this;
        var win = button.up('winNoteAdd');
        win.getLayout().setActiveItem('scrnApTree');
    },
    btnNoteAddNext: function(button){
        var me      = this;
        var win     = button.up('winNoteAdd');
        win.refreshGrid.getStore().load();
        var form    = win.down('form');
        form.submit({
            clientValidation: true,
            url: me.getUrlNoteAdd(),
            params: {for_id : win.noteForId},
            success: function(form, action) {
                win.close();
                win.refreshGrid.getStore().load();
                me.reload();
                Ext.ux.Toaster.msg(
                    i18n('sItem_created'),
                    i18n('sItem_created_fine'),
                    Ext.ux.Constants.clsInfo,
                    Ext.ux.Constants.msgInfo
                );      
            },
            scope       : me,
            failure     : Ext.ux.formFail
        });
    },
    noteDelete: function(button){
        var me      = this;
        var grid    = button.up('gridNote');
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
                                    i18n('sItem_deleted'),
                                    i18n('sItem_deleted_fine'),
                                    Ext.ux.Constants.clsInfo,
                                    Ext.ux.Constants.msgInfo
                                );
                                grid.getStore().load();   //Update the count
                                me.reload();   
                            },
                            failure: function(batch,options,c,d){
                                Ext.ux.Toaster.msg(
                                    i18n('sItem_deleted'),
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
    
    //Redirecting
    redirectAp: function(button){
        var me      = this;
        var tab     = button.up("#tabAccessPoints");
        var store   = tab.down("gridUnknownAps").getStore();
        if(tab.down("gridUnknownAps").getSelectionModel().getCount() == 0){
            Ext.ux.Toaster.msg(
                i18n('sSelect_an_item'),
                i18n('sFirst_select_an_item'),
                Ext.ux.Constants.clsWarn,
                Ext.ux.Constants.msgWarn
            );
            
        }else{
            var sr          = tab.down("gridUnknownAps").getSelectionModel().getLastSelected();
            var id          = sr.getId();
            var new_server  = sr.get('new_server');

            if(!Ext.WindowManager.get('winApUnknownRedirectId')){
                var w = Ext.widget('winApUnknownRedirect',
                {
                    id              :'winApUnknownRedirectId',
					unknownApId   : id,
					new_server	    : new_server,
                    store           : store
                });
                w.show();         
            }
        }
    },
	btnRedirectApSave: function(button){
        var me      = this;
        var win     = button.up("winApUnknownRedirect");        
        var form    = win.down('form');
        form.submit({
            clientValidation: true,
            url: me.getUrlRedirectAp(),
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
            scope       : me,
            failure     : Ext.ux.formFail
        });
    }
});
