Ext.define('Rd.view.aps.pnlAccessPointCommonSettings', {
    extend  : 'Ext.panel.Panel',
    alias   : 'widget.pnlAccessPointCommonSettings',
    border  : false,
    layout  : 'hbox',
    align   : 'stretch',
    bodyStyle: {backgroundColor : Rd.config.panelGrey },
    requires: [
        'Ext.tab.Panel',
        'Ext.form.Panel',
        'Ext.form.field.Text',
        'Rd.view.components.cmbCountries',      
        'Rd.view.components.cmbTimezones'
    ],
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
                    labelWidth      : Rd.config.labelWidth,
                    maxWidth        : Rd.config.maxWidth, 
                    margin          : Rd.config.fieldMargin
                },
                items       : [{
                    layout  : 'fit',
                    xtype   : 'tabpanel',
                    margins : '0 0 0 0',
                    plain   : false,
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
                                    fieldLabel  : i18n("sPassword"),
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
                            title       : i18n("sMonitor"),
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
                                    fieldLabel  : i18n("sHeartbeat_interval"),
                                    value       : 60,
                                    maxValue    : 21600,
                                    minValue    : 60
                                },    
                                {
                                    xtype       : 'numberfield',
                                    name        : 'heartbeat_dead_after',
                                    itemId      : 'heartbeat_dead_after',
                                    fieldLabel  : i18n("sHeartbeat_is_dead_after"),
                                    value       : 600,
                                    maxValue    : 21600,
                                    minValue    : 300
                                }             
                            ]
                        },
                        {
                            title       : i18n("sGateway"),
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
                                    fieldLabel  : i18n("sWait_time_for_DHCP_IP"),
                                    value       : 120,
                                    maxValue    : 600,
                                    minValue    : 120,
                                    labelWidth  : 280
                                },
                                {
                                    xtype       : 'checkbox',      
                                    fieldLabel  : i18n("sUse_previous_settings_when_DHCP_fails"),
                                    name        : 'gw_use_previous',
                                    inputValue  : 'gw_use_previous',
						            itemId		: 'gw_use_previous',
                                    checked     : true,
                                    labelClsExtra: 'lblRd',
                                    labelWidth  : 280
                                }       
                            ]
                        }
                    ]
                }],
                buttons: [
                    {
                        itemId  : 'save',
                        formBind: true,
                        text    : i18n("sSave"),
                        scale   : 'large',
                        glyph   : Rd.config.icnYes,
                        margin  : Rd.config.buttonMargin
                    }
                ]
            };
        me.callParent(arguments);
    }
});
