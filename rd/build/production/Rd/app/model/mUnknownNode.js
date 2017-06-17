Ext.define('Rd.model.mUnknownNode', {
    extend: 'Ext.data.Model',
    fields: [
         {name: 'id',               type: 'int'     },
         {name: 'mac',              type: 'string'  },
         {name: 'vendor',         	type: 'string'  },
		 {name: 'from_ip',         	type: 'string'  },
		 {name: 'vendor',         	type: 'string'  },
		 {name: 'gateway',  		type: 'bool'    },
		 {name: 'last_contact',    	type: 'date',       dateFormat: 'Y-m-d H:i:s'   },
         'last_contact_human',
         'new_server',
         'new_server_status'
        ]
});
