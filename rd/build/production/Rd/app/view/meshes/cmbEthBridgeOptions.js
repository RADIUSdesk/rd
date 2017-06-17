Ext.define('Rd.view.meshes.cmbEthBridgeOptions', {
    extend          : 'Ext.form.ComboBox',
    alias           : 'widget.cmbEthBridgeOptions',
    fieldLabel      : 'Bridge with',
    labelSeparator  : '',
    queryMode       : 'local',
    valueField      : 'id',
    displayField    : 'name',
    allowBlank      : false,
    editable        : false,
    mode            : 'local',
    itemId          : 'eth_br_with',
    name            : 'eth_br_with',
    value           : '0', //Default value
    labelClsExtra   : 'lblRd',
    initComponent	: function(){
        var me      = this;
        var s       = Ext.create('Ext.data.Store', {
            fields: [
                {name: 'id',    type: 'int'},
                {name: 'name',  type: 'string'}
            ],
            proxy: {
                    type    	: 'ajax',
                    format  	: 'json',
                    batchActions: true, 
					extraParams	: { 'mesh_id' : me.meshId}, 
                    url     	: '/cake2/rd_cake/meshes/mesh_exit_view_eth_br.json',
                    reader: {
                        type	: 'json',
                        rootProperty	: 'items',
                        messageProperty: 'message'
                    }
            },
            autoLoad: true
        });
        me.store = s;
        me.callParent(arguments);
    }
});
