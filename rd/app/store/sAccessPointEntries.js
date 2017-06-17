Ext.define('Rd.store.sAccessPointEntries', {
    extend      : 'Ext.data.Store',
    model       : 'Rd.model.mAccessPointEntry',
    remoteSort  : false,
    proxy: {
            type    : 'ajax',
            format  : 'json',
            batchActions: true, 
            url     : '/cake2/rd_cake/ap_profiles/ap_profile_entries_index.json',
            reader: {
                type            : 'json',
                rootProperty    : 'items',
                messageProperty : 'message',
                totalProperty   : 'totalCount' //Required for dynamic paging
            },
            api: {
                destroy  : '/cake2/rd_cake/ap_profiles/ap_profile_entry_delete.json'
            },
            simpleSortMode: true //This will only sort on one column (sort) and a direction(dir) value ASC or DESC
    },
    autoLoad    : false
});
