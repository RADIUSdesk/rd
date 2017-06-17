Ext.define('Rd.view.dynamicClients.pnlDynamicClientGraphs', {
    extend  : 'Ext.tab.Panel',
    alias   : 'widget.pnlDynamicClientGraphs',
    layout  : 'fit',
    margin  : '0 0 0 0',
    plain   : true,
    border  : false,
    tabPosition: 'top',
    cls     : 'subTab',
    dynamic_client_id : null,
    initComponent: function(){
        var me      = this;      
        me.items    =   [
            {
                title   : i18n('sDaily'),
                itemId  : "daily",
                xtype   : 'pnlUsageGraph',
                span    : 'daily',
                layout  : 'fit',
                username: me.dynamic_client_id,
                type    : 'dynamic_client'
            },
            {
                title   : i18n('sWeekly'),
                itemId  : "weekly",
                xtype   : 'pnlUsageGraph',
                span    : 'weekly',
                layout  : 'fit',
                username: me.dynamic_client_id,
                type    : 'dynamic_client'
            },
            {
                title   : i18n('sMonthly'),
                itemId  : "monthly",
                layout  : 'fit',
                xtype   : 'pnlUsageGraph',
                span    : 'monthly',
                username: me.dynamic_client_id,
                type    : 'dynamic_client'
            }
        ];
        me.callParent(arguments);
    }
});
