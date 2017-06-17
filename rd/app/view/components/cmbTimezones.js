Ext.define('Rd.view.components.cmbTimezones', {
    extend          : 'Ext.form.ComboBox',
    alias           : 'widget.cmbTimezones',
    fieldLabel      : 'Timezone',
    labelSeparator  : '',
    queryMode       : 'local',
    valueField      : 'id',
    displayField    : 'name',
    editable        : false,
    mode            : 'local',
    name            : 'timezone',
    multiSelect     : false,
    labelClsExtra   : 'lblRd',
    allowBlank      : false,
    initComponent: function(){
        var me      = this;
        var s       = Ext.create('Ext.data.Store', {
            fields: ['id', 'name'],
            proxy: {
                    type    : 'ajax',
                    format  : 'json',
                    batchActions: true, 
                    url     : '/cake2/rd_cake/meshes/timezone_index.json',
                    reader: {
                        type            : 'json',
                        rootProperty            : 'items',
                        messageProperty : 'message'
                    }
            },
            autoLoad: true
        });
        me.store = s;
        me.callParent(arguments);
    }
});
