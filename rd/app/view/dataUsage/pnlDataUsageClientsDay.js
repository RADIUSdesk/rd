Ext.define('Rd.view.dataUsage.pnlDataUsageClientsDay', {
    extend  : 'Ext.panel.Panel',
    alias   : 'widget.pnlDataUsageClientsDay',
    title   : "Today",
    headerPosition: 'right',
    height  : 550,
    margin  : 0,
    padding : 0,
    layout: {
        type    : 'vbox',
        align   : 'stretch'
    },
    initComponent: function() {
        var me      = this; 
        var m       = 5;
        var p       = 5;
        Ext.create('Ext.data.Store', {
            storeId : 'dayClientsStore',
            fields  :[ 
                {name: 'nasid',         type: 'string'},
                {name: 'data_in',       type: 'int'},
                {name: 'data_out',      type: 'int'},
                {name: 'data_total',    type: 'int'}
            ]
        });
        me.items = [
            {
                xtype   : 'panel',
                flex    : 1,
                border  : false,
                layout: {
                    type    : 'hbox',
                    align   : 'stretch'
                },
                items : [
                    {
                        xtype   : 'panel',
                        margin  : m,
                        padding : p,
                        flex    : 1,
                        bodyCls : 'pnlInfo',
                        layout  : 'fit',
                        border  : true,
                        ui      : 'light',
                        itemId  : 'dailyTotal',
                        tpl     : new Ext.XTemplate(
                            '<div class="divInfo">',   
                            '<tpl if="type==\'realm\'"><h2 style="color:#303030;font-weight:lighter;"><i class="fa fa-dribbble"></i> {item_name}</h2></tpl>',
                            '<tpl if="type==\'nas_id\'"><h2 style="color:#033278;font-weight:lighter;"><i class="fa fa-wifi"></i> {item_name}</h2></tpl>',
                            '<h1 style="font-size:250%;font-weight:lighter;">{data_total}</h1>',       
                            '<p style="color: #000000; font-size:110%;">',
                                '<span class="grpUp"><i class="fa fa-arrow-circle-down"></i></span> In: {data_in}',
                                '&nbsp;&nbsp;&nbsp;&nbsp;',
                                '<span class="grpDown"><i class="fa fa-arrow-circle-up"></i></span> Out: {data_out}',
                            '</p>',
                            '</div>'
                        ),
                        data    : {
                        },
                        bbar    : ['->',{ 
                            xtype   : 'button',    
                            scale   : 'large',  
                            text    : 'See More..',
                            glyph   : Rd.config.icnView,
                            itemId  : 'btnSeeMore'
                        }]
                    },
                    {
                        xtype   : 'pnlDataUsageClientDetail',
                        margin  : m,
                        padding : p,
                        hidden  : true,
                        flex    : 1  
                    },
                    {
                        flex            : 1,
                        margin          : m,
                        padding         : p,
                        border          : false,
                        itemId          : 'plrDaily',
                        xtype           : 'polar',
                        innerPadding    : 10,
                        interactions    : ['rotate', 'itemhighlight'],
                        store: Ext.data.StoreManager.lookup('dayClientsStore'),
                        series: {
                           type         : 'pie',
                          
                           highlight    : true,
                           angleField   : 'data_total',
                           label        : {
                               field    : 'name',
                               display  : 'rotate'
                           },
                           donut        : 10,    
                           tooltip : {
                                trackMouse: true,
                                renderer: function (tooltip, record, item) {
                                    tooltip.setHtml(
                                        "<h2>"+record.get('nasid')+"</h2><h3>"+Ext.ux.bytesToHuman(record.get('data_total'))+"</h3>"
                                        
                                    
                                    );
                                }
                            }    
                        }
                    },
                    {
                        xtype   : 'grid',
                        margin  : m,
                        padding : p,
                        ui      : 'light',
                        title   : 'RADIUS Clients For Today',
                        itemId  : 'gridTopTenDaily',
                        border  : true,       
                        store   : Ext.data.StoreManager.lookup('dayClientsStore'),
                        emptyText: 'No RADIUS Clients for Today',
                        columns: [
                            { text: 'NAS Id',  dataIndex: 'nasid', flex: 1},
                            { text: 'Data In',   dataIndex: 'data_in',  hidden: true, renderer: function(value){
                                    return Ext.ux.bytesToHuman(value)              
                                } 
                            },
                            { text: 'Data Out',  dataIndex: 'data_out', hidden: true,renderer: function(value){
                                    return Ext.ux.bytesToHuman(value)              
                                } 
                            },
                            { text: 'Data Total',dataIndex: 'data_total',tdCls: 'gridMain',renderer: function(value){
                                    return Ext.ux.bytesToHuman(value)              
                                } 
                            }
                        ],
                        flex: 1
                    }
                ]
            },
            {
                xtype   : 'panel',
                flex    : 1,
                border  : false,
                layout: {
                    type    : 'hbox',
                    align   : 'stretch'
                },
                items   : [
                    {
                        xtype   : 'pnlDataUsageGraph',
                        flex    : 2,
                        margin  : m,
                        padding : p,
                        layout  : 'fit',
                        border  : false
                        
                    }
                ]
            }
        ];
        me.callParent(arguments);
    }
});
