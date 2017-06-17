Ext.define('Rd.store.sAccessPointEntryPoints', {
    extend: 'Ext.data.Store',
    model: 'Rd.model.mAccessPointEntryPoint',
    proxy: {
            type    : 'ajax',
            format  : 'json',
            batchActions: true, 
            url     : '/cake2/rd_cake/ap_profiles/access_point_entry_points.json',
            reader: {
                type            : 'json',
                rootProperty    : 'items',
                messageProperty : 'message'
            }
    },
    autoLoad: false
});
