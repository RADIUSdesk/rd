Ext.define('Rd.store.sLanguages', {
    extend: 'Ext.data.Store',
    fields: ['id', 'country', 'language', 'icon_file', 'text','rtl'],
    proxy: {
            'type'  :'rest',
            'url'   : '/cake2/rd_cake/phrase_values/l_languages.json', 
            reader: {
                type            : 'json',
                rootProperty    : 'items'
            }
    },
    autoLoad: true
});
