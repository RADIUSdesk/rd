Ext.define('Rd.view.permanentUsers.gridPermanentUsers' ,{
    extend:'Ext.grid.Panel',
    alias : 'widget.gridPermanentUsers',
    multiSelect: true,
    store : 'sPermanentUsers',
    stateful: true,
    stateId: 'StateGridPermanentUsers',
    stateEvents:['groupclick','columnhide'],
    border: false,
    requires: [
        'Rd.view.components.ajaxToolbar'
    ],
    viewConfig: {
        loadMask:true
    },
    urlMenu: '/cake2/rd_cake/permanent_users/menu_for_grid.json',
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
            {xtype: 'rownumberer',stateId: 'StateGridPermanentUsers1'},
            { text: i18n('sOwner'),        dataIndex: 'owner',      tdCls: 'gridTree', flex: 1,filter: {type: 'string'},stateId: 'StateGridPermanentUsers2', hidden: true
            },
            { text: i18n('sUsername'),     dataIndex: 'username',   tdCls: 'gridMain', flex: 1,filter: {type: 'string'},stateId: 'StateGridPermanentUsers3'},
            { text: i18n('sAuth_type'),    dataIndex: 'auth_type',  tdCls: 'gridTree', flex: 1,filter: {type: 'string'},stateId: 'StateGridPermanentUsers4'},
            { text: i18n('sRealm'),        dataIndex: 'realm',      tdCls: 'gridTree', flex: 1,filter: {type: 'string'}, sortable: false,stateId: 'StateGridPermanentUsers5'},
            { text: i18n('sProfile'),      dataIndex: 'profile',    tdCls: 'gridTree', flex: 1,filter: {type: 'string'}, sortable: false,stateId: 'StateGridPermanentUsers6'},
            {
                text        : i18n('sName'),
                flex        : 1,
                dataIndex   : 'name',
                tdCls       : 'gridTree',
                hidden      : true,
                filter      : {type: 'string'},stateId: 'StateGridPermanentUsers7'
            },
            {
                text        : i18n('sSurname'),
                flex        : 1,
                dataIndex   : 'surname',
                tdCls       : 'gridTree',
                hidden      : true,
                filter      : {type: 'string'},stateId: 'StateGridPermanentUsers8'
            },
            {
                text        : i18n('sPhone'),
                dataIndex   : 'phone',
                tdCls       : 'gridTree',
                hidden      : true,
                filter      : {type: 'string'},stateId: 'StateGridPermanentUsers9'
            },
            {
                text        : i18n('s_email'),
                flex        : 1,
                dataIndex   : 'email',
                tdCls       : 'gridTree',
                hidden      : true,
                filter      : {type: 'string'},stateId: 'StateGridPermanentUsers10'
            },
            {
                text        : i18n('sAddress'),
                flex        : 1,
                dataIndex   : 'address',
                tdCls       : 'gridTree',
                hidden      : true,
                filter      : {type: 'string'},stateId: 'StateGridPermanentUsers11'
            },
            { 
                text        : i18n('sActive'),  
                xtype       : 'templatecolumn', 
                tpl         : new Ext.XTemplate(
                                "<tpl if='active == true'><div class=\"hasRight\">"+i18n("sYes")+"</div></tpl>",
                                "<tpl if='active == false'><div class=\"noRight\">"+i18n("sNo")+"</div></tpl>"
                            ),
                dataIndex   : 'active',
                filter      : { type: 'boolean'},stateId: 'StateGridPermanentUsers12'
            },
            {
                text        : i18n('sLast_accept_time'),
                flex        : 1,
                dataIndex   : 'last_accept_time',
                tdCls       : 'gridTree',
                hidden      : true,
                filter      : {type: 'date'},stateId: 'StateGridPermanentUsers13'
            },
            {
                text        : i18n('sLast_accept_nas'),
                flex        : 1,
                dataIndex   : 'last_accept_nas',
                tdCls       : 'gridTree',
                hidden      : true,
                filter      : {type: 'string'},stateId: 'StateGridPermanentUsers14'
            },
            {
                text        : i18n('sLast_reject_time'),
                flex        : 1,
                dataIndex   : 'last_reject_time',
                tdCls       : 'gridTree',
                hidden      : true,
                filter      : {type: 'date'},stateId: 'StateGridPermanentUsers15'
            },
            {
                text        : i18n('sLast_reject_nas'),
                flex        : 1,
                dataIndex   : 'last_reject_nas',
                tdCls       : 'gridTree',
                hidden      : true,
                filter      : {type: 'string'},stateId: 'StateGridPermanentUsers16'
            },
            {
                text        : i18n('sLast_reject_message'),
                flex        : 1,
                dataIndex   : 'last_reject_message',
                tdCls       : 'gridTree',
                hidden      : true,
                filter      : {type: 'string'},stateId: 'StateGridPermanentUsers17'
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
                },stateId: 'StateGridPermanentUsers18'
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
                },stateId: 'StateGridPermanentUsers19'
            },
			{
                text        : 'Static IP',
                flex        : 1,
                dataIndex   : 'static_ip',
                tdCls       : 'gridTree',
                hidden      : true,
                filter      : {type: 'string'},
				stateId		: 'StateGridPermanentUsers20'
            },
			{
                text        : 'Extra name',
                flex        : 1,
                dataIndex   : 'extra_name',
                tdCls       : 'gridTree',
                hidden      : true,
                filter      : {type: 'string'},
				stateId		: 'StateGridPermanentUsers21'
            },
			{
                text        : 'Extra value',
                flex        : 1,
                dataIndex   : 'extra_value',
                tdCls       : 'gridTree',
                hidden      : true,
                filter      : {type: 'string'},
				stateId		: 'StateGridPermanentUsers22'
            },
            { 
                text    : i18n('sNotes'),
                sortable: false,
                width   : 130,
                xtype   : 'templatecolumn', 
                tpl     : new Ext.XTemplate(
                                "<tpl if='notes == true'><span class=\"fa fa-thumb-tack fa-lg txtGreen\"></tpl>"
                ),
                dataIndex: 'notes',stateId: 'StateGridPermanentUsers23'
            }      
        ];

 
        me.callParent(arguments);
    }
});
