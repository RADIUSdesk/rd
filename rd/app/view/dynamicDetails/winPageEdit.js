Ext.define('Rd.view.dynamicDetails.winPageEdit', {
    extend: 'Ext.window.Window',
    alias : 'widget.winPageEdit',
    title : i18n('sEdit_own_page'),
    layout: 'fit',
    autoShow: false,
    width:    600,
    height:   450,
    iconCls: 'edit',
    glyph: Rd.config.icnEdit,
    dynamic_detail_id: undefined,
    grid:  undefined,
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
                xtype           : 'textfield',
                name            : "id",
                hidden          : true
            },
            {
                xtype           : 'textfield',
                fieldLabel      : i18n('sName'),
                name            : "name",
                labelClsExtra   : 'lblRdReq',
                allowBlank      : false
            },
            {
                xtype           : 'htmleditor',
                width           : 580,
                height          : 250,
                //fieldLabel      : i18n('sContent'),
                name            : "content",
                labelClsExtra   : 'lblRdReq',
                allowBlank      : false
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
    },
    initComponent: function() {
        var me      = this;
        this.callParent(arguments);
    }
});
