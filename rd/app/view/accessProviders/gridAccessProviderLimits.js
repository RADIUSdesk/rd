Ext.define('Rd.view.accessProviders.gridAccessProviderLimits' ,{
    extend      :'Ext.grid.Panel',
    alias       : 'widget.gridAccessProviderLimits',
    multiSelect : true,
    stateful    : true,
    stateId     : 'StateGridApl',
    stateEvents :['groupclick','columnhide'],
    border      : false,
    comp_id     :  null,
    tbar        : [
        { xtype: 'buttongroup', title: i18n('sAction'),items : [ 
            {   xtype: 'button',  glyph: Rd.config.icnReload,  scale: 'large', itemId: 'reload',   tooltip:    i18n('sReload')}
        ]}      
    ],
    initComponent: function(){
        var me = this;
        
        //Very important to avoid weird behaviour:
        me.plugins = [Ext.create('Ext.grid.plugin.CellEditing', {
                clicksToEdit: 1
        })];
        
        var rowEditing = Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToMoveEditor: 1,
            autoCancel: false
        });
        
        me.plugins = [rowEditing];
        
        //Create a store specific to this Access Provider
        me.store = Ext.create(Ext.data.Store,{
            fields: [
                {name: 'id',        type: 'int'   },
                {name: 'alias',     type: 'string'},
		        {name: 'active',    type: 'bool'  },
		        {name: 'count',     type: 'int'   },
		        {name:'description',type: 'string'}
            ],
            proxy: {
                type        : 'ajax',
                format      : 'json',
                batchActions: true,
                extraParams : { 'ap_id' : me.ap_id },
                reader      : {
                    type            : 'json',
                    rootProperty    : 'items',
                    messageProperty : 'message'
                },
                writer      : { 
                    writeAllFields: true 
                },
                api         : {
                    create      : '/cake2/rd_cake/limits/add.json',
                    read        : '/cake2/rd_cake/limits/index.json',
                    update      : '/cake2/rd_cake/limits/edit.json',
                    destroy     : '/cake2/rd_cake/limits/delete.json'
                }
            },
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
                    } 
                },
                update: function(store, records, action, options,a,b) {
                    if(action == 'edit'){ //Filter for edit (after commited a second action will fire called commit)
                        store.sync({
                            success: function(batch,options){
                                Ext.ux.Toaster.msg(
                                    i18n('sUpdated_item'),
                                    i18n('sItem_has_been_updated'),
                                    Ext.ux.Constants.clsInfo,
                                    Ext.ux.Constants.msgInfo
                                ); 
                                store.load();  
                            },
                            failure: function(batch,options){
                                Ext.ux.Toaster.msg(
                                    i18n('sProblems_updating_the_item'),
                                    i18n('sItem_could_not_be_updated'),
                                    Ext.ux.Constants.clsWarn,
                                    Ext.ux.Constants.msgWarn
                                );
                                store.load();
                            }
                        });
                    }
                },
                scope: this
            },
            autoLoad: true,
            autoSync: false    
        });
        
        me.columns = [
            {xtype: 'rownumberer',stateId: 'StateGridApl1'},
            {
                text        : 'Item to Limit',
                dataIndex   : 'alias',
                tdCls       : 'gridTree',
                stateId     : 'StateGridApl12'
            },
            { 
                text        : i18n('sActive'),  
                xtype       : 'templatecolumn', 
                tpl         : new Ext.XTemplate(
                                "<tpl if='active == true'><div class=\"fieldGreen\"><i class=\"fa fa-check-circle\"></i> "+i18n("sYes")+"</div></tpl>",
                                "<tpl if='active == false'><div class=\"fieldRed\"><i class=\"fa fa-times-circle\"></i> "+i18n("sNo")+"</div></tpl>"
                            ),
                dataIndex   : 'active',
                stateId: 'StateGridApl3',
                editor: {
                    xtype   : 'checkbox',
                    cls     : 'x-grid-checkheader-editor'
                }
            },
            {
                text        : 'Limit count',
                dataIndex   : 'count',
                tdCls       : 'gridTree',
                stateId     : 'StateGridApl14',
                editor: {
                    xtype       : 'numberfield',
                    allowBlank  : false,
                    minValue    : 0,
                    maxValue    : 150000
                }
            },
            {
                text        : 'Description',
                flex        : 1,
                dataIndex   : 'description',
                tdCls       : 'gridTree',
                stateId     : 'StateGridApl15'
            }
        ];
        me.callParent(arguments);
    }
});
