Ext.define('Rd.view.meshes.gridNodeLists' ,{
    extend		:'Ext.grid.Panel',
    alias 		: 'widget.gridNodeLists',
    multiSelect	: true,
    stateful	: true,
    stateId		: 'StateGridNodeLists',
    stateEvents	:['groupclick','columnhide'],
    border		: false,
	//store		: 'sNodeLists',
    requires	: [
        'Rd.view.components.ajaxToolbar'
    ],
    viewConfig: {
        loadMask:true
    },
    urlMenu: '/cake2/rd_cake/meshes/menu_for_nodes_grid.json', //Same restrictions on the nodes will aplly here!
    plugins     : 'gridfilters',  //*We specify this
    initComponent: function(){
        var me      = this;

		me.store    = Ext.create(Rd.store.sNodeLists,{
            listeners: {
                load: function(store, records, successful) {
                    if(!successful){
                        Ext.ux.Toaster.msg(
                            i18n('sError_encountered'),
                            store.getProxy().getReader().rawData.message.message,
                            Ext.ux.Constants.clsWarn,
                            Ext.ux.Constants.msgWarn
                        );
                        //console.log(store.getProxy().getReader().rawData.message.message);
                    }
                },
                update: function(store, records, success, options) {
                    store.sync({
                        success: function(batch,options){
                            Ext.ux.Toaster.msg(
                                i18n('sUpdated_item'),
                                i18n('sItem_has_been_updated'),
                                Ext.ux.Constants.clsInfo,
                                Ext.ux.Constants.msgInfo
                            );   
                        },
                        failure: function(batch,options){
                            Ext.ux.Toaster.msg(
                                i18n('sProblems_updating_the_item'),
                                i18n('sItem_could_not_be_updated'),
                                Ext.ux.Constants.clsWarn,
                                Ext.ux.Constants.msgWarn
                            );
                        }
                    });
                },
                scope: this
            },
            autoLoad: false 
        });

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
            {xtype: 'rownumberer',stateId: 'StateGridNodeLists1'},
			{ 
                text        : i18n('sOwner'), 
                dataIndex   : 'owner', 
                tdCls       : 'gridTree', 
                flex        : 1,
                stateId     : 'StateGridNodeLists2', 
                sortable    : false,
                hidden      : true
            },
			{ text: 'Mesh',  dataIndex: 'mesh',  tdCls: 'gridTree', flex: 1,filter: {type: 'string'},stateId: 'StateGridNodeLists3'},
			{ 
                text        : i18n('sName'),   
                dataIndex   : 'name',  
                tdCls       : 'gridTree',
                width		: 130,
                renderer    : function(value,metaData, record){
                	var gateway = record.get('gateway');
                    if(gateway == 'yes'){
                        return "<div class=\"fieldGreen\" style=\"text-align:left;\"> "+value+"</div>";
                    }
                    if(gateway == 'no'){
                        return "<div class=\"fieldGrey\" style=\"text-align:left;\"> "+value+"</div>";
                    }  	             
                },
                stateId     : 'StateGridNodeLists4',
                flex        : 1,
                filter      : {type: 'string'}
            },
            { 
				text		: i18n('sDescription'), 
				dataIndex	: 'description',  
				tdCls		: 'gridTree', 
				flex		: 1,
				filter		: {type: 'string'},
				stateId		: 'StateGridNodeLists5'
			},
            { 
				text		: 'MAC Address',      	
				dataIndex	: 'mac',          
				tdCls		: 'gridTree', 
				flex		: 1,
				filter		: {type: 'string'},
				stateId: 'StateGridNodeLists6'
			},
            { 
				text		: i18n('sHardware'),      
				dataIndex	: 'hardware',     
				tdCls		: 'gridTree', 
				flex		: 1,
				filter		: {type: 'string'},
				stateId		: 'StateGridNodeLists7'
			},
            { 
                text        : 'Last contact',   
                dataIndex   : 'last_contact',  
                tdCls       : 'gridTree', 
                flex        : 1,
                renderer    : function(v,metaData, record){
                    var value = record.get('state');
                    if(value != 'never'){                    
                        var last_contact_human     = record.get('last_contact_human');
                        if(value == 'up'){
                            return "<div class=\"fieldGreenWhite\">"+last_contact_human+"</div>";
                        }
                        if(value == 'down'){
                            return "<div class=\"fieldRedWhite\">"+last_contact_human+"</div>";
                        }

                    }else{
                        return "<div class=\"fieldBlue\">Never</div>";
                    }              
                },stateId: 'StateGridNodeLists8'
            },
            { text: i18n('sIP_Address'), dataIndex: 'ip', tdCls: 'gridTree', flex: 1,filter : {type: 'string'}, stateId: 'StateGridNodeLists9'},
            { 
                text    : 'OpenVPN Connections',
                sortable: false,
                width   : 150,
                hidden  : true,
                flex    : 1,
                tdCls   : 'gridTree',
                xtype   : 'templatecolumn', 
                tpl:    new Ext.XTemplate(
                    '<tpl for="openvpn_list">',     // interrogate the realms property within the data
                        "<tpl if='lc_human == \"never\"'><div class=\"fieldBlue\">{name}</div>",
                        "<div style=\"font-size: 12px;\">(Never tested {name})</div>",
                        '<tpl else>',
                            "<tpl if='state == true'>",
                                "<div class=\"fieldGreen\">{name}</div>",
                                "<div style=\"font-size: 12px; color:#4d4d4d;\">Tested up {lc_human}</div>",
                            "</tpl>",
                            "<tpl if='state == false'>",
                                "<div class=\"fieldRed\">{name}</div>",
                                "<div style=\"font-size: 12px; color:#4d4d4d;\">Tested down {lc_human}</div>",
                            "</tpl>",
                        "</tpl>",
                    '</tpl>'
                ),
                dataIndex: 'openvpn_list',
                stateId : 'StateGridNodeLists10'
            }
        ];
        me.callParent(arguments);
    }
});
