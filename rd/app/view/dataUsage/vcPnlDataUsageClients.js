Ext.define('Rd.view.dataUsage.vcPnlDataUsageClients', {
    extend  : 'Ext.app.ViewController',
    alias   : 'controller.vcPnlDataUsageClients', 
    onClickTodayButton: function(button){
        var me = this;
        me.getView().setScrollY(0,{duration: 1000});
    },
    onClickThisWeekButton: function(button){
        var me = this;
        var h_one = me.getView().down("pnlDataUsageClientsDay").getHeight();
        me.getView().setScrollY(h_one+1,{duration: 1000});
    },
    onClickThisMonthButton: function(button){
        var me = this;
        var h_one = me.getView().down("pnlDataUsageClientsDay").getHeight();
        var h_two  = me.getView().down("pnlDataUsageClientsWeek").getHeight();
        me.getView().setScrollY(h_one+h_two+1,{duration: 1000});
    }
});
