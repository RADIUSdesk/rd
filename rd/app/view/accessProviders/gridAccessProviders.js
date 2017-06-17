Ext.define('Rd.view.accessProviders.gridAccessProviders' ,{
    extend      : 'Ext.grid.Panel',
    alias       : 'widget.gridAccessProviders',
    multiSelect : true,
    store       : 'sAccessProvidersGrid',
    stateful    : true,
    stateId     : 'StateGridAccessProviders',
    stateEvents : ['groupclick','columnhide'],
    border      : false,
    requires: [
                'Rd.view.components.ajaxToolbar'
    ],
    urlMenu     : '/cake3/rd_cake/access-providers/menu_for_grid.json', 
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
            {xtype: 'rownumberer', stateId: 'StateGridAccessProviders1'},
            {
                text        : i18n('sOwner'),
                sortable    : true,
                flex        : 1,
                dataIndex   : 'owner',
                tdCls       : 'gridTree',
                filter      : {type: 'string'}, stateId: 'StateGridAccessProviders2'
            },
            {
                text        : i18n('sUsername'),
                sortable    : true,
                flex        : 1,
                dataIndex   : 'username',
                tdCls       : 'gridMain',
                filter      : {type: 'string'}, stateId: 'StateGridAccessProviders3'
            },
            {
                text        : i18n('sName'),
                dataIndex   : 'name',
                tdCls       : 'gridTree',
                filter      : {type: 'string'}, stateId: 'StateGridAccessProviders4'
            },
            {
                text        : i18n('sSurname'),
                dataIndex   : 'surname',
                tdCls       : 'gridTree',
                filter      : {type: 'string'}, stateId: 'StateGridAccessProviders5'
            },
            {
                text        : i18n('sPhone'),
                dataIndex   : 'phone',
                tdCls       : 'gridTree',
                filter      : {type: 'string'}, stateId: 'StateGridAccessProviders6'
            },
            {
                text        : i18n('s_email'),
                flex        : 1,
                dataIndex   : 'email',
                tdCls       : 'gridTree',
                filter      : {type: 'string'}, stateId: 'StateGridAccessProviders7'
            },
            { 
                text        : i18n('sActive'),  
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
                }, stateId: 'StateGridAccessProviders9'
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
                stateId		: 'StateGridAccessProviders10',
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
                stateId		: 'StateGridAccessProviders11'
            },
             { 
                text    : i18n('sNotes'),
                sortable: false,
                width   : 130,
                xtype   : 'templatecolumn', 
                tpl     : new Ext.XTemplate(
                                "<tpl if='notes == true'><span class=\"fa fa-thumb-tack fa-lg txtGreen\"></tpl>"
                ),
                dataIndex: 'notes', stateId: 'StateGridAccessProviders12'
            }      
        ]; 
        me.callParent(arguments);
    }
});
