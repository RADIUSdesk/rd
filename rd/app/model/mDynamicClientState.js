Ext.define('Rd.model.mDynamicClientState', {
    extend: 'Ext.data.Model',
    fields: [
         {name: 'id',           type: 'int'     },
         {name: 'state',        type: 'bool' },
         {name: 'time',         type: 'string'  },
         {name: 'start',        type: 'string'  },
         {name: 'end',          type: 'string'  }
        ]
});
