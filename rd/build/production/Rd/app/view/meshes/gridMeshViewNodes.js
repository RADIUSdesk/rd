Ext.define('Rd.view.meshes.gridMeshViewNodes' ,{
    extend      :'Ext.grid.Panel',
    alias       : 'widget.gridMeshViewNodes',
    requires    : [
        'Rd.store.sMeshViewNodes',
        'Rd.model.mMeshViewNode'
    ],
    multiSelect : true,
    stateful    : true,
    stateId     : 'StateGridMeshViewNodes',
    stateEvents :['groupclick','columnhide'],
    border      : false,
    viewConfig: {
        loadMask:true
    },
    tbar: [
        { xtype: 'buttongroup', title: i18n('sAction'), items : [
            { xtype: 'splitbutton',  iconCls: 'b-reload',    glyph: Rd.config.icnReload ,scale: 'large', itemId: 'reload',   tooltip:    i18n('sReload'),
                menu: {
                    items: [
                        '<b class="menu-title">Reload every:</b>',
                        {'text': '30 seconds',  'itemId': 'mnuRefresh30s','group': 'refresh','checked': false },
                        {'text': '1 minute',    'itemId': 'mnuRefresh1m', 'group': 'refresh','checked': false },
                        {'text': '5 minutes',   'itemId': 'mnuRefresh5m', 'group': 'refresh','checked': false },
                        {'text':'Stop auto reload','itemId':'mnuRefreshCancel', 'group': 'refresh', 'checked':true}
                    ]
                }
            },
            { xtype: 'button', text: 'Past hour',    toggleGroup: 'time_n', enableToggle : true, scale: 'large', itemId: 'hour', pressed: true},
            { xtype: 'button', text: 'Past day',     toggleGroup: 'time_n', enableToggle : true, scale: 'large', itemId: 'day' },
            { xtype: 'button', text: 'Past week',    toggleGroup: 'time_n', enableToggle : true, scale: 'large', itemId: 'week'}
        ]}    
    ],
    bbar: [
        {   xtype: 'component', itemId: 'count',   tpl: i18n('sResult_count_{count}'),   style: 'margin-right:5px', cls: 'lblYfi' }
    ],
    features: [{
        //ftype: 'grouping',
        ftype               : 'groupingsummary',
        //groupHeaderTpl      : '<span style="color:green;">{name}</span><span style="color:grey;"> Last contact 2014-06-14 07:21</span>',
        groupHeaderTpl: [
            '<span class="{children:this.formatColor}">{name}</span><span class="grpInfo"> {children:this.getLastContact}</span>',
            {
                formatColor: function(children) {
                    var fc = children[0];
                    var state = fc.get('state');
                    if(state == 'never'){
                        return 'grpNever';
                    }
                    if(state == 'down'){
                        return 'grpDown';
                    }
                    if(state == 'up'){
                        return 'grpUp';
                    }
                }
            },
            {
                getLastContact: function(children) {
                    var fc = children[0];
                    var c = fc.get('l_contact_human');
                    if(c == null){
                        return '(never)';
                    }
                    return c;
                }
            }
        ],
        hideGroupedHeader   : true,
        enableGroupingMenu  : false,
        startCollapsed      : true
    }],
    initComponent: function(){
        var me      = this;
        me.store    = Ext.create(Rd.store.sMeshViewNodes,{
            groupField: 'name',
            listeners: {
                load: function(store, records, successful) {
                    if(!successful){
                        Ext.ux.Toaster.msg(
                            'Error encountered',
                            store.getProxy().getReader().rawData.message.message,
                            Ext.ux.Constants.clsWarn,
                            Ext.ux.Constants.msgWarn
                        );
                        //console.log(store.getProxy().getReader().rawData.message.message);
                    }else{
                        var count   = me.getStore().getTotalCount();
                        me.down('#count').update({count: count});
                    }   
                },
                update: function(store, records, success, options) {
                    store.sync({
                        success: function(batch,options){
                            Ext.ux.Toaster.msg(
                                i18n('sUpdated_item'),
                                i18n('sItem_has_been_updated'),
                                Ext.ux.Constants.clsInfo,
                                Ext.ux.Constants.msgInfo
                            );   
                        },
                        failure: function(batch,options){
                            Ext.ux.Toaster.msg(
                                i18n('sProblems_updating_the_item'),
                                i18n('sItem_could_not_be_updated'),
                                Ext.ux.Constants.clsWarn,
                                Ext.ux.Constants.msgWarn
                            );
                        }
                    });
                },
                scope: this
            },
            autoLoad: false 
        });
        me.store.getProxy().setExtraParam('mesh_id',me.meshId);
        //me.store.load();

        me.columns  = [
            { xtype: 'rownumberer',                                                         stateId: 'StateGridMeshViewNodes1'},
            { text: i18n("sName"),      dataIndex: 'name',      tdCls: 'gridMain', flex: 1, stateId: 'StateGridMeshViewNodes2'},     
            { text: 'MAC Address',      dataIndex: 'mac',       tdCls: 'gridMain', flex: 1, stateId: 'StateGridMeshViewNodes3',
                summaryType     : 'count',
                summaryRenderer : function(value, summaryData, dataIndex) {
                    
                    var tx_bytes =summaryData.record.get('tx_bytes'); //Assume that if the tx_bytes are zero - we have no devices
                    if(tx_bytes == 0){
                        return 'No devices';
                    }else{
                        return ((value === 0 || value > 1) ? '(' + value + ' Devices)' : '(1 Device)');
                    }
                }
            },
            { text: 'Vendor',           dataIndex: 'vendor',    tdCls: 'gridTree', flex: 1, stateId: 'StateGridMeshViewNodes4'},
            {   text: 'Data Tx',        dataIndex: 'tx_bytes',  tdCls: 'gridTree', flex: 1, stateId: 'StateGridMeshViewNodes5',
                renderer    : function(value){
                    return Ext.ux.bytesToHuman(value)              
                },
                summaryType: 'sum',
                summaryRenderer : function(value){
                    return Ext.ux.bytesToHuman(value)
                }
            },
            {   text: 'Data Rx',        dataIndex: 'rx_bytes',  tdCls: 'gridTree', flex: 1, stateId: 'StateGridMeshViewNodes6',
                renderer    : function(value){
                    return Ext.ux.bytesToHuman(value)              
                },
                summaryType: 'sum',
                summaryRenderer : function(value){
                    return Ext.ux.bytesToHuman(value)
                }
            },
            {   text: 'Signal avg',       dataIndex: 'signal_avg',tdCls: 'gridTree', stateId: 'StateGridMeshViewNodes7',
                width: 150,
                renderer: function (v, m, r) {
                    if(v != null){
                        var bar = r.get('signal_avg_bar');
                        var cls = 'wifigreen';
                        if(bar < 0.3){
                            cls = 'wifired';   
                        }
                        if((bar > 0.3)&(bar < 0.5)){
                            cls = 'wifiyellow';
                        } 
                        var id = Ext.id();
                        Ext.defer(function () {
                            Ext.widget('progressbar', {
                                renderTo    : id,
                                value       : bar,
                                width       : 140,
                                text        : v+" dBm",
                                cls         : cls
                            });
                        }, 50);
                        return Ext.String.format('<div id="{0}"></div>', id);
                    }else{
                        return "N/A";
                    }
                }
            },
            {   text: 'Latest signal',       dataIndex: 'signal',    tdCls: 'gridTree', stateId: 'StateGridMeshViewNodes8',
                width: 150,
                renderer: function (v, m, r) {
                    if(v != null){
                        var bar = r.get('signal_bar');
                        var cls = 'wifigreen';
                        if(bar < 0.3){
                            cls = 'wifired';   
                        }
                        if((bar >= 0.3)&(bar <= 0.5)){
                            cls = 'wifiyellow';
                        } 
                        var id = Ext.id();
                        Ext.defer(function () {
                            var p = Ext.widget('progressbar', {
                                renderTo    : id,
                                value       : bar,
                                width       : 140,
                                text        : v+" dBm",
                                cls         : cls
                            });
                        
                            //Fetch some variables:
                            var txbr    = r.get('l_tx_bitrate');
                            var rxbr    = r.get('l_rx_bitrate');
                            var t       = r.get('l_modified_human');
                            var ltx     = Ext.ux.bytesToHuman(r.get('l_tx_bytes'));
                            var lrx     = Ext.ux.bytesToHuman(r.get('l_rx_bytes'));
                            var tx_f    = r.get('l_tx_failed');
                            var tx_r    = r.get('l_tx_retries');
                            var auth    = r.get('l_authenticated');
                            var authz   = r.get('l_authorized');
                            var e       = r.get('l_entry');

                            var t  = Ext.create('Ext.tip.ToolTip', {
                                target  : id,
                                border  : true,
                                anchor  : 'left',
                                title   : 'Latest connection detail',
                                html    : [
                                    "<div class='divMapAction'>",
                                        "<label class='lblMap'>Time</label><label class='lblValue'>"+t+"</label>",
                                        "<div style='clear:both;'></div>",
                                        "<label class='lblMap'>SSID</label><label class='lblValue'>"+e+"</label>",
                                        "<div style='clear:both;'></div>",
                                        "<label class='lblMap'>Tx Speed</label><label class='lblValue'>"+txbr+"Mb/s</label>",
                                        "<div style='clear:both;'></div>",
                                        "<label class='lblMap'>Rx Speed</label><label class='lblValue'>"+rxbr+"Mb/s</label>",
                                        "<div style='clear:both;'></div>",
                                        "<label class='lblMap'>Tx bytes</label><label class='lblValue'>"+ltx+"</label>",
                                        "<div style='clear:both;'></div>",
                                        "<label class='lblMap'>Rx bytes</label><label class='lblValue'>"+lrx+"</label>",
                                        "<div style='clear:both;'></div>",
                                        "<label class='lblMap'>Tx failed</label><label class='lblValue'>"+tx_f+"</label>",
                                        "<div style='clear:both;'></div>",
                                        "<label class='lblMap'>Tx retries</label><label class='lblValue'>"+tx_r+"</label>",
                                        "<div style='clear:both;'></div>",
                                        "<label class='lblMap'>Authenticated</label><label class='lblValue'>"+auth+"</label>",
                                        "<div style='clear:both;'></div>",
                                        "<label class='lblMap'>Authorized</label><label class='lblValue'>"+authz+"</label>",
                                        "<div style='clear:both;'></div>",
                                    "</div>" 
                                ]
                            });

                        }, 50);
                        return Ext.String.format('<div id="{0}"></div>', id);
                    }else{
                        return "N/A";
                    }
                }
            }
        ];
        me.callParent(arguments);
    }
});
