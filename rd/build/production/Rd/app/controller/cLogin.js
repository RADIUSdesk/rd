Ext.define('Rd.controller.cLogin', {
    extend: 'Ext.app.Controller',
    views:  ['login.pnlLogin'],
    stores: ['sLanguages'],
    config: {
        urlLogin    : '/cake2/rd_cake/desktop/authenticate.json',
        urlWallpaper: 'resources/images/wallpapers/3.jpg'
    },
    refs: [
        { ref: 'viewP',         selector: 'viewP',          xtype: 'viewP',      autoCreate: true },
        { ref: 'pnlLogin',      selector: 'pnlLogin',       xtype: 'pnlLogin',   autoCreate: false}
    ], 
   init: function() {
        me = this;
        if (me.inited) {
            return;
        }
        me.inited = true;
        me.control({
            '#winLogin button[type="submit"]': {
                click: me.login
            },
            'pnlLogin #cmbLanguage': {
                select: me.onLanguageSelect
            },
            '#inpPassword': {
                specialkey: function(field, e) {
                    if(e.getKey() == e.ENTER) {
                        var form = field.up('form');
                        var btn  = form.down('button[type="submit"]');
                        btn.fireEvent('click', btn);
                    }
                }
            }
        });
    },
    actionIndex: function(){
        var me = this;
        //Populate the Language store with a list of languages
        me.getStore('sLanguages').loadData(me.application.getLanguages());
        
        var li = me.getView('login.pnlLogin').create({'url':me.getUrlWallpaper()});
        var vp = me.getViewP();
        vp.removeAll(true);
        vp.add([li]);

        //Get record and value
        //Get the value of the previous language
        li.down("#cmbLanguage").setValue(me.application.getSelLanguage());
    },

    login: function(button){
        var me      = this;
        var win    = button.up('window'),
        form        = win.down('form');
        form.submit({
            clientValidation: true,
            url: me.getUrlLogin(),
            success: function(form, action) {
                me.application.setDesktopData(action.result.data);

                //Set the token cookie
                var now = new Date();
                now.setDate(now.getDate() + 1);
                Ext.util.Cookies.set("Token", action.result.data.token, now, "/", null, false);

                //Add the token and language (the 3rd place where we can ser extraParams - remember each time we set it overrides!
                Ext.Ajax.setExtraParams({'token': action.result.data.token,'sel_language': me.application.getSelLanguage()});

                me.getViewP().removeAll(true);
                win.close();
                me.application.runAction('cDesktop','Index');
            },
            failure: Ext.ux.formFail
        });
    },
    actionExit: function() {
        var me = this;
        me.getViewP().removeAll(true);     //Remove the current panel that fills the viewport

        var desktop = this.application.getController('cDesktop');
        desktop.closeAllWindows();

        Ext.util.Cookies.clear("Token");
        me.actionIndex();
    },
    onLanguageSelect: function(combo, record){
        var sr = record;

        Ext.MessageBox.show({
           title: i18n('sNew_language_selected'),
           msg: i18n('sChanging_language_please_wait')+'...',
           closable: false,
           width:300,
           wait:true,
           waitConfig: {interval:200},
           icon:'ext-mb-download', //custom class in msg-box.html
           animateTarget: combo
        });

        //We do this to allow the display of the message and then reloading else it leave the user confused
        setTimeout(function(){
            Ext.MessageBox.hide();
            Ext.state.Manager.set('rdLanguage',sr.getId());
            Ext.state.Manager.set('rdLanguageRtl',sr.get('rtl'));
            console.log(Ext.state.Manager.get('rdLanguageRtl'));
            location.reload();
        }, 1000);  
    }
});
