Ext.define('Rd.view.realms.pnlRealmGraphs', {
    extend  : 'Ext.tab.Panel',
    alias   : 'widget.pnlRealmGraphs',
    layout  : 'fit',
    margin  : '0 0 0 0',
    plain   : true,
    border  : false,
    tabPosition: 'top',
    cls     : 'subTab',
    initComponent: function(){
        var me      = this;      
        me.items    =   [
            {
                title   : i18n('sDaily'),
                itemId  : "daily",
                xtype   : 'pnlUsageGraph',
                span    : 'daily',
                layout  : 'fit',
                username: me.realm_id,
                type    : 'realm'
            },
            {
                title   : i18n('sWeekly'),
                itemId  : "weekly",
                xtype   : 'pnlUsageGraph',
                span    : 'weekly',
                layout  : 'fit',
                username: me.realm_id,
                type    : 'realm'
            },
            {
                title   : i18n('sMonthly'),
                itemId  : "monthly",
                layout  : 'fit',
                xtype   : 'pnlUsageGraph',
                span    : 'monthly',
                username: me.realm_id,
                type    : 'realm'
            }
        ];
        me.callParent(arguments);
    }
});
