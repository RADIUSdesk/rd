Ext.define('Rd.view.dynamicDetails.gridDynamicDetails' ,{
    extend      :'Ext.grid.Panel',
    alias       : 'widget.gridDynamicDetails',
    multiSelect : true,
    store       : 'sDynamicDetails',
    stateful    : true,
    stateId     : 'StateGridDynamicDetails',
    stateEvents :['groupclick','columnhide'],
    border      : false,
    requires    : [
        'Rd.view.components.ajaxToolbar'
    ],
    plugins     : 'gridfilters',  //*We specify this
    urlMenu     : '/cake3/rd_cake/dynamic-details/menu-for-grid.json',
   
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
            {xtype: 'rownumberer',stateId: 'StateGridDynamicDetails1'},
            { text: i18n('sOwner'),    dataIndex: 'owner',     tdCls: 'gridTree', flex: 1, filter: {type: 'string'},stateId: 'StateGridDynamicDetails2',
                hidden: true
            },
            { text: i18n('sName'),     dataIndex: 'name',      tdCls: 'gridMain', flex: 1, filter: {type: 'string'},stateId: 'StateGridDynamicDetails3'},
            { text: i18n('sPhone'),    dataIndex: 'phone',     tdCls: 'gridTree', flex: 1, filter: {type: 'string'},   hidden: true,stateId: 'StateGridDynamicDetails4'},
            { text: i18n('sFax'),      dataIndex: 'fax',       tdCls: 'gridTree', flex: 1, filter: {type: 'string'},   hidden: true,stateId: 'StateGridDynamicDetails5'},
            { text: i18n('sCell'),     dataIndex: 'cell',      tdCls: 'gridTree', flex: 1, filter: {type: 'string'},   hidden: true,stateId: 'StateGridDynamicDetails6'},
            { text: i18n('s_email'),   dataIndex: 'email',     tdCls: 'gridTree', flex: 1, filter: {type: 'string'},   hidden: true,stateId: 'StateGridDynamicDetails7'},
            { text: i18n('sURL'),      dataIndex: 'url',       tdCls: 'gridTree', flex: 1, filter: {type: 'string'},   hidden: true,stateId: 'StateGridDynamicDetails8'},
			{ text: 'Theme',           dataIndex: 'theme',     tdCls: 'gridTree', flex: 1, filter: {type: 'string'},stateId: 'StateGridDynamicDetails8a'},
            { 
                text:   i18n('sAvailable_to_sub_providers'),
                flex: 1,  
                xtype:  'templatecolumn', 
                tpl:    new Ext.XTemplate(
                            "<tpl if='available_to_siblings == true'><div class=\"fieldGreen\">"+i18n('sYes')+"</div></tpl>",
                            "<tpl if='available_to_siblings == false'><div class=\"fieldRed\">"+i18n('sNo')+"</div></tpl>"
                        ),
                dataIndex: 'available_to_siblings',
                    filter      : {
                        type    : 'boolean',
                        defaultValue   : false,
                        yesText : 'Yes',
                        noText  : 'No'
                },stateId: 'StateGridDynamicDetails9'
            },
            { 
                text    : i18n('sNotes'),
                sortable: false,
                width   : 130,
                xtype   : 'templatecolumn', 
                tpl     : new Ext.XTemplate(
                                "<tpl if='notes == true'><span class=\"fa fa-thumb-tack fa-lg txtGreen\"></tpl>"
                ),
                dataIndex: 'notes',stateId: 'StateGridDynamicDetails10'
            }
        ];
        me.callParent(arguments);
    }
});
