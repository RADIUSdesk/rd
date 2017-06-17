Ext.define('Rd.view.meshes.winMeshAddNodeAction', {
    extend      : 'Ext.window.Window',
    alias       : 'widget.winMeshAddNodeAction',
    title       : i18n('sAdd_a_command'),
    layout      : 'fit',
    autoShow    : false,
    width       : 350,
    height      : 220,
    glyph       : Rd.config.icnAdd,
    nodeId      : null,
    grid        : null,
    initComponent: function() {
        var me = this;
        me.items = [
            {
                xtype       : 'form',
                border      : false,
                layout      : 'anchor',
                autoScroll  : true,
                defaults    : {
                    anchor  : '100%'
                },
                fieldDefaults: {
                    msgTarget       : 'under',
                    labelClsExtra   : 'lblRd',
                    labelAlign      : 'left',
                    labelSeparator  : '',
                    margin          : 15
                },
                defaultType: 'textfield',
                items: [
                     {
                        xtype       : 'textfield',
                        name        : "node_id",
                        hidden      : true,
                        value       : me.nodeId
                    },
                    {
                        fieldLabel  : i18n('sCommand'),
                        name        : "command",
                        allowBlank  : false,
                        blankText   : i18n('sSupply_a_value'),
                        labelClsExtra: 'lblRdReq'
                    }
                ],
                buttons: [
                    {
                        itemId  : 'save',
                        text    : i18n('sOK'),
                        scale   : 'large',
                        glyph   : Rd.config.icnNext,
                        formBind: true,
                        margin  : '0 20 40 0'
                    }
                ]
            }
        ];
        me.callParent(arguments);
    }
});
