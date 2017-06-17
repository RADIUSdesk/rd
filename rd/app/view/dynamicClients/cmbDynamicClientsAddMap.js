Ext.define('Rd.view.dynamicClients.cmbDynamicClientsAddMap', {
    extend          : 'Ext.form.ComboBox',
    alias           : 'widget.cmbDynamicClientsAddMap',
    fieldLabel      : 'Available',
    labelSeparator  : '',
    queryMode       : 'local',
    valueField      : 'id',
    displayField    : 'name',
    editable        : false,
    mode            : 'local',
    multiSelect     : false,
    labelClsExtra   : 'lblRdReq',
    allowBlank      : false,
    initComponent: function(){
        var me      = this;
        var s       = Ext.create('Ext.data.Store', {
            fields	: ['id', 'name'],
            proxy	: {
                    type    : 'ajax',
                    format  : 'json',
                    batchActions: true,
                    url     : '/cake2/rd_cake/dynamic_clients/clients_avail_for_map.json',
                    reader: {
                        type            : 'json',
                        rootProperty    : 'items',
                        messageProperty : 'message'
                    }
            },
            autoLoad: true
        });
		
        me.store = s;
        me.callParent(arguments);
    }
});
