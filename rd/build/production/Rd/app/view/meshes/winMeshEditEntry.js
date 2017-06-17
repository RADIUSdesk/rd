Ext.define('Rd.view.meshes.winMeshEditEntry', {
    extend:     'Ext.window.Window',
    alias :     'widget.winMeshEditEntry',
    closable:   true,
    draggable:  true,
    resizable:  true,
    title:      i18n('sEdit_mesh_entry_point'),
    width:      400,
    height:     400,
    plain:      true,
    border:     false,
    layout:     'fit',
    iconCls:    'edit',
    glyph:      Rd.config.icnEdit,
    autoShow:   false,
    mesh_id:    '',
    defaults: {
            border: false
    },
    requires: [
        'Ext.tab.Panel',
        'Ext.form.Panel',
        'Ext.form.field.Text',
        'Rd.view.meshes.cmbEncryptionOptions'
    ],
     initComponent: function() {
        var me = this;  
        var frmData = Ext.create('Ext.form.Panel',{
            border:     false,
            layout:     'fit',
            itemId:     'scrnData',
            autoScroll: true,
            fieldDefaults: {
                msgTarget   : 'under',
                labelClsExtra: 'lblRd',
                labelAlign  : 'left',
                labelSeparator: '',
                labelClsExtra: 'lblRd',
                labelWidth  : Rd.config.labelWidth,
                maxWidth    : Rd.config.maxWidth, 
                margin      : Rd.config.fieldMargin
            },
            defaultType: 'textfield',
            buttons : [
                {
                    itemId: 'save',
                    text: i18n('sOK'),
                    scale: 'large',
                    iconCls: 'b-btn_ok',
                    glyph   : Rd.config.icnYes,
                    formBind: true,
                    margin: Rd.config.buttonMargin
                }
            ],
            items:[
                {
                    xtype   : 'tabpanel',
                    layout  : 'fit',
                    xtype   : 'tabpanel',
                    margins : '0 0 0 0',
                    plain   : true,
                    tabPosition: 'bottom',
                    border  : false,
                    items   : [
                        { 
                            'title'     : i18n('sBasic_info'),
                            'layout'    : 'anchor',
                            itemId      : 'tabRequired',
                            defaults    : {
                                anchor: '100%'
                            },
                            autoScroll:true,
                            items       : [
                                {
                                    itemId  : 'id',
                                    xtype   : 'textfield',
                                    name    : "id",
                                    hidden  : true
                                }, 
                                {
                                    itemId  : 'mesh_id',
                                    xtype   : 'textfield',
                                    name    : "mesh_id",
                                    hidden  : true
                                }, 
                                {
                                    xtype       : 'textfield',
                                    fieldLabel  : i18n('sSSID'),
                                    name        : 'name',
                                    allowBlank  : false,
                                    blankText   : i18n("sSupply_a_value"),
                                    labelClsExtra: 'lblRdReq'
                                },
                                {
                                    xtype       : 'checkbox',      
                                    fieldLabel  : i18n('sHidden'),
                                    name        : 'hidden',
                                    inputValue  : 'hidden',
                                    checked     : false,
                                    labelClsExtra: 'lblRdReq'
                                },
                                {
                                    xtype       : 'checkbox',      
                                    fieldLabel  : i18n('sClient_isolation'),
                                    name        : 'isolate',
                                    inputValue  : 'isolate',
                                    checked     : false,
                                    labelClsExtra: 'lblRdReq'
                                },
                                {
                                    xtype       : 'checkbox',      
                                    fieldLabel  : i18n('sApply_to_all_nodes'),
                                    name        : 'apply_to_all',
                                    inputValue  : 'apply_to_all',
                                    checked     : false,
                                    labelClsExtra: 'lblRdReq'
                                }
                            ]
                        },
                        { 
                            'title'     : i18n('sEncryption'),
                            'layout'    : 'anchor',
                            itemId      : 'tabContact',
                            defaults    : {
                                anchor: '100%'
                            },
                            autoScroll:true,
                            items       : [          
                                { 
                                    xtype           : 'cmbEncryptionOptions', 
                                    labelClsExtra   : 'lblRdReq',
                                    allowBlank      : false 
                                },
                                {
                                    xtype       : 'textfield',
                                    fieldLabel  : i18n('sKey'),
                                    name        : 'key',
                                    itemId      : 'key',
                                    allowBlank  : false,  
                                    blankText   : i18n("sSupply_a_value"),
                                    labelClsExtra: 'lblRdReq',
                                    hidden      : true,
                                    disabled    : true
                                }, 
                                {
                                    xtype       : 'textfield',
                                    fieldLabel  : i18n('sRADIUS_server'),
                                    name        : 'auth_server',
                                    itemId      : 'auth_server',
                                    allowBlank  : false,
                                    blankText   : i18n("sSupply_a_value"),
                                    labelClsExtra: 'lblRdReq',
                                    hidden      : true,
                                    disabled    : true
                                },
                                {
                                    xtype       : 'textfield',
                                    fieldLabel  : i18n('sShared_secret'),
                                    name        : 'auth_secret',
                                    itemId      : 'auth_secret',
                                    allowBlank  : false,
                                    blankText   : i18n("sSupply_a_value"),
                                    labelClsExtra: 'lblRdReq',
                                    hidden      : true,
                                    disabled    : true
                                }
                            ]
                        }
                    ]
                }
            ]
        });

        me.items = frmData;
        me.callParent(arguments);
    }
});
