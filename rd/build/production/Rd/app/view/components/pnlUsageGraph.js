Ext.define('Rd.view.components.pnlUsageGraph', {
    extend  : 'Ext.panel.Panel',
    alias   : 'widget.pnlUsageGraph',
    margins : '0 0 0 0',
    plain   : true,
    border  : false,
    tbar    : [
        { 
            xtype   : 'buttongroup',
            items : [
                { 
                    xtype       : 'button', 
                    glyph       : Rd.config.icnReload,   
                    scale       : 'large', 
                    itemId      : 'reload',   
                    tooltip     : i18n('sReload')
                },
                {
                    xtype       : 'datefield',
                    fieldLabel  : i18n('sDay'),
                    name        : 'day',
                    itemId      : 'day',
                    value       : new Date(),
                    format      : 'Y-m-d',
                    labelClsExtra: 'lblRd',
                    labelAlign  : 'left',
                    labelWidth  : 50,
                    margin      : 2,
                    labelSeparator: ''
                }
            ]              
        },
        '->',
        {   
            xtype   : 'component', 
            itemId  : 'totals',  
            tpl     : i18n('tpl_In_{in}_Out_{out}_Total_{total}'), 
            style   : 'margin-right:5px', 
            cls     : 'lblRd' 
        }
    ],

    //The following attributes will influence the query for the stats

    type        : 'permanent', //Can be permanent, voucher, device, nas, realm
    span        : 'daily', //Can be daily weekly or monthly
    username    : false, //if device = mac; if nas = nas_id if realm = realm_id    
    initComponent: function(){
        var me      = this;
        me.store    = Ext.create(Ext.data.Store,{
            model: 'Rd.model.mUserStat',
            proxy: {
                type        : 'ajax',
                format      : 'json',
                extraParams : { 'username' : me.username, 'type' : me.type, 'span' : me.span },
                url         : '/cake2/rd_cake/user_stats/index.json',
                reader      : {
                    keepRawData     : true,
                    type            : 'json',
                    rootProperty    : 'items',
                    messageProperty : 'message'
                }
            },
            listeners   : {
                beforeload  : function(s){
                    chart.setLoading(true);
                },
                load        : function(s){
                    chart.setLoading(false);
                    var rawData     = chart.getStore().getProxy().getReader().rawData;
                    var totalIn     = Ext.ux.bytesToHuman(rawData.totalIn);
                    var totalOut    = Ext.ux.bytesToHuman(rawData.totalOut);
                    var totalInOut  = Ext.ux.bytesToHuman(rawData.totalInOut);
                    me.down('#totals').update({'in': totalIn, 'out': totalOut, 'total': totalInOut });
                }
            },
            autoLoad: false   
        });

    var chart = Ext.create('Ext.chart.CartesianChart',{
             insetPadding: {
                top     : 20,
                left    : 20,
                right   : 20,
                bottom  : 20
            },
            width: '100%',
            store       : me.store,
            legend: {
                docked      : 'bottom'
            },
            axes: [
                {
                    type        : 'numeric',
                    position    : 'left',
                    adjustByMajorUnit: true,
                    grid        : true,
                    fields      : ['data_in', 'data_out'],
                    renderer    : function(axis, label, layoutContext) {
                        return Ext.ux.bytesToHuman(label);
                    },
                    minimum: 0
                }, {
                    type        : 'category',
                    position    : 'bottom',
                    grid        : false,
                    fields      : ['time_unit']
                }
            ],
            interactions: ['itemhighlight'],
            series: [
                {
                    type    : 'bar',
                    title   : [ 'Data In', 'Data out' ],
                    xField  : 'time_unit',
                    yField  : ['data_in', 'data_out'],
                    stacked : true,
                    style   : {
                        opacity: 0.80
                    },
                    highlight: {
                        fillStyle: 'yellow'
                    },
                    tooltip: {
                        renderer: function (tooltip, record, item) {
                            var di = Ext.ux.bytesToHuman(record.get("data_in"));
                            var dout = Ext.ux.bytesToHuman(record.get("data_out"));
                            tooltip.setHtml("Data in <b>"+di+"</b><br>Data out <b>"+dout+"</b>");    
                            
                        }
                    }
                }
            ]
        });
        
        me.items = chart;
        me.callParent(arguments);
    }
});
