Ext.define('Rd.controller.cProfiles', {
    extend: 'Ext.app.Controller',
    actionIndex: function(){

        var me = this;
        var desktop = this.application.getController('cDesktop');
        var win = desktop.getWindow('profilesWin');
        if(!win){
            win = desktop.createWindow({
                id: 'profilesWin',
                //title: i18n('sProfiles_manager'),
                btnText: i18n('sProfiles_manager'),
                width:800,
                height:400,
                iconCls: 'profiles',
                glyph: Rd.config.icnProfile,
                animCollapse:false,
                border:false,
                constrainHeader:true,
                layout: 'border',
                stateful: true,
                stateId: 'profilesWin',
                items: [
                    {
                        region: 'north',
                        xtype:  'pnlBanner',
                        heading: i18n('sProfiles_manager'),
                        image:  'resources/images/48x48/profiles.png'
                    },
                    {
                        region  : 'center',
                        xtype   : 'panel',
                        layout  : 'fit',
                        border  : false,
                        xtype   : 'gridProfiles'
                    }
                ]
            });
        }
        desktop.restoreWindow(win);    
        return win;
    },

    views:  [
        'components.pnlBanner',             'profiles.gridProfiles',    'profiles.winProfileAddWizard',
        'components.winCsvColumnSelect',    'components.winNote',       'components.winNoteAdd',
        'profiles.winComponentManage'
    ],
    stores: ['sProfiles', 'sAccessProvidersTree', 'sProfileComponents'],
    models: ['mProfile',  'mAccessProviderTree'],
    selectedRecord: null,
    config: {
        urlAdd:             '/cake2/rd_cake/profiles/add.json',
        urlEdit:            '/cake2/rd_cake/profiles/edit.json',
        urlDelete:          '/cake2/rd_cake/profiles/delete.json',
        urlApChildCheck:    '/cake2/rd_cake/access_providers/child_check.json',
        urlExportCsv:       '/cake2/rd_cake/profiles/export_csv',
        urlNoteAdd:         '/cake2/rd_cake/profiles/note_add.json',
        urlManageComponents:'/cake2/rd_cake/profiles/manage_components.json'
    },
    refs: [
        {  ref: 'grid',  selector:   'gridProfiles'}       
    ],
    init: function() {
        var me = this;
        if (me.inited) {
            return;
        }
        me.inited = true;
        
        me.control({
            'gridProfiles #reload': {
                click:      me.reload
            }, 
            'gridProfiles #add'   : {
                click:      me.add
            },
            'gridProfiles #delete'   : {
                click:      me.del
            },
            'gridProfiles #edit'   : {
                click:      me.edit
            },
            'gridProfiles #note'   : {
                click:      me.note
            },
            'gridProfiles #csv'  : {
                click:      me.csvExport
            },
            'gridProfiles'   : {
                select:      me.select
            },
            'winProfileAddWizard #btnTreeNext' : {
                click:  me.btnTreeNext
            },
            'winProfileAddWizard #btnDataPrev' : {
                click:  me.btnDataPrev
            },
            'winProfileAddWizard #btnDataNext' : {
                click:  me.btnDataNext
            },
            '#winCsvColumnSelectProfiles #save': {
                click:  me.csvExportSubmit
            },
            'gridNote[noteForGrid=profiles] #reload' : {
                click:  me.noteReload
            },
            'gridNote[noteForGrid=profiles] #add' : {
                click:  me.noteAdd
            },
            'gridNote[noteForGrid=profiles] #delete' : {
                click:  me.noteDelete
            },
            'gridNote[noteForGrid=profiles]' : {
                itemclick: me.gridNoteClick
            },
            'winNoteAdd[noteForGrid=profiles] #btnTreeNext' : {
                click:  me.btnNoteTreeNext
            },
            'winNoteAdd[noteForGrid=profiles] #btnNoteAddPrev'  : {   
                click: me.btnNoteAddPrev
            },
            'winNoteAdd[noteForGrid=profiles] #btnNoteAddNext'  : {   
                click: me.btnNoteAddNext
            },
            'winComponentManage #save' : {
                click:  me.btnComponentManageSave
            },
            'winComponentManage radiogroup' : {
                change: me.radioComponentManage
            }
        });
    },
    reload: function(){
        var me =this;
        me.getGrid().getSelectionModel().deselectAll(true);
        me.getGrid().getStore().load();
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
                        if(!me.application.runAction('cDesktop','AlreadyExist','winProfileAddWizardId')){
                            var w = Ext.widget('winProfileAddWizard',{id:'winProfileAddWizardId'});
                            me.application.runAction('cDesktop','Add',w);         
                        }
                    }else{
                        if(!me.application.runAction('cDesktop','AlreadyExist','winProfileAddWizardId')){
                            var w = Ext.widget('winProfileAddWizard',
                                {id:'winProfileAddWizardId',startScreen: 'scrnData',user_id:'0',owner: i18n('sLogged_in_user'), no_tree: true}
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
            var win = button.up('winProfileAddWizard');
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
        var win     = button.up('winProfileAddWizard');
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
                me.getStore('sProfiles').load();
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
                        success: function(batch,options){console.log('success');
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

    edit: function(button){
        var me      = this;
        var grid    = button.up('grid');
        //Find out if there was something selected
        if(grid.getSelectionModel().getCount() == 0){
             Ext.ux.Toaster.msg(
                        i18n('sSelect_an_item'),
                        i18n('sFirst_select_an_item_to_edit'),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
            );
        }else{
            if(!me.application.runAction('cDesktop','AlreadyExist','winComponentManageId')){
                var w = Ext.widget('winComponentManage',{id:'winComponentManageId'});
                me.application.runAction('cDesktop','Add',w);       
            }    
        }
    },

    radioComponentManage: function(rbg){
        var me      = this;
        var form    = rbg.up('form');
        var cmb     = form.down('combo');
        var prior   = form.down('numberfield');

        if((rbg.getValue().rb == 'add')||(rbg.getValue().rb == 'remove')){
            cmb.setVisible(true);
            cmb.setDisabled(false);
        }else{
            cmb.setVisible(false);
            cmb.setDisabled(true);
        }

        if(rbg.getValue().rb == 'add'){
            prior.setVisible(true);
            prior.setDisabled(false);
        }else{
            prior.setVisible(false);
            prior.setDisabled(true);
        }
    },

    btnComponentManageSave: function(button){
        var me      = this;
        var win     = button.up('window');
        var form    = win.down('form');
        var cmb     = form.down('combo');
        var rbg     = form.down('radiogroup');

        //For these two we need to have a value selected
        if((rbg.getValue().rb == 'add')||(rbg.getValue().rb == 'remove')){
            if(cmb.getValue() == null){
                Ext.ux.Toaster.msg(
                        i18n('sSelect_an_item'),
                        i18n('sSelect_a_component_to_add_or_remove'),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
                );
                 return;
            } 
        }

        var extra_params    = {};
        var s               = me.getGrid().getSelectionModel().getSelection();
        Ext.Array.each(s,function(record){
            var r_id = record.getId();
            extra_params[r_id] = r_id;
        });

        //Checks passed fine...      
        form.submit({
            clientValidation: true,
            url: me.getUrlManageComponents(),
            params: extra_params,
            success: function(form, action) {
                win.close();
                me.getGrid().getStore().load();
                Ext.ux.Toaster.msg(
                    i18n('sProfiles_modified'),
                    i18n('sProfiles_modified_fine'),
                    Ext.ux.Constants.clsInfo,
                    Ext.ux.Constants.msgInfo
                );
            },
            failure: Ext.ux.formFail
        });
    },
    csvExport: function(button,format) {
        var me          = this;
        var columns     = me.getGrid().columns;
        var col_list    = [];
        Ext.Array.each(columns, function(item,index){
            if(item.dataIndex != ''){
                var chk = {boxLabel: item.text, name: item.dataIndex, checked: true};
                col_list[index] = chk;
            }
        }); 

        if(!me.application.runAction('cDesktop','AlreadyExist','winCsvColumnSelectProfiles')){
            var w = Ext.widget('winCsvColumnSelect',{id:'winCsvColumnSelectProfiles',columns: col_list});
            me.application.runAction('cDesktop','Add',w);         
        }
    },
    csvExportSubmit: function(button){

        var me      = this;
        var win     = button.up('window');
        var form    = win.down('form');

        var chkList = form.query('checkbox');
        var c_found = false;
        var columns = [];
        var c_count = 0;
        Ext.Array.each(chkList,function(item){
            if(item.getValue()){ //Only selected items
                c_found = true;
                columns[c_count] = {'name': item.getName()};
                c_count = c_count +1; //For next one
            }
        },me);

        if(!c_found){
            Ext.ux.Toaster.msg(
                        i18n('sSelect_one_or_more'),
                        i18n('sSelect_one_or_more_columns_please'),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
            );
        }else{     
            //next we need to find the filter values:
            var filters     = [];
            var f_count     = 0;
            var f_found     = false;
            var filter_json ='';
            me.getGrid().filters.filters.each(function(item) {
                if (item.active) {
                    f_found         = true;
                    var ser_item    = item.serialize();
                    ser_item.field  = item.dataIndex;
                    filters[f_count]= ser_item;
                    f_count         = f_count + 1;
                }
            });   
            var col_json        = "columns="+Ext.JSON.encode(columns);
            var extra_params    = Ext.Object.toQueryString(Ext.Ajax.extraParams);
            var append_url      = "?"+extra_params+'&'+col_json;
            if(f_found){
                filter_json = "filter="+Ext.JSON.encode(filters);
                append_url  = append_url+'&'+filter_json;
            }
            window.open(me.getUrlExportCsv()+append_url);
            win.close();
        }
    },

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
                
                if(!me.application.runAction('cDesktop','AlreadyExist','winNoteProfiles'+sr.getId())){
                    var w = Ext.widget('winNote',
                        {
                            id          : 'winNoteProfiles'+sr.getId(),
                            noteForId   : sr.getId(),
                            noteForGrid : 'profiles',
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
                        if(!me.application.runAction('cDesktop','AlreadyExist','winNoteProfilesAdd'+grid.noteForId)){
                            var w   = Ext.widget('winNoteAdd',
                            {
                                id          : 'winNoteProfilesAdd'+grid.noteForId,
                                noteForId   : grid.noteForId,
                                noteForGrid : grid.noteForGrid,
                                refreshGrid : grid
                            });
                            me.application.runAction('cDesktop','Add',w);       
                        }
                    }else{
                        if(!me.application.runAction('cDesktop','AlreadyExist','winNoteProfilesAdd'+grid.noteForId)){
                            var w   = Ext.widget('winNoteAdd',
                            {
                                id          : 'winNoteProfilesAdd'+grid.noteForId,
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
        console.log(win.noteForId);
        console.log(win.noteForGrid);
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
    }
});
