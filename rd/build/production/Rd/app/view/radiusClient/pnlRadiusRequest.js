Ext.define('Rd.view.radiusClient.frmRadiusRequest', {
    extend  : 'Ext.form.Panel',
    alias   : 'widget.frmRadiusRequest',
    html    : '',
    autoScroll : true,
    autoCreate: true,
    requires: [
        'Rd.view.radiusClient.cmbRequestType'
    ],
    initComponent: function(){
        var me = this; 
        me.items = {



        }

     
        me.callParent(arguments);
    }
});

