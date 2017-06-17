Ext.define('Rd.store.sAccessPointExits', {
    extend: 'Ext.data.Store',
    model: 'Rd.model.mAccessPointExit',
    proxy: {
            type    : 'ajax',
            format  : 'json',
            batchActions: true, 
            url     : '/cake2/rd_cake/ap_profiles/ap_profile_exits_index.json',
            reader: {
                type            : 'json',
                rootProperty    : 'items',
                messageProperty : 'message'
            },
            api: {
                destroy  : '/cake2/rd_cake/ap_profiles/ap_profile_exit_delete.json'
            }
    },
    autoLoad: false
});
