Ext.define('Rd.view.aps.vcApViewEntries', {
    extend  : 'Ext.app.ViewController',
    alias   : 'controller.vcApViewEntries',
    span    : 'hour',   //Default start value
    init: function() {
    
    },
    onBtnTimeToggled   : function(button){
        var me      = this;
        if(button.pressed){
            me.span = button.getItemId();
            me.load();
        }
    },
    onBtnReloadClick: function(button){
        var me      = this;
        me.load();
    },
    gridAfterRender: function(pnl){
        var me      = this;
        me.load();
    },
    load    : function(){
        var me      = this;
        var apId    = me.getView().apId;
        me.getView().getStore().getProxy().setExtraParams({'timespan': me.span,'ap_id':apId});
        me.getView().getStore().load();
    }  
});
