Ext.define('Rd.controller.cAccessProviders', {
    extend: 'Ext.app.Controller',
    actionIndex: function(pnl){
        var me = this;
        if (me.populated) {
            return; 
        }     
        pnl.add({
            xtype   : 'tabpanel',
            border  : false,
            plain   : true,
            itemId  : 'tabAccessProviders',
            cls     : 'subSubTab', //Make darker -> Maybe grey
            items   : [{ 'title' : i18n('sHome'), xtype :'gridAccessProviders','glyph': Rd.config.icnHome}]
        });
        me.populated = true;
    },
    views:  [
        'accessProviders.pnlAccessProvider',    'accessProviders.pnlAccessProviderDetail',
        'accessProviders.treeApUserRights',     'accessProviders.gridRealms',   
        'accessProviders.gridAccessProviders',  'accessProviders.winApAddWizard',
        'components.winCsvColumnSelect',        'components.winNote',                   'components.winNoteAdd',
        'permanentUsers.winPermanentUserPassword','components.winEnableDisable',        'components.vCmbLanguages',
        'accessProviders.gridAccessProviderLimits'
    ],
    stores: ['sLanguages',  'sApRights',    'sAccessProvidersGrid',     'sAccessProvidersTree'],
    models: ['mApUserRight','mApRealms',    'mAccessProviderGrid',      'mAccessProviderTree'],
    selectedRecord: undefined,
    config: {
        urlAdd          : '/cake3/rd_cake/access-providers/add.json', //Keep this still the original untill everything is ported for AROs
        urlEdit         : '/cake3/rd_cake/access-providers/edit.json',
        urlDelete       : '/cake3/rd_cake/access-providers/delete.json', //Keep this still the original untill everything is ported for AROs
        urlApChildCheck : '/cake3/rd_cake/access-providers/child-check.json',
        urlExportCsv    : '/cake3/rd_cake/access-providers/exportCsv',
        urlNoteAdd      : '/cake3/rd_cake/access-providers/note_add.json',
        urlViewAPDetail : '/cake3/rd_cake/access-providers/view.json',
        urlEnableDisable: '/cake3/rd_cake/access-providers/enable_disable.json',
        urlChangePassword:'/cake3/rd_cake/access-providers/change_password.json',
        urlLimitCheck   : '/cake2/rd_cake/limits/limit_check.json'
    },
    refs: [
        { ref:  'winAccessProviders',   selector:   '#accessProvidersWin'},
        { ref:  'grid',                 selector:   'gridAccessProviders'}
    ],
    init: function() {
        var me = this;
        if (me.inited) {
            return;
        }
        me.inited = true;
        
        me.control({
            '#tabAccessProviders' : {
                destroy   :      me.appClose   
            },
            'gridAccessProviders #reload': {
                click:      me.reload
            },
            'gridAccessProviders #add': {
                click:      me.add
            },
            'gridAccessProviders #edit': {
                click:      me.edit
            },
            'gridAccessProviders #delete': {
                click:      me.del
            },
            'gridAccessProviders #note'   : {
                click:      me.note
            },
            'gridAccessProviders #csv'  : {
                click:      me.csvExport
            },
            'gridAccessProviders #password'  : {
                click:      me.changePassword
            },
            'gridAccessProviders #enable_disable' : {
                click:      me.enableDisable
            },
            'gridAccessProviders'       : {
                activate:      me.gridActivate,
                itemclick:  me.gridClick
            },
            'winApAddWizard #btnTreeNext': {
                click:      me.btnTreeNext
            },
            'winApAddWizard #btnDetailPrev': {
                click:      me.btnDetailPrev
            },
            'winApAddWizard #save': {
                click:      me.addSubmit
            },
            'winAccessProviderDetail #save': {
                click:      me.addSubmit
            },
            'pnlAccessProvider pnlAccessProviderDetail #save': {
                click:      me.editSubmit
            },
            'pnlAccessProvider treeApUserRights #reload': {
                click:      me.apRightReload
            },
            'pnlAccessProvider treeApUserRights #expand': {
                click:      me.apRightExpand
            },
            'pnlAccessProvider treeApUserRights advCheckColumn': {
                checkchange: me.apRightChange
            },
            'pnlAccessProvider gridApRealms #reload': {
                click:      me.apRealmsReload
            },
            'pnlAccessProvider gridAccessProviderLimits #reload': {
                click:      me.limitsReload
            },
            '#winCsvColumnSelectAp #save': {
                click:  me.csvExportSubmit
            },
            'gridNote[noteForGrid=access-providers] #reload' : {
                click:  me.noteReload
            },
            'gridNote[noteForGrid=access-providers] #add' : {
                click:  me.noteAdd
            },
            'gridNote[noteForGrid=access-providers] #delete' : {
                click:  me.noteDelete
            },
            'gridNote[noteForGrid=access-providers]' : {
                itemclick: me.gridNoteClick
            },
            'winNoteAdd[noteForGrid=access-providers] #btnTreeNext' : {
                click:  me.btnNoteTreeNext
            },
            'winNoteAdd[noteForGrid=access-providers] #btnNoteAddPrev'  : {   
                click: me.btnNoteAddPrev
            },
            'winNoteAdd[noteForGrid=access-providers] #btnNoteAddNext'  : {   
                click: me.btnNoteAddNext
            },
            'pnlAccessProvider #tabDetail': {
                beforerender:   me.tabDetailActivate,
                activate:       me.tabDetailActivate
            },
            'pnlAccessProvider #tabRealms': {
                activate:       me.tabRealmsActivate
            },
            'pnlAccessProvider #tabRights': {
                activate:       me.tabRightsActivate
            },
             'pnlAccessProvider #tabLimits': {
                activate:       me.tabLimitsActivate
            },
            'winPermanentUserPassword #save': {
                click: me.changePasswordSubmit
            },
            '#winEnableDisableUser #save': {
                click: me.enableDisableSubmit
            }
        });;
    },
    appClose:   function(){
        var me          = this;
        me.populated    = false;
    },
    reload: function(){
        var me = this;
        me.getGrid().getSelectionModel().deselectAll(true);
        me.getGrid().getStore().load();
    },
    gridActivate: function(g){
        var me = this;
        g.getStore().load();
    },
    gridClick:  function(grid, record, item, index, event){
        var me                  = this;
        me.selectedRecord = record;
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
    add:    function(){
        var me = this;
        Ext.Ajax.request({
            url: me.getUrlApChildCheck(),
            method: 'GET',
            success: function(response){
                var jsonData    = Ext.JSON.decode(response.responseText);
                if(jsonData.success){
                    if(jsonData.items.tree == true){
                        if(!Ext.WindowManager.get('winApAddWizardId')){
                            var w = Ext.widget('winApAddWizard',
                            {
                                id          :'winApAddWizardId',
                                no_tree     : false,
                                selLanguage : me.application.getSelLanguage()
                            });
                            w.show();         
                        }   
                    }else{
                        if(!Ext.WindowManager.get('winApAddWizardId')){
                            var w = Ext.widget('winApAddWizard',
                            {
                                id          :'winApAddWizardId',
                                noTree      : true,
                                selLanguage : me.application.getSelLanguage(),
                                startScreen : 'scrnData',
                                user_id     : '0',
                                owner       : i18n('sLogged_in_user')
                            });
                            w.show()         
                        }
                    }
                }   
            },
            scope: me
        });
    },
    btnTreeNext: function(button){
        var me      = this;
        var tree    = button.up('treepanel');
        //Get selection:
        var sr      = tree.getSelectionModel().getLastSelected();
        if(sr){    
            var win = button.up('winApAddWizard');
            win.down('#owner').setValue(sr.get('username'));
            win.down('#parent_id').setValue(sr.getId());
            win.getLayout().setActiveItem('scrnData');
            var tp = win.down('tabpanel');  //Reset to zero
            tp.setActiveTab(0);
        }else{
            Ext.ux.Toaster.msg(
                        i18n('sSelect_an_owner'),
                        i18n('sFirst_select_an_Access_Provider_who_will_be_the_owner'),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
            );
        }
    },
    btnDetailPrev: function(button){
        var me = this;
        var win = button.up('winApAddWizard');
        win.getLayout().setActiveItem('scrnApTree');
    },
    addSubmit: function(button){
        var me       = this;
        var win     = button.up('window');
        var form    = win.down('form');
        form.submit({
            clientValidation: true,
            url: me.getUrlAdd(),
            success: function(form, action) {
                win.close();
                me.reload();
                Ext.ux.Toaster.msg(
                    i18n('sNew_item_created'),
                    i18n('sItem_created_fine'),
                    Ext.ux.Constants.clsInfo,
                    Ext.ux.Constants.msgInfo
                );
            },
            //Focus on the first tab as this is the most likely cause of error 
            failure: function(form,action){
                var tp = win.down('tabpanel');
                tp.setActiveTab(0);
                Ext.ux.formFail(form,action)
            }
        });
    },
    edit:   function(){ 
        var me = this;
        //See if there are anything selected... if not, inform the user
        var sel_count = me.getGrid().getSelectionModel().getCount();
        if(sel_count == 0){
            Ext.ux.Toaster.msg(
                        i18n('sSelect_an_item'),
                        i18n('sFirst_select_an_item'),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
            );
        }else{

            var selected    =  me.getGrid().getSelectionModel().getSelection();
            var count       = selected.length;         
            Ext.each(me.getGrid().getSelectionModel().getSelection(), function(sr,index){

                //Check if the node is not already open; else open the node:
                var tp          = me.getGrid().up('tabpanel');
                var ap_id       = sr.getId();
                var ap_tab_id   = 'apTab_'+ap_id;
                var nt          = tp.down('#'+ap_tab_id);
                if(nt){
                    tp.setActiveTab(ap_tab_id); //Set focus on  Tab
                    return;
                }

                var ap_tab_name = sr.get('username');
                
                 Ext.Ajax.request({
                    url: me.getUrlLimitCheck(),
                    method: 'GET',
                    success: function(response){
                        var jsonData    = Ext.JSON.decode(response.responseText);
                        console.log(jsonData);
                        if(jsonData.success){
                            var enabled = jsonData.data.enabled;
                            //Tab not there - add one
                            tp.add({ 
                                title :     ap_tab_name,
                                itemId:     ap_tab_id,
                                closable:   true,
                                iconCls:    'edit', 
                                glyph:      Rd.config.icnEdit,
                                layout:     'fit', 
                                items:      {'xtype' : 'pnlAccessProvider',ap_id: ap_id, limits:enabled}
                            });
                            tp.setActiveTab(ap_tab_id); //Set focus on Add Tab
                        }
                    },
                    scope: me
                });                     
            });
        }
    },
    editSubmit: function(button){
        var me      = this;
        var form    = button.up('form');
        form.submit({
            clientValidation: true,
            url: me.getUrlEdit(),
            success: function(f, action) {
                Ext.ux.Toaster.msg(
                    i18n('sItem_updated'),
                    i18n('sItem_updated_fine'),
                    Ext.ux.Constants.clsInfo,
                    Ext.ux.Constants.msgInfo
                );
                
                //Refresh the form
                var ap_id = form.down('#ap_id').getValue();
                form.load({
                    url :me.getUrlViewAPDetail(), 
                    method:'GET',
                    params:{ap_id:ap_id},
                    success    : function(a,b,c){
                        if(b.result.data.wl_img != null){
                            var img = form.down("#imgWlLogo");
                            img.setSrc(b.result.data.wl_img);
                        }
                    }
                });
                    
            },
            failure: Ext.ux.formFail
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
                        success: function(response){
                            var jsonData    = Ext.JSON.decode(response.responseText);
                            if(jsonData.success){ //success=true
                                Ext.ux.Toaster.msg(
                                    i18n('sItem_deleted'),
                                    i18n('sItem_deleted_fine'),
                                    Ext.ux.Constants.clsInfo,
                                    Ext.ux.Constants.msgInfo
                                );
                                me.reload(); //Reload from server
                            }else{ //success=false
                                 Ext.ux.Toaster.msg(
                                    i18n('sProblems_deleting_item'),
                                    jsonData.message,
                                    Ext.ux.Constants.clsWarn,
                                    Ext.ux.Constants.msgWarn
                                );
                                me.reload(); //Reload from server
                            }   
                        },                                   
                        failure: Ext.ux.ajaxFail
                    });
                }
            });
        }
    },
    apRightReload: function(button){
        var me = this;
        var tree = button.up('treeApUserRights');
        tree.getStore().load();
    },
    apRightExpand: function(button){
        var me = this;
        var tree = button.up('treeApUserRights');
        var sel_count = tree.getSelectionModel().getCount();
        if(sel_count == 0){
            Ext.ux.Toaster.msg(
                        i18n('sSelect_a_node'),
                        i18n('sFirst_select_a_node_to_expand'),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
            );
        }else{
            var sr = tree.getSelectionModel().getLastSelected();
            tree.expandNode(sr,true); 
        }
    },
    apRightChange: function(i){
        var me      = this;
        var tree    = i.up('treeApUserRights');
        tree.getStore().sync({
            success: function(batch,options){
                Ext.ux.Toaster.msg(
                    i18n('sRight_Changed'),
                    i18n('sRight_changed_fine'),
                    Ext.ux.Constants.clsInfo,
                    Ext.ux.Constants.msgInfo
                ); 
            },
            failure: function(batch,options){
                Ext.ux.Toaster.msg(
                    i18n('sProblems_changing_right'),
                    i18n('sThere_were_some_problems_experienced_during_changing_of_the_right'),
                    Ext.ux.Constants.clsWarn,
                    Ext.ux.Constants.msgWarn
                );
            }
        });
    },
    apRealmsReload: function(button){
        var me = this;
        var grid = button.up('gridApRealms');
        grid.getStore().load();
    },
    limitsReload: function(button){
        var me = this;
        var grid = button.up('grid');
        grid.getStore().load();
    },
    onStoreApLoaded: function() {
        var me      = this;
        var count   = me.getStore('sAccessProvidersGrid').getTotalCount();
        me.getGrid().down('#count').update({count: count});
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

        if(!Ext.WindowManager.get('winCsvColumnSelectAp')){
            var w = Ext.widget('winCsvColumnSelect',{id:'winCsvColumnSelectAp',columns: col_list});
            w.show();        
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
                 
            var filter_collection = me.getGrid().getStore().getFilters();     
            if(filter_collection.count() > 0){
                var i = 0;
                while (f_count < filter_collection.count()) { 

                    //console.log(filter_collection.getAt(f_count).serialize( ));
                    f_found         = true;
                    var ser_item    = filter_collection.getAt(f_count).serialize( );
                    ser_item.field  = ser_item.property;
                    filters[f_count]= ser_item;
                    f_count         = f_count + 1;
                    
                }     
            }
             
            var col_json        = "columns="+encodeURIComponent(Ext.JSON.encode(columns));
            var extra_params    = Ext.Object.toQueryString(Ext.Ajax.getExtraParams());
            var append_url      = "?"+extra_params+'&'+col_json;
            if(f_found){
                filter_json = "filter="+encodeURIComponent(Ext.JSON.encode(filters));
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
                
                if(!Ext.WindowManager.get('winNoteAp'+sr.getId())){
                    var w = Ext.widget('winNote',
                        {
                            id          : 'winNoteAp'+sr.getId(),
                            noteForId   : sr.getId(),
                            noteForGrid : 'access-providers',
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
                        if(!Ext.WindowManager.get('winNoteApAdd'+grid.noteForId)){
                            var w   = Ext.widget('winNoteAdd',
                            {
                                id          : 'winNoteApAdd'+grid.noteForId,
                                noteForId   : grid.noteForId,
                                noteForGrid : grid.noteForGrid,
                                refreshGrid : grid
                            });
                            w.show();       
                        }
                    }else{
                        if(!Ext.WindowManager.get('winNoteApAdd'+grid.noteForId)){
                            var w   = Ext.widget('winNoteAdd',
                            {
                                id          : 'winNoteApAdd'+grid.noteForId,
                                noteForId   : grid.noteForId,
                                noteForGrid : grid.noteForGrid,
                                refreshGrid : grid,
                                startScreen : 'scrnNote',
                                user_id     : '0',
                                owner       : i18n('sLogged_in_user'),
                                no_tree     : true
                            });
                            w.show();       
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
    tabDetailActivate : function(tab){
        var me      = this;
        var form    = tab.down('form');
        var ap_id  = tab.up('pnlAccessProvider').ap_id;
        form.load({
            url :me.getUrlViewAPDetail(), 
            method:'GET',
            params:{ap_id:ap_id},
            success    : function(a,b,c){
                if(b.result.data.wl_img != null){
                    var img = form.down("#imgWlLogo");
                    img.setSrc(b.result.data.wl_img);
                }
            }
        });
    },
    changePassword: function(){
        var me = this;
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
                if(!Ext.WindowManager.get('winPermanentUsersPassword'+sr.getId())){
                    var w = Ext.widget('winPermanentUserPassword',
                        {
                            id          : 'winPermanentUsersPassword'+sr.getId(),
                            user_id     : sr.getId(),
                            username    : sr.get('username'),
                            title       : i18n('sChange_password_for')+' '+sr.get('username')
                        });
                    w.show();      
                }
            }    
        }
    },
    changePasswordSubmit: function(button){
        var me      = this;
        var win     = button.up('window');
        var form    = win.down('form');

        var extra_params        = {};
        var sr                  = me.getGrid().getSelectionModel().getLastSelected();
        extra_params['user_id'] = sr.getId();

        //Checks passed fine...      
        form.submit({
            clientValidation    : true,
            url                 : me.getUrlChangePassword(),
            params              : extra_params,
            success             : function(form, action) {
                win.close();
                me.reload();
                Ext.ux.Toaster.msg(
                    i18n('sPassword_changed'),
                    i18n('sPassword_changed_fine'),
                    Ext.ux.Constants.clsInfo,
                    Ext.ux.Constants.msgInfo
                );
            },
            failure             : Ext.ux.formFail
        });
    },
    enableDisable: function(button){
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
            if(!Ext.WindowManager.get('winEnableDisableUser')){
                var w = Ext.widget('winEnableDisable',{id:'winEnableDisableUser'});
                w.show();       
            }    
        }
    },
    enableDisableSubmit:function(button){

        var me      = this;
        var win     = button.up('window');
        var form    = win.down('form');

        var extra_params    = {};
        var s               = me.getGrid().getSelectionModel().getSelection();
        Ext.Array.each(s,function(record){
            var r_id = record.getId();
            extra_params[r_id] = r_id;
        });

        //Checks passed fine...      
        form.submit({
            clientValidation    : true,
            url                 : me.getUrlEnableDisable(),
            params              : extra_params,
            success             : function(form, action) {
                win.close();
                me.reload();
                Ext.ux.Toaster.msg(
                    i18n('sItems_modified'),
                    i18n('sItems_modified_fine'),
                    Ext.ux.Constants.clsInfo,
                    Ext.ux.Constants.msgInfo
                );
            },
            failure             : Ext.ux.formFail
        });
    }, 
    tabRealmsActivate:  function(t){
        var me = this;
        t.getStore().load();
    },
    tabRightsActivate:  function(t){
        var me = this;
        t.getStore().load();
    },
    tabLimitsActivate: function(t){
        var me = this;
        t.getStore().load();
    } 
});
