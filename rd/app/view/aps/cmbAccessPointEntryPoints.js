Ext.define('Rd.view.aps.cmbAccessPointEntryPoints', {
    extend          : 'Ext.form.ComboBox',
    alias           : 'widget.cmbAccessPointEntryPoints',
    fieldLabel      : i18n("sConnects_with"),
    labelSeparator  : '',
    queryMode       : 'local',
    valueField      : 'id',
    displayField    : 'name',
    editable        : false,
    mode            : 'local',
    itemId          : 'entry_points',
    name            : 'entry_points[]',
    multiSelect     : true,
    labelClsExtra   : 'lblRdReq',
    allowBlank      : true,
    initComponent: function(){
        var me      = this;
        var s       = Ext.create('Ext.data.Store', {
            fields: [
                {name: 'id',    type: 'int'},
                {name: 'name',  type: 'string'}
            ],
            proxy: {
                    type    : 'ajax',
                    format  : 'json',
                    url     : '/cake2/rd_cake/ap_profiles/ap_profile_entry_points.json',
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
