Ext.define('Rd.view.devices.gridDevices' ,{
    extend:'Ext.grid.Panel',
    alias : 'widget.gridDevices',
    multiSelect: true,
    store : 'sDevices',
    stateful: true,
    stateId: 'StateGridDevices',
    stateEvents:['groupclick','columnhide'],
    border: false,
    requires: [
        'Rd.view.components.ajaxToolbar'
    ],
    viewConfig: {
        loadMask:true
    },
    urlMenu: '/cake2/rd_cake/devices/menu_for_grid.json',
    plugins     : 'gridfilters',  //*We specify this
   
    initComponent: function(){
        var me      = this;        
        me.tbar     = Ext.create('Rd.view.components.ajaxToolbar',{'url': me.urlMenu});
        me.bbar     =  [
            {
                xtype       : 'pagingtoolbar',
                store       : me.store,
                dock        : 'bottom',
                displayInfo : true
            }  
        ];

        me.columns  = [
            {xtype: 'rownumberer',stateId: 'StateGridDevices1'},
            { text: i18n('sOwner'),         dataIndex: 'user',     tdCls: 'gridTree', flex: 1,filter: {type: 'string'},stateId: 'StateGridDevices2'},
            { text: i18n('sMAC_address'),   dataIndex: 'name',      tdCls: 'gridMain', flex: 1,filter: {type: 'string'},stateId: 'StateGridDevices3'},
            { text: i18n('sDescription'),   dataIndex: 'description',tdCls: 'gridTree', flex: 1,filter: {type: 'string'},stateId: 'StateGridDevices4'},
           // { text: i18n('sVendor'),        dataIndex: 'vendor',    tdCls: 'gridTree', flex: 1,filter: {type: 'string'}},
            { text: i18n('sRealm'),         dataIndex: 'realm',     tdCls: 'gridTree', flex: 1,filter: {type: 'string'}, sortable: false,stateId: 'StateGridDevices5'},
            { text: i18n('sProfile'),       dataIndex: 'profile',   tdCls: 'gridTree', flex: 1,filter: {type: 'string'}, sortable: false,stateId: 'StateGridDevices6'},
            { 
                text        : i18n('sActive'),  
                xtype       : 'templatecolumn', 
                tpl         : new Ext.XTemplate(
                                "<tpl if='active == true'><div class=\"hasRight\">"+i18n("sYes")+"</div></tpl>",
                                "<tpl if='active == false'><div class=\"noRight\">"+i18n("sNo")+"</div></tpl>"
                            ),
                dataIndex   : 'active',
                filter      : { type: 'boolean'},stateId: 'StateGridDevices7'
            },
            {
                text        : i18n('sLast_accept_time'),
                flex        : 1,
                dataIndex   : 'last_accept_time',
                tdCls       : 'gridTree',
                hidden      : true,
                filter      : {type: 'date'},stateId: 'StateGridDevices8'
            },
            {
                text        : i18n('sLast_accept_nas'),
                flex        : 1,
                dataIndex   : 'last_accept_nas',
                tdCls       : 'gridTree',
                hidden      : true,
                filter      : {type: 'string'},stateId: 'StateGridDevices9'
            },
            {
                text        : i18n('sLast_reject_time'),
                flex        : 1,
                dataIndex   : 'last_reject_time',
                tdCls       : 'gridTree',
                hidden      : true,
                filter      : {type: 'date'},stateId: 'StateGridDevices10'
            },
            {
                text        : i18n('sLast_reject_nas'),
                flex        : 1,
                dataIndex   : 'last_reject_nas',
                tdCls       : 'gridTree',
                hidden      : true,
                filter      : {type: 'string'},stateId: 'StateGridDevices11'
            },
            {
                text        : i18n('sLast_reject_message'),
                flex        : 1,
                dataIndex   : 'last_reject_message',
                tdCls       : 'gridTree',
                hidden      : true,
                filter      : {type: 'string'},stateId: 'StateGridDevices12'
            },
            {
                header      : i18n('sData_used'),
                dataIndex   : 'perc_data_used',
                width       : 110,
                hidden      : true,
                renderer: function (v, m, r) {
                    if(v != null){
                        var id = Ext.id();
                        Ext.defer(function () {
                            Ext.widget('progressbar', {
                                renderTo: id,
                                value: v / 100,
                                width: 100,
                                text: v +" %"
                            });
                        }, 50);
                        return Ext.String.format('<div id="{0}"></div>', id);
                    }else{
                        return "N/A";
                    }
                },stateId: 'StateGridDevices13'
            },
            {
                header      : i18n('sTime_used'),
                dataIndex   : 'perc_time_used',
                width       : 110,
                hidden      : true,
                renderer: function (v, m, r) {
                    if(v != null){
                        var id = Ext.id();
                        Ext.defer(function () {
                            Ext.widget('progressbar', {
                                renderTo: id,
                                value: v / 100,
                                width: 100,
                                text: v+" %"
                            });
                        }, 50);
                        return Ext.String.format('<div id="{0}"></div>', id);
                    }else{
                        return "N/A";
                    }
                },stateId: 'StateGridDevices14'
            },
            { 
                text    : i18n('sNotes'),
                sortable: false,
                width   : 130,
                xtype   : 'templatecolumn', 
                tpl     : new Ext.XTemplate(
                                "<tpl if='notes == true'><span class=\"fa fa-thumb-tack fa-lg txtGreen\"></tpl>"
                ),
                dataIndex: 'notes',stateId: 'StateGridDevices15'
            }      
        ];

        me.callParent(arguments);
    }
});
