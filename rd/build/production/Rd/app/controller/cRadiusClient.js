Ext.define('Rd.controller.cRadiusClient', {
    extend          : 'Ext.app.Controller',
    cmbVRendered    : false,
    vRecord         : undefined,
    cmbPuRendered   : false,
    puRecord        : undefined,
    cmbDRendered    : false,
    dRecord         : undefined,
    views:  [
        'components.pnlBanner',     'radiusClient.frmRadiusRequest',    'radiusClient.pnlRadiusReply'
    ],
    stores: [],
    models: ['mPermanentUser','mDevice', 'mVoucher' ],
    config: {
        urlRequest:         '/cake2/rd_cake/free_radius/test_radius.json'
    },
    refs: [
        { ref:  'cmbVoucher',       selector: 'cmbVoucher'          },
        { ref:  'cmbPermanent',     selector: 'cmbPermanentUser'    },
        { ref:  'cmbDevice',        selector: 'cmbDevice'           },
        { ref:  'cmbUserType',      selector: 'cmbUserType'         },
        { ref:  'pnlRadiusReply',   selector: 'pnlRadiusReply'      }
    ],
    init: function() {
        var me = this;
        if (me.inited) {
            return;
        }
        me.inited = true;
        me.control({
            'frmRadiusRequest cmbUserType': {
                change:      me.userTypeChange
            },
            'frmRadiusRequest cmbPermanentUser': {
                render:      me.renderEventPu
            },
            'frmRadiusRequest cmbDevice': {
                render:      me.renderEventD
            },
            'frmRadiusRequest cmbVoucher': {
                render:      me.renderEventV
            },
            'frmRadiusRequest #send': {
                click:      me.submitRequest
            },
            '#radiusClientWin'     : {
                destroy:    me.onDestroy
            } 
        });
    },
    actionIndex: function(){

        var me = this;
        var desktop = this.application.getController('cDesktop');
        var win = desktop.getWindow('radiusClientWin');
        if(!win){
            win = desktop.createWindow({
                id: 'radiusClientWin',
                //title:i18n('sRADIUS_client'),
                btnText:i18n('sRADIUS_client'),
                width:900,
                height:400,
                iconCls: 'radius_client',
                glyph: Rd.config.icnRadius,
                animCollapse:false,
                border:false,
                constrainHeader:true,
                layout: 'border',
                stateful: true,
                stateId: 'radiusClientWin',
                items: [
                    {
                        region: 'north',
                        xtype:  'pnlBanner',
                        heading:i18n('sRADIUS_client'),
                        image:  'resources/images/48x48/radius_client.png'
                    },
                    {
                        region  : 'center',
                        layout  : {
                            type    : 'hbox',
                            align   : 'stretch'
                        },
                        margins : '0 0 0 0',
                        border  : false,
                        
                        items   : [ 
                            {
                                title       : i18n('sRequest'),
                                flex        : 1,
                                xtype       : 'frmRadiusRequest',
                                margin      : 5
                            },
                            {
                                title       : i18n('sReply'),
                                flex        : 1,
                                xtype       : 'pnlRadiusReply',
                                margin      : 5
                            }
                        ]
                    }
                ]
            });
        }
        desktop.restoreWindow(win);    
        return win;
    },
    actionTestVoucher: function(v_record){
        var me = this;
        me.vRecord = v_record;
        if(me.cmbVRendered == true){
            console.log("Combo already rendered... set it");
            me.getCmbUserType().setValue('voucher');
            me.getCmbVoucher().getStore().loadData([me.vRecord],false);
            me.getCmbVoucher().setValue(me.vRecord.getId());
        }
        me.actionIndex();
    },

    actionTestPermanent: function(pu_record){
        var me = this;
        me.puRecord = pu_record;
        if(me.cmbPuRendered == true){
            console.log("Combo already rendered... set it");
            me.getCmbUserType().setValue('permanent');
            me.getCmbPermanent().getStore().loadData([me.puRecord],false);
            me.getCmbPermanent().setValue(me.puRecord.getId());
        }
        me.actionIndex();
    },
    actionTestDevice: function(d_record){
        var me = this;
        me.dRecord = d_record;
        if(me.cmbDRendered == true){
            console.log("Combo already rendered... set it");
            me.getCmbUserType().setValue('device');
            me.getCmbDevice().getStore().loadData([me.dRecord],false);
            me.getCmbDevice().setValue(me.dRecord.getId());
        }
        me.actionIndex();
    },
    renderEventV: function(cmb){
        var me = this;
        me.cmbVRendered = true;
        if(me.vRecord != undefined){
            console.log("Voucher detail present first time combo render");
            me.getCmbUserType().setValue('voucher');
            me.getCmbVoucher().getStore().loadData([me.vRecord],false);
            me.getCmbVoucher().setValue(me.vRecord.getId());
        } 
    },
    renderEventPu: function(cmb){
        var me = this;
        me.cmbPuRendered = true;
        if(me.puRecord != undefined){
            console.log("Permanent detail present first time combo render");
            me.getCmbUserType().setValue('permanent');
            me.getCmbPermanent().getStore().loadData([me.puRecord],false);
            me.getCmbPermanent().setValue(me.puRecord.getId());
        } 
    },
    renderEventD: function(cmb){
        var me = this;
        me.cmbDRendered = true;
        if(me.dRecord != undefined){
            console.log("Permanent detail present first time combo render");
            me.getCmbUserType().setValue('device');
            me.getCmbDevice().getStore().loadData([me.dRecord],false);
            me.getCmbDevice().setValue(me.dRecord.getId());
        } 
    },
    onDestroy: function(w){
        var me = this;
        //Clean up some object variables
        me.puRecord         = undefined;
        me.cmbPuRendered    = false;

        me.dRecord         = undefined;
        me.cmbDRendered    = false;

        me.vRecord         = undefined;
        me.cmbVRendered    = false;
    },
    userTypeChange: function(cmb,new_val,old_val){
        var me = this;
        var pu = cmb.up('form').down('cmbPermanentUser');
        var d  = cmb.up('form').down('cmbDevice');
        var v  = cmb.up('form').down('cmbVoucher');
        if(new_val == 'voucher'){

            v.setVisible(true);
            v.setDisabled(false);

            d.setVisible(false);
            d.setDisabled(true);

            pu.setVisible(false);
            pu.setDisabled(true);
        }


        if(new_val == 'device'){

            d.setVisible(true);
            d.setDisabled(false);

            v.setVisible(false);
            v.setDisabled(true);

            pu.setVisible(false);
            pu.setDisabled(true);
        }

        if(new_val == 'permanent'){

            pu.setVisible(true);
            pu.setDisabled(false);

            v.setVisible(false);
            v.setDisabled(true);

            d.setVisible(false);
            d.setDisabled(true);
        }
    },
    submitRequest: function(b){
        me = this;
        var form = b.up('form');
        me.getPnlRadiusReply().setLoading(true, true);
        form.submit({
            clientValidation: true,
            url: me.getUrlRequest(),
            success: function(form, action,b,c) {
                me.getPnlRadiusReply().setLoading(false);
                me.getPnlRadiusReply().update(action.result.data);
                console.log(action.result); 
            },
            failure: function(form,action){
                me.getPnlRadiusReply().setLoading(false);
                console.log(form);
                console.log(action); 
            }
        });
    }
});
