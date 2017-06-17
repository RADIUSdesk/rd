Ext.define('Rd.store.sAccessProvidersGrid', {
    extend      : 'Ext.data.Store',
    model       : 'Rd.model.mAccessProviderGrid',
    pageSize    : 100,
    remoteSort  : true,
    proxy: {
            type    : 'ajax',
            format  : 'json',
            batchActions: true, 
            url     : '/cake2/rd_cake/access_providers/index.json',
            reader: {
                type            : 'json',
                rootProperty    : 'items',
                messageProperty : 'message',
                totalProperty   : 'totalCount' 
            },
            simpleSortMode: true 
    },
    autoLoad: false
});
