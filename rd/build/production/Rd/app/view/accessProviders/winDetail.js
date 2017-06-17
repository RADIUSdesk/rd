Ext.define('Rd.view.accessProviders.winDetail', {
    extend: 'Ext.window.Window',
    alias : 'widget.winAccessProviderDetail',
    title : i18n('sNew_Access_Provider'),
    layout: 'fit',
    autoShow: true,
    width: 400,
    height: 500,
    resizable:  false,
    iconCls: 'add',
    glyph: Rd.config.icnAdd,  
    parent_name: '',
    parent_id: '',
    initComponent: function(){
        var me = this;
        me.items = [{ 'xtype' : 'frmAccessProviderDetail', 'pwdHidden': false, parent_id: this.parent_id, parent_name: this.parent_name}];
        me.callParent(arguments);
    }
    
});
