Ext.define('Rd.controller.cUtilities', {
    extend: 'Ext.app.Controller',
    actionIndex: function(pnl){

        var me = this;   
        if (me.populated) {
            return; 
        }     
        pnl.add({
            xtype   : 'pnlUtilities',
            border  : true,
            itemId  : 'tabUtilities',
            plain   : true
        });
        me.populated = true;
    },

    views:  [
        'utilities.pnlUtilities'
    ],
    stores: [],
    models: [],
    config: {
       
    },
    refs: [
         {  ref: 'pnlUtilities',     selector:   'pnlUtilities'}
    ],
    init: function() {
        var me = this;
        if (me.inited) {
            return;
        }
        me.inited = true;
        me.control({
            '#tabUtilities' : {
                destroy   :      me.appClose   
            },
            'pnlUtilities #btnRadiusClient' : {
                click   : function(btn){
                    me.application.runAction('cRadiusClient','Index')
                } 
            },
            'pnlUtilities #btnPassword' : {
                click   : function(btn){
                    me.application.runAction('cPassword','Index')
                } 
            },
            'pnlUtilities #btnActivityMonitor' : {
                click   : me.openActivityMonitor
            },
            'pnlUtilities #btnDataUsage' : {
                click   : me.openDataUsage
            },
            'pnlUtilities #btnSetupWizard' : {
                click   : function(btn){
                    me.application.runAction('cSetupWizard','Index')
                } 
            }
        });
    },
    appClose:   function(){
        var me          = this;
        me.populated    = false;
    },
    openActivityMonitor: function(btn){
        var me  = this;
        var pnl = me.getPnlUtilities();
        me.application.runAction('cActivityMonitor','Index',pnl); 
    },
    openDataUsage: function(btn){
        var me  = this;
        var pnl = me.getPnlUtilities();
        var tp  = pnl.up('tabpanel');
        var check_if_there = tp.down('#cDataUsage');
        
        if(check_if_there){
            tp.setActiveTab('cDataUsage');
            return;
        }
        
        tp.add({
             title   : 'Data Usage',
             glyph   : Rd.config.icnData,
             id      : 'cDataUsage',
             layout  : 'fit'
        
        });
        tp.setActiveTab('cDataUsage');
        me.application.runAction('cDataUsage','Index',tp);
    }
});
