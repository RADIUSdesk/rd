Ext.define('Rd.controller.cDataUsage', {
    extend: 'Ext.app.Controller',
    actionIndex: function(pnl){

        var me = this;   
        if (me.populated) {
            return; 
        }     
        pnl.add({
            xtype   : 'pnlDataUsage',
            border  : true,
            itemId  : 'tabDataUsage',
            plain   : true
        });
        me.populated = true;
    },

    views:  [
        'dataUsage.pnlDataUsage',
        'components.cmbRealm',
        'components.pnlUsageGraph',
        'dataUsage.pnlDataUsageDay',
        'dataUsage.pnlDataUsageWeek',
        'dataUsage.pnlDataUsageMonth',
        'dataUsage.pnlDataUsageGraph',
        'dataUsage.pnlDataUsageUserDetail'
    ],
    stores: [],
    models: ['mRealm','mUserStat'],
    selectedRecord: null,
    config: {
        urlUsageForRealm    : '/cake2/rd_cake/data_usage/usage_for_realm.json',
        username            : false,
        type                : 'realm' //default is realm
    },
    refs: [
         {  ref: 'pnlDataUsageDay',     selector:   'pnlDataUsageDay'},
         {  ref: 'pnlDataUsageWeek',    selector:   'pnlDataUsageWeek'},
         {  ref: 'pnlDataUsageMonth',   selector:   'pnlDataUsageMonth'},
         {  ref: 'pnlDataUsage',        selector:   'pnlDataUsage'}      
    ],
    init: function() {
        var me = this;
        if (me.inited) {
            return;
        }
        me.inited = true;
        me.control({
            'pnlDataUsage'  : {
                afterlayout : me.resizeSegments
            },
            '#tabDataUsage' : {
                destroy   :      me.appClose   
            },
            '#tabDataUsage cmbRealm' : {
                change      : me.realmChange,
                afterrender : me.afterRenderEventRealm   
            },
            '#tabDataUsage #reload' : {
                click      : me.reload   
            },
            '#tabDataUsage #btnShowRealm' : {
                click       : me.btnShowRealmClick
            },
            '#tabDataUsage grid' : {
                rowclick    : me.rowClickEvent
            },
            '#tabDataUsage #btnSeeMore' : {
                click       : me.openActivityViewer
            }
        });
    },
    appClose:   function(){
        var me          = this;
        me.populated    = false;
    },
    reload: function(){
        var me = this;
        me.fetchDataUsage();
    },
    realmChange: function(cmb){
        var me = this;
        me.setType('realm')
        me.setUsername(cmb.getValue())
        me.fetchDataUsage();
    },
    afterRenderEventRealm: function(cmb){
        var me      = this;
        var dd      = me.application.getDashboardData();
        var rn      = dd.data_usage.realm_name;
        var r_id    = dd.data_usage.realm_id;
        var rec     = Ext.create('Rd.model.mRealm', {name: rn, id: r_id});
        cmb.getStore().loadData([rec],false);
        cmb.setValue(r_id);
    },
    fetchDataUsage: function(){
        var me = this;
        
        me.getPnlDataUsage().setLoading(true);
        Ext.Ajax.request({
                url: me.getUrlUsageForRealm(),
                params: {
                    type    : me.getType(),
                    username: me.getUsername()
                },
                method: 'GET',
                success: function(response){
                    var jsonData = Ext.JSON.decode(response.responseText);

                    me.getPnlDataUsage().setLoading(false);
                    
                    if(jsonData.success){    
                        console.log(jsonData);
                        me.paintDataUsage(jsonData.data);
                    }else{

                      
                    }
                }
            });
    },
    paintDataUsage: function(data){
        var me          = this;    
        var totalDay    = me.getPnlDataUsageDay().down('#dailyTotal');
        var totalWeek   = me.getPnlDataUsageWeek().down('#weeklyTotal');
        var totalMonth  = me.getPnlDataUsageMonth().down('#monthlyTotal');
        
        data.daily.totals.data_in = Ext.ux.bytesToHuman(data.daily.totals.data_in);
        data.daily.totals.data_out = Ext.ux.bytesToHuman(data.daily.totals.data_out);
        data.daily.totals.data_total = Ext.ux.bytesToHuman(data.daily.totals.data_total);
        
        totalDay.setData(data.daily.totals);
        
        data.weekly.totals.data_in      = Ext.ux.bytesToHuman(data.weekly.totals.data_in);
        data.weekly.totals.data_out     = Ext.ux.bytesToHuman(data.weekly.totals.data_out);
        data.weekly.totals.data_total   = Ext.ux.bytesToHuman(data.weekly.totals.data_total);
        
        totalWeek.setData(data.weekly.totals);
        
        data.monthly.totals.data_in     = Ext.ux.bytesToHuman(data.monthly.totals.data_in);
        data.monthly.totals.data_out    = Ext.ux.bytesToHuman(data.monthly.totals.data_out);
        data.monthly.totals.data_total  = Ext.ux.bytesToHuman(data.monthly.totals.data_total);
        
        totalMonth.setData(data.monthly.totals);
          
        Ext.data.StoreManager.lookup('dayStore').setData(data.daily.top_ten);
        Ext.data.StoreManager.lookup('activeStore').setData(data.daily.active_sessions);
        me.getPnlDataUsageDay().down('cartesian').getStore().setData(data.daily.graph.items);
        
        Ext.data.StoreManager.lookup('weekStore').setData(data.weekly.top_ten);
        me.getPnlDataUsageWeek().down('cartesian').getStore().setData(data.weekly.graph.items);
        
        Ext.data.StoreManager.lookup('monthStore').setData(data.monthly.top_ten);
        me.getPnlDataUsageMonth().down('cartesian').getStore().setData(data.monthly.graph.items);
        
        if(data.user_detail != undefined){
            me.paintUserDetail(data.user_detail); 
        }else{
            me.hideUserDetail();   
        }     
    },
    paintUserDetail: function(user_detail){
        var me          = this; 
        me.getPnlDataUsageDay().down('pnlDataUsageUserDetail').paintUserDetail(user_detail);
        me.getPnlDataUsageDay().down('#plrDaily').hide();
        me.getPnlDataUsageDay().down('pnlDataUsageUserDetail').show();
        
        me.getPnlDataUsageWeek().down('pnlDataUsageUserDetail').paintUserDetail(user_detail);
        me.getPnlDataUsageWeek().down('#plrWeekly').hide();
        me.getPnlDataUsageWeek().down('pnlDataUsageUserDetail').show();
        
        me.getPnlDataUsageMonth().down('pnlDataUsageUserDetail').paintUserDetail(user_detail);
        me.getPnlDataUsageMonth().down('#plrMonthly').hide();
        me.getPnlDataUsageMonth().down('pnlDataUsageUserDetail').show();
           
    },
    hideUserDetail: function(){
        var me          = this; 
        me.getPnlDataUsageDay().down('#plrDaily').show();
        me.getPnlDataUsageDay().down('pnlDataUsageUserDetail').hide();
        
        me.getPnlDataUsageWeek().down('#plrWeekly').show();
        me.getPnlDataUsageWeek().down('pnlDataUsageUserDetail').hide();
        
        me.getPnlDataUsageMonth().down('#plrMonthly').show();
        me.getPnlDataUsageMonth().down('pnlDataUsageUserDetail').hide();
    
    },
    rowClickEvent: function(grid,record){
        var me          = this;
        var username    = record.get('username');
        me.getPnlDataUsage().down('#btnShowRealm').show();
        me.getPnlDataUsage().down('cmbRealm').setDisabled(true);
        me.setUsername(username);
        me.setType('user'); 
        me.fetchDataUsage();
    },
    btnShowRealmClick: function(btn){
        var me = this;
        me.getPnlDataUsage().down('cmbRealm').setDisabled(false);
        btn.hide();
        me.setUsername(me.getPnlDataUsage().down('cmbRealm').getValue());
        me.setType('realm');  
        me.fetchDataUsage();
    },
    resizeSegments: function(pnl){
        var me = this;
        if(pnl.getHeight() > 400){
            me.getPnlDataUsageDay().setHeight((pnl.getHeight()-40));
            me.getPnlDataUsageWeek().setHeight((pnl.getHeight()-40));
            me.getPnlDataUsageMonth().setHeight((pnl.getHeight()-40));
        }
    },
    openActivityViewer: function(btn){
        var me  = this;
        var pnl = me.getPnlDataUsage();
        me.application.runAction('cActivityMonitor','Index',pnl); 
    }
});
