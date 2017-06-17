Ext.define('Rd.view.dynamicClients.pnlDynamicClientDynamicClient', {
    extend              : 'Ext.panel.Panel',
    xtype               : 'pnlDynamicClientDynamicClient',
    border              : false,
    dynamic_client_id   : null,
    layout              : 'hbox',
    bodyStyle           : {backgroundColor : Rd.config.panelGrey },
    requires: [
        'Ext.tab.Panel',
        'Ext.form.Panel',
        'Ext.form.field.Text',    
        'Rd.view.components.cmbTimezones'
    ],
    initComponent       : function(){
    
        var me = this;
        var monitor_types = Ext.create('Ext.data.Store', {
            fields: ['id', 'text'],
            data : [
                {"id":"off",        "text": i18n("sOff")},
                {"id":"heartbeat",  "text": i18n("sHeartbeat")},
                {"id":"websocket",  "text": 'Websocket'}
            ]
        });

        // Create the combo box, attached to the states data store
        var cmbMt = Ext.create('Ext.form.ComboBox', {
            fieldLabel      : i18n('sMonitor_method'),
            store           : monitor_types ,
            itemId          : 'monitorType',
            name            : 'monitor',
            queryMode       : 'local',
            displayField    : 'text',
            valueField      : 'id',
            value           : 'off'
        });

        me.items =  { 
                xtype   :  'form',
                height  : '100%', 
                width   :  500,
                layout  : 'fit',
                autoScroll:true,
                frame   : false,
                fieldDefaults: {
                    msgTarget: 'under',
                    labelClsExtra: 'lblRd',
                    labelAlign: 'left',
                    labelSeparator: '',
                    margin: Rd.config.fieldMargin,
                    labelWidth: Rd.config.labelWidth,
                    maxWidth: Rd.config.maxWidth  
                },
                items       : [
                    {
                        xtype   : 'tabpanel',
                        layout  : 'fit',
                        xtype   : 'tabpanel',
                        margins : '0 0 0 0',
                        plain   : false,
                        tabPosition: 'bottom',
                        border  : false,
                        items   : [
                        { 
                            title   : 'Basic',
                            layout  : 'anchor',
                            itemId  : 'tabBasic',
                            autoScroll: true,
                            defaults: {
                                anchor  : '100%'
                            },
                            items:[
                                {
                                    itemId  : 'user_id',
                                    xtype   : 'textfield',
                                    name    : 'dynamic_client_id',
                                    hidden  : true,
                                    value   : me.dynamic_client_id
                                },
                                {
                                    xtype       : 'textfield',
                                    fieldLabel  : i18n('sName'),
                                    name        : "name",
                                    allowBlank  : false,
                                    blankText   : i18n("sSupply_a_value"),
                                    labelClsExtra: 'lblRdReq'
                                },
                                {
                                    xtype       : 'textfield',
                                    fieldLabel  : 'NAS-Identifier',
                                    name        : "nasidentifier",
                                    allowBlank  : true,
                                    labelClsExtra: 'lblRd'
                                },
                                {
                                    xtype       : 'textfield',
                                    fieldLabel  : 'Called-Station-Id',
                                    name        : "calledstationid",
                                    allowBlank  : true,
                                    labelClsExtra: 'lblRd'
                                }  
                            ]
                        },
                        { 
                            title   : 'Monitor',
                            itemId  : 'tabMonitor',
                            autoScroll: true,
                            layout    : 'anchor',
                            defaults    : {
                                anchor  : '100%'
                            },
                            items: [
                                cmbMt,
                                {
                                    xtype: 'numberfield',
                                    anchor: '100%',
                                    name: 'heartbeat_dead_after',
                                    itemId: 'heartbeat_dead_after',
                                    fieldLabel: i18n('sHeartbeat_is_dead_after'),
                                    value: 300,
                                    maxValue: 21600,
                                    minValue: 300,
                                    hidden: true
                                }
                            ]
                        },
                        { 
                            title   : 'Maps',
                            itemId  : 'tabMaps',
                            autoScroll: true,
                            layout    : 'anchor',
                            defaults    : {
                                anchor  : '100%'
                            },
                            items   : [
                                    {
                                    xtype       : 'numberfield',
                                    name        : 'lon',  
                                    fieldLabel  : i18n('sLongitude'),
                                    value       : 0,
                                    maxValue    : 180,
                                    minValue    : -180,
                                    decimalPrecision: 14,
                                    labelClsExtra: 'lblRd'
                                },
                                {
                                    xtype       : 'numberfield',
                                    name        : 'lat',  
                                    fieldLabel  : i18n('sLatitude'),
                                    value       : 0,
                                    maxValue    : 90,
                                    minValue    : -90,
                                    decimalPrecision: 14,
                                    labelClsExtra: 'lblRd'
                                },
                                {
                                    xtype       : 'checkbox',      
                                    boxLabel    : i18n('sDispaly_on_public_maps'),
                                    name        : 'on_public_maps',
                                    inputValue  : 'on_public_maps',
                                    checked     : false,
                                    cls         : 'lblRd',
                                    margin: Rd.config.fieldMargin
                                }    
                            ]
                        },
                        { 
                            title   : 'Enhancements',
                            itemId  : 'tabEnhancements',
                            autoScroll: true,
                            layout    : 'anchor',
                            defaults    : {
                                anchor  : '100%'
                            },
                            items: [
                                {
                                    xtype       : 'checkbox',      
                                    boxLabel    : i18n('sActive'),
                                    name        : 'active',
                                    inputValue  : 'active',
                                    itemId      : 'active',
                                    checked     : true,
                                    cls         : 'lblRd'
                                },
                                {
                                    xtype       : 'checkbox',      
                                    boxLabel    : i18n('sAlso_show_to_sub_providers'),
                                    name        : 'available_to_siblings',
                                    inputValue  : 'available_to_siblings',
                                    itemId      : 'a_to_s',
                                    checked     : false,
                                    cls         : 'lblRd'
                                },  
                                {
                                    xtype       : 'checkbox',      
                                    boxLabel    : i18n('sAuto_close_stale_sessions'),
                                    name        : 'session_auto_close',
                                    itemId      : 'chkSessionAutoClose',
                                    inputValue  : 'session_auto_close',
                                    checked     : true,
                                    cls         : 'lblRd',
                                    margin: Rd.config.fieldMargin
                                },
                                {
                                    xtype       : 'numberfield',
                                    itemId      : 'nrSessionDeadTime',
                                    anchor      : '100%',
                                    name        : 'session_dead_time',
                                    fieldLabel  : i18n('sAuto_close_activation_time'),
                                    value       : 300,
                                    maxValue    : 21600,
                                    minValue    : 300,
                                    hidden      : false
                                },
                                {
                                    xtype       : 'cmbTimezones',
                                    required    : false,
                                    value       : 24,
                                    allowBlank  : true
                                } 
                            ]
                        }
                    ]
                  }             
                ],
                buttons: [
                    {
                        itemId: 'save',
                        formBind: true,
                        text: i18n('sSave'),
                        scale: 'large',
                        iconCls: 'b-save',
                        glyph: Rd.config.icnYes,
                        margin: Rd.config.buttonMargin
                    }
                ]
            };

        me.callParent(arguments);
    }
});
