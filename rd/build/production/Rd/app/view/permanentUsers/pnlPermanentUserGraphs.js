Ext.define('Rd.view.permanentUsers.pnlPermanentUserGraphs', {
    extend  : 'Ext.tab.Panel',
    alias   : 'widget.pnlPermanentUserGraphs',
    layout  : 'fit',
    margin  : '0 0 0 0',
    plain   : true,
    border  : false,
    tabPosition: 'top',
    cls     : 'subTab',
    requires: [
        'Rd.view.components.pnlUsageGraph'
    ],
    pu_name: undefined,
    initComponent: function(){
        var me = this;      
        me.items   =   [
            {
                title   : i18n('sDaily'),
                itemId  : "daily",
                xtype   : 'pnlUsageGraph',
                span    : 'daily',
                layout  : 'fit',
                username: me.pu_name,
                type    : 'permanent'
            },
            {
                title   : i18n('sWeekly'),
                itemId  : "weekly",
                xtype   : 'pnlUsageGraph',
                span    : 'weekly',
                layout  : 'fit',
                username: me.pu_name,
                type    : 'permanent'
            },
            {
                title   : i18n('sMonthly'),
                itemId  : "monthly",
                layout  : 'fit',
                xtype   : 'pnlUsageGraph',
                span    : 'monthly',
                username: me.pu_name,
                type    : 'permanent'
            }
        ];
        me.callParent(arguments);
    }
});
