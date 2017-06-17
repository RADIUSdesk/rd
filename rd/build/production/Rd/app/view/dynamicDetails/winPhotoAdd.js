Ext.define('Rd.view.dynamicDetails.winPhotoAdd', {
    extend: 'Ext.window.Window',
    alias : 'widget.winPhotoAdd',
    title : i18n('sAdd_photo'),
    layout: 'fit',
    autoShow: false,
    width:    400,
    height:   400,
    iconCls: 'add',
    glyph: Rd.config.icnAdd,
    dynamic_detail_id: undefined,
    data_view:  undefined,
    items:  {
        xtype   :  'form', 
        layout  : 'anchor',
        autoScroll:true,
        frame   : false,
        defaults    : {
            anchor: '100%'
        },
        fieldDefaults: {
            msgTarget: 'under',
            labelClsExtra: 'lblRd',
            labelAlign: 'left',
            labelSeparator: '',
            margin: Rd.config.fieldMargin,
            labelWidth: Rd.config.labelWidth
        },
        items       : [
            {
                xtype       : 'textfield',
                fieldLabel  : i18n('sTitle'),
                name        : "title",
                labelClsExtra: 'lblRdReq',
                allowBlank  : false
            },
            {
                xtype       : 'textareafield',
                grow        : true,
                name        : 'description',
                fieldLabel  : i18n('sDescription'),
                anchor      : '100%',
                labelClsExtra: 'lblRdReq',
                allowBlank  : false
            },
            {
                xtype       : 'textfield',
                fieldLabel  : 'URL',
                name        : 'url',
                labelClsExtra: 'lblRd',
                vtype       : 'url'
            },
            {
                xtype: 'filefield',
                itemId: 'form-file',
                emptyText: i18n('sSelect_an_image'),
                fieldLabel: i18n('sPhoto'),
                allowBlank  : false,
                labelClsExtra: 'lblRdReq',
                name: 'photo',
                buttonText: '',
                buttonConfig: {
                    iconCls: 'upload-icon',
                    glyph: Rd.config.icnFolder
                }     
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
            },
            {
                itemId: 'cancel',
                text: i18n('sCancel'),
                scale: 'large',
                iconCls: 'b-close',
                glyph: Rd.config.icnClose,
                margin: Rd.config.buttonMargin
            }
        ]
    },
    initComponent: function() {
        var me      = this;
        this.callParent(arguments);
    }
});
