Ext.define('Rd.view.aps.gridApLists' ,{
    extend		: 'Ext.grid.Panel',
    alias 		: 'widget.gridApLists',
    multiSelect	: true,
    stateful	: true,
    stateId		: 'StateGridApLists',
    stateEvents	: ['groupclick','columnhide'],
    store       : 'sApLists',
    border		: false,
    requires	: [
        'Rd.view.components.ajaxToolbar'
    ],
    viewConfig  : {
        loadMask:true
    },
    urlMenu     : '/cake2/rd_cake/ap_profiles/menu_for_aps_grid.json', 
    plugins     : [
        'gridfilters',
        {
            ptype: 'rowexpander',
            rowBodyTpl : new Ext.XTemplate(
                '<div style="color:grey;  background-color:white; padding:5px;">',
                '<img src="resources/images/MESHdesk/{hardware}.png" alt="{hardware}" height="72" style="float: left; padding-right: 20px;">',
                '<h2>{name}</h2>',
                '<span>{hw_human}</span>',
                '</div>',
                '<div class="sectionHeader">',
                    '<h2>DEVICE INFORMATION (for the past hour)</h2>',
                '</div>',
                "<div style='background-color:white; padding:5px;'>",
                   '<ul class="fa-ul">',    
                    "<tpl if='state == \"never\"'>",
                    "<li style='color:blue;'><i class='fa-li fa fa-question-circle'></i>Never connected before</li>",
                    "</tpl>",
                    "<tpl if='state == \"down\"'>",
                    "<li style='color:red;'><i class='fa-li fa  fa-exclamation-circle'></i>Offline (last check-in <b>{last_contact_human}</b> ago).</li>",
                    "</tpl>",
                    "<tpl if='state == \"up\"'>",
                    '<li style="color:green;"><i class="fa-li fa fa-check-circle"></i>Online (last check-in <b>{last_contact_human}</b> ago).</li>',
                    "</tpl>",
                    '<tpl for="ssids">',
                        '<li><i class="fa-li fa fa-wifi"></i><b>{name}</b> had <b>{users}</b> users.</li>',
                    '</tpl>',                  
  '<li><i class="fa-li fa fa-info-circle"></i>Public IP <b>{last_contact_from_ip}</b>.</li>',
  '<li><i class="fa-li fa fa-database"></i>Data usage <b>{data_past_hour}</b>.</li>',
  '<li><i class="fa-li fa fa-link"></i>Last connection from <b>{newest_station}</b> which was <b>{newest_time}</b> ({newest_vendor}).</li>',
'</ul>',
                "</div>"
            )
        }
    ],
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
            {xtype: 'rownumberer',stateId: 'StateGridApLists1'},
			{ 
                text        : i18n("sOwner"), 
                dataIndex   : 'owner', 
                tdCls       : 'gridTree', 
                flex        : 1,
                stateId     : 'StateGridApLists2', 
                sortable    : false,
                hidden      : true
            },
			{ text: i18n("sProfile"),  dataIndex: 'ap_profile',  tdCls: 'gridTree', flex: 1,filter: {type: 'string'},stateId: 'StateGridApLists3'},
            { text: i18n("sName"),  dataIndex: 'name',  tdCls: 'gridMain', flex: 1,filter: {type: 'string'},stateId: 'StateGridApLists4'},
            { 
				text		: i18n("sDescription"), 
				dataIndex	: 'description',  
				tdCls		: 'gridTree', 
				flex		: 1,
				filter		: {type: 'string'},
				stateId		: 'StateGridApLists5',
				hidden      : true
			},
            { 
				text		: i18n("sMAC_address"),      	
				dataIndex	: 'mac',          
				tdCls		: 'gridTree', 
				flex		: 1,
				filter		: {type: 'string'},
				stateId     : 'StateGridApLists6'
			},
            { 
				text		: i18n("sHardware"),      
				dataIndex	: 'hardware',     
				tdCls		: 'gridTree', 
				flex		: 1,
				filter		: {type: 'string'},
				stateId		: 'StateGridApLists7',
				hidden      : true
			},
			{ 
                text        : i18n("sLast_contact"),   
                dataIndex   : 'last_contact',  
                tdCls       : 'gridTree', 
                width       : 170,
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
                },stateId: 'StateGridApLists8'
            },
            { 

                text        : i18n("sFrom_IP"), 
                dataIndex   : 'last_contact_from_ip',          
                tdCls       : 'gridTree', 
                width       : 170,
                xtype       :  'templatecolumn', 
                tpl         :  new Ext.XTemplate(
                    '<tpl if="Ext.isEmpty(last_contact_from_ip)"><div class=\"fieldGreyWhite\">Not Available</div>',
                    '<tpl else>',
                    '<div class=\"fieldGreyWhite\">{last_contact_from_ip}</div>',
                    "<tpl if='Ext.isEmpty(city)'><tpl else>",
                        '<div><b>{city}</b>  ({postal_code})</div>',
                    "</tpl>",
                    "<tpl if='Ext.isEmpty(country_name)'><tpl else>",
                        '<div><b>{country_name}</b> ({country_code})</div>',
                    "</tpl>",
                    "</tpl>"   
                ), 
                filter		: {type: 'string'},stateId: 'StateGridApLists9'
            },
            { 
                text    : 'Last command',
                sortable: false,
                width   : 170,
                tdCls   : 'gridTree', 
                xtype   : 'templatecolumn', 
                tpl:    new Ext.XTemplate(
                "<tpl if='last_cmd_status == \"\"'><div class=\"fieldBlue\">(nothing)</div></tpl>", 
                "<tpl if='last_cmd_status == \"awaiting\"'><div class=\"fieldBlue\"><i class=\"fa fa-clock-o\"></i> {last_cmd}</div></tpl>",
                "<tpl if='last_cmd_status == \"fetched\"'><div class=\"fieldGreen\"><i class=\"fa fa-check-circle\"></i> {last_cmd}</div></tpl>"
                ),
                stateId	: 'StateGridApLists10',
				hidden	: false
            },
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
                stateId	: 'StateGridApLists11'
            }
        ];
        me.callParent(arguments);
    }
});
