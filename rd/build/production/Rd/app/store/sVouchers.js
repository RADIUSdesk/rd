Ext.define('Rd.store.sVouchers', {
    extend      : 'Ext.data.Store',
    model       : 'Rd.model.mVoucher',
    pageSize    : 100,
    remoteSort  : true,
    proxy: {
            type    : 'ajax',
            format  : 'json',
            batchActions: true, 
            url     : '/cake2/rd_cake/vouchers/index.json',
            reader: {
                type: 'json',
                rootProperty: 'items',
                messageProperty: 'message',
                totalProperty: 'totalCount' //Required for dynamic paging
            },
            api: {
                destroy  : '/cake2/rd_cake/vouchers/delete.json'
            },
            simpleSortMode: true //This will only sort on one column (sort) and a direction(dir) value ASC or DESC
    },
    autoLoad: false
});
