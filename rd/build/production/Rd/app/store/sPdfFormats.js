Ext.define('Rd.store.sPdfFormats', {
    extend: 'Ext.data.Store',
    model: 'Rd.model.mPdfFormat',
    proxy: {
            type    : 'ajax',
            format  : 'json',
            batchActions: true, 
            url     : '/cake2/rd_cake/vouchers/pdf_voucher_formats.json',
            reader: {
                type: 'json',
                rootProperty: 'items',
                messageProperty: 'message'
            }
    },
    autoLoad: true
});
