Ext.define('Rd.view.dashboard.winDashboardSettings', {
    extend  : 'Ext.window.Window',
    alias   : 'widget.winDashboardSettings',
    title   : i18n('sSettings'),
    layout  : 'fit',
    autoShow: false,
    width   : 350,
    height  : 300,
    glyph   : Rd.config.icnSpanner,
    requires: [
        'Rd.view.components.cmbRealm'
    ],
    initComponent: function() {
        var me      = this;
        me.items    = [
            {
                xtype       : 'form',
                border      : false,
                layout      : 'anchor',
                autoScroll  : true,
                defaults    : {
                    anchor  : '100%'
                },
                fieldDefaults   : {
                    msgTarget       : 'under',
                    labelClsExtra   : 'lblRd',
                    labelAlign      : 'left',
                    labelSeparator  : '',
                    margin          : 15
                },
                defaultType     : 'textfield',
                items: [
                    {
                        xtype   : 'cmbRealm'
                    },
                    {
                        xtype       : 'checkbox',      
                        boxLabel  : 'Show Data Usage',
                        name        : 'show_data_usage',
                        inputValue  : 'show_data_usage',
                        checked     : true,
                        cls         : 'lblRdReq'
                    },
                    {
                        xtype       : 'checkbox',      
                        boxLabel    : 'Compact View',
                        name        : 'compact_view',
                        inputValue  : 'compact_view',
                        checked     : true,
                        cls         : 'lblRdReq'
                    }/*,
                    {
                        xtype       : 'checkbox',      
                        boxLabel  : 'Show Recent Failures',
                        name        : 'show_recent_failures',
                        inputValue  : 'show_recent_failures',
                        checked     : true,
                        cls         : 'lblRdReq'
                    }*/
                ],
                buttons: [
                    {
                        itemId      : 'save',
                        text        : i18n('sOK'),
                        scale       : 'large',
                        glyph       : Rd.config.icnYes,
                        formBind    : true,
                        margin      : Rd.config.buttonMargin
                    }
                ]
            }
        ];
        me.callParent(arguments);
    }
});
