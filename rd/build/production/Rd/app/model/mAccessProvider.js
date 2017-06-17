Ext.define('Rd.model.mAccessProvider', {
    extend: 'Ext.data.Model',
    fields: [
         {name: 'id',       type: 'int'     },
         {name: 'username', type: 'string'  },
         'name','surname', 'phone', 'email', 'address', 'monitor', 'active','language'
        ],
    idProperty: 'id',
    //This is a funny - since a model is not traditionally associated with a tree view we have to create a 'dummy proxy'
    //which allows for the fake deleting of a model instance. We then call the store's sync method to do the real thing
    proxy: {
            type: 'ajax',
            //the store will get the content from the .json file
            url: '/cake2/rd_cake/access_providers.json',
            format  : 'json',
            batchActions: true, 
            reader: {
                type: 'json',
                rootProperty: 'items',
                messageProperty: 'message'
            },
            api: {
                destroy : '/cake2/rd_cake/access_providers/dummy_delete.json'
            }
    }
});
