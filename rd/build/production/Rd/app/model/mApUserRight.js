Ext.define('Rd.model.mApUserRight', {
    extend: 'Ext.data.Model',
    fields: [
         {name: 'id',           type: 'int'     },
         {name: 'alias',        type: 'string'  },
         {name: 'comment',      type: 'string'  },
         {name: 'group_right'},
         {name: 'parent_id',    type: 'int'     },
         {name: 'allowed',      type: 'bool'    }
        ],
    idProperty: 'id',
    //This is a funny - since a model is not traditionally associated with a tree view we have to create a 'dummy proxy'
    //which allows for the fake deleting of a model instance. We then call the store's sync method to do the real thing
    proxy: {
            type: 'ajax',
            //the store will get the content from the .json file
            url: '/cake2/rd_cake/acos_rights/index_ap.json',
            format  : 'json',
            batchActions: true, 
            reader: {
                type: 'json',
                rootProperty: 'items',
                messageProperty: 'message'
            },
            api: {
                destroy : '/cake2/rd_cake/acos_rights/dummy_delete.json'
            }
    }
});
