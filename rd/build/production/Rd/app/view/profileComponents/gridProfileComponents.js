Ext.define('Rd.view.profileComponents.gridProfileComponents' ,{
    extend:'Ext.grid.Panel',
    alias : 'widget.gridProfileComponents',
    multiSelect: true,
    store : 'sProfileComponents',
    stateful: true,
    stateId: 'StateGridProfileComponents',
    stateEvents:['groupclick','columnhide'],
    border: false,
    requires: [
        'Rd.view.components.ajaxToolbar'
    ],
    viewConfig: {
        loadMask:true
    },
    urlMenu: '/cake2/rd_cake/profile_components/menu_for_grid.json',
    plugins     : 'gridfilters',
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
            {xtype: 'rownumberer',stateId: 'StateGridProfileComponents1'},
            { text: i18n('sOwner'),        dataIndex: 'owner', tdCls: 'gridTree', flex: 1,filter: {type: 'string'},stateId: 'StateGridProfileComponents2',
                hidden : true 
            },
            { text: i18n('sName'),         dataIndex: 'name',  tdCls: 'gridMain', flex: 1,filter: {type: 'string'},stateId: 'StateGridProfileComponents3'},
            { text: i18n('sCheck_attribute_count'),  dataIndex: 'check_attribute_count',  tdCls: 'gridTree', flex: 1,stateId: 'StateGridProfileComponents4'},
            { text: i18n('sReply_attribute_count'),  dataIndex: 'reply_attribute_count',  tdCls: 'gridTree', flex: 1,stateId: 'StateGridProfileComponents5'},
            { 
                text:   i18n('sAvailable_to_sub_providers'),
                flex: 1,  
                xtype:  'templatecolumn', 
                tpl:    new Ext.XTemplate(
                            "<tpl if='available_to_siblings == true'><div class=\"fieldGreen\">"+i18n("sYes")+"</div></tpl>",
                            "<tpl if='available_to_siblings == false'><div class=\"fieldRed\">"+i18n("sNo")+"</div></tpl>"
                        ),
                dataIndex: 'available_to_siblings',
                filter  : {
                    type: 'boolean'    
                },stateId: 'StateGridProfileComponents6'
            },
            { 
                text    : i18n('sNotes'),
                sortable: false,
                width   : 130,
                xtype   : 'templatecolumn', 
                tpl     : new Ext.XTemplate(
                                "<tpl if='notes == true'><span class=\"fa fa-thumb-tack fa-lg txtGreen\"></tpl>"
                ),
                dataIndex: 'notes',stateId: 'StateGridProfileComponents7'
            }      
        ];  
        me.callParent(arguments);
    }
});
