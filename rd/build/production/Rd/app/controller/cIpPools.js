Ext.define('Rd.controller.cIpPools', {
    extend: 'Ext.app.Controller',
    actionIndex: function(){

        var me = this;
        var desktop = this.application.getController('cDesktop');
        var win = desktop.getWindow('iPPools');
        if(!win){
            win = desktop.createWindow({
                id          : 'iPPools',
                btnText     : 'IP Pools',
                width       :800,
                height      :400,
                glyph       : Rd.config.icnIP,
                animCollapse:false,
                border      :false,
                constrainHeader:true,
                layout      : 'border',
                stateful    : true,
                stateId     : 'iPPools',
                items: [
                    {
                        region: 'north',
                        xtype:  'pnlBanner',
                        heading: 'IP Pools',
                        image:  'resources/images/48x48/ip_pools.png'
                    },
                    {
                        region  : 'center',
                        xtype   : 'panel',
                        layout  : 'fit',
                        border  : false,
						xtype   : 'gridIpPools'
                    }
                ]
            });
        }
        desktop.restoreWindow(win);    
        return win;
    },

    views:  [
        'components.pnlBanner',			'iPPools.gridIpPools', 			'iPPools.winIpPoolsAddWizard',
		'iPPools.winIpPoolEdit',		'components.cmbPermanentUser'
    ],
    stores: ['sIpPools'	, 'sPermanentUsers' ],
    models: ['mIpPool'	, 'mPermanentUser'  ],
    selectedRecord: null,
    config: {
        urlExportCsv    : '/cake2/rd_cake/ip_pools/export_csv',
        urlAddPool      : '/cake2/rd_cake/ip_pools/add_pool.json',
		urlAddIp        : '/cake2/rd_cake/ip_pools/add_ip.json',
        urlDelete       : '/cake2/rd_cake/ip_pools/delete.json',
		urlEdit         : '/cake2/rd_cake/ip_pools/edit.json',
        urlView       	: '/cake2/rd_cake/ip_pools/view.json'
    },
    refs: [
        {  ref: 'grid',  selector: 'gridIpPools'}       
    ],
    init: function() {
        var me = this;
        if (me.inited) {
            return;
        }
        me.inited = true;

        me.control({
            'gridIpPools #reload': {
                click:      me.reload
            },
            'gridIpPools #add': {
                click:      me.add
            },
            'winIpPoolsAddWizard #btnScrnChoiceNext' : {
                click:  me.btnScrnChoiceNext
            },
			'winIpPoolsAddWizard #btnNewPoolPrev' : {
                click:  me.btnScrnBackToStart
            },
			'winIpPoolsAddWizard #btnExistingPoolPrev' : {
                click:  me.btnScrnBackToStart
            },
			'winIpPoolsAddWizard #btnNewPoolNext' : {
                click:  me.btnNewPoolNext
            },
			'winIpPoolsAddWizard #btnExistingPoolNext' : {
                click:  me.btnExistingPoolNext
            },
            'gridIpPools #edit': {
                click:      me.edit
            }, 
            'gridIpPools #delete': {
                click:      me.del
            }, 
            'gridIpPools #csv'  : {
                //click:      me.csvExport
            },
			'winIpPoolEdit #save': {
                click: me.btnEditSave
            }
        });
    },
	reload: function(){
        var me =this;
        me.getGrid().getSelectionModel().deselectAll(true);
        me.getGrid().getStore().load();
    },
	onStoreIpPoolsLoaded: function() {
        var me      = this;
        var count   = me.getStore('sIpPools').getTotalCount();
        me.getGrid().down('#count').update({count: count});
    },
	add: function(button){
		var w = Ext.widget('winIpPoolsAddWizard',{id:'winIpPoolsAddWizardId'});
    	me.application.runAction('cDesktop','Add',w);
	},
	btnScrnChoiceNext: function(button){
        var me      = this;
        var win     = button.up('winIpPoolsAddWizard');
        var form    = button.up('form');
        var rbg     = form.down('radiogroup');

        if(rbg.getValue().rb == 'new_pool'){
            win.getLayout().setActiveItem('scrnNewPool'); 
        }

        if(rbg.getValue().rb == 'new_ip'){
            win.getLayout().setActiveItem('scrnExistingPool'); 
        }
    },
	btnScrnBackToStart: function(button){
        var me      = this;
        var win     = button.up('winIpPoolsAddWizard');
        win.getLayout().setActiveItem('scrnChoice');
    },
	btnNewPoolNext: function(button){
		var me      = this;
		var win     = button.up('window');
		var form    = button.up('form');
        form.submit({
            clientValidation: true,
            url: me.getUrlAddPool(),
            success: function(form, action) {
                win.close();
                me.getStore('sIpPools').load();
                Ext.ux.Toaster.msg(
                    i18n('sNew_item_created'),
                    i18n('sItem_created_fine'),
                    Ext.ux.Constants.clsInfo,
                    Ext.ux.Constants.msgInfo
                );
            },
            failure: Ext.ux.formFail
        });
	},
	btnExistingPoolNext: function(button){
		var me      = this;
		var win     = button.up('window');
		var form    = button.up('form');
		form.submit({
            clientValidation: true,
            url: me.getUrlAddIp(),
            success: function(form, action) {
                win.close();
                me.getStore('sIpPools').load();
                Ext.ux.Toaster.msg(
                    i18n('sNew_item_created'),
                    i18n('sItem_created_fine'),
                    Ext.ux.Constants.clsInfo,
                    Ext.ux.Constants.msgInfo
                );
            },
            failure: Ext.ux.formFail
        });
	},
	del:   function(){
        var me      = this;     
        //Find out if there was something selected
        if(me.getGrid().getSelectionModel().getCount() == 0){
             Ext.ux.Toaster.msg(
                        i18n('sSelect_an_item'),
                        i18n('sFirst_select_an_item_to_delete'),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
            );
        }else{
            Ext.MessageBox.confirm(i18n('sConfirm'), i18n('sAre_you_sure_you_want_to_do_that_qm'), function(val){
                if(val== 'yes'){

                    var selected    = me.getGrid().getSelectionModel().getSelection();
                    var list        = [];
                    Ext.Array.forEach(selected,function(item){
                        var id = item.getId();
                        Ext.Array.push(list,{'id' : id});
                    });

                    Ext.Ajax.request({
                        url: me.getUrlDelete(),
                        method: 'POST',          
                        jsonData: list,
                        success: function(batch,options){
                            Ext.ux.Toaster.msg(
                                i18n('sItem_deleted'),
                                i18n('sItem_deleted_fine'),
                                Ext.ux.Constants.clsInfo,
                                Ext.ux.Constants.msgInfo
                            );
                            me.reload(); //Reload from server
                        },                                    
                        failure: function(batch,options){
                            Ext.ux.Toaster.msg(
                                i18n('sProblems_deleting_item'),
                                batch.proxy.getReader().rawData.message.message,
                                Ext.ux.Constants.clsWarn,
                                Ext.ux.Constants.msgWarn
                            );
                            me.reload(); //Reload from server
                        }
                    });
                }
            });
        }
    },
	edit: function(button){
        var me      = this;
        var store   = me.getGrid().getStore();

        if( me.getGrid().getSelectionModel().getCount() == 0){
             Ext.ux.Toaster.msg(
                        i18n('sSelect_an_item'),
                        i18n('sFirst_select_an_item'),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
            );
        }else{
            var sr      =  me.getGrid().getSelectionModel().getLastSelected();
            var id      = sr.getId();
            if(!me.application.runAction('cDesktop','AlreadyExist','winIpPoolEditId')){
                var w = Ext.widget('winIpPoolEdit',
                {
                    id          :'winIpPoolEditId',
                    store       : store,
                    poolId      : id,
					record		: sr
                });
                me.application.runAction('cDesktop','Add',w);         
            }else{
                var w       = me.getEditWin();
                w.poolId    = id;
				w.record	= sr;
                me.load(w)
            } 
        }     
    },
	btnEditSave:  function(button){
        var me      = this;
        var win     = button.up("winIpPoolEdit");
        var form    = win.down('form');
        form.submit({
            clientValidation: true,
            url: me.getUrlEdit(),
            success: function(form, action) {
                win.close();
                win.store.load();
                Ext.ux.Toaster.msg(
                    i18n('sItem_updated'),
                    i18n('sItem_updated_fine'),
                    Ext.ux.Constants.clsInfo,
                    Ext.ux.Constants.msgInfo
                );
            },
            failure: Ext.ux.formFail
        });
    }
});
