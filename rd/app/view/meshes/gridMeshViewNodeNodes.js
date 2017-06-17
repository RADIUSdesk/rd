Ext.define('Rd.view.meshes.gridMeshViewNodeNodes' ,{
    extend      :'Ext.grid.Panel',
    alias       : 'widget.gridMeshViewNodeNodes',
    requires    : [
        'Rd.store.sMeshViewNodeNodes',
        'Rd.model.mMeshViewNodeNode'
    ],
    multiSelect : true,
    stateful    : true,
    stateId     : 'StateGridMeshViewNodeNodes',
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
        me.store    = Ext.create(Rd.store.sMeshViewNodeNodes,{});
        me.store.getProxy().setExtraParam('mesh_id',me.meshId);
              
        me.bbar     =  [
            {
                 xtype       : 'pagingtoolbar',
                 store       : me.store,
                 dock        : 'bottom',
                 displayInfo : true
            }  
        ];

        me.columns  = [
            { xtype: 'rownumberer',                                                         stateId: 'StateGridMeshViewNodeNodes1'},
            { text: i18n("sName"),      dataIndex: 'name',      tdCls: 'gridMain', flex: 1, stateId: 'StateGridMeshViewNodeNodes2'},     
            { text: 'MAC Address',      dataIndex: 'mac',       tdCls: 'gridTree', flex: 1, stateId: 'StateGridMeshViewNodeNodes3',
                summaryType     : 'count',
                summaryRenderer : function(value, summaryData) {
                    var tx_bytes =summaryData.txBytes; 
                    if(tx_bytes == 0){
                        return 'No peers';
                    }else{
                        return ((value === 0 || value > 1) ? '(' + value + ' Peers)' : '(1 Peer)');
                    }
                }
            },
            { text: 'Vendor',           dataIndex: 'vendor',    tdCls: 'gridTree', flex: 1, stateId: 'StateGridMeshViewEntries4'},
            {   text: 'Data Tx',        dataIndex: 'tx_bytes',  tdCls: 'gridTree', flex: 1, stateId: 'StateGridMeshViewEntries5', itemId: 'txBytes',
                renderer    : function(value){
                    return Ext.ux.bytesToHuman(value)              
                },
                summaryType: 'sum',
                summaryRenderer : function(value){
                    return Ext.ux.bytesToHuman(value)
                }
            },
            {   text: 'Data Rx',        dataIndex: 'rx_bytes',  tdCls: 'gridTree', flex: 1, stateId: 'StateGridMeshViewEntries6',
                renderer    : function(value){
                    return Ext.ux.bytesToHuman(value)              
                },
                summaryType: 'sum',
                summaryRenderer : function(value){
                    return Ext.ux.bytesToHuman(value)
                }
            },
            {   text: 'Signal avg',       dataIndex: 'signal_avg',tdCls: 'gridTree', stateId: 'StateGridMeshViewEntries7',
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
            {   text: 'Latest signal',       dataIndex: 'signal',    tdCls: 'gridTree', stateId: 'StateGridMeshViewEntries8',
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

                            var t  = Ext.create('Ext.tip.ToolTip', {
                                target  : id,
                                border  : true,
                                anchor  : 'left',
                                html    : [
                                    "<div>",
                                        "<h2>Latest connection detail</h2>",
                                        "<label class='lblTipItem'>Time</label><label class='lblTipValue'>"+t+"</label>",
                                        "<div style='clear:both;'></div>",
                                        "<label class='lblTipItem'>Tx Speed</label><label class='lblTipValue'>"+txbr+"Mb/s</label>",
                                        "<div style='clear:both;'></div>",
                                        "<label class='lblTipItem'>Rx Speed</label><label class='lblTipValue'>"+rxbr+"Mb/s</label>",
                                        "<div style='clear:both;'></div>",
                                        "<label class='lblTipItem'>Tx bytes</label><label class='lblTipValue'>"+ltx+"</label>",
                                        "<div style='clear:both;'></div>",
                                        "<label class='lblTipItem'>Rx bytes</label><label class='lblTipValue'>"+lrx+"</label>",
                                        "<div style='clear:both;'></div>",
                                        "<label class='lblTipItem'>Tx failed</label><label class='lblTipValue'>"+tx_f+"</label>",
                                        "<div style='clear:both;'></div>",
                                        "<label class='lblTipItem'>Tx retries</label><label class='lblTipValue'>"+tx_r+"</label>",
                                        "<div style='clear:both;'></div>",
                                        "<label class='lblTipItem'>Authenticated</label><label class='lblTipValue'>"+auth+"</label>",
                                        "<div style='clear:both;'></div>",
                                        "<label class='lblTipItem'>Authorized</label><label class='lblTipValue'>"+authz+"</label>",
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
