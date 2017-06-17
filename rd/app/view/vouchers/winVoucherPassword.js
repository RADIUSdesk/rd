Ext.define('Rd.view.vouchers.winVoucherPassword', {
    extend: 'Ext.window.Window',
    alias : 'widget.winVoucherPassword',
    title : i18n('sChange_password'),
    layout: 'fit',
    autoShow: false,
    width:    350,
    height:   250,
    iconCls: 'rights',
    glyph   : Rd.config.icnLock,
    initComponent: function() {
        var me = this;
        this.items = [
            {
                xtype: 'form',
                border:     false,
                layout:     'anchor',
                autoScroll: true,
                defaults: {
                    anchor: '100%'
                },
                fieldDefaults: {
                    msgTarget: 'under',
                    labelClsExtra: 'lblRd',
                    labelAlign: 'left',
                    labelSeparator: '',
                    margin: 15
                },
                defaultType: 'textfield',
                items: [
                    {
                        xtype       : 'textfield',
                        fieldLabel  : i18n('sPassword'),
                        allowBlank  : false,
                        labelClsExtra: 'lblRdReq',
                        name        : "password",
                        allowBlank  : false
                    }
                ],
                buttons: [
                    {
                        itemId: 'save',
                        text: i18n('sOK'),
                        scale: 'large',
                        iconCls: 'b-next',
                        glyph   : Rd.config.icnYes,   
                        formBind: true,
                        margin: '0 20 40 0'
                    }
                ]
            }
        ];
        this.callParent(arguments);
    }
});
