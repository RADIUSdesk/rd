Ext.define('Rd.model.mAccessProviderGrid', {
    extend: 'Ext.data.Model',
    fields: [
         {name: 'id',       type: 'int'     },
         {name: 'owner', type: 'string'  },
         {name: 'username', type: 'string'  },
         'name','surname', 'phone', 'email', 'monitor', 'active','language','notes'
    ]
});
