Ext.define('Rd.store.sNodeLists', {
    extend: 'Ext.data.Store',
    model: 'Rd.model.mNodeList',
    //To force server side sorting:
    remoteSort: true,
    proxy: {
            type    : 'ajax',
            format  : 'json',
            batchActions: true, 
            url     : '/cake2/rd_cake/node_lists/index.json',
            reader: {
                type            : 'json',
                rootProperty            : 'items',
                messageProperty : 'message',
                totalProperty   : 'totalCount' //Required for dynamic paging
            },
			api: {
                destroy  : '/cake2/rd_cake/meshes/mesh_node_delete.json'
            },
            simpleSortMode: true //This will only sort on one column (sort) and a direction(dir) value ASC or DESC
    },
    autoLoad: false
});
