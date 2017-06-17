Ext.define('Rd.model.mDevice', {
    extend: 'Ext.data.Model',
    fields: [
         {name: 'id',           type: 'int'     },
         {name: 'user',         type: 'string'  },
         {name: 'permanent_user_id'},
         {name: 'realm',        type: 'string'  },
         {name: 'profile',      type: 'string'  },
         {name: 'profile_id'},
         'perc_time_used',
         'perc_data_used',
         'name', 'description','vendor_id',
         {name: 'active',       type: 'bool'    },
         {name: 'last_accept_time'},
         {name: 'last_accept_nas'},
         {name: 'last_reject_time'},
         {name: 'last_reject_nas'},
         {name: 'last_reject_message'},
         {name: 'from_date'},
         {name: 'to_date'},
         'data_usage',
         'time_usage',
         {name: 'notes',        type: 'bool'},
         {name: 'update',       type: 'bool'},
         {name: 'delete',       type: 'bool'}
        ]
});
