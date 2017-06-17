Ext.define('Rd.store.sUnknownNodes', {
    extend: 'Ext.data.Store',
    model: 'Rd.model.mUnknownNode',
    //To force server side sorting:
    remoteSort: false,
    proxy: {
            type    : 'ajax',
            format  : 'json',
            batchActions: true, 
            url     : '/cake2/rd_cake/node_lists/unknown_nodes.json',
            reader: {
                type            : 'json',
                rootProperty            : 'items',
                messageProperty : 'message',
                totalProperty   : 'totalCount' //Required for dynamic paging
            },
			api: {
                destroy  : '/cake2/rd_cake/node_lists/unknown_node_delete.json'
            },
            simpleSortMode: true //This will only sort on one column (sort) and a direction(dir) value ASC or DESC
    },
    autoLoad: false
});
