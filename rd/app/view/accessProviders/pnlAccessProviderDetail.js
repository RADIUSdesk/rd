Ext.define('Rd.view.accessProviders.pnlAccessProviderDetail', {
    extend  : 'Ext.panel.Panel',
    alias   : 'widget.pnlAccessProviderDetail',
    border  : false,
    ap_id  : null,
    layout: 'hbox',
    bodyStyle: {backgroundColor : Rd.config.panelGrey },
    requires    : [
        'Rd.view.accessProviders.vcAccessProviderDetails'
    ],
    controller  : 'vcAccessProviderDetails',
    initComponent: function(){
        var me = this;

        me.items =  { 
                xtype   :  'form',
                height  : '100%', 
                width   :  450,
                layout  : 'fit',
                autoScroll:true,
                frame   : true,
                border  : false,
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
                                defaults    : {
                                    anchor  : '100%'
                                },
                                autoScroll:true,
                                items: [
                                    {
                                        itemId      : 'owner',
                                        xtype       : 'displayfield',
                                        fieldLabel  : i18n('sOwner'),
                                        value       : me.owner,
                                        name        : 'owner',
                                        labelClsExtra: 'lblRdReq'
                                    },
                                    {
                                        xtype   : 'textfield',
                                        name    : "id",
                                        hidden  : true,
                                        value   : me.ap_id,
                                        itemId  : 'ap_id'
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
                                    }
                                ]
                            },
                            { 
                                'title'     : i18n('sOptional_info'),
                                'layout'    : 'anchor',
                                defaults    : {
                                    anchor  : '100%'
                                },
                                autoScroll:true,
                                items: [
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
                            },
                            { 
                                title       : 'White Label',
                                'layout'    : 'anchor',
                                defaults    : {
                                    anchor  : '100%'
                                },
                                autoScroll:true,
                                items: [
                                     {
                                        xtype       : 'checkbox',
                                        itemId      : 'chkWlActive',      
                                        fieldLabel  : i18n('sActivate'),
                                        name        : 'wl_active',
                                        inputValue  : 'wl_active',
                                        checked     : false,
                                        listeners   : {
                                            change : 'onChkWlActiveChange'
                                        } 
                                    },
                                    {
                                        xtype       : 'textfield',
                                        fieldLabel  : 'Header Text',
                                        name        : "wl_header",
                                        itemId      : 'txtWlHeader',
                                        disabled    : true
                                    },
                                    {
                                        xtype       : 'colorfield',
                                        fieldLabel  : 'Header Background',
                                        name        : 'wl_h_bg',
                                        beforeBodyEl: [
                                            '<div class="' + Ext.baseCSSPrefix + 'colorpicker-field-swatch custom-color-picker-swatch">' +
                                                '<div id="{id}-swatchEl" data-ref="swatchEl" class="' + Ext.baseCSSPrefix +
                                                        'colorpicker-field-swatch-inner"></div>' +
                                            '</div>'
                                        ],
                                        value       : '#FFFFFF',
                                        itemId      : 'clrWlHeaderBg',
                                        disabled    : true
                                    },
                                    {
                                        xtype       : 'colorfield',
                                        fieldLabel  : 'Header Foreground',
                                        name        : 'wl_h_fg',
                                        beforeBodyEl: [
                                            '<div class="' + Ext.baseCSSPrefix + 'colorpicker-field-swatch custom-color-picker-swatch">' +
                                                '<div id="{id}-swatchEl" data-ref="swatchEl" class="' + Ext.baseCSSPrefix +
                                                        'colorpicker-field-swatch-inner"></div>' +
                                            '</div>'
                                        ],
                                        value       : '#4b4c4c',
                                        itemId      : 'clrWlHeaderFg',
                                        disabled    : true
                                        
                                    },
                                    {
                                        xtype       : 'textfield',
                                        fieldLabel  : 'Footer Text',
                                        name        : "wl_footer",
                                        itemId      : 'txtWlFooter',
                                        disabled    : true
                                    },
                                    {
                                        xtype       : 'checkbox',
                                        itemId      : 'chkWlImgActive',      
                                        fieldLabel  : 'Include Logo',
                                        name        : 'wl_img_active',
                                        inputValue  : 'wl_img_active',
                                        checked     : false,
                                        disabled    : true,
                                        listeners   : {
                                            change : 'onChkWlImgActiveChange'
                                        } 
                                    },
                                    {
                                        xtype       : 'filefield',
                                        itemId      : 'flWlImgFileUpload',
                                        emptyText   : i18n('sSelect_an_image'),
                                        fieldLabel  : 'New Logo File',
                                        allowBlank  : true,
                                        name        : 'wl_img_file_upload',
                                        buttonText  : '',
                                      //  disabled    : true,
                                        buttonConfig: {
                                            iconCls: 'upload-icon',
                                            glyph: Rd.config.icnFolder
                                        }  
                                    },
                                    {
                                        xtype       : 'textfield',
                                        name        : "wl_img_file",
                                        hidden      : true
                                    },
                                    {
                                        xtype       : 'image',
                                      //  src         : '/cake3/rd_cake/img/access_providers/logo.png', //Souces it when form loads
                                        autoEl      : 'div',
                                        title       : 'Current Logo',
                                        itemId      : 'imgWlLogo'
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
