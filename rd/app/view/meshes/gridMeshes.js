Ext.define('Rd.view.meshes.gridMeshes' ,{
    extend:'Ext.grid.Panel',
    alias : 'widget.gridMeshes',
    multiSelect: true,
    store : 'sMeshes',
    stateful: true,
    stateId: 'StateGridMeshes',
    stateEvents:['groupclick','columnhide'],
    border: false,
    requires: [
        'Rd.view.components.ajaxToolbar'
    ],
    viewConfig: {
        loadMask:true
    },
    urlMenu: '/cake2/rd_cake/meshes/menu_for_grid.json',
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
            {xtype: 'rownumberer',stateId: 'StateGridMeshes1'},
            { text: i18n('sOwner'),     dataIndex: 'owner',         tdCls: 'gridTree', flex: 1,filter: {type: 'string'},stateId: 'StateGridMeshes2',
                hidden: true
            
            },
            { text: i18n('sName'),      dataIndex: 'name',          tdCls: 'gridMain', flex: 1,filter: {type: 'string'},stateId: 'StateGridMeshes3'},
			{ 
                text:   i18n('sAvailable_to_sub_providers'),
                flex: 1,
				hidden: true,  
                xtype:  'templatecolumn', 
                tpl:    new Ext.XTemplate(
                            "<tpl if='available_to_siblings == true'><div class=\"fieldGreen\">"+i18n("sYes")+"</div></tpl>",
                            "<tpl if='available_to_siblings == false'><div class=\"fieldRed\">"+i18n("sNo")+"</div></tpl>"
                        ),
                dataIndex: 'available_to_siblings',
                filter      : {
                        type    : 'boolean',
                        defaultValue   : false,
                        yesText : 'Yes',
                        noText  : 'No'
                },stateId: 'StateGridSsids4'
            },
            { text: i18n('sSSID'),      dataIndex: 'ssid',          tdCls: 'gridTree', flex: 1,filter: {type: 'string'},hidden: true,stateId: 'StateGridMeshes5'},
            { text: i18n('sBSSID'),    dataIndex: 'bssid',         tdCls: 'gridTree', flex: 1,filter: {type: 'string'},hidden: true,stateId: 'StateGridMeshes6'},           
            { 
                text        : i18n('sNode_count'),
                dataIndex   : 'node_count',    
                xtype       : 'templatecolumn', 
                tpl         : new Ext.XTemplate(
                            "<tpl><div class=\"fieldGreyWhite\">{node_count}</div></tpl>"
                        ),  
                stateId     : 'StateGridMeshes7', 
                width       : Rd.config.gridNumberCol        
            },
            { 
                text        : i18n('sNodes_up'),  
                dataIndex   : 'nodes_up',      
                xtype       :  'templatecolumn', 
                tpl         :    new Ext.XTemplate(
                            "<tpl if='nodes_up &gt; 0'><div class=\"fieldGreenWhite\">{nodes_up}</div>",
                            "<tpl else><div class=\"fieldBlue\">{nodes_up}</div></tpl>"
                        ),
                stateId     : 'StateGridMeshes8',
                width       : Rd.config.gridNumberCol
            },

            { 
                text        : i18n('sNodes_down'),  
                dataIndex   : 'nodes_down',      
                xtype       :  'templatecolumn', 
                tpl         :    new Ext.XTemplate(
                            "<tpl if='nodes_down &gt; 0'><div class=\"fieldRedWhite\">{nodes_down}</div>",
                            "<tpl else><div class=\"fieldBlue\">{nodes_down}</div></tpl>"
                        ),
                stateId     : 'StateGridMeshes9',
                width       : Rd.config.gridNumberCol
            },
            { 
                text    : i18n('sNotes'),
                sortable: false,
                width   : 130,
                xtype   : 'templatecolumn', 
                tpl     : new Ext.XTemplate(
                                "<tpl if='notes == true'><span class=\"fa fa-thumb-tack fa-lg txtGreen\"></tpl>"
                ),
                dataIndex: 'notes',stateId: 'StateGridMeshes10'
            }      
        ];
        me.callParent(arguments);
    }
});
