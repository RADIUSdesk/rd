Ext.define('Rd.view.dataUsage.pnlDataUsageUserDetail', {
    extend      : 'Ext.panel.Panel',
    alias       : 'widget.pnlDataUsageUserDetail',
    scrollable  : true,
    layout: {
        type    : 'vbox',
        align   : 'stretch'
    },
    border      : true,
    ui          : 'light',
    title       : "Today",
    headerPosition: 'right',
    initComponent: function() {
        var me      = this;
        
        me.items = [
        
            {
                xtype   : 'progressbar',
                itemId  : 'pbData',
                text    : '<i class="fa  fa-database"></i> Data Usage',
                height  : 20,
                margin  : 5,
                //cls     : 'wifired',
                value   : 1 
            },
            {
                xtype   : 'progressbar',
                itemId  : 'pbTime',
                text    : '<i class="fa fa-clock-o"></i> Time Usage',
                height  : 20,
                margin  : 5,
                width   : '100%',
                //cls     : 'wifigreen',
                value   : 0.5
            },
            {
                xtype   : 'panel',
                itemId  : 'pnlInfo',
                height  : 150,
                tpl     : new Ext.XTemplate(
                    '<div>',   
                        '<ul class="fa-ul">',    
                            "<tpl if='type == \"voucher\"'>",
                            "<li style='color:blue;'><i class='fa-li fa  fa-ticket'></i> Voucher</li>",
                            "</tpl>",
                            "<li><i class='fa-li fa  fa-cubes'></i> {profile}</li>",
                            "<li><i class='fa-li fa  fa-star'></i><b>Created</b> {created}</li>",
                            '<tpl if="Ext.isDefined(last_reject_time)">', 
                                "<li style='color:red;'><i class='fa-li fa fa-warning'></i> <b>Last Failed Login</b> {last_reject_time}</li>",
                            "</tpl>",
                            '<tpl if="Ext.isDefined(last_reject_message)">', 
                                "<li style='color:red;'><i class='fa-li fa fa-warning'></i> <b>Failed Login Message</b> {last_reject_message}</li>",
                            "</tpl>",
                            '<tpl if="Ext.isDefined(last_accept_time)">', 
                                "<li style='color:green;'><i class='fa-li fa fa-check-circle'></i> <b>Last Good Login</b> {last_accept_time}</li>",
                            "</tpl>",
                            '<tpl if="Ext.isDefined(data_cap)">', 
                                "<li><i class='fa-li fa fa-database'></i> <b>Data Cap</b> {data_cap}</li>",
                            "</tpl>",
                            '<tpl if="Ext.isDefined(data_used)">', 
                                "<li style='color:blue;'><i class='fa-li fa fa-database'></i> <b>Data Used</b> {data_used}</li>",
                            "</tpl>",
                            '<tpl if="Ext.isDefined(time_cap)">', 
                                "<li><i class='fa-li fa fa-clock-o'></i> <b>Time Cap</b> {time_cap}</li>",
                            "</tpl>",
                            '<tpl if="Ext.isDefined(time_used)">', 
                                "<li style='color:blue;'><i class='fa-li fa fa-clock-o'></i> <b>Time Used</b> {time_used}</li>",
                            "</tpl>",
                        '</ul>',
                    '</div>'
                )
            } 
        ]; 
        
        me.callParent(arguments);
    },
    paintUserDetail: function(user_detail){
    
        var me = this;
        
        if(Ext.isDefined(user_detail.username)){ 
            me.setTitle(user_detail.username);
        }
        
        if(Ext.isDefined(user_detail.type)){ 
            if(user_detail.type == 'voucher'){
                me.setGlyph(Rd.config.icnVoucher);
            }
            if(user_detail.type == 'user'){
                me.setGlyph(Rd.config.icnUser);
            }
            if(user_detail.type == 'device'){
                me.setGlyph(Rd.config.icnDevice);
            }
            
        }
        
        if(Ext.isDefined(user_detail.perc_data_used)){
        
            if(user_detail.perc_data_used < 70){
                var cls = "wifigreen";
                me.down('#pbData').toggleCls("wifiyellow",false);
                me.down('#pbData').toggleCls("wifired",false);
                me.down('#pbData').toggleCls(cls,true);     
            } 
            if(user_detail.perc_data_used >= 70 && user_detail.perc_data_used < 90){
                cls = "wifiyellow";
                me.down('#pbData').toggleCls("wifigreen",false);
                me.down('#pbData').toggleCls("wifired",false);
                me.down('#pbData').toggleCls(cls,true);   
            }
            if(user_detail.perc_data_used >= 90){
                cls = "wifired"
                me.down('#pbData').toggleCls("wifigreen",false);
                me.down('#pbData').toggleCls("wifiyellow",false);
                me.down('#pbData').toggleCls(cls,true);   
            }
            var str_data_usage = '<i class="fa  fa-database"></i>  Data Usage '+user_detail.perc_data_used+' %';
            var val_data_usage = user_detail.perc_data_used / 100;
            
            me.down('#pbData').show().setValue(val_data_usage).updateText(str_data_usage);
        }else{
            me.down('#pbData').hide()
        }
        
        if(Ext.isDefined(user_detail.perc_time_used)){
        
            if(user_detail.perc_time_used < 70){
                var cls = "wifigreen";
                me.down('#pbTime').toggleCls("wifiyellow",false);
                me.down('#pbTime').toggleCls("wifired",false);
                me.down('#pbTime').toggleCls(cls,true);     
            } 
            if(user_detail.perc_time_used >= 70 && user_detail.perc_time_used < 90){
                cls = "wifiyellow";
                me.down('#pbTime').toggleCls("wifigreen",false);
                me.down('#pbTime').toggleCls("wifired",false);
                me.down('#pbTime').toggleCls(cls,true);   
            }
            if(user_detail.perc_time_used >= 90){
                cls = "wifired"
                me.down('#pbTime').toggleCls("wifigreen",false);
                me.down('#pbTime').toggleCls("wifiyellow",false);
                me.down('#pbTime').toggleCls(cls,true);   
            }
            
            var str_time_usage = '<i class="fa fa-clock-o"></i> Time Usage '+user_detail.perc_time_used+' %';
            var val_time_usage = user_detail.perc_time_used / 100;  
         
            me.down('#pbTime').show().setValue(val_time_usage).updateText(str_time_usage);
        }else{
            me.down('#pbTime').hide()
        }
        
        me.down('#pnlInfo').setData(user_detail)
    
    }
});
