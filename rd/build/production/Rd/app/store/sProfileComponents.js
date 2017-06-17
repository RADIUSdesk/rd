Ext.define('Rd.store.sProfileComponents', {
    extend      : 'Ext.data.Store',
    model       : 'Rd.model.mProfileComponent',
    pageSize    : 100,
    remoteSort  : true,
    proxy: {
            type    : 'ajax',
            format  : 'json',
            batchActions: true, 
            url     : '/cake2/rd_cake/profile_components/index.json',
            reader: {
                type: 'json',
                rootProperty: 'items',
                messageProperty: 'message',
                totalProperty: 'totalCount' //Required for dynamic paging
            },
            api: {
                destroy  : '/cake2/rd_cake/profile_components/delete.json'
            },
            simpleSortMode: true //This will only sort on one column (sort) and a direction(dir) value ASC or DESC
    },
    autoLoad: true
});
