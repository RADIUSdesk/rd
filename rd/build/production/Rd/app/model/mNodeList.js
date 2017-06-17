Ext.define('Rd.model.mNodeList', {
    extend: 'Ext.data.Model',
    fields: [
         {name: 'id',               type: 'int'     },
         {name: 'mesh_id',          type: 'int'     },
		 {name: 'mesh',          	type: 'string'  },
         {name: 'name',             type: 'string'  },
		 {name: 'owner',        	type: 'string'  },
         {name: 'description',      type: 'string'  },
         {name: 'mac',              type: 'string'  },
         {name: 'hardware',         type: 'string'  },
         {name: 'power',            type: 'int'     },
         {name: 'ip',               type: 'string'  },
		 {name: 'last_contact',    	type: 'date',       dateFormat: 'Y-m-d H:i:s'   },
		 {name: 'available_to_siblings',  type: 'bool'},
         {name: 'update',       	type: 'bool'},
         {name: 'delete',       	type: 'bool'},
         'last_contact_human',
         'state'
        ]
});
