Ext.define('Rd.view.meshes.gridMeshEntries' ,{
    extend:'Ext.grid.Panel',
    alias : 'widget.gridMeshEntries',
    multiSelect: true,
    stateful: true,
    stateId: 'StateGridMeshEntries',
    stateEvents:['groupclick','columnhide'],
    border: false,
    requires: [
        'Rd.view.components.ajaxToolbar'
    ],
    viewConfig: {
        loadMask:true
    },
    urlMenu: '/cake2/rd_cake/meshes/menu_for_entries_grid.json',
    bbar: [
        {   xtype: 'component', itemId: 'count',   tpl: i18n('sResult_count_{count}'),   style: 'margin-right:5px', cls: 'lblYfi' }
    ],
    initComponent: function(){
        var me      = this;

        me.store    = Ext.create(Rd.store.sMeshEntries,{
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
        me.store.load();

        me.tbar     = Ext.create('Rd.view.components.ajaxToolbar',{'url': me.urlMenu});
        me.columns  = [
            {xtype: 'rownumberer', stateId: 'StateGridMeshEntries1'},
            { text: i18n("sSSID"),                 dataIndex: 'name',          tdCls: 'gridMain', flex: 1, stateId: 'StateGridMeshEntries2'},
            { 
                text        : i18n("sEncryption"),   
                dataIndex   : 'encryption',  
                tdCls       : 'gridTree', 
                flex        : 1,
                renderer    : function(value,metaData, record){
                    if(value == 'none'){                    
                       return i18n("sNone")
                    }
                    if(value == 'wep'){
                        return i18n("sWEP")
                    } 
                    if(value == 'psk'){
                        return i18n("sWPA_Personal")
                    } 
                    if(value == 'psk2'){
                        return i18n("sWPA2_Personal")
                    } 
                    if(value == 'wpa'){
                        return i18n("sWPA_Enterprise")
                    } 
                    if(value == 'wpa2'){
                        return i18n("sWPA2_Enterprise")
                    }             
                }, stateId: 'StateGridMeshEntries3'
            },
            { text: i18n("sHidden"),               dataIndex: 'hidden',        tdCls: 'gridTree', flex: 1, stateId: 'StateGridMeshEntries4'},
            { text: i18n("sClient_isolation"),     dataIndex: 'isolate',       tdCls: 'gridTree', flex: 1, stateId: 'StateGridMeshEntries5'},
            { text: i18n("sApply_to_all_nodes"),   dataIndex: 'apply_to_all',  tdCls: 'gridTree', flex: 1, stateId: 'StateGridMeshEntries6'}
        ];
        me.callParent(arguments);
    }
});
