Ext.define('Rd.view.components.chrtBasicPie', {
    extend      : 'Ext.chart.Chart',
    width       : 250,
    height      : 350,
    animate     : true,
    shadow      : true,
    legend      : {
        position    : 'bottom'
    },
    insetPadding: 25,
   // theme       : 'Base:gradients', 
    initComponent: function() {
/*
        var me = this;
        me.series = [
        {
            type        : 'pie',
            angleField  : 'data',
            showInLegend: true,
            tips: {
                trackMouse  : true,
                width       : 140,
                height      : 28,
                renderer    : function(storeItem, item) {
                    // calculate and display percentage on hover
                    var total = 0;
                    me.store.each(function(rec) {
                    total += rec.get('data');
                });
                me.setTitle(storeItem.get('name') + ': ' + Math.round(storeItem.get('data') / total * 100) + '%'+ " ("+storeItem.get('data')+")");
            }
        },
        highlight: {
            segment: {
                margin: 20
            }
        },
        label: {
            field       : 'name',
            display     : 'rotate',
            contrast    : true,
            font        : '14px Arial'
            }
        }];    
        */   
        me.callParent(arguments);
    }
});
