Ext.define('Rd.controller.cDashboard', {
    extend: 'Ext.app.Controller',
    views: [
        'components.cmbRealm',
        'dashboard.pnlDashboard',
        'dashboard.tpDashboard',
        'dashboard.winPasswordChanger',
        'dashboard.winDashboardSettings'
    ],
    config: {
        urlChangePassword           : '/cake3/rd_cake/dashboard/change_password.json',
        urlSettingsSubmit           : '/cake3/rd_cake/dashboard/settings_submit.json',
        urlViewSettings             : '/cake3/rd_cake/dashboard/settings_view.json'
    },
    models: [
        'mRealm'
    ],
    requires: [
 
    ],
    stores: [
    
    ], 
    refs: [
        {   ref: 'viewP',           selector: 'viewP',          xtype: 'viewP',    autoCreate: true},
        {   ref: 'pnlDashboard',    selector: 'pnlDashboard',   xtype: 'pnlDashboard' }
    ],
    init: function() {
        var me  = this;
        if (me.inited) {
            return;
        }
        me.inited = true; 
        this.control(
            {           
                'tpDashboard #cWelcome' : {
				    activate	: function(pnl){
				        me.application.runAction('cWelcome','Index',pnl);
				    }
			    },         
                'tpDashboard #cDataUsage' : {
				    activate	: function(pnl){
				        me.application.runAction('cDataUsage','Index',pnl);
				    }
			    },  
			    'tpDashboard #cUtilities' : {
				    activate	: function(pnl){
				        me.application.runAction('cUtilities','Index',pnl);
				    }
			    },    
                'tpDashboard #cAccessProviders' : {
				    activate	: function(pnl){
				        me.application.runAction('cAccessProviders','Index',pnl);
				    }
			    },
			    'tpDashboard #cMeshes' : {
				    activate	: function(pnl){
				        me.application.runAction('cMeshes','Index',pnl);
				        me.updateBanner(pnl);
				    }
			    },
			    'tpDashboard #cAccessPoints' : {
				    activate	: function(pnl){
				        me.application.runAction('cAccessPoints','Index',pnl);
				        me.updateBanner(pnl);
				    }
			    },
			    'tpDashboard #cProfileComponents' : {
				    activate	: function(pnl){
				        me.application.runAction('cProfileComponents','Index',pnl);
				    }
			    },
			    'tpDashboard #cProfiles' : {
				    activate	: function(pnl){
				        me.application.runAction('cProfiles','Index',pnl);
				    }
			    },  
			    'tpDashboard #cActivityMonitor' : {
				    activate	: function(pnl){
				        me.application.runAction('cActivityMonitor','Index',pnl);
				    }
			    }, 
			    'tpDashboard #cPermanentUsers' : {
				    activate	: function(pnl){
				        me.application.runAction('cPermanentUsers','Index',pnl);
				    }
			    },
			    'tpDashboard #cVouchers' : {
				    activate	: function(pnl){
				        me.application.runAction('cVouchers','Index',pnl);
				    }
			    },
			    'tpDashboard #cDevices' : {
				    activate	: function(pnl){
				        me.application.runAction('cDevices','Index',pnl);
				    }
			    },
			    'tpDashboard #cTopUps' : {
				    activate	: function(pnl){
				        me.application.runAction('cTopUps','Index',pnl);
				    }
			    },
			    'tpDashboard #cRealms' : {
				    activate	: function(pnl){
				        me.application.runAction('cRealms','Index',pnl);
				    }
			    }, 
			    'tpDashboard #cDynamicClients' : {
				    activate	: function(pnl){
				        me.application.runAction('cDynamicClients','Index',pnl);
				    }
			    },
			    'tpDashboard #cNas' : {
				    activate	: function(pnl){
				        me.application.runAction('cNas','Index',pnl);
				    }
			    },
			    'tpDashboard #cTags' : {
				    activate	: function(pnl){
				        me.application.runAction('cTags','Index',pnl);
				    }
			    },
			    'tpDashboard #cSsids' : {
				    activate	: function(pnl){
				        me.application.runAction('cSsids','Index',pnl);
				    }
			    },
			    'tpDashboard #cDynamicDetails' : {
				    activate	: function(pnl){
				        me.application.runAction('cDynamicDetails','Index',pnl);
				    }
			    },
			    'tpDashboard #cOpenvpnServers' : {
				    activate	: function(pnl){
				        me.application.runAction('cOpenvpnServers','Index',pnl);
				    }
			    },
			    'tpDashboard #cIpPools' : {
				    activate	: function(pnl){
				        me.application.runAction('cIpPools','Index',pnl);
				    }
			    },
			    'tpDashboard #cAcos' : {
				    activate	: function(pnl){
				        me.application.runAction('cAcos','Index',pnl);
				    }
			    },
			    'tpDashboard #cLogViewer' : {
				    activate	: function(pnl){
				        me.application.runAction('cLogViewer','Index',pnl);
				    }
			    },
			    'tpDashboard #cDebug' : {
				    activate	: function(pnl){
				        me.application.runAction('cDebug','Index',pnl);
				    }
			    },
			    'pnlDashboard  #mnuLogout' : {
			        click   : me.onLogout
			    },
			    'pnlDashboard  #mnuSettings' : {
			        click   : me.onSettings
			    },
			    'winDashboardSettings #save': {
                    'click' : me.onSettingsSubmit
                },
                'winDashboardSettings': {
                    beforeshow:      me.loadSettings
                },
			    'pnlDashboard  #mnuPassword' : {
			        click   : me.onPassword
			    },
			    'winPasswordChanger #save': {
                    'click' : me.onChangePassword
                },
                
                //Add-on
                'tpDashboard #cGlobalDomains' : {
				    activate	: function(pnl){
				        me.application.runAction('cGlobalDomains','Index',pnl);
				    }
			    },
			    'tpDashboard #cCategories' : {
				    activate	: function(pnl){
				       me.application.runAction('cCategories','Index',pnl);
				    }
			    },
			    'tpDashboard #cFilters' : {
				    activate	: function(pnl){
				        me.application.runAction('cFilters','Index',pnl);
				    }
			    },
			    'tpDashboard #cBlackLists' : {
				    activate	: function(pnl){
				        me.application.runAction('cBlackLists','Index',pnl);
				    }
			    },
			    'tpDashboard #cWhiteLists' : {
				    activate	: function(pnl){
				        me.application.runAction('cWhiteLists','Index',pnl);
				    }
			    },
			    'tpDashboard #cSchedules' : {
				    activate	: function(pnl){
				        me.application.runAction('cSchedules','Index',pnl);
				    }
			    },   
			    'tpDashboard #cPolicies' : {
				    activate	: function(pnl){
				        me.application.runAction('cPolicies','Index',pnl);
				    }
			    },
			    'tpDashboard > tabpanel' : {
                    activate   : me.updateBanner
                }     
		    }
        );
    },
    actionIndex: function(){
        var me      = this;
        var dd      = me.application.getDashboardData();
        var user    = dd.user.username;
        var cls     = dd.user.cls;
        //We first create a plain dashboard
        var pnlDash = me.getView('dashboard.pnlDashboard').create({dashboard_data: dd});
        var tpDash  = me.getView('dashboard.tpDashboard').create({dashboard_data: dd});
        var vp = me.getViewP();
        vp.removeAll(true);
        vp.add([pnlDash]);
        pnlDash.add([tpDash]);
    },
    onLogout: function(b){
        var me = this;
        b.up('panel').close();
        me.getViewP().removeAll(true);
        me.application.runAction('cLogin','Exit');
    },
    loadSettings: function(win){
        var me      = this; 
        var form    = win.down('form'); 
        form.load({
            url         :me.getUrlViewSettings(), 
            method      :'GET',
            success     : function(a,b,c){
                var cmb     = form.down("cmbRealm");
                var rec     = Ext.create('Rd.model.mRealm', {name: b.result.data.realm_name, id: b.result.data.realm_id});
                cmb.getStore().loadData([rec],false);
                cmb.setValue(b.result.data.realm_id);  
            }
        });    
    },
    onSettings: function(b){
        var me = this;
        if(!Ext.WindowManager.get('winDashboardSettingsId')){
            var w = Ext.widget('winDashboardSettings',{
                id  :'winDashboardSettingsId'
            });
            w.show();        
        }  
    },
    onSettingsSubmit: function(button){
        var me      = this;
        var form    = button.up('form');
        var win     = button.up('window');
        form.submit({
            clientValidation: true,
            url: me.getUrlSettingsSubmit(),
            success: function(form, action) {
                win.close();
                Ext.ux.Toaster.msg(
                    i18n('sItem_updated'),
                    i18n('sItem_updated_fine'),
                    Ext.ux.Constants.clsInfo,
                    Ext.ux.Constants.msgInfo
                );
            },
            failure: Ext.ux.formFail
        });
    },
    onPassword: function(b){
        var me = this;
        if(!Ext.WindowManager.get('winPasswordChangerId')){
            var w = Ext.widget('winPasswordChanger',{
                id  :'winPasswordChangerId'
            });
            w.show();        
        }
    },
    onChangePassword: function(button){
        var me      = this;
        var form    = button.up('form');
        var win     = button.up('window');

        form.submit({
            clientValidation: true,
            url: me.getUrlChangePassword(),
            success: function(form, action) {
                //Important to update the token for the next requests
                //Set the token cookie
                var now = new Date();
                now.setDate(now.getDate() + 1);
                Ext.util.Cookies.set("Token", action.result.data.token, now, "/", null, false);
                
                var token = action.result.data.token; 
                Ext.Ajax.setExtraParams({token : token});
                win.close();
                Ext.ux.Toaster.msg(
                    i18n('sItem_updated'),
                    i18n('sItem_updated_fine'),
                    Ext.ux.Constants.clsInfo,
                    Ext.ux.Constants.msgInfo
                );
            },
            failure: Ext.ux.formFail
        });
    },
    updateBanner: function(tabpanel) {  
        var glyph   = tabpanel.getGlyph();
        var title   = tabpanel.getTitle();
        var iConfig = tabpanel.getInitialConfig();
        if(iConfig.tooltip !== undefined){
            title = iConfig.tooltip;
        }
        //Glyph needs to be witout '@FontAwesome';
        glyph       = glyph.replace('@FontAwesome', "");
        
        //Now we can set it in the header...
        var pnlDashboard = tabpanel.up('pnlDashboard');
        pnlDashboard.down('#tbtHeader').setData({fa_value:'&#'+glyph+';', value :title});
    }
});
