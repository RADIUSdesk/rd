Ext.define('Rd.view.charts.pnlChartBase', {
    extend      : 'Ext.panel.Panel',
    xtype       : 'pnlChartBase',
    layout      : 'fit' ,
    requires	: [
        'Rd.view.components.cmbRealm'
    ],
    initComponent : function(){
    
        var me = this;
        me.tbar  = [
            { xtype: 'buttongroup', items : [
                { xtype: 'button',  glyph: Rd.config.icnReload ,scale: 'small', itemId: 'reload',   tooltip: i18n("sReload")},
                {
                    xtype       : 'cmbRealm',
                    allowBlank  : false,
                    labelClsExtra: 'lblRdReq',
                    itemId      : 'realm',
                    labelWidth  : 40,
                    extraParam  : me.apId
                },
                { xtype: 'button', text: 'Past day',     toggleGroup: 'time_n', enableToggle : true, scale: 'small', itemId: 'day', pressed: true },
                { xtype: 'button', text: 'Past week',    toggleGroup: 'time_n', enableToggle : true, scale: 'small', itemId: 'week'},
                { xtype: 'button', text: 'Past month',   toggleGroup: 'time_n', enableToggle : true, scale: 'small', itemId: 'month'} 
            ]}    
        ];
        me.callParent(arguments);
    }
});
