Ext.define('Rd.store.sThemes', {
    extend: 'Ext.data.Store',
    model: 'Rd.model.mTheme',
    proxy: {
            type    : 'ajax',
            format  : 'json',
            batchActions: true, 
            url     : '/cake2/rd_cake/dynamic_details/available_themes.json',
            reader: {
                type: 'json',
                rootProperty: 'items',
                messageProperty: 'message'
            }
    },
    autoLoad: true
});
