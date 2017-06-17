Ext.define('Rd.store.sProfiles', {
    extend      : 'Ext.data.Store',
    model       : 'Rd.model.mProfile',
    pageSize    : 100,
    remoteSort  : true,
    //remoteFilter: true,
    proxy: {
            type    : 'ajax',
            format  : 'json',
            batchActions: true, 
            url     : '/cake2/rd_cake/profiles/index.json',
            reader: {
                type: 'json',
                rootProperty: 'items',
                messageProperty: 'message',
                totalProperty: 'totalCount' //Required for dynamic paging
            },
            api: {
                destroy  : '/cake2/rd_cake/profiles/delete.json'
            },
            simpleSortMode: true //This will only sort on one column (sort) and a direction(dir) value ASC or DESC
    },
    autoLoad: true
});
