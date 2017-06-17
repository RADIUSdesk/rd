Ext.define('Rd.view.dynamicDetails.pnlDynamicDetailDetail', {
    extend  : 'Ext.panel.Panel',
    alias   : 'widget.pnlDynamicDetailDetail',
    border  : false,
    dynamic_detail_id: null,
    layout: 'hbox',
    bodyStyle: {backgroundColor : Rd.config.panelGrey },
    initComponent: function(){
        var me = this;

        me.items =  { 
                xtype   :  'form',
                height  : '100%', 
                width   :  450,
                layout  : 'fit',
                autoScroll:true,
                frame   : true,
                fieldDefaults: {
                    msgTarget       : 'under',
                    labelClsExtra   : 'lblRd',
                    labelAlign      : 'left',
                    labelSeparator  : '',
                    margin          : Rd.config.fieldMargin,
                    labelWidth      : Rd.config.labelWidth,
                    maxWidth        : Rd.config.maxWidth  
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
                                'title'     : i18n('sRequired_info'),
                                'layout'    : 'anchor',
                                itemId      : 'tabRequired',
                                defaults    : {
                                    anchor: '100%'
                                },
                                autoScroll:true,
                                items       : [
                                    {
                                        itemId      : 'owner',
                                        xtype       : 'displayfield',
                                        fieldLabel  : i18n('sOwner'),
                                        value       : me.owner,
                                        name        : 'owner',
                                        labelClsExtra: 'lblRdReq'
                                    },
                                    {
                                        xtype: 'textfield',
                                        name : "id",
                                        hidden: true
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
                                        xtype       : 'checkbox',      
                                        fieldLabel  : i18n('sMake_available_to_sub_providers'),
                                        name        : 'available_to_siblings',
                                        inputValue  : 'available_to_siblings',
                                        checked     : false,
                                        labelClsExtra: 'lblRdReq'
                                    }
                                ]
                            },
                            { 
                                'title'     : i18n('sContact_detail'),
                                'layout'    : 'anchor',
                                itemId      : 'tabContact',
                                defaults    : {
                                    anchor: '100%'
                                },
                                autoScroll:true,
                                items       : [         
                                    {
                                        xtype       : 'textfield',
                                        fieldLabel  : i18n('sPhone'),
                                        name        : "phone",
                                        allowBlank  : true,
                                        vtype       : 'Numeric'
                                    },
                                    {
                                        xtype       : 'textfield',
                                        fieldLabel  : i18n('sFax'),
                                        name        : "fax",
                                        allowBlank  : true,
                                        vtype       : 'Numeric'
                                    },
                                    {
                                        xtype       : 'textfield',
                                        fieldLabel  : i18n('sCell'),
                                        name        : "cell",
                                        allowBlank  : true,
                                        vtype       : 'Numeric'
                                    },
                                    {
                                        xtype       : 'textfield',
                                        fieldLabel  : i18n('s_email'),
                                        name        : "email",
                                        allowBlank  : true,
                                        vtype       : 'email'
                                    },
                                    {
                                        xtype       : 'textfield',
                                        fieldLabel  : i18n('sURL'),
                                        name        : "url",
                                        allowBlank  : true,
                                        vtype       : 'url'
                                    }
                                ]
                            },
                            { 
                                'title'     : i18n('sAddress'),
                                'layout'    : 'anchor',
                                itemId      : 'tabAddress',
                                defaults    : {
                                    anchor: '100%'
                                },
                                autoScroll:true,
                                items       : [         
                                    {
                                        xtype       : 'textfield',
                                        fieldLabel  : i18n('sStreet_Number'),
                                        name        : "street_no",
                                        allowBlank  : true
                                    },
                                    {
                                        xtype       : 'textfield',
                                        fieldLabel  : i18n('sStreet'),
                                        name        : "street",
                                        allowBlank  : true,
                                        margin: 15
                                    },
                                    {
                                        xtype       : 'textfield',
                                        fieldLabel  : i18n('sTown_fs_Suburb'),
                                        name        : "town_suburb",
                                        allowBlank  : true,
                                        margin: 15
                                    },
                                    {
                                        xtype       : 'textfield',
                                        fieldLabel  : i18n('sCity'),
                                        name        : "city",
                                        allowBlank  : true
                                    },
                                    {
                                        xtype       : 'textfield',
                                        fieldLabel  : i18n('sCountry'),
                                        name        : "country",
                                        allowBlank  : true
                                    },
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
