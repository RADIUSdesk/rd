Ext.define('Rd.store.sWallpapers', {
    extend: 'Ext.data.Store',
    model: 'Rd.model.mWallpaper',
    proxy: {
            'type'  :'ajax',
            'url'   : '/cake2/rd_cake/desktop/list_wallpapers.json',
            format  : 'json',
            reader: {
                type: 'json',
                rootProperty: 'items'
            }
    },
    autoLoad: true
});
