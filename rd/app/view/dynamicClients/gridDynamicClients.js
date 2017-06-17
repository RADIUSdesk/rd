Ext.define('Rd.view.dynamicClients.gridDynamicClients' ,{
    extend      : 'Ext.grid.Panel',
    alias       : 'widget.gridDynamicClients',
    multiSelect : true,
    store       : 'sDynamicClients',
    stateful    : true,
    stateId     : 'StateGridDc1',
    stateEvents : ['groupclick','columnhide'],
    border      : false,
    requires    : [
        'Rd.view.components.ajaxToolbar'
    ],
    viewConfig: {
        loadMask:true
    },
    urlMenu: '/cake2/rd_cake/dynamic_clients/menu_for_grid.json',
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
            {xtype: 'rownumberer',stateId: 'StateGridDc1'},
            { text: i18n('sOwner'),        dataIndex: 'owner', tdCls: 'gridTree', flex: 1,filter: {type: 'string'},stateId: 'StateGridDc2',
                hidden: true
            },
            { text: i18n('sName'),         dataIndex: 'name',  tdCls: 'gridMain', flex: 1,filter: {type: 'string'},stateId: 'StateGridDc3'},
            { text: i18n('sNAS-Identifier'),dataIndex: 'nasidentifier',tdCls: 'gridMain', flex: 1, filter: {type: 'string'},stateId: 'StateGridDc4'},
            { text: i18n('sCalled-Station-Id'),dataIndex: 'calledstationid',tdCls: 'gridTree', flex: 1, filter: {type: 'string'},stateId: 'StateGridDc5',
                hidden: true
            },
            
            { 
                text        : i18n('sActive'), 
                width       : 130,
                hidden      : true,
                tdCls       : 'gridTree',
                xtype       : 'templatecolumn', 
                tpl         : new Ext.XTemplate(
                                "<tpl if='active == true'><div class=\"fieldGreen\">"+i18n("sYes")+"</div></tpl>",
                                "<tpl if='active == false'><div class=\"fieldRed\">"+i18n("sNo")+"</div></tpl>"
                            ),
                dataIndex   : 'active',
                filter      : {
                        type    : 'boolean',
                        defaultValue   : false,
                        yesText : 'Yes',
                        noText  : 'No'
                },stateId: 'StateGridDc6'
            },
            { 
                text:   'To Sub-Providers',
                width:  130,
                hidden  : true,
                tdCls   : 'gridTree',
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
                },stateId: 'StateGridDc7'
            },
            { 
                text    :   i18n('sRealms'),
                sortable: false,
                width   :  150,
                tdCls   : 'gridTree',
                xtype   :  'templatecolumn', 
                tpl:    new Ext.XTemplate(
                            '<tpl if="Ext.isEmpty(realms)"><div class=\"fieldBlueWhite\">Available to all!</div></tpl>', //Warn them when available     to all
                            '<tpl for="realms">',     // interrogate the realms property within the data
                                "<tpl if='available_to_siblings == true'><div class=\"fieldGreen\">{name}</div></tpl>",
                                "<tpl if='available_to_siblings == false'><div class=\"fieldRed\">{name}</div></tpl>",
                            '</tpl>'
                        ),
                dataIndex: 'realms'
            },
            { 
                text        : 'Last Contact',   
                dataIndex   : 'last_contact',
                width       : 150, 
                tdCls       : 'gridTree', 
                renderer    : function(v,metaData, record){
                    if(record.get('last_contact') == null){
                        return "<div class=\"fieldBlueWhite\">Never</div>";
                    }
                    var last_contact_human  = record.get('last_contact_human');
                    var green_flag          = false; //We show contact from the last seconds and minutes as geeen
                    if(
                        (last_contact_human.match(/just now/g))||
                        (last_contact_human.match(/minute/g))||
                        (last_contact_human.match(/second/g))
                    ){
                        green_flag = true;
                    }
                    if(green_flag){
                        return "<div class=\"fieldGreenWhite\">"+last_contact_human+"</div>";
                    }else{
                        return "<div class=\"fieldPurpleWhite\">"+last_contact_human+"</div>";
                    }     
                },stateId: 'StateGridUdc4'
            },
			{ 

                text        : 'From IP', 
                dataIndex   : 'last_contact_ip',          
                tdCls       : 'gridTree', 
                width       : 150,
                xtype       :  'templatecolumn', 
                tpl         :  new Ext.XTemplate(
                    '<tpl if="Ext.isEmpty(last_contact_ip)"><div class=\"fieldGreyWhite\">Not Available</div>',
                    '<tpl else>',
                    '<div class=\"fieldGreyWhite\">{last_contact_ip}</div>',
                    "<tpl if='Ext.isEmpty(city)'><tpl else>",
                        '<div><b>{city}</b>  ({postal_code})</div>',
                    "</tpl>",
                    "<tpl if='Ext.isEmpty(country_name)'><tpl else>",
                        '<div><b>{country_name}</b> ({country_code})</div>',
                    "</tpl>",
                    "</tpl>"   
                ), 
                filter		: {type: 'string'},stateId: 'StateGridUdc5'
            },
            { 
                text        : i18n("sStatus"),   
                dataIndex   : 'status',  
                tdCls       : 'gridTree', 
                width       :  130,
                hidden      : true,
                renderer    : function(value,metaData, record){
                    if(value != 'unknown'){                    
                        var online      = record.get('status_time');
                        if(value == 'up'){
                            return "<div class=\"fieldGreen\">"+i18n("sUp")+" "+Ext.ux.secondsToHuman(online)+"</div>";
                        }
                        if(value == 'down'){
                            return "<div class=\"fieldRed\">"+i18n("sDown")+" "+Ext.ux.secondsToHuman(online)+"</div>";
                        }

                    }else{
                        return "<div class=\"fieldBlue\">"+i18n("sUnknown")+"</div>";
                    }              
                },stateId: 'StateGridDc10'
            },
            { 
                text    : i18n('sNotes'),
                sortable: false,
                width   : 130,
                hidden  : true,
                xtype   : 'templatecolumn', 
                tpl     : new Ext.XTemplate(
                                "<tpl if='notes == true'><span class=\"fa fa-thumb-tack fa-lg txtGreen\"></tpl>"
                ),
                dataIndex: 'notes',stateId: 'StateGridDc11'
            }     
        ];     
        me.callParent(arguments);
    }
});
