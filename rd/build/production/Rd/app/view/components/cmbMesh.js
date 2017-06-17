Ext.define('Rd.view.components.cmbMesh', {
    extend          : 'Ext.form.ComboBox',
    alias           : 'widget.cmbMesh',
    fieldLabel      : 'Mesh',
    labelSeparator  : '',
    forceSelection  : true,
    queryMode       : 'remote',
    valueField      : 'id',
    displayField    : 'name',
    typeAhead       : true,
    allowBlank      : false,
    mode            : 'local',
    name            : 'mesh_id',
    labelClsExtra   : 'lblRd',
    extraParam      : false,
    initComponent   : function() {
        var me= this;
        var s = Ext.create('Ext.data.Store', {
        fields: ['id', 'name'],
        proxy: {
                type    : 'ajax',
                format  : 'json',
                batchActions: true, 
                url     : '/cake2/rd_cake/meshes/index.json',
                reader: {
                    type            : 'json',
                    rootProperty            : 'items',
                    messageProperty : 'message'
                }
            },
            autoLoad    : false
        });

        if(me.extraParam){
        	s.getProxy().setExtraParam('ap_id',me.extraParam);
        }
        me.store = s;
        this.callParent(arguments);
    }
});
