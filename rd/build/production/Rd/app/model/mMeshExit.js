Ext.define('Rd.model.mMeshExit', {
    extend: 'Ext.data.Model',
    fields: [
         {name: 'id',               type: 'int'     },
         {name: 'mesh_id',          type: 'int'     },
         {name: 'name',             type: 'string'  },
         {name: 'type',             type: 'string'  },
         'connects_with',
         {name: 'auto_detect',      type: 'bool'}
        ]
});
