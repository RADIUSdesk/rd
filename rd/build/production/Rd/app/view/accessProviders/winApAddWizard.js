Ext.define('Rd.view.accessProviders.winApAddWizard', {
    extend:     'Ext.window.Window',
    alias :     'widget.winApAddWizard',
    closable:   true,
    draggable:  false,
    resizable:  false,
    title:      i18n('sNew_Access_Provider'),
    width:      400,
    height:     500,
    plain:      true,
    border:     false,
    layout:     'card',
    iconCls:    'add',
    glyph:      Rd.config.icnAdd,  
    autoShow:   false,
    defaults: {
            border: false
    },
    no_tree: false, //If the user has no children we don't bother giving them a branchless tree
    user_id: '',
    owner: '',
    urlCheckbox: '/cake2/rd_cake/access_providers/record_activity_checkbox.json',
    startScreen: 'scrnApTree', //Default start screen
    requires: [
        'Ext.layout.container.Card',
        'Ext.form.Panel',
        'Ext.form.field.Text',
        'Ext.form.FieldContainer',
        'Ext.form.field.Radio'
    ],
    initComponent: function() {
        var me = this;
        var scrnApTree      = me.mkScrnApTree();
        var scrnData      = me.mkScrnData();
        me.items = [
              scrnApTree,
              scrnData
        ];  
        this.callParent(arguments);
        me.getLayout().setActiveItem(me.startScreen);
    },

    //____ AccessProviders tree SCREEN ____
    mkScrnApTree: function(){
        var pnlTree = Ext.create('Rd.view.components.pnlAccessProvidersTree',{
            itemId: 'scrnApTree'
        });
        return pnlTree;
    },

    //_______ Data for Access Provider  _______
    mkScrnData: function(){
        var me      = this;
    
        var aCb = Ext.create('Rd.view.components.ajaxCheckbox',{
            'url'       :      me.urlCheckbox,
            fieldLabel  : i18n('sRecord_all_acivity'),
            name        : 'monitor',
            inputValue  : 'monitor',
            checked     : true,
            cls         : 'lblRd'
        });

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
                    itemId: 'btnDetailPrev',
                    text: i18n('sPrev'),
                    scale: 'large',
                    iconCls: 'b-prev',
                    glyph   : Rd.config.icnBack,  
                    margin: Rd.config.buttonMargin
                },
                {
                    itemId: 'save',
                    text: i18n('sOK'),
                    scale: 'large',
                    iconCls: 'b-btn_ok',
                    glyph: Rd.config.icnNext,  
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
                    cls     : 'subTab',
                    items   : [
                        { 
                            'title'     : i18n('sRequired_info'),
                            'layout'    : 'anchor',
                            itemId      : 'tabRequired',
                            autoScroll  : true,
                            defaults    : {
                                anchor: '100%'
                            },
                           items       : [
                                {
                                    itemId  : 'parent_id',
                                    xtype   : 'textfield',
                                    name    : "parent_id",
                                    value   : me.user_id,
                                    hidden  : true
                                },   
                                {
                                    itemId      : 'owner',
                                    xtype       : 'displayfield',
                                    fieldLabel  : i18n('sOwner'),
                                    value       : me.owner,
                                    labelClsExtra: 'lblRdReq'
                                },
                                {
                                    xtype       : 'textfield',
                                    fieldLabel  : i18n('sUsername'),
                                    name        : "username",
                                    allowBlank  :false,
                                    blankText   : i18n("sEnter_a_value"),
                                    labelClsExtra: 'lblRdReq'
                                },
                                {
                                    xtype       : 'textfield',
                                    fieldLabel  : i18n('sPassword'),
                                    name        : "password",
                                    allowBlank  :false,
                                    blankText   : i18n("sEnter_a_value"),
                                    labelClsExtra: 'lblRdReq'
                                }, 
                                { 
                                    xtype       : 'cmbLanguages', 
                                    width       : 350, 
                                    fieldLabel  : i18n('sLanguage'),  
                                    name        : 'language', 
                                    allowBlank  : false,
                                    labelClsExtra: 'lblRdReq' 
                                },
                                {
                                    xtype       : 'checkbox',      
                                    fieldLabel  : i18n('sActivate'),
                                    name        : 'active',
                                    inputValue  : 'active',
                                    checked     : true,
                                    cls         : 'lblRd'
                                },
                                aCb  //Ajax checkbox - state depends on the rights of the AP and their own record activity setting
                            ]
                        },
                        { 
                            'title'     : i18n('sOptional_info'),
                            'layout'    : 'anchor',
                            itemId      : 'tabOptional',
                            autoScroll  : true,
                            defaults    : {
                                anchor: '100%'
                            },
                            items       : [
                                {
                                    xtype       : 'textfield',
                                    fieldLabel  : i18n('sName'),
                                    name        : "name"
                                },
                                {
                                    xtype       : 'textfield',
                                    fieldLabel  : i18n('sSurname'),
                                    name        : "surname"
                                },
                                {
                                    xtype       : 'textfield',
                                    fieldLabel  : i18n('sPhone'),
                                    name        : "phone",
                                    vtype       : 'Numeric'
                                },
                                {
                                    xtype       : 'textfield',
                                    fieldLabel  : i18n('s_email'),
                                    name        : "email",
                                    vtype       : 'email'
                                },
                                {
                                    xtype     : 'textareafield',
                                    grow      : true,
                                    name      : 'address',
                                    fieldLabel: i18n('sAddress'),
                                    anchor    : '100%'
                                }
                            ]
                        }
                    ]
                }
            ]
        });
        return frmData;
    }
    
});
