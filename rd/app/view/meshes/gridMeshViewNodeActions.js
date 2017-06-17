Ext.define('Rd.view.meshes.gridMeshViewNodeActions' ,{
    extend		:'Ext.grid.Panel',
    alias 		: 'widget.gridMeshViewNodeActions',
    border		: false,
    stateful	: true,
    multiSelect	: true,
    stateId		: 'StateGMVNA',
	nodeId		: '',
    stateEvents	:['groupclick','columnhide'],
    viewConfig	: {
        preserveScrollOnRefresh: true
    },
    requires	: [
        'Rd.view.components.ajaxToolbar',
		'Rd.model.mMeshViewNodeAction'
    ],
    urlMenu:        '/cake2/rd_cake/node_actions/menu_for_grid.json',
    urlIndex:       '/cake2/rd_cake/node_actions/index.json',
    columns: [
        {xtype: 'rownumberer',stateId: 'StateGMVNA1'},
        { text: i18n('sAction'),     dataIndex: 'action',        tdCls: 'gridTree', flex: 1, sortable: true,stateId: 'StateGMVNA2'},
        { text: i18n('sCommand'),    dataIndex: 'command',       tdCls: 'gridTree', flex: 1, sortable: true,stateId: 'StateGMVNA3'},
        { 
            text        : i18n('sStatus'),
            flex        : 1,
            tdCls       : 'gridTree',  
            xtype       : 'templatecolumn', 
            tpl         : new Ext.XTemplate(
                            "<tpl if='status == \"awaiting\"'><div class=\"fieldBlue\"><i class=\"fa fa-clock-o\"></i> "+i18n('sAwaiting')+"</div></tpl>",
                            "<tpl if='status == \"fetched\"'><div class=\"fieldGreenWhite\"><i class=\"fa fa-check-circle\"></i> "+i18n('sFetched')+"</div></tpl>"
            ),
            dataIndex   : 'status',stateId: 'StateGMVNA4'
        },
        { text: i18n('sCreated'),    dataIndex: 'created',       tdCls: 'gridTree', flex: 1, sortable: true,stateId: 'StateGMVNA5'},
        { text: i18n('sModified'),   dataIndex: 'modified',      tdCls: 'gridTree', flex: 1, sortable: true,stateId: 'StateGMVNA6'}
    ],
    initComponent: function(){

       var me      = this;  
        me.tbar     = Ext.create('Rd.view.components.ajaxToolbar',{'url': me.urlMenu});

        //Create a store specific to this Owner
        me.store = Ext.create(Ext.data.Store,{
            model: 'Rd.model.mMeshViewNodeAction',
            proxy: {
                type: 'ajax',
                format  : 'json',
                batchActions: true, 
                url   : me.urlIndex,
                reader: {
                    type: 'json',
                    rootProperty: 'items',
                    messageProperty: 'message'
                },
                api: {
                    destroy  : '/cake2/rd_cake/node_actions/delete.json'
                },
                simpleSortMode: true //This will only sort on one column (sort) and a direction(dir) value ASC or DESC
            },
            autoLoad: false    
        });
		me.store.getProxy().setExtraParam('node_id',me.nodeId);
		
		me.bbar     =  [
            {
                xtype       : 'pagingtoolbar',
                store       : me.store,
                dock        : 'bottom',
                displayInfo : true
            }  
        ];
		
        me.callParent(arguments);
    }
});
