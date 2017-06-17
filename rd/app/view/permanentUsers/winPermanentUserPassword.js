Ext.define('Rd.view.permanentUsers.winPermanentUserPassword', {
    extend: 'Ext.window.Window',
    alias : 'widget.winPermanentUserPassword',
    title : i18n('sChange_password'),
    layout: 'fit',
    autoShow: false,
    width:    350,
    height:   250,
    iconCls: 'rights',
    glyph: Rd.config.icnKey,
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
                        glyph: Rd.config.icnNext,
                        formBind: true,
                        margin: '0 20 40 0'
                    }
                ]
            }
        ];
        this.callParent(arguments);
    }
});
