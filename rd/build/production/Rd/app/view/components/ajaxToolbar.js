Ext.define('Rd.view.components.ajaxToolbar', {
    extend:'Ext.toolbar.Toolbar',
    alias :'widget.ajaxToolbar',
    url: null,
    initComponent: function(){
        var me = this;
        Ext.Ajax.request({
            url: me.url,
            method: 'GET',
            success: me.buildToolbar,
            scope: me
        });
        me.callParent(arguments);
    },
    buildToolbar: function(response){
        var me          = this;
        var jsonData    = Ext.JSON.decode(response.responseText);
        if(jsonData.success){
            Ext.each(jsonData.items,function(i){
                me.add(i);
            });
        }
    }
});

