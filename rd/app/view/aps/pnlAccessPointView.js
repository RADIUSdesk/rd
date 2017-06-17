Ext.define('Rd.view.aps.pnlAccessPointView', {
    extend      : 'Ext.tab.Panel',
    alias       : 'widget.pnlAccessPointView',
    border      : false,
    plain       : true,
    cls         : 'subTab',
    tabPosition : 'top',
    ap_id       : undefined,
    apName      : undefined,
    initComponent: function() {
        var me      = this;     
        me.items    = [
		    {
                title   : i18n("sOverview"),
                itemId  : 'tabAccessPointViewOverwiew',
			    xtype	: 'pnlChartApMain',	   
                apId    : me.ap_id
            },
            {
                title   : i18n("sSSID_to_Device"),
                itemId  : 'tabAccessPointViewEntries',
                xtype   : 'gridApViewEntries',
                apId    : me.ap_id
            }
        ];
        me.callParent(arguments);
    }
});
