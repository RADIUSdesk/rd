Ext.define('Rd.view.login.pnlLogin', {  
    extend      : 'Ext.panel.Panel',
    border      : false,
    autoCreate  : false,
    xtype       : 'pnlLogin',
    layout      : 'fit',
    requires    : ['Rd.view.components.compWallpaper'],
    url         : null,   //Placheholder for wallpaper URL   
    title       : Rd.config.headerName,
    dockedItems : [{
        xtype   : 'toolbar',
        dock    : 'bottom',
        ui      : 'footer', 
        items   : [
            '->', 
            '<b>'+Rd.config.footerName+"</b> "+Rd.config.footerLicense //2012-2016 GPL license'
        ]
    }],    
    initComponent: function () {
        var me      = this;
        me.items    = [{'xtype' : 'compWallpaper','url' : me.url}];
        me.add(me.loginWindow());
        me.callParent(arguments);
    },
    loginWindow: function(){
        var lw = Ext.create('Ext.window.Window',{
            itemId      : 'winLogin',
            layout      : 'fit',
            autoShow    : true,
            closable    : false,
            draggable   : false,
            resizable   : false,
            title       : i18n('sAuthenticate_please'),
            glyph       : Rd.config.icnLock,
            width       : 300,
            height      : 310,
            plain       : true,
            border      : false,
            items : [
                {
                    xtype       : 'form',
                    border      : false,
                    layout      : 'anchor',
                    height      : '100%',
                    bodyPadding : 20,
                    fieldDefaults: {
                        msgTarget       : 'under',
                        labelAlign      : 'top',
                        anchor          : '100%',
                        labelSeparator  : '',
                        labelClsExtra   : 'lblRd'
                    },
                    defaultType : 'textfield',
                    items: [
                        {
                            itemId      : 'inpUsername',
                            name        : "username",
                            fieldLabel  : i18n('sUsername'),
                            allowBlank  : false,
                            blankText   : i18n('sEnter_username')
                        },
                        {
                            itemId      : 'inpPassword',                            
                            name        : 'password',
                            fieldLabel  : i18n('sPassword'),
                            inputType   : 'password',
                            allowBlank  : false,
                            blankText   : i18n('sEnter_password')
                        }
                    ],
                    dockedItems: [{
                        xtype   : 'toolbar',
                        dock    : 'bottom',
                        ui      : 'footer',
                        padding : 0,
                        items: [ '->',
                            {
                                text    : i18n('sOK'),
                                margin  : Rd.config.buttonMargin,
                                action  : 'ok',
                                type    : 'submit',
                                formBind: true,
                                scale   : 'large',
                                glyph   : Rd.config.icnYes
                            }  
                        ]
                    }]
                }
            ] 
        });
    }
});
