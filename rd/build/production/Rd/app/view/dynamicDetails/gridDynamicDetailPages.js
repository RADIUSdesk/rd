Ext.define('Rd.view.dynamicDetails.gridDynamicDetailPages' ,{
    extend:'Ext.grid.Panel',
    alias : 'widget.gridDynamicDetailPages',
    multiSelect: true,
    stateful: true,
    stateId: 'StateGridDynamicDetailPages',
    stateEvents:['groupclick','columnhide'],
    border: false,
    dynamic_detail_id: undefined,
    requires: [
        'Rd.view.components.ajaxToolbar'
    ],
    viewConfig: {
        loadMask:true
    },
    urlMenu: '/cake2/rd_cake/dynamic_details/menu_for_dynamic_pages.json',
    bbar: [
        {   xtype: 'component', itemId: 'count',   tpl: i18n('sResult_count_{count}'),   style: 'margin-right:5px', cls: 'lblYfi' }
    ],
    columns: [
            {xtype: 'rownumberer',stateId: 'StateGridDynamicDetailPages1'},
            { text: i18n('sName'),          dataIndex: 'name',      tdCls: 'gridMain', width: 200,stateId: 'StateGridDynamicDetailPages2'},
            { text: i18n('sContent'),       dataIndex: 'content',   tdCls: 'gridTree', flex: 1,stateId: 'StateGridDynamicDetailPages3'}
    ],
    initComponent: function(){
        var me      = this;
        me.tbar     = Ext.create('Rd.view.components.ajaxToolbar',{'url': me.urlMenu});

        //Create a store specific to this Dynamic Detail
        me.store = Ext.create(Ext.data.Store,{
            model: 'Rd.model.mDynamicPage',
            //To force server side sorting:
            remoteSort: true,
            proxy: {
                type    : 'ajax',
                format  : 'json',
                batchActions: true, 
                url     : '/cake2/rd_cake/dynamic_details/index_page.json',
                extraParams: { 'dynamic_detail_id' : me.dynamic_detail_id },
                reader: {
                    type: 'json',
                    rootProperty: 'items',
                    messageProperty: 'message',
                    totalProperty: 'totalCount' //Required for dynamic paging
                },
                api: {
                    destroy  : '/cake2/rd_cake/dynamic_details/delete_page.json'
                },
                simpleSortMode: true //This will only sort on one column (sort) and a direction(dir) value ASC or DESC
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
                    }else{
                        var count       = me.getStore().getTotalCount();
                        me.down('#count').update({count: count});
                    }   
                },
                scope: this
            }  
        });

        me.callParent(arguments);
    }
});
