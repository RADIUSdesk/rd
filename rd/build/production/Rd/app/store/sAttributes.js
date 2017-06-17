Ext.define('Rd.store.sAttributes', {
    extend: 'Ext.data.Store',
    model: 'Rd.model.mAttribute',
    proxy: {
            'type'  :'ajax',
            'url'   : '/cake2/rd_cake/profile_components/attributes.json',
            format  : 'json',
            reader: {
                type: 'json',
                rootProperty: 'items'
            }
    },
    autoLoad: true
});
