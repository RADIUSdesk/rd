Ext.define('Rd.view.vouchers.gridVouchers' ,{
    extend:'Ext.grid.Panel',
    alias : 'widget.gridVouchers',
    multiSelect: true,
    store : 'sVouchers',
    stateful: true,
    stateId: 'StateGridVouchers',
    stateEvents:['groupclick','columnhide'],
    border: false,
    requires: [
        'Rd.view.components.ajaxToolbar'
    ],
    viewConfig: {
        loadMask:true
    },
    urlMenu: '/cake2/rd_cake/vouchers/menu_for_grid.json',
    plugins     : 'gridfilters',  //*We specify this
    initComponent: function(){
        var me      = this;
        me.bbar     =  [
            {
                xtype       : 'pagingtoolbar',
                store       : me.store,
                dock        : 'bottom',
                displayInfo : true
            }  
        ];     
        me.tbar     = Ext.create('Rd.view.components.ajaxToolbar',{'url': me.urlMenu});

        me.columns  = [
            {xtype: 'rownumberer',stateId: 'StateGridVouchers1'},
            { text: i18n('sOwner'),        dataIndex: 'owner',      tdCls: 'gridTree', flex: 1,filter: {type: 'string'},stateId: 'StateGridVouchers2',
                hidden: true
            },  
            { text: i18n('sName'),         dataIndex: 'name',       tdCls: 'gridMain', flex: 1,filter: {type: 'string'},stateId: 'StateGridVouchers3'},
            { text: i18n('sPassword'),     dataIndex: 'password',   tdCls: 'gridTree', flex: 1,filter: {type: 'string'}, sortable: false,stateId: 'StateGridVouchers4', hidden: true},
            { 
                text        : i18n('sBatch'),
                sortable    : true,
                flex        : 1,  
                xtype       : 'templatecolumn', 
                tpl:        new Ext.XTemplate(
                                '<tpl if="Ext.isEmpty(batch)"><div class=\"fieldBlue\">'+i18n('s_br_Single_voucher_br')+'</div>',
                                '<tpl else><div class=\"fieldGrey\">','{batch}','</div></tpl>' 
                            ),
                dataIndex   : 'batch',
                filter: { type: 'string'},stateId: 'StateGridVouchers5'
            },
            { text: i18n('sRealm'),        dataIndex: 'realm',     tdCls: 'gridTree', flex: 1,filter: {type: 'string'}, sortable : false,stateId: 'StateGridVouchers6'},
            { text: i18n('sProfile'),      dataIndex: 'profile',   tdCls: 'gridTree', flex: 1,filter: {type: 'string'}, sortable : false,stateId: 'StateGridVouchers7'},
            {
                header: i18n('sData_used'),
                dataIndex: 'perc_data_used',
                width: 110,
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
                },stateId: 'StateGridVouchers8'
            },
            {
                header: i18n('sTime_used'),
                dataIndex: 'perc_time_used',
                width: 110,
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
                },stateId: 'StateGridVouchers9'
            },
            { 
                text        : i18n('sStatus'),
                flex        : 1,  
                xtype       : 'templatecolumn', 
                tpl         : new Ext.XTemplate(
                                "<tpl if='status == \"new\"'><div class=\"fieldGreen\">"+i18n('sNew')+"</div></tpl>",
                                "<tpl if='status == \"used\"'><div class=\"fieldYellow\">"+i18n('sUsed')+"</div></tpl>",
                                "<tpl if='status == \"depleted\"'><div class=\"fieldOrange\">"+i18n('sDepleted')+"</div></tpl>",
                                "<tpl if='status == \"expired\"'><div class=\"fieldRed\">"+i18n('sExpired')+"</div></tpl>"
                ),
                dataIndex   : 'status',
                filter      : {
                                type    : 'list',
                                phpMode : false,
                                options : ['new', 'used', 'depleted', 'expired']
                              },stateId: 'StateGridVouchers10'
            },
            {
                text        : i18n('sLast_accept_time'),
                flex        : 1,
                dataIndex   : 'last_accept_time',
                tdCls       : 'gridTree',
                hidden      : true,
                filter      : {type: 'date'},stateId: 'StateGridVouchers11'
            },
            {
                text        : i18n('sLast_accept_nas'),
                flex        : 1,
                dataIndex   : 'last_accept_nas',
                tdCls       : 'gridTree',
                hidden      : true,
                filter      : {type: 'string'},stateId: 'StateGridVouchers12'
            },
            {
                text        : i18n('sLast_reject_time'),
                flex        : 1,
                dataIndex   : 'last_reject_time',
                tdCls       : 'gridTree',
                hidden      : true,
                filter      : {type: 'date'},stateId: 'StateGridVouchers13'
            },
            {
                text        : i18n('sLast_reject_nas'),
                flex        : 1,
                dataIndex   : 'last_reject_nas',
                tdCls       : 'gridTree',
                hidden      : true,
                filter      : {type: 'string'},stateId: 'StateGridVouchers14'
            },
            {
                text        : i18n('sLast_reject_message'),
                flex        : 1,
                dataIndex   : 'last_reject_message',
                tdCls       : 'gridTree',
                hidden      : true,
                filter      : {type: 'string'},stateId: 'StateGridVouchers15'
            },
            {
                text        : 'Extra field name',
                flex        : 1,
                dataIndex   : 'extra_name',
                tdCls       : 'gridTree',
                hidden      : true,
                filter      : {type: 'string'},stateId: 'StateGridVouchers16'
            },
            {
                text        : 'Extra field value',
                flex        : 1,
                dataIndex   : 'extra_value',
                tdCls       : 'gridTree',
                hidden      : true,
                filter      : {type: 'string'},stateId: 'StateGridVouchers17'
            }         
        ];
        
        me.callParent(arguments);
    }
});
