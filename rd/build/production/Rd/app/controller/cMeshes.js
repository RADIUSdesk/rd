Ext.define('Rd.controller.cMeshes', {
    extend: 'Ext.app.Controller',
    actionIndex: function(){
        var me      = this;
        var desktop = this.application.getController('cDesktop');
        var win     = desktop.getWindow('meshWin');
        if(!win){
            win = desktop.createWindow({
                id      : 'meshWin',
                //title   : 'MESHdesk overview',
                btnText : i18n('sMESHdesk_overview'),
                width   : 800,
                height  : 400,
                iconCls : 'mesh',
                glyph   : Rd.config.icnMesh,
                animCollapse:false,
                border  :false,
                constrainHeader:true,
                layout  : 'border',
                stateful: true,
                stateId : 'meshWin',
                items: [
                    {
                        region  : 'north',
                        xtype   : 'pnlBanner',
                        heading : i18n('sMESHdesk_overview'),
                        image   : 'resources/images/48x48/mesh.png'
                    },
					{
                        region  : 'center',
                        xtype   : 'panel',
                        layout  : 'fit',
                        border  : false,
                        items   : [{
                            xtype   : 'tabpanel',
                            layout  : 'fit',
                            margins : '0 0 0 0',
                            border  : true,
                            plain   : false,
                            items   : [
								{ 'title' : i18n('sHome'), 	'xtype':'gridMeshes',		'glyph': Rd.config.icnHome},
								{ 'title' : 'Known nodes', 	'xtype':'gridNodeLists',	'glyph': Rd.config.icnThumbUp},
								{ 'title' : 'Unknown nodes','xtype':'gridUnknownNodes',	'glyph': Rd.config.icnThumbDown}
                        ]}]
                    }
                ]
            });
        }
        desktop.restoreWindow(win);    
        return win;
    },

    views:  [
        'components.pnlBanner',     'meshes.gridMeshes',        'meshes.winMeshAddWizard',
		'meshes.gridNodeLists',		'meshes.winMeshEditNode',	'meshes.gridUnknownNodes',
		'meshes.winMeshAttachNode',
        'meshes.winMeshUnknownRedirect',
        'meshes.cmbHardwareOptions', 'meshes.cmbStaticEntries', 'meshes.cmbStaticExits'
    ],
    stores      : [
		'sMeshes',   'sAccessProvidersTree', 'sNodeLists', 				'sUnknownNodes',
		'sMeshEntries', 'sMeshExits', 	'sMeshEntryPoints'
	],
    models      : ['mMesh',     'mAccessProviderTree', 'mNodeList', 	'mUnknownNode'
    ],
    selectedRecord: null,
    config      : {
        urlAdd:             '/cake2/rd_cake/meshes/add.json',
        urlDelete:          '/cake2/rd_cake/meshes/delete.json',
        urlApChildCheck:    '/cake2/rd_cake/access_providers/child_check.json',
        urlNoteAdd:         '/cake2/rd_cake/meshes/note_add.json',
		urlAddNode:         '/cake2/rd_cake/meshes/mesh_node_add.json',
        urlViewNode:        '/cake2/rd_cake/meshes/mesh_node_view.json',
        urlEditNode:        '/cake2/rd_cake/meshes/mesh_node_edit.json',
        urlAdvancedSettingsForModel : '/cake2/rd_cake/meshes/advanced_settings_for_model.json',
        urlRedirectNode :   '/cake2/rd_cake/nodes/redirect_unknown.json'
    },
    refs: [
        {  ref: 'grid',         selector: 'gridMeshes'} 
    ],
    init: function() {
        var me = this;
        if (me.inited) {
            return;
        }
        me.inited = true;

        me.control({
            '#meshWin'    : {
                beforeshow:      me.winClose,
                destroy   :      me.winClose
            },
			'#meshWin gridMeshes' : {
				activate	: me.gridActivate
			},
			'#meshWin gridNodeLists' : {
				activate	: me.gridActivate
			},
			'#meshWin gridUnknownNodes' : {
				activate	: me.gridActivate
			},
			'#meshWin gridMeshes' : {
				activate	: me.gridActivate
			},
            'gridMeshes #reload': {
                click:      me.reload
            },
            'gridMeshes #reload menuitem[group=refresh]'   : {
                click:      me.reloadOptionClick
            },  
            'gridMeshes #add'   : {
                click:      me.add
            },
            'gridMeshes #delete'   : {
                click:      me.del
            },
            'gridMeshes #edit'   : {
                click:      me.edit
            },
            'gridMeshes #view'   : {
                click:      me.view
            },
            'gridMeshes #note'   : {
                click:      me.note
            },
            'gridMeshes'   		: {
                select:      me.select
            },
            'gridNote[noteForGrid=meshes] #reload' : {
                click:  me.noteReload
            },
            'gridNote[noteForGrid=meshes] #add' : {
                click:  me.noteAdd
            },
            'gridNote[noteForGrid=meshes] #delete' : {
                click:  me.noteDelete
            },
            'gridNote[noteForGrid=meshes]' : {
                itemclick: me.gridNoteClick
            },
            'winNoteAdd[noteForGrid=meshes] #btnTreeNext' : {
                click:  me.btnNoteTreeNext
            },
            'winNoteAdd[noteForGrid=meshes] #btnNoteAddPrev'  : {   
                click: me.btnNoteAddPrev
            },
            'winNoteAdd[noteForGrid=meshes] #btnNoteAddNext'  : {   
                click: me.btnNoteAddNext
            },
            'winMeshAddWizard #btnTreeNext' : {
                click:  me.btnTreeNext
            },
            'winMeshAddWizard #btnDataPrev' : {
                click:  me.btnDataPrev
            },
            'winMeshAddWizard #btnDataNext' : {
                click:  me.btnDataNext
            },

			//Known nodes
			'gridNodeLists #reload': {
                click:      me.gridNodeListsReload
            },
            'gridNodeLists #reload menuitem[group=refresh]'   : {
                click:      me.reloadNodeListsOptionClick
            }, 
			'gridNodeLists #add': {
                click:  me.addNode
            },
            '#winMeshAddNodeMain' : {
                beforeshow:  me.loadAdvancedWifiSettings
            },
            '#winMeshAddNodeMain #save' : {
                click:  me.btnAddNodeSave
            },
			'#winMeshAddNodeMain cmbHardwareOptions': {
                change: me.cmbHardwareOptionsChange
            },
            'gridNodeLists #delete': {
                click: me.delNode
            },
            'gridNodeLists #edit': {
                click:  me.editNode
            },
            '#winMeshEditNodeMain': {
                beforeshow:      me.loadNode
            },
            '#winMeshEditNodeMain #save': {
                click: me.btnEditNodeSave
            },
			'#winMeshEditNodeMain cmbHardwareOptions': {
                change: me.cmbHardwareOptionsChange
            },
			//Dual RADIO Choices

            //Add
			'#winMeshAddNodeMain #chkRadio0Enable'	: {
				change	: me.chkRadioEnableChange
			},
			'#winMeshAddNodeMain #chkRadio1Enable' : {
				change	: me.chkRadioEnableChange
			},
			'#winMeshAddNodeMain #chkRadio0Mesh' : {
				change	: me.chkRadioMeshChange
			},
			'#winMeshAddNodeMain #chkRadio1Mesh' : {
				change	: me.chkRadioMeshChange
			},
            '#winMeshAddNodeMain radio[name=radio0_band]' : {
                change  : me.radio_0_BandChange  
            },
            '#winMeshAddNodeMain radio[name=radio1_band]' : {
                change  : me.radio_1_BandChange  
            },

            //Edit
            '#winMeshEditNodeMain #chkRadio0Enable'	: {
				change	: me.chkRadioEnableChange
			},
			'#winMeshEditNodeMain #chkRadio1Enable' : {
				change	: me.chkRadioEnableChange
			},
			'#winMeshEditNodeMain #chkRadio0Mesh' : {
				change	: me.chkRadioMeshChange
			},
			'#winMeshEditNodeMain #chkRadio1Mesh' : {
				change	: me.chkRadioMeshChange
			},
            '#winMeshEditNodeMain radio[name=radio0_band]' : {
                change  : me.radio_0_BandChange  
            },
            '#winMeshEditNodeMain radio[name=radio1_band]' : {
                change  : me.radio_1_BandChange  
            },

            //Attach
            'winMeshAttachNode' : {
                beforeshow:  me.loadAdvancedWifiSettings
            },

			//VOIP Choices

            //Add
            '#winMeshAddNodeMain #chkSip'	: {
				change	: me.chkSipChange
			},
			'#winMeshAddNodeMain #chkAsterisk' : {
				change	: me.chkAsteriskChange
			},
            //Edit
			'#winMeshEditNodeMain #chkSip'	: {
				change	: me.chkSipChange
			},
			'#winMeshEditNodeMain #chkAsterisk' : {
				change	: me.chkAsteriskChange
			},

			'gridUnknownNodes #reload': {
                click:      me.gridUnknownNodesReload
            },
            'gridUnknownNodes #reload menuitem[group=refresh]'   : {
                click:      me.reloadUnknownNodesOptionClick
            },

			'gridUnknownNodes #attach': {
                click:  me.attachNode
            },
			'winMeshAttachNode cmbHardwareOptions': {
                change: me.cmbHardwareOptionsChange
            },
			'winMeshAttachNode #save' : {
				click: me.btnAttachNodeSave
			},
			'gridUnknownNodes #delete': {
                click: me.delUnknownNode
            },

            'gridUnknownNodes #redirect' : {
                click: me.redirectNode
            },
            'winMeshUnknownRedirect #save' : {
				click: me.btnRedirectNodeSave
			}
        });
    },
    winClose:   function(){
        var me = this;
        if(me.autoReload != undefined){
            clearInterval(me.autoReload);   //Always clear
        }
        if(me.autoReloadNodeLists != undefined){
            clearInterval(me.autoReloadNodeLists);
        }
        
        if(me.autoReloadUnknownNodes != undefined){
            clearInterval(me.autoReloadUnknownNodes);
        }
    },
	gridActivate: function(g){
        var me = this;
        g.getStore().load();
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
        b.setIconCls('b-reload_time');
        b.setGlyph(Rd.config.icnTime);

        if(n == 'mnuRefreshCancel'){
            b.setGlyph(Rd.config.icnReload);
            b.setIconCls('b-reload');
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
    add: function(button){
        
        var me = this;
        //We need to do a check to determine if this user (be it admin or acess provider has the ability to add to children)
        //admin/root will always have, an AP must be checked if it is the parent to some sub-providers. If not we will 
        //simply show the nas connection typer selection 
        //if it does have, we will show the tree to select an access provider.
        Ext.Ajax.request({
            url: me.getUrlApChildCheck(),
            method: 'GET',
            success: function(response){
                var jsonData    = Ext.JSON.decode(response.responseText);
                if(jsonData.success){
                        
                    if(jsonData.items.tree == true){
                        if(!me.application.runAction('cDesktop','AlreadyExist','winMeshAddWizardId')){
                            var w = Ext.widget('winMeshAddWizard',{id:'winMeshAddWizardId'});
                            me.application.runAction('cDesktop','Add',w);         
                        }
                    }else{
                        if(!me.application.runAction('cDesktop','AlreadyExist','winMeshAddWizardId')){
                            var w = Ext.widget('winMeshAddWizard',
                                {id:'winMeshAddWizardId',startScreen: 'scrnData',user_id:'0',owner: i18n('sLogged_in_user'), no_tree: true}
                            );
                            me.application.runAction('cDesktop','Add',w);         
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
            var win = button.up('winMeshAddWizard');
            win.down('#owner').setValue(sr.get('username'));
            win.down('#user_id').setValue(sr.getId());
            win.getLayout().setActiveItem('scrnData');
        }else{
            Ext.ux.Toaster.msg(
                        i18n('sSelect_an_owner'),
                        i18n('sFirst_select_an_Access_Provider_who_will_be_the_owner'),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
            );
        }
    },
    btnDataPrev:  function(button){
        var me      = this;
        var win     = button.up('winMeshAddWizard');
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
                me.getStore('sMeshes').load();
                Ext.ux.Toaster.msg(
                    i18n('sNew_item_created'),
                    i18n('sItem_created_fine'),
                    Ext.ux.Constants.clsInfo,
                    Ext.ux.Constants.msgInfo
                );
            },
            failure: Ext.ux.formFail
        });
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
    view: function(button){
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
                        i18n('sLimit_the_selection'),
                        i18n('sSelection_limited_to_one'),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
                );
            }else{
                var sr      = me.getGrid().getSelectionModel().getLastSelected();
                var id      = sr.getId();
                var name    = sr.get('name');  
				me.application.runAction('cMeshViews','Index',id,name); 
            }
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
                        i18n('sLimit_the_selection'),
                        i18n('sSelection_limited_to_one'),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
                );
            }else{
                var sr      = me.getGrid().getSelectionModel().getLastSelected();
                var id      = sr.getId();
                var name    = sr.get('name');  
				me.application.runAction('cMeshEdits','Index',id,name); 
            }
        }
    },
    del:   function(){
        var me      = this;     
        //Find out if there was something selected
        if(me.getGrid().getSelectionModel().getCount() == 0){
             Ext.ux.Toaster.msg(
                        i18n('sSelect_an_item'),
                        i18n('sFirst_select_an_item_to_delete'),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
            );
        }else{
            Ext.MessageBox.confirm(i18n('sConfirm'), i18n('sAre_you_sure_you_want_to_do_that_qm'), function(val){
                if(val== 'yes'){

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
            });
        }
    },
    //Notes for MESHes
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
                        i18n('sLimit_the_selection'),
                        i18n('sSelection_limited_to_one'),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
                );
            }else{

                //Determine the selected record:
                var sr = me.getGrid().getSelectionModel().getLastSelected();
                
                if(!me.application.runAction('cDesktop','AlreadyExist','winNoteMeshes'+sr.getId())){
                    var w = Ext.widget('winNote',
                        {
                            id          : 'winNoteMeshes'+sr.getId(),
                            noteForId   : sr.getId(),
                            noteForGrid : 'meshes',
                            noteForName : sr.get('name')
                        });
                    me.application.runAction('cDesktop','Add',w);       
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
                        if(!me.application.runAction('cDesktop','AlreadyExist','winNoteMeshesAdd'+grid.noteForId)){
                            var w   = Ext.widget('winNoteAdd',
                            {
                                id          : 'winNoteMeshesAdd'+grid.noteForId,
                                noteForId   : grid.noteForId,
                                noteForGrid : grid.noteForGrid,
                                refreshGrid : grid
                            });
                            me.application.runAction('cDesktop','Add',w);       
                        }
                    }else{
                        if(!me.application.runAction('cDesktop','AlreadyExist','winNoteMeshesAdd'+grid.noteForId)){
                            var w   = Ext.widget('winNoteAdd',
                            {
                                id          : 'winNoteMeshesAdd'+grid.noteForId,
                                noteForId   : grid.noteForId,
                                noteForGrid : grid.noteForGrid,
                                refreshGrid : grid,
                                startScreen : 'scrnNote',
                                user_id     : '0',
                                owner       : i18n('sLogged_in_user'),
                                no_tree     : true
                            });
                            me.application.runAction('cDesktop','Add',w);       
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
                        i18n('sSelect_an_owner'),
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
                    i18n('sNew_item_created'),
                    i18n('sItem_created_fine'),
                    Ext.ux.Constants.clsInfo,
                    Ext.ux.Constants.msgInfo
                );
            },
            failure: Ext.ux.formFail
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
                            grid.getStore().load();   //Update the count
                            me.reload();   
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

	//___Konw nodes___
	gridNodeListsReload: function(button){
        var me  = this;
        var g = button.up('gridNodeLists');
        g.getStore().load();
    },
    reloadNodeListsOptionClick: function(menu_item){
        var me      = this;
        var n       = menu_item.getItemId();
        var b       = menu_item.up('button'); 
        var interval= 30000; //default
        clearInterval(me.autoReloadNodeLists);   //Always clear
        b.setGlyph(Rd.config.icnTime);

        if(n == 'mnuRefreshCancel'){
            b.setGlyph(Rd.config.icnReload);
            b.setIconCls('b-reload');
            return;
        }
        
        if(n == 'mnuRefresh1m'){
           interval = 60000
        }

        if(n == 'mnuRefresh5m'){
           interval = 360000
        }
        me.autoReloadNodeLists = setInterval(function(){        
            me.gridNodeListsReload(b);
        },  interval);  
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

        if(window.getItemId() != 'winMeshEditNodeMain'){
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

    addNode: function(button){
        var me      = this;     
        var win     = button.up("#meshWin"); 
        var store   = win.down("gridNodeLists").getStore();
		var hide_power  = true; //FIXME To be fixed with real value from mesh
        if(!me.application.runAction('cDesktop','AlreadyExist','winMeshAddNodeId')){
            var w = Ext.widget('winMeshAddNode',
            {
                id          :'winMeshAddNodeId',
                store       : store,
                meshId      : '',
				meshName	: '',
                itemId      : 'winMeshAddNodeMain'	
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
        var grid    = win.down("gridNodeLists");
    
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
        var win     = button.up("#meshWin");
        var store   = win.down("gridNodeLists").getStore();
        if(win.down("gridNodeLists").getSelectionModel().getCount() == 0){
             Ext.ux.Toaster.msg(
                        i18n('sSelect_an_item'),
                        i18n('sFirst_select_an_item'),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
            );
        }else{
            var sr      = win.down("gridNodeLists").getSelectionModel().getLastSelected();
            var id      = sr.getId();
            var meshId  = sr.get('mesh_id');
			var meshName= sr.get('mesh');

			//Determine if we can show a power bar or not.
			var hide_power = true; //FIXME To be fiexed with real value from mesh
            if(!me.application.runAction('cDesktop','AlreadyExist','winMeshEditNodeId')){
                var w = Ext.widget('winMeshEditNode',
                {
                    id          :'winMeshEditNodeId',
                    store       : store,
                    nodeId      : id,
                    meshId      : meshId,
					meshName	: meshName,
					hidePower	: hide_power,
                    itemId      : 'winMeshEditNodeMain'
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
	gridUnknownNodesReload: function(button){
        var me  = this;
        var g = button.up('gridUnknownNodes');
        g.getStore().load();
    },
    reloadUnknownNodesOptionClick: function(menu_item){
        var me      = this;
        var n       = menu_item.getItemId();
        var b       = menu_item.up('button'); 
        var interval= 30000; //default
        clearInterval(me.autoReloadUnknownNodes);   //Always clear
        b.setGlyph(Rd.config.icnTime);

        if(n == 'mnuRefreshCancel'){
            b.setGlyph(Rd.config.icnReload);
            b.setIconCls('b-reload');
            return;
        }
        
        if(n == 'mnuRefresh1m'){
           interval = 60000
        }

        if(n == 'mnuRefresh5m'){
           interval = 360000
        }
        me.autoReloadUnknownNodes = setInterval(function(){        
            me.gridUnknownNodesReload(b);
        },  interval);  
    },

	//_______ Unknown Nodes ______
	attachNode: function(button){
        var me      = this;
        var win     = button.up("#meshWin");
        var store   = win.down("gridUnknownNodes").getStore();
        if(win.down("gridUnknownNodes").getSelectionModel().getCount() == 0){
             Ext.ux.Toaster.msg(
                        i18n('sSelect_an_item'),
                        i18n('sFirst_select_an_item'),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
            );
        }else{
            var sr      = win.down("gridUnknownNodes").getSelectionModel().getLastSelected();
            var id      = sr.getId();
            var meshId  = '';
			var meshName= '';
			var mac		= sr.get('mac');

			//Determine if we can show a power bar or not.
			var hide_power = true; //FIXME To be fiexed with real value from mesh
            if(!me.application.runAction('cDesktop','AlreadyExist','winMeshAttachNodeId')){
                var w = Ext.widget('winMeshAttachNode',
                {
                    id          :'winMeshAttachNodeId',
                    store       : store,
					mac			: mac
                });
                me.application.runAction('cDesktop','Add',w);         
            }
        }
    },
	btnAttachNodeSave: function(button){
        var me      = this;
        var win     = button.up("winMeshAttachNode");
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
	delUnknownNode:   function(btn){
        var me      = this;
        var win     = btn.up("window");
        var grid    = win.down("gridUnknownNodes");
    
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
    //Redirecting
    redirectNode: function(button){
        var me      = this;
        var win     = button.up("#meshWin");
        var store   = win.down("gridUnknownNodes").getStore();
        if(win.down("gridUnknownNodes").getSelectionModel().getCount() == 0){
             Ext.ux.Toaster.msg(
                        i18n('sSelect_an_item'),
                        i18n('sFirst_select_an_item'),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
            );
        }else{
            var sr          = win.down("gridUnknownNodes").getSelectionModel().getLastSelected();
            var id          = sr.getId();
            var new_server  = sr.get('new_server');
            if(!me.application.runAction('cDesktop','AlreadyExist','winMeshUnknownRedirectId')){
                var w = Ext.widget('winMeshUnknownRedirect',
                {
                    id              :'winMeshUnknownRedirectId',
                    unknownNodeId   : id,
					new_server	    : new_server,
                    store           : store
                });
                me.application.runAction('cDesktop','Add',w);         
            }
        }
    },
	btnRedirectNodeSave: function(button){
        var me      = this;
        var win     = button.up("winMeshUnknownRedirect");
        var form    = win.down('form');
        form.submit({
            clientValidation: true,
            url: me.getUrlRedirectNode(),
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
    }

});
