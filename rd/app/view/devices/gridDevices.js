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
    urlMenu: '/cake3/rd_cake/devices/menu-for-grid.json',
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
            { text: i18n('sOwner'),dataIndex: 'permanent_user',     tdCls: 'gridTree', flex: 1,filter: {type: 'string'},stateId: 'StateGridDevices2'},
            { text: i18n('sMAC_address'),   dataIndex: 'name',      tdCls: 'gridMain', flex: 1,filter: {type: 'string'},stateId: 'StateGridDevices3'},
            { text: i18n('sDescription'),   dataIndex: 'description',tdCls: 'gridTree', flex: 1,filter: {type: 'string'},stateId: 'StateGridDevices4'},
           // { text: i18n('sVendor'),        dataIndex: 'vendor',    tdCls: 'gridTree', flex: 1,filter: {type: 'string'}},
            { text: i18n('sRealm'),         dataIndex: 'realm',     tdCls: 'gridTree', flex: 1,filter: {type: 'string'}, sortable: false,stateId: 'StateGridDevices5'},
            { text: i18n('sProfile'),       dataIndex: 'profile',   tdCls: 'gridTree', flex: 1,filter: {type: 'string'}, sortable: false,stateId: 'StateGridDevices6'},
            { 
                text        : i18n('sActive'),
                tdCls       : 'gridTree',   
                xtype       : 'templatecolumn', 
                tpl         : new Ext.XTemplate(
                                 "<tpl if='active == true'><div class=\"fieldGreen\"><i class=\"fa fa-check-circle\"></i> "+i18n("sYes")+"</div></tpl>",
                                "<tpl if='active == false'><div class=\"fieldRed\"><i class=\"fa fa-times-circle\"></i> "+i18n("sNo")+"</div></tpl>"
                            ),
                dataIndex   : 'active',
                filter      : {
                        type            : 'boolean',
                        defaultValue    : false,
                        yesText         : 'Yes',
                        noText          : 'No'
                },stateId: 'StateGridDevices7'
            },
            { 
                text        : i18n('sLast_accept_time'),
                dataIndex   : 'last_accept_time',
                tdCls       : 'gridTree',
                hidden      : true, 
                xtype       : 'templatecolumn', 
                tpl         : new Ext.XTemplate(
                    "<div class=\"fieldBlue\">{last_accept_time_in_words}</div>"
                ),
                flex        : 1,
                filter      : {type: 'date',dateFormat: 'Y-m-d'},
                stateId		: 'StateGridDevices8'
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
                dataIndex   : 'last_reject_time',
                tdCls       : 'gridTree',
                hidden      : true, 
                xtype       : 'templatecolumn', 
                tpl         : new Ext.XTemplate(
                    "<div class=\"fieldBlue\">{last_reject_time_in_words}</div>"
                ),
                flex        : 1,
                filter      : {type: 'date',dateFormat: 'Y-m-d'},
                stateId		: 'StateGridDevices10'
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
                hidden      : true,
                dataIndex   : 'perc_data_used',
                width       : 110,
                xtype       : 'widgetcolumn',
                tdCls       : 'gridTree',
                widget: {
                    xtype   : 'progressbarwidget'
                },
                onWidgetAttach: function(column, widget, record) {
                    var v = record.get('perc_data_used');
                    widget.toggleCls("wifigreen",true);
                    if(v == null){
                     widget.setText('');
                    }else{
                        var cls = "wifigreen";
                        if(v > 70){
                            cls = "wifiyellow";
                        }
                        if(v > 90){
                            cls = "wifired"
                        }  
                        widget.setValue(v / 100);
                        widget.setText( v +" %");
                        widget.toggleCls(cls,true);
                    }    
                },
                stateId: 'StateGridDevices13'
            },          
            {
                header      : i18n('sTime_used'),
                hidden      : true,
                dataIndex   : 'perc_time_used',
                width       : 110,
                xtype       : 'widgetcolumn',
                tdCls       : 'gridTree',
                widget      : {
                    xtype   : 'progressbarwidget'
                },
                onWidgetAttach: function(column, widget, record) {
                    var v = record.get('perc_time_used');            
                    widget.toggleCls("wifired",true);
                    if(v == null){
                      widget.setText('');
                    }else{
                        var cls = "wifigreen";
                        if(v > 70){
                            cls = "wifiyellow";
                        }
                        if(v > 90){
                            cls = "wifired"
                        }  
                        widget.setValue(v / 100);
                        widget.setText( v +" %");
                        widget.toggleCls(cls,true);
                    }    
                },
                stateId: 'StateGridDevices14'
            },
            { 
                text        : 'Created',
                dataIndex   : 'created', 
                tdCls       : 'gridTree',
                hidden      : true,  
                xtype       : 'templatecolumn', 
                tpl         : new Ext.XTemplate(
                    "<div class=\"fieldBlue\">{created_in_words}</div>"
                ),
                stateId		: 'StateGridDevices15',
                filter      : {type: 'date',dateFormat: 'Y-m-d'},
                flex        : 1
            },  
            { 
                text        : 'Modified',
                dataIndex   : 'modified', 
                tdCls       : 'gridTree',
                hidden      : true, 
                xtype       : 'templatecolumn', 
                tpl         : new Ext.XTemplate(
                    "<div class=\"fieldBlue\">{modified_in_words}</div>"
                ),
                flex        : 1,
                filter      : {type: 'date',dateFormat: 'Y-m-d'},
                stateId		: 'StateGridDevices16'
            },   
            { 
                text    : i18n('sNotes'),
                sortable: false,
                width   : 130,
                xtype   : 'templatecolumn', 
                tdCls   : 'gridTree',
                tpl     : new Ext.XTemplate(
                                "<tpl if='notes == true'><span class=\"fa fa-thumb-tack fa-lg txtGreen\"></tpl>"
                ),
                dataIndex: 'notes',stateId: 'StateGridDevices17'
            }      
        ];

        me.callParent(arguments);
    }
});
