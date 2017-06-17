Ext.define('Rd.view.components.cmbApProfile', {
    extend          : 'Ext.form.ComboBox',
    alias           : 'widget.cmbApProfile',
    fieldLabel      : 'Profile',
    labelSeparator  : '',
    forceSelection  : true,
    queryMode       : 'remote',
    valueField      : 'id',
    displayField    : 'name',
    typeAhead       : true,
    allowBlank      : false,
    mode            : 'local',
    name            : 'ap_profile_id',
    labelClsExtra   : 'lblRd',
    extraParam      : false,
    initComponent   : function() {
        var me= this;
        var s = Ext.create('Ext.data.Store', {
        fields: ['id', 'name'],
        proxy: {
                type    : 'ajax',
                format  : 'json',
                url     : '/cake2/rd_cake/ap_profiles/index.json',
                reader: {
                    type            : 'json',
                    rootProperty    : 'items',
                    messageProperty : 'message'
                }
            },
            autoLoad    : false
        });
        if(me.extraParam){
        	s.getProxy().setExtraParam('ap_profile_id',me.extraParam);
        }
        me.store = s;
        this.callParent(arguments);
    }
});
