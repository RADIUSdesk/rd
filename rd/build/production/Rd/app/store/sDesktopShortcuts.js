Ext.define('Rd.store.sDesktopShortcuts', {
    extend: 'Ext.data.Store',
    model: 'Rd.model.mDesktopShortcut',
   /* data: [
                    { name: i18n('sVouchers'),         iconCls: 'vouchers-shortcut',   controller: 'cVouchers' },
                    { name: i18n('sPermanent_Users'),  iconCls: 'users-shortcut',      controller: 'cPermanentUsers' },
                    { name: i18n('sBYOD_manager'),     iconCls: 'byod-shortcut',       controller: 'cDevices' },
                    { name: i18n('sActivity_monitor'), iconCls: 'activity-shortcut',   controller: 'cActivityMonitor' }
    ],*/
    proxy: {
            'type'  :'rest',
            url     : '/cake2/rd_cake/desktop/desktop_shortcuts.json',
            reader: {
                type            : 'json',
                rootProperty    : 'items',
                messageProperty : 'message',
                totalProperty   : 'totalCount' //Required for dynamic paging
            }
    },
    autoLoad: true
});
