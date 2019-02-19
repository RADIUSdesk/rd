Ext.define('Rd.view.dataUsage.pnlDataUsageClients', {
    extend      : 'Ext.panel.Panel',
    alias       : 'widget.pnlDataUsageClients',
    scrollable  : true,
    layout      : {
      type  : 'vbox',
      align : 'stretch'  
    },
    requires: [
        'Rd.view.dataUsage.vcPnlDataUsageClients',
        'Rd.view.dataUsage.pnlDataUsageClientsDay',
        'Rd.view.dataUsage.pnlDataUsageClientsWeek',
        'Rd.view.dataUsage.pnlDataUsageClientsMonth',
        'Rd.view.components.cmbRealm'
    ],
    controller : 'vcPnlDataUsageClients',
    initComponent: function() {
        var me      = this;
        
        me.dockedItems= [{
            xtype   : 'toolbar',
            dock    : 'top',
            cls     : 'subTab', //Make darker -> Maybe grey
            frame   : true,
            border  : true,
            items   : [
                    { 
                        xtype   : 'button',  
                        glyph   : Rd.config.icnReload,    
                        scale   : 'small', 
                        itemId  : 'reload',   
                        tooltip: i18n('sReload')
                    },
                    {
                        xtype   : 'cmbRealm',
                        width   : 300,
                        labelWidth : 50 
                    },
                    { 
                        xtype   : 'button',    
                        scale   : 'small',
                        itemId  : 'btnShowRealm',  
                        text    : 'Show Realm Data',
                        hidden  : true
                    },
                    '|',
                    { 
                        xtype       : 'button', 
                        glyph       : Rd.config.icnHourStart,
                        text        : 'Today',
                        listeners   : {
                            click: 'onClickTodayButton'
                        }
                    },  
                    { 
                        xtype       : 'button', 
                        glyph       : Rd.config.icnHourHalf,
                        text        : 'This Week',
                        listeners   : {
                            click: 'onClickThisWeekButton'
                        }
                    },
                    { 
                        xtype       : 'button', 
                        glyph       : Rd.config.icnHourEnd,
                        text        : 'This Month',
                        listeners   : {
                             click: 'onClickThisMonthButton'
                        }
                    }
                ]
            }
        ]; 
          
        me.items = [
            {
                xtype   : 'pnlDataUsageClientsDay',
                glyph   : Rd.config.icnHourStart
            },
            {
                xtype   : 'pnlDataUsageClientsWeek',
                glyph   : Rd.config.icnHourHalf
            },
            {
                xtype   : 'pnlDataUsageClientsMonth',
                glyph   : Rd.config.icnHourEnd
            }
        ];
        
        me.callParent(arguments);
    }
});
