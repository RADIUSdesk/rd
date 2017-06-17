Ext.define('Rd.view.aps.gridApProfiles' ,{
    extend      :'Ext.grid.Panel',
    alias       : 'widget.gridApProfiles',
    multiSelect : true,
    store       : 'sApProfiles',
    stateful    : true,
    stateId     : 'StateGridApProfiles',
    stateEvents :['groupclick','columnhide'],
    border      : false,
    requires: [
        'Rd.view.components.ajaxToolbar'
    ],
    viewConfig  : {
        loadMask    : true
    },
    urlMenu     : '/cake2/rd_cake/ap_profiles/menu_for_grid.json',
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
            {xtype: 'rownumberer',stateId: 'StateGridApProfiles1'},
            { text: i18n("sOwner"),     dataIndex: 'owner',         tdCls: 'gridTree', flex: 1,filter: {type: 'string'},stateId: 'StateGridApProfiles2', hidden : true},
            { text: i18n("sName"),      dataIndex: 'name',          tdCls: 'gridMain', flex: 1,filter: {type: 'string'},stateId: 'StateGridApProfiles3'},
			{ 
                text:   i18n("sAvailable_to_sub_providers"),
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
                },stateId: 'StateGridApProfiles4'
            },
            { 
                text        : 'AP Count',
                dataIndex   : 'ap_count',    
                xtype       :  'templatecolumn', 
                tpl         :    new Ext.XTemplate(
                            "<tpl><div class=\"fieldGreyWhite\">{ap_count}</div></tpl>"
                        ),  
                stateId     : 'StateGridApProfiles7', 
                width       : Rd.config.gridNumberCol
                
                },
            { 
                text        : 'APs Up',  
                dataIndex   : 'aps_up',      
                xtype       :  'templatecolumn', 
                tpl         :    new Ext.XTemplate(
                            "<tpl if='aps_up &gt; 0'><div class=\"fieldGreenWhite\">{aps_up}</div>",
                            "<tpl else><div class=\"fieldBlue\">{aps_up}</div></tpl>"
                        ),
                stateId     : 'StateGridApProfiles8',
                width       : Rd.config.gridNumberCol
            },

            { 
                text        : 'APs Down',  
                dataIndex   : 'aps_down',      
                xtype       :  'templatecolumn', 
                tpl         :    new Ext.XTemplate(
                            "<tpl if='aps_down &gt; 0'><div class=\"fieldRedWhite\">{aps_down}</div>",
                            "<tpl else><div class=\"fieldBlue\">{aps_down}</div></tpl>"
                        ),
                stateId     : 'StateGridApProfiles9',
                width       : Rd.config.gridNumberCol
            },
            { 
                text    : i18n("sNotes"),
                sortable: false,
                width   : 130,
                xtype   : 'templatecolumn', 
                tpl     : new Ext.XTemplate(
                                "<tpl if='notes == true'><span class=\"fa fa-thumb-tack fa-lg txtGreen\"></tpl>"
                ),
                dataIndex: 'notes',stateId: 'StateGridApProfiles10'
            }      
        ];
        me.callParent(arguments);
    }
});
