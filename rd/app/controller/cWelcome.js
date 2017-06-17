Ext.define('Rd.controller.cWelcome', {
    extend: 'Ext.app.Controller',
    actionIndex: function(pnl){
        var me = this; 
        if (me.populated) {
            return; 
        }    
        pnl.setHtml(
            '\
            <h2>Welcome to this controll panel</h2>\
            Please start off by creating a new <b>Realm</b>.\
            '
        );
        me.populated = true;
    },
    init: function() {
        var me = this;
        if (me.inited) {
            return;
        }
        me.inited = true;
    }
});
