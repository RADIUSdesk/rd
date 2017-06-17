Ext.define('Rd.view.components.cmbCountries', {
    extend          : 'Ext.form.ComboBox',
    alias           : 'widget.cmbCountries',
    fieldLabel      : 'Country',
    labelSeparator  : '',
    queryMode       : 'local',
    valueField      : 'id',
    displayField    : 'name',
    editable        : false,
    mode            : 'local',
    name            : 'country',
    multiSelect     : false,
    labelClsExtra   : 'lblRdReq',
    allowBlank      : false,
    initComponent: function(){
        var me      = this;
        var s       = Ext.create('Ext.data.Store', {
            fields: [
                {name: 'id',    type: 'string'},
                {name: 'name',  type: 'string'}
            ],
            proxy: {
                    type    : 'ajax',
                    format  : 'json',
                    batchActions: true, 
                    url     : '/cake2/rd_cake/meshes/country_index.json',
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
