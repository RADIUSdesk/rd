Ext.define('Rd.controller.cActivityMonitor', {
    extend: 'Ext.app.Controller',
    actionIndex: function(){
        var me = this;
        var desktop = this.application.getController('cDesktop');
        var win = desktop.getWindow('activityMonitorWin');
        if(!win){
            win = desktop.createWindow({
                id: 'activityMonitorWin',
                //title: i18n('sActivity_monitor'),
                btnText: i18n('sActivity_monitor'),
                width:800,
                height:400,
                iconCls: 'activity',
                glyph: Rd.config.icnActivity,
                animCollapse:false,
                border:false,
                constrainHeader:true,
                layout: 'border',
                stateful: true,
                stateId: 'activityMonitorWin',
                items: [
                    {
                        region: 'north',
                        xtype:  'pnlBanner',
                        heading: i18n('sActivity_monitor'),
                        image:  'resources/images/48x48/activity.png'
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
                                { 'title' : i18n('sAccounting_data'),       xtype: 'gridRadaccts'},
                                { 'title' : i18n('sAuthentication_data'),   xtype: 'gridRadpostauths'},
                                { 'title' : i18n('sFreeRADIUS_info'),         xtype: 'pnlRadius'}
                            ]}
                        ]
                    }
                ]
            });
        }
        desktop.restoreWindow(win);    
        return win;
    },

    views:  [
       'components.pnlBanner',  'activityMonitor.gridRadaccts', 'activityMonitor.gridRadpostauths', 'components.cmbNas',
        'activityMonitor.pnlRadius',    'components.winCsvColumnSelect',    'components.pnlUsageGraph'
    ],
    stores: [ 'sRadaccts',  'sRadpostauths'  ],
    models: [ 'mRadacct',   'mRadpostauth', 'mNas', 'mUserStat' ],
    selectedRecord: null,
    specific_nas : undefined,
    config: {
      //  urlEdit:            '/cake2/rd_cake/profiles/edit.json',
        urlExportCsvAcct:     '/cake2/rd_cake/radaccts/export_csv',
        urlExportCsvAuth:     '/cake2/rd_cake/radpostauths/export_csv',
        urlKickActive:        '/cake2/rd_cake/radaccts/kick_active.json',
        urlCloseOpen:         '/cake2/rd_cake/radaccts/close_open.json'
        
    },
    refs: [
        {  ref: 'grid',                 selector:   'gridRadaccts'} ,
        {  ref: 'gridRadpostauths',     selector:   'gridRadpostauths'}      
    ],
    init: function() {
        var me = this;
        if (me.inited) {
            return;
        }
        me.inited = true;

        me.getStore('sRadaccts').addListener('load',        me.onStoreRadacctsLoaded,       me);

        me.control({
            '#activityMonitorWin'    : {
                destroy:      me.winClose
            },
            'gridRadaccts #reload': {
                click:      me.reload
            },
            'gridRadaccts #reload menuitem[group=refresh]'   : {
                click:      me.acctReloadOptionClick
            }, 
            'gridRadaccts #connected': {
                click:      me.reload
            },
            'gridRadaccts #csv'  : {
                click:      me.csvExportAcct
            },
            'gridRadaccts #graph'  : {
                click:      me.usageGraph
            },
            'gridRadaccts #kick'  : {
                click:      me.kickActive
            },
            'gridRadaccts #close'  : {
                click:      me.closeOpen
            },
            'gridRadaccts'   : {
              //  select:      me.select
            },
            'gridRadaccts'   : {
                activate:      me.reload
            },
            'gridRadpostauths #reload': {
                click:      me.reloadPostAuths
            },
            'gridRadpostauths'   : {
                activate:      me.gridActivate
            },
            'gridRadpostauths #reload menuitem[group=refresh]'   : {
                click:      me.authReloadOptionClick
            },
            'gridRadpostauths #csv'  : {
                click:      me.csvExportAuth
            },
            'pnlRadius #reload': {
                click:      me.radiusReload
            },
            'pnlRadius #reload menuitem[group=refresh]'   : {
                click:      me.radiusReloadOptionClick
            }, 
            'pnlRadius': {
                activate:       me.radiusActivate
            },
            'pnlRadius  cmbNas': {
                change:         me.cmbNasChange
            },
            '#winCsvColumnSelectAcct #save': {
                click:  me.csvExportSubmitAcct
            },
            '#winCsvColumnSelectAuth #save': {
                click:  me.csvExportSubmitAuth
            },
            '#daily' : {
                activate:      me.loadGraph
            },
            '#daily #reload' : {
                click:      me.reloadDailyGraph
            },
            '#daily #day' : {
                change:      me.changeDailyGraph
            },
            '#weekly' : {
                activate:      me.loadGraph
            },
            '#weekly #reload' : {
                click:      me.reloadWeeklyGraph
            },
            '#weekly #day' : {
                change:      me.changeWeeklyGraph
            },
            '#monthly' : {
                activate:      me.loadGraph
            },
            '#monthly #reload' : {
                click:      me.reloadMonthlyGraph
            },
            '#monthly #day' : {
                change:      me.changeMonthlyGraph
            }     
        });

    },
    reload: function(){
        var me =this;
        //Determine what we need to show....
        var only_connected = me.getGrid().down('#connected');
        if(only_connected == null){
            only_connected = true; //Default only active
        }else{
            only_connected = only_connected.pressed; //Default only active
        }
        me.getStore('sRadaccts').getProxy().extraParams = {only_connected: only_connected};
        me.getStore('sRadaccts').load();
    },
    onStoreRadacctsLoaded: function() {
        var me          = this;
        var totalIn     = Ext.ux.bytesToHuman(me.getStore('sRadaccts').getProxy().getReader().rawData.totalIn);
        var totalOut    = Ext.ux.bytesToHuman(me.getStore('sRadaccts').getProxy().getReader().rawData.totalOut);
        var totalInOut  = Ext.ux.bytesToHuman(me.getStore('sRadaccts').getProxy().getReader().rawData.totalInOut);
        me.getGrid().down('#totals').update({'in': totalIn, 'out': totalOut, 'total': totalInOut });
    },
    gridActivate: function(g){
        var me = this;
        g.getStore().load();
    },
    //Post auths related
    reloadPostAuths: function(){
        var me =this;
        me.getStore('sRadpostauths').load();
    },
    winClose:   function(){
        var me = this;

        if(me.autoReloadAcct != undefined){
            clearInterval(me.autoReloadAcct);   //Always clear
        }
        if(me.autoReloadAuth != undefined){
            clearInterval(me.autoReloadAuth);   //Always clear
        }
        if(me.autoReloadRadius != undefined){
            clearInterval(me.autoReloadRadius);   //Always clear
        }
    },
    acctReloadOptionClick: function(menu_item){
        var me      = this;
        var n       = menu_item.getItemId();
        var b       = menu_item.up('button'); 
        var interval= 30000; //default
        clearInterval(me.autoReloadAcct);   //Always clear
        b.setIconCls('b-reload_time');
        b.setGlyph(Rd.config.icnTime);

        if(n == 'mnuRefreshCancel'){
            b.setIconCls('b-reload');
            b.setGlyph(Rd.config.icnReload);
            return;
        }
        
        if(n == 'mnuRefresh1m'){
           interval = 60000
        }

        if(n == 'mnuRefresh5m'){
           interval = 360000
        }
        me.autoReloadAcct = setInterval(function(){        
            me.reload();
        },  interval);  
    },
    authReloadOptionClick: function(menu_item){
        var me      = this;
        var n       = menu_item.getItemId();
        var b       = menu_item.up('button'); 
        var interval= 30000; //default
        clearInterval(me.autoReloadAuth);   //Always clear
        b.setIconCls('b-reload_time');
        b.setGlyph(Rd.config.icnTime);
        
        if(n == 'mnuRefreshCancel'){
            b.setIconCls('b-reload');
            b.setGlyph(Rd.config.icnReload);
            return;
        }
        
        if(n == 'mnuRefresh1m'){
           interval = 60000
        }

        if(n == 'mnuRefresh5m'){
           interval = 360000
        }
        me.autoReloadAuth = setInterval(function(){        
            me.reloadPostAuths();
        },  interval);  
    },
    radiusReload: function(button){
        var me = this;
        var panel = button.up('pnlRadius');

        var params = {};
        panel.down('#status').update({mesg: 'fetching the latest info'});
        if(me.specific_nas != undefined){
            console.log(me.specific_nas);
            params.nas_id = me.specific_nas;
        }

        //Get the latest
        Ext.Ajax.request({
            url: '/cake2/rd_cake/free_radius/index.json',
            method: 'GET',
            params: params,
            success: function(response){
                var jsonData    = Ext.JSON.decode(response.responseText);
                if(jsonData.success){
                    panel.authBasicStore.loadData(jsonData.items.auth_basic);
                    panel.authDetailStore.loadData(jsonData.items.auth_detail);
                    panel.acctDetailStore.loadData(jsonData.items.acct_detail);
                    panel.down('#status').update({mesg: 'idle'}); //Clear the info
                }
            },
            scope: me
        });
    },
    radiusActivate: function(pnl){
        var me = this;
        var button = pnl.down("#reload");
        me.radiusReload(button);
    },
    radiusReloadOptionClick: function(menu_item){
        var me      = this;
        var n       = menu_item.getItemId();
        var b       = menu_item.up('button'); 
        var interval= 30000; //default
        clearInterval(me.autoReloadRadius);   //Always clear
        b.setIconCls('b-reload_time');
        b.setGlyph(Rd.config.icnTime);

        if(n == 'mnuRefreshCancel'){
            b.setIconCls('b-reload');
            b.setGlyph(Rd.config.icnReload);
            return;
        }
        
        if(n == 'mnuRefresh1m'){
           interval = 60000
        }

        if(n == 'mnuRefresh5m'){
           interval = 360000
        }
        me.autoReloadRadius = setInterval(function(){        
            me.radiusReload(b);
        },  interval);  
    },
    cmbNasChange:   function(cmb){
        var me      = this;
        var value   = cmb.getValue();
        var s       = cmb.getStore();
        //Test to see if there is a record in the store with this ID
        var r       = s.getById(value);
        if(r != null){
           me.specific_nas = value;
        }
    },
    csvExportAcct: function(button,format) {
        var me          = this;
        var columns     = me.getGrid().columns;
        var col_list    = [];
        Ext.Array.each(columns, function(item,index){
            if(item.dataIndex != ''){
                var chk = {boxLabel: item.text, name: item.dataIndex, checked: true};
                col_list[index] = chk;
            }
        }); 

        if(!me.application.runAction('cDesktop','AlreadyExist','winCsvColumnSelectAcct')){
            var w = Ext.widget('winCsvColumnSelect',{id:'winCsvColumnSelectAcct',columns: col_list});
            me.application.runAction('cDesktop','Add',w);         
        }
    },
    csvExportSubmitAcct: function(button){

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
            window.open(me.getUrlExportCsvAcct()+append_url);
            win.close();
        }
    },

    csvExportAuth: function(button,format) {
        var me          = this;
        var columns     = me.getGridRadpostauths().columns;
        var col_list    = [];
        Ext.Array.each(columns, function(item,index){
            if(item.dataIndex != ''){
                var chk = {boxLabel: item.text, name: item.dataIndex, checked: true};
                col_list[index] = chk;
            }
        }); 

        if(!me.application.runAction('cDesktop','AlreadyExist','winCsvColumnSelectAuth')){
            var w = Ext.widget('winCsvColumnSelect',{id:'winCsvColumnSelectAuth',columns: col_list});
            me.application.runAction('cDesktop','Add',w);         
        }
    },
    csvExportSubmitAuth: function(button){

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
            window.open(me.getUrlExportCsvAuth()+append_url);
            win.close();
        }
    },
   
    closeOpen : function(button){

        var me      = this;
        var grid    = button.up('grid');
        //Find out if there was something selected
        if(grid.getSelectionModel().getCount() == 0){ 
             Ext.ux.Toaster.msg(
                        i18n('sSelect_an_item'),
                        i18n('sFirst_select_an_item'),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
            );
        }else{

            //________________
            var extra_params    = {};
            var s               = grid.getSelectionModel().getSelection();
            Ext.Array.each(s,function(record){
                var r_id = record.getId();
                extra_params[r_id] = r_id;
            });
     
            Ext.Ajax.request({
                url: me.getUrlCloseOpen(),
                method: 'GET',
                params: extra_params,
                success: function(response){
                    var jsonData    = Ext.JSON.decode(response.responseText);
                    if(jsonData.success){
                        Ext.ux.Toaster.msg(
                                    i18n('sItem_updated'),
                                    i18n('sItem_updated_fine'),
                                    Ext.ux.Constants.clsInfo,
                                    Ext.ux.Constants.msgInfo
                        );
                        me.reload();    
                    }   
                },
                scope: me
            });
            //_____________________ 

  
        }


    },

    kickActive: function(button){

        var me      = this;
        var grid    = button.up('grid');
        //Find out if there was something selected
        if(grid.getSelectionModel().getCount() == 0){ 
             Ext.ux.Toaster.msg(
                i18n('sSelect_an_item'),
                i18n('sFirst_select_an_item'),
                Ext.ux.Constants.clsWarn,
                Ext.ux.Constants.msgWarn
            );
        }else{
            Ext.ux.Toaster.msg(
                'Sending request',
                'Please be patient',
                Ext.ux.Constants.clsInfo,
                Ext.ux.Constants.msgInfo
            );
            button.setDisabled(true);
            //________________
            var extra_params    = {};
            var s               = grid.getSelectionModel().getSelection();
            Ext.Array.each(s,function(record){
                var r_id = record.getId();
                extra_params[r_id] = r_id;
            });
    
            Ext.Ajax.request({
                url: me.getUrlKickActive(),
                method: 'GET',
                params: extra_params,
                success: function(response){
                    button.setDisabled(false);
                    var jsonData    = Ext.JSON.decode(response.responseText);
                    if(jsonData.success){
                        Ext.ux.Toaster.msg(
                            i18n('sItem_updated'),
                            i18n('sItem_updated_fine'),
                            Ext.ux.Constants.clsInfo,
                            Ext.ux.Constants.msgInfo
                        );
                        me.reload();    
                    }   
                },
                scope: me
            });
            //_____________________  

        }

    },
    usageGraph : function(button){

        var me      = this;
        var grid    = button.up('grid');
        //Find out if there was something selected
        if(grid.getSelectionModel().getCount() == 0){ 
             Ext.ux.Toaster.msg(
                i18n('sSelect_an_item'),
                i18n('sFirst_select_an_item'),
                Ext.ux.Constants.clsWarn,
                Ext.ux.Constants.msgWarn
            );
        }else{
            var selected    =  grid.getSelectionModel().getSelection();
            var count       = selected.length;         
            Ext.each(grid.getSelectionModel().getSelection(), function(sr,index){

                //Check if the node is not already open; else open the node:
                var tp          = grid.up('tabpanel');

                var graph_tab_name  = sr.get('username');
                graph_tab_name      = graph_tab_name.replace("@","_");//Replece @
                graph_tab_name      = graph_tab_name.toLowerCase();//Make lower case
                var type            = sr.get('user_type');
                //username
                var username        = sr.get('username');
                if(type == 'device'){
                    graph_tab_name  = sr.get('callingstationid');
                    username        = sr.get('callingstationid');
                }

                var graph_id    = 'graphTab_'+graph_tab_name;
                var grapht      = tp.down('#'+graph_id);
                if(grapht){
                    tp.setActiveTab(graph_id); //Set focus on  Tab
                    return;
                }
                //Tab not there - add one
                tp.add({ 
                    title       : type+' '+graph_tab_name,
                    itemId      : graph_id,
                    closable    : true,
                    iconCls     : 'graph',
                    glyph       : Rd.config.icnGraph, 
                    layout      :  'fit', 
                    xtype       : 'tabpanel',
                    margins     : '0 0 0 0',
                    plain       : true,
                    border      : true,
                    tabPosition: 'bottom',
                    items   :   [
                        {
                            title   : "Daily",
                            itemId  : "daily",
                            xtype   : 'pnlUsageGraph',
                            span    : 'daily',
                            layout  : 'fit',
                            username: username,
                            type    : type
                        },
                        {
                            title   : "Weekly",
                            itemId  : "weekly",
                            xtype   : 'pnlUsageGraph',
                            span    : 'weekly',
                            layout  : 'fit',
                            username: username,
                            type    : type
                        },
                        {
                            title   : "Monthly",
                            itemId  : "monthly",
                            layout  : 'fit',
                            xtype   : 'pnlUsageGraph',
                            span    : 'monthly',
                            username: username,
                            type    : type
                        }
                    ]
                });
                tp.setActiveTab(graph_id); //Set focus on Add Tab
            });

        }        

    },
    loadGraph: function(tab){
        var me  = this;
        tab.down("chart").setLoading(true);
        //Get the value of the Day:
        var day = tab.down('#day');
        tab.down("chart").getStore().getProxy().setExtraParam('day',day.getValue());
        me.reloadChart(tab);
    },
    reloadDailyGraph: function(btn){
        var me  = this;
        tab     = btn.up("#daily");
        me.reloadChart(tab);
    },
    changeDailyGraph: function(d,new_val, old_val){
        var me      = this;
        var tab     = d.up("#daily");
        tab.down("chart").getStore().getProxy().setExtraParam('day',new_val);
        me.reloadChart(tab);
    },
    reloadWeeklyGraph: function(btn){
        var me  = this;
        tab     = btn.up("#weekly");
        me.reloadChart(tab);
    },
    changeWeeklyGraph: function(d,new_val, old_val){
        var me      = this;
        var tab     = d.up("#weekly");
        tab.down("chart").getStore().getProxy().setExtraParam('day',new_val);
        me.reloadChart(tab);
    },
    reloadMonthlyGraph: function(btn){
        var me  = this;
        tab     = btn.up("#monthly");
        me.reloadChart(tab);
    },
    changeMonthlyGraph: function(d,new_val, old_val){
        var me      = this;
        var tab     = d.up("#monthly");
        tab.down("chart").getStore().getProxy().setExtraParam('day',new_val);
        me.reloadChart(tab);
    },
    reloadChart: function(tab){
        var me      = this;
        var chart   = tab.down("chart");
        chart.setLoading(true); //Mask it
        chart.getStore().load({
            scope: me,
            callback: function(records, operation, success) {
                chart.setLoading(false);
                if(success){
                    Ext.ux.Toaster.msg(
                            "Graph fetched",
                            "Graph detail fetched OK",
                            Ext.ux.Constants.clsInfo,
                            Ext.ux.Constants.msgInfo
                        );
                    //-- Show totals
                    var rawData     = chart.getStore().getProxy().getReader().rawData;
                    var totalIn     = Ext.ux.bytesToHuman(rawData.totalIn);
                    var totalOut    = Ext.ux.bytesToHuman(rawData.totalOut);
                    var totalInOut  = Ext.ux.bytesToHuman(rawData.totalInOut);
                    tab.down('#totals').update({'in': totalIn, 'out': totalOut, 'total': totalInOut });

                }else{
                    Ext.ux.Toaster.msg(
                            "Problem fetching graph",
                            "Problem fetching graph detail",
                            Ext.ux.Constants.clsWarn,
                            Ext.ux.Constants.msgWarn
                        );
                } 
            }
        });   
    }
});
