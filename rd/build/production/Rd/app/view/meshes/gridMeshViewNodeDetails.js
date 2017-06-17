Ext.define('Rd.view.meshes.gridMeshViewNodeDetails' ,{
    extend		:'Ext.grid.Panel',
    alias 		: 'widget.gridMeshViewNodeDetails',
    multiSelect	: true,
    stateful	: true,
    stateId		: 'StateGMVND',
    stateEvents:['groupclick','columnhide'],
    border		: false,
	requires    : [
		'Rd.view.components.ajaxToolbar',
        'Rd.store.sNodeDetails',
        'Rd.model.mNodeDetail'
    ],
    viewConfig	: {
        loadMask	:true
    },
    urlMenu		: '/cake2/rd_cake/meshes/menu_for_node_details_grid.json',
    plugins     : [
        {
            ptype: 'rowexpander',
            rowBodyTpl : new Ext.XTemplate(
                '<div style="color:grey;  background-color:white; padding:5px;">',
                '<img src="resources/images/MESHdesk/{hardware}.png" alt="{hardware}" height="72" style="float: left; padding-right: 20px;">',
                '<h2>{name}</h2>',
                '<span>{hw_human}</span>',
                '</div>',
                '<div class="sectionHeader">',
                    '<h2>DEVICE INFORMATION</h2>',
                '</div>',
                "<div style='background-color:white; padding:5px;'>",
                    "<label class='lblMap'>MESH IP  </label><label class='lblValue'>{ip}</label>",
					"<div style='clear:both;'></div>",
					"<label class='lblMap'>Main MAC </label><label class='lblValue'> {mac}</label>",
					"<div style='clear:both;'></div>",
                    "<label class='lblMap'>Description </label><label class='lblValue'> {description}</label>",
					"<div style='clear:both;'></div>",
                    "<label class='lblMap'>On public maps </label><label class='lblValue'> {on_public_maps}</label>",
					"<div style='clear:both;'></div>",
					"<label class='lblMap'>Status </label>",
                    "<tpl if='state == \"down\"'><label class='lblValue fieldRedWhite'>{state}</label></tpl>",
                    "<tpl if='state == \"up\"'><label class='lblValue fieldGreenWhite'> {state}</label></tpl>",
					"<div style='clear:both;'></div>",
                    "<label class='lblMap'>Uptime </label><label class='lblValue'> {uptime}</label><br>",
                "</div>"
            )
        }
    ],
    initComponent: function(){
        var me      = this;

        me.store    = Ext.create(Rd.store.sNodeDetails,{});
        me.store.getProxy().setExtraParam('mesh_id',me.meshId);   
        me.tbar     = Ext.create('Rd.view.components.ajaxToolbar',{'url': me.urlMenu});
        
        me.columns  = [
            {xtype: 'rownumberer',stateId: 'StateGMVND1'},
            { text: i18n.sName,              dataIndex: 'name',          tdCls: 'gridTree', width: 130,stateId: 'StateGMVND2'},
            { text: i18n.sDescription,       dataIndex: 'description',   tdCls: 'gridTree', flex: 1,stateId: 'StateGMVND3', hidden : true},
            { text: 'MAC_Address',       		dataIndex: 'mac',           tdCls: 'gridTree', width: 130,stateId: 'StateGMVND4'},
            { text: i18n.sHardware,          dataIndex: 'hw_human',      tdCls: 'gridTree', flex: 1,stateId: 'StateGMVND5', hidden : true},
            { text: i18n.sPower,             dataIndex: 'power',         tdCls: 'gridTree', flex: 1,stateId: 'StateGMVND6', hidden : true},
            { text: i18n.sIP_Address,        dataIndex: 'ip',            tdCls: 'gridTree', width: 110,stateId: 'StateGMVND7'},
			{ text: 'Uptime',        			dataIndex: 'uptime',   		tdCls: 'gridTree', width: 110,stateId: 'StateGMVND8'},
			{ text: 'System time',      		dataIndex: 'system_time',   tdCls: 'gridTree', width: 110,stateId: 'StateGMVND9'},
			{ 
                text        : 'System load',   
                dataIndex   : 'mem_total',  
                tdCls       : 'gridTree', 
                width		: 130,
                renderer    : function(value,metaData, record){
                	var mem_free 	= record.get('mem_free');
                    var load		= record.get('load_1')+" "+record.get('load_2')+" "+record.get('load_3');
					return Ext.ux.bytesToHuman(mem_free)+"/"+Ext.ux.bytesToHuman(value)+"<br>("+load+")";	             
                },stateId: 'StateGMVND10',
                hidden : true
            },
           	{ 
                text    : 'Firmware',
                sortable: false,
                flex    : 1,  
                xtype   : 'templatecolumn', 
                tpl:    new Ext.XTemplate(
                            '<tpl if="Ext.isEmpty(release)"><div class=\"gridRealm noRight\">Not available</div></tpl>', 
                            '<tpl for="release">',     
                                "<tpl>{value}<br></tpl>",
                            '</tpl>'
                        ),
                dataIndex: 'release',stateId: 'StateGMVND11',
				hidden	: true
            }, 
            { 
                text    : 'Hardware',
                sortable: false,
                flex    : 1,  
                xtype   :  'templatecolumn', 
                tpl:    new Ext.XTemplate(
                            '<tpl if="Ext.isEmpty(cpu)"><div class=\"gridRealm noRight\">Not available</div></tpl>', 
                            '<tpl for="cpu">',     
                                "<tpl>{value}<br></tpl>",
                            '</tpl>'
                        ),
                dataIndex: 'cpu',stateId: 'StateGMVND12',
				hidden	: true
            },
			{ 
                text        : 'Last contact',   
                dataIndex   : 'state',  
                tdCls       : 'gridTree', 
                flex        : 1,
                renderer    : function(value,metaData, record){
                    if(value != 'never'){                    
                        var last_contact     = record.get('last_contact_human');
                        if(value == 'up'){
                            return "<div class=\"fieldGreen\">"+last_contact+"</div>";
                        }
                        if(value == 'down'){
                            return "<div class=\"fieldRed\">"+last_contact+"</div>";
                        }

                    }else{
                        return "<div class=\"fieldBlue\">Never</div>";
                    }              
                },stateId: 'StateGMVND13'
            },
			{ 
                text    : 'Last command',
                sortable: false,
                flex    : 1,  
                xtype   : 'templatecolumn', 
                tpl:    new Ext.XTemplate(
                            "<tpl if='last_cmd_status == \"\"'><div class=\"fieldBlue\">(nothing)</div></tpl>", 
                            "<tpl if='last_cmd_status == \"awaiting\"'><div class=\"fieldBlue\">{last_cmd}</div></tpl>",
                            "<tpl if='last_cmd_status == \"fetched\"'><div class=\"fieldGreen\">{last_cmd}</div></tpl>"
                        ),
                stateId	: 'StateGMVND14',
				hidden	: false
            }
        ];
        me.callParent(arguments);
    }
});
