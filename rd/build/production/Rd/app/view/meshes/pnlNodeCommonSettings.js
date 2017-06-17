Ext.define('Rd.view.meshes.pnlNodeCommonSettings', {
    extend  : 'Ext.panel.Panel',
    alias   : 'widget.pnlNodeCommonSettings',
    border  : false,
    layout  : 'hbox',
    align   : 'stretch',
    bodyStyle: {backgroundColor : Rd.config.panelGrey },
    initComponent: function(){
        var me = this;
        me.items =  { 
                xtype   :  'form',
                height  : '100%', 
                width   :  400,
                layout  :  'fit',
                autoScroll:true,
                frame   : true,
                fieldDefaults: {
                    msgTarget       : 'under',
                    labelClsExtra   : 'lblRd',
                    labelAlign      : 'left',
                    labelSeparator  : '',
                    labelClsExtra   : 'lblRd',
                    labelWidth      : Rd.config.labelWidth,
                    maxWidth        : Rd.config.maxWidth, 
                    margin          : Rd.config.fieldMargin
                },
                items       : [{
                    layout  : 'fit',
                    xtype   : 'tabpanel',
                    margins : '0 0 0 0',
                    plain   : true,
                    tabPosition: 'bottom',
                    border  : false,
                    items   :  [
                        {
                            title       : 'System',
                            layout      : 'anchor',
                            defaults    : {
                                anchor: '100%'
                            },
                            autoScroll:true,
                            items       :[
                                {
                                    xtype       : 'textfield',
                                    fieldLabel  : i18n('sPassword'),
                                    name        : 'password',
                                    allowBlank  : false,
                                    blankText   : i18n("sSupply_a_value"),
                                    labelClsExtra: 'lblRdReq'
                                },
                                {
                                    xtype       : 'cmbCountries',
                                    anchor      : '100%',
                                    labelClsExtra: 'lblRdReq'
                                },
                                {
                                    xtype       : 'cmbTimezones',
                                    anchor      : '100%',
                                    labelClsExtra: 'lblRdReq'
                                }             
                            ]

                        },
                        {
                            title       : 'WiFi',
                            layout      : 'anchor',
                            defaults    : {
                                anchor: '100%'
                            },
                            autoScroll:true,
                            items       :[
                                {
                                    xtype       : 'numberfield',
                                    anchor      : '100%',
                                    name        : 'two_chan',
                                    fieldLabel  : i18n('s2_pt_4G_Channel'),
                                    value       : 5,
                                    maxValue    : 14,
                                    minValue    : 1,
                                    labelClsExtra: 'lblRdReq'
                                },
                                {
                                    xtype       : 'cmbFiveGigChannels',
                                    anchor      : '100%',
                                    labelClsExtra: 'lblRdReq'
                                },
                                {
                                    xtype       : 'textfield',
                                    fieldLabel  : 'Client Key',
                                    name        : 'client_key',
                                    allowBlank  : false,
                                    blankText   : i18n.sSupply_a_value,
                                    labelClsExtra: 'lblRdReq',
                                    minLength   : 8
                                }            
                            ]
                        },
                        {
                            title       : 'Bridge',
                            layout      : 'anchor',
                            defaults    : {
                                anchor: '100%'
                            },
                            autoScroll:true,
                            items       :[
                                {
                                    xtype       : 'checkbox',      
                                    fieldLabel  : 'Bridge Ethernet port',
                                    name        : 'eth_br_chk',
                                    inputValue  : 'eth_br_chk',
						            itemId		: 'eth_br_chk',
                                    checked     : true,
                                    labelClsExtra: 'lblRd'
                                },
					            {
						            xtype		: 'cmbEthBridgeOptions',
						            meshId		: me.meshId,
						            disabled	: true
					            },
					            {
                                    xtype       : 'checkbox',      
                                    fieldLabel  : 'Apply bridge to all nodes',
                                    name        : 'eth_br_for_all',
                                    inputValue  : 'eth_br_for_all',
						            itemId		: 'eth_br_for_all',
                                    checked     : true,
                                    labelClsExtra: 'lblRd',
						            disabled	: true
                                }             
                            ]
                        },
                        {
                            title       : 'Monitor',
                            layout      : 'anchor',
                            defaults    : {
                                anchor: '100%'
                            },
                            autoScroll:true,
                            items       :[
                                {
                                    xtype       : 'numberfield',
                                    name        : 'heartbeat_interval',
                                    itemId      : 'heartbeat_interval',
                                    fieldLabel  : i18n('sHeartbeat_interval'),
                                    value       : 60,
                                    maxValue    : 21600,
                                    minValue    : 60
                                },    
                                {
                                    xtype       : 'numberfield',
                                    name        : 'heartbeat_dead_after',
                                    itemId      : 'heartbeat_dead_after',
                                    fieldLabel  : i18n('sHeartbeat_is_dead_after'),
                                    value       : 600,
                                    maxValue    : 21600,
                                    minValue    : 300
                                }             
                            ]
                        },
                        {
                            title       : 'Gateway',
                            layout      : 'anchor',
                            defaults    : {
                                anchor: '100%'
                            },
                            fieldDefaults: {
                                labelWidth      : 300
                            },
                            autoScroll:true,
                            items       :[
                                {
                                    xtype       : 'numberfield',
                                    name        : 'gw_dhcp_timeout',
                                    itemId      : 'gw_dhcp_timeout',
                                    fieldLabel  : 'Wait time for DHCP IP',
                                    value       : 120,
                                    maxValue    : 600,
                                    minValue    : 120,
                                    labelWidth  : 280
                                },
                                {
                                    xtype       : 'checkbox',      
                                    fieldLabel  : 'Use previous settings when DHCP fails',
                                    name        : 'gw_use_previous',
                                    inputValue  : 'gw_use_previous',
						            itemId		: 'gw_use_previous',
                                    checked     : true,
                                    labelClsExtra: 'lblRd',
                                    labelWidth  : 280
                                },  
                                {
                                    xtype       : 'checkbox',      
                                    fieldLabel  : 'Reboot node if gateway is unreachable',
                                    name        : 'gw_auto_reboot',
                                    inputValue  : 'gw_auto_reboot',
						            itemId		: 'gw_auto_reboot',
                                    checked     : true,
                                    labelClsExtra: 'lblRd',
                                    labelWidth  : 280
                                },     
                                {
                                    xtype       : 'numberfield',
                                    name        : 'gw_auto_reboot_time',
                                    itemId      : 'gw_auto_reboot_time',
                                    fieldLabel  : 'Reboot trigger time',
                                    value       : 600,
                                    maxValue    : 3600,
                                    minValue    : 240,
                                    labelWidth  : 280
                                }             
                            ]
                        }
                    ]
                }],
                buttons: [
                    {
                        itemId: 'save',
                        formBind: true,
                        text: i18n('sSave'),
                        scale: 'large',
                        iconCls: 'b-save',
                        glyph   : Rd.config.icnYes,
                        margin: Rd.config.buttonMargin
                    }
                ]
            };
        me.callParent(arguments);
    }
});
