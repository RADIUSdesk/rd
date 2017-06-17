Ext.define('Rd.model.mMesh', {
    extend: 'Ext.data.Model',
    fields: [
         {name: 'id',               type: 'int'     },
         {name: 'name',             type: 'string'  },
         {name: 'ssid',             type: 'string'  },
         {name: 'bssid',            type: 'string'  },
         {name: 'owner',            type: 'string'  },
         {name: 'node_count',       type: 'int'},
		 {name: 'available_to_siblings',  type: 'bool'},
         {name: 'nodes_up',         type: 'int'},
         {name: 'nodes_down',       type: 'int'},
         {name: 'connected_users',  type: 'int'},
         {name: 'notes',            type: 'bool'},
         {name: 'update',           type: 'bool'},
         {name: 'delete',           type: 'bool'},
         {name: 'view',             type: 'bool'}
        ]
});
