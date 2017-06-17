Ext.define('Rd.view.dashboard.pnlDashboard', {
    extend  : 'Ext.panel.Panel',
    alias   : 'widget.pnlDashboard',
    layout  : 'fit',
    dashboard_data  : undefined,
    initComponent: function () {
        var me = this;
      
        var username =  me.dashboard_data.user.username;
        
        //Some initial values
        var header  = Rd.config.headerName;
        var lA      = Rd.config.levelAColor; 
       // var stA     = 'color:'+lA+';font-weight:200; letter-spacing: 2px;';
        var stA     = 'color:'+lA+';font-weight:200; font-size: smaller;';
        var tpl     = new Ext.XTemplate('<h1>'+header+'<span style="'+stA+'"> | <i class="fa">{fa_value}</i> {value}</span><h1>');
        
        var footer  = Rd.config.footerName;
        var style   = {}; //Empty Style
        var imgActive = false; //No Image
        var imgFile   = '';
        var fg      = false;
        
        if(me.dashboard_data.white_label.active == true){
            header  = me.dashboard_data.white_label.hName;
            footer  = me.dashboard_data.white_label.fName;
            
            var bg  = me.dashboard_data.white_label.hBg;
            style   = {
                'background' : bg
            };
            
            fg      = me.dashboard_data.white_label.hFg;
            if(me.dashboard_data.white_label.imgActive == true){
                var img = me.dashboard_data.white_label.imgFile;
                var tpl = new Ext.XTemplate(
                '<img src="'+img+'" alt="Logo" style="float:left; padding-right: 20px;">',
                '<h1 style="color:'+fg+';">'+header+'<span style="'+stA+'"> | <i class="fa">{fa_value}</i> {value}</span><h1>');
            }else{
                var tpl = new Ext.XTemplate('<h1 style="color:'+fg+';">'+header+'<span style="'+stA+'">',
                ' | <i class="fa">{fa_value}</i> {value}</span><h1>');
            }
        }      
        
        me.dockedItems = [
            {
                xtype   : 'toolbar',
                dock    : 'bottom',
                ui      : 'footer', 
                items   : [
                    '<b><i class="fa fa-graduation-cap"></i> '+username+'</b>',
                    '->', 
                    '<b>'+footer+"</b> "+Rd.config.footerLicense
                ]
            },
            {
                xtype   : 'toolbar',
                dock    : 'top',
                ui      : 'default',
                style   : style,
                items   : [              
                    {
                        xtype   : 'tbtext',
                        itemId  : 'tbtHeader', 
                        tpl     : tpl,
                        data    : {headerName:Rd.config.headerName}
                    },
                    '->',
                    {
                    xtype   : 'button',
                    glyph   : Rd.config.icnMenu,
                    scale   : 'medium',
                    menu    : [
                            {   text:i18n('sLogout'),      glyph : Rd.config.icnPower,  itemId: 'mnuLogout'},'-',
                            {   text:i18n('sSettings'),    glyph : Rd.config.icnSpanner,itemId: 'mnuSettings'},
                            {   text:i18n('sPassword'),    glyph : Rd.config.icnLock,   itemId: 'mnuPassword'    }
                        ]  
                    } 
                ]
            }
        ];    
        this.callParent();
    }
});


