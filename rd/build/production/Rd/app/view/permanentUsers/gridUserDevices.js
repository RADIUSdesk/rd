Ext.define('Rd.view.permanentUsers.gridUserDevices' ,{
    extend:'Ext.grid.Panel',
    alias : 'widget.gridUserDevices',
    multiSelect: true,
    stateful: true,
    stateId: 'StateGridUserRadaccts',
    stateEvents:['groupclick','columnhide'],
    border: false,
    requires: [
        'Rd.view.components.ajaxToolbar'
    ],
    viewConfig: {
        loadMask:true
    },
    urlMenu: '/cake2/rd_cake/permanent_users/menu_for_user_devices.json',
    plugins     : 'gridfilters',  //*We specify this
    bbar: [
        {   xtype: 'component', itemId: 'count',   tpl: i18n('sResult_count_{count}'),   style: 'margin-right:5px', cls: 'lblYfi' }
    ],
    columns: [
            {xtype: 'rownumberer',stateId: 'StateGridUserRadaccts1'},
            { text: i18n('sOwner'),         dataIndex: 'user',    tdCls: 'gridTree', flex: 1,filter: {type: 'string'},stateId: 'StateGridUserRadaccts2'},
            { text: i18n('sMAC_address'),   dataIndex: 'name',    tdCls: 'gridTree', flex: 1,filter: {type: 'string'},stateId: 'StateGridUserRadaccts3'},
            { text: i18n('sDescription'),   dataIndex: 'description',tdCls: 'gridTree', flex: 1,filter: {type: 'string'},stateId: 'StateGridUserRadaccts4'},
            { text: i18n('sRealm'),         dataIndex: 'realm',     tdCls: 'gridTree', flex: 1,filter: {type: 'string'}, sortable: false,stateId: 'StateGridUserRadaccts5'},
            { text: i18n('sProfile'),       dataIndex: 'profile',   tdCls: 'gridTree', flex: 1,filter: {type: 'string'}, sortable: false,stateId: 'StateGridUserRadaccts6'},
            { 
                text        : i18n('sActive'),  
                xtype       : 'templatecolumn', 
                tpl         : new Ext.XTemplate(
                                "<tpl if='active == true'><div class=\"hasRight\">"+i18n("sYes")+"</div></tpl>",
                                "<tpl if='active == false'><div class=\"noRight\">"+i18n("sNo")+"</div></tpl>"
                            ),
                dataIndex   : 'active',
                filter      : { type: 'boolean'},stateId: 'StateGridUserRadaccts7'
            },
            {
                text        : i18n('sLast_accept_time'),
                flex        : 1,
                dataIndex   : 'last_accept_time',
                tdCls       : 'gridTree',
                hidden      : true,
                filter      : {type: 'date'}
            },
            {
                text        : i18n('sLast_accept_nas'),
                flex        : 1,
                dataIndex   : 'last_accept_nas',
                tdCls       : 'gridTree',
                hidden      : true,
                filter      : {type: 'string'}, stateId: 'StateGridUserRadaccts8'
            },
            {
                text        : i18n('sLast_reject_time'),
                flex        : 1,
                dataIndex   : 'last_reject_time',
                tdCls       : 'gridTree',
                hidden      : true,
                filter      : {type: 'date'}, stateId: 'StateGridUserRadaccts9'
            },
            {
                text        : i18n('sLast_reject_nas'),
                flex        : 1,
                dataIndex   : 'last_reject_nas',
                tdCls       : 'gridTree',
                hidden      : true,
                filter      : {type: 'string'},stateId: 'StateGridUserRadaccts10'
            },
            {
                text        : i18n('sLast_reject_message'),
                flex        : 1,
                dataIndex   : 'last_reject_message',
                tdCls       : 'gridTree',
                hidden      : true,
                filter      : {type: 'string'},stateId: 'StateGridUserRadaccts11'
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
                },stateId: 'StateGridUserRadaccts12'
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
                },stateId: 'StateGridUserRadaccts13'
            },
            { 
                text    : i18n('sNotes'),
                sortable: false,
                width   : 130,
                xtype   : 'templatecolumn', 
                tpl     : new Ext.XTemplate(
                                "<tpl if='notes == true'><div class=\"note\">"+i18n("sExisting_Notes")+"</div></tpl>"
                ),
                dataIndex: 'notes',stateId: 'StateGridUserRadaccts14'
            }      
    ],
    username: 'nobody', //dummy value
    initComponent: function(){
        var me      = this;
      
        me.tbar     = Ext.create('Rd.view.components.ajaxToolbar',{'url': me.urlMenu+'?user_id='+me.user_id+'&username='+me.username});

        //Create a store specific to this Permanent User
        me.store = Ext.create(Ext.data.Store,{
            model: 'Rd.model.mDevice',
            buffered: true,
            leadingBufferZone: 450, 
            pageSize: 150,
            //To force server side sorting:
            remoteSort: true,
            proxy: {
                type    : 'ajax',
                format  : 'json',
                batchActions: true, 
                url     : '/cake2/rd_cake/devices/index.json',
                extraParams: { 'permanent_user_id' : me.user_id },
                reader: {
                    keepRawData     : true,
                    type: 'json',
                    rootProperty: 'items',
                    messageProperty: 'message',
                    totalProperty: 'totalCount' //Required for dynamic paging
                },
                api: {
                    destroy  : '/cake2/rd_cake/devices/delete.json'
                },
                simpleSortMode: true //This will only sort on one column (sort) and a direction(dir) value ASC or DESC
            },
            listeners: {
                load: function(store, records, successful) {
                    if(!successful){
                        Ext.ux.Toaster.msg(
                            'Error encountered',
                            store.getProxy().getReader().rawData.message.message,
                            Ext.ux.Constants.clsWarn,
                            Ext.ux.Constants.msgWarn
                        );
                        //console.log(store.getProxy().getReader().rawData.message.message);
                    }else{
                        var count       = me.getStore().getTotalCount();
                        me.down('#count').update({count: count});

                    }   
                },
                scope: this
            }   
        });
       
        me.callParent(arguments);
    }
});
