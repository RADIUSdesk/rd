Ext.define('Rd.store.sUnknownAps', {
    extend      : 'Ext.data.Store',
    model       : 'Rd.model.mUnknownAp',
    //To force server side sorting:
    remoteSort  : false,
    proxy: {
            type    : 'ajax',
            format  : 'json', 
            url     : '/cake2/rd_cake/unknown_aps/index.json',
            reader: {
                type            : 'json',
                rootProperty    : 'items',
                messageProperty : 'message',
                totalProperty   : 'totalCount' //Required for dynamic paging
            },
            api: {
                destroy  : '/cake2/rd_cake/unknown_aps/delete.json'
            },
            simpleSortMode: true //This will only sort on one column (sort) and a direction(dir) value ASC or DESC
    },
    autoLoad: false
});
