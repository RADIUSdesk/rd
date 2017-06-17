Ext.define('Rd.view.dataUsage.pnlDataUsage', {
    extend      : 'Ext.panel.Panel',
    alias       : 'widget.pnlDataUsage',
    scrollable  : true,
    layout      : {
      type  : 'vbox',
      align : 'stretch'  
    },
    requires: [
        'Rd.view.dataUsage.vcPnlDataUsage',
        'Rd.view.dataUsage.pnlDataUsageDay',
        'Rd.view.dataUsage.pnlDataUsageWeek',
        'Rd.view.dataUsage.pnlDataUsageMonth',
        'Rd.view.components.cmbRealm'
    ],
    controller : 'vcPnlDataUsage',
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
                        xtype   : 'cmbRealm'
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
                xtype   : 'pnlDataUsageDay',
                glyph   : Rd.config.icnHourStart
            },
            {
                xtype   : 'pnlDataUsageWeek',
                glyph   : Rd.config.icnHourHalf
            },
            {
                xtype   : 'pnlDataUsageMonth',
                glyph   : Rd.config.icnHourEnd
            }
        ];
        
        me.callParent(arguments);
    }
});
