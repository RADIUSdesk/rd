Ext.define('Rd.view.meshes.winMeshEditExit', {
    extend:     'Ext.window.Window',
    alias :     'widget.winMeshEditExit',
    closable:   true,
    draggable:  true,
    resizable:  true,
    title:      i18n('sEdit_mesh_exit_point'),
    width:      530,
    height:     530,
    plain:      true,
    border:     false,
    layout:     'fit',
    iconCls:    'edit',
    glyph: Rd.config.icnEdit,
    autoShow:   false,
    meshId:    '',
    exitId:     '',
    store:      undefined,
    defaults: {
            border: false
    },
    requires: [
        'Ext.tab.Panel',
        'Ext.form.Panel',
        'Ext.form.field.Text',
        'Rd.view.meshes.cmbEncryptionOptions',
        'Rd.store.sEncryptionOptions',
        'Rd.model.mEncryptionOption',
        'Rd.view.components.cmbOpenVpnServers',
        'Rd.view.meshes.vcMeshExitPoint'
    ],
    controller  : 'vcMeshExitPoint',
    initComponent: function() {
        var me = this;

        //Set the combo
        var cmbConnectWith = Ext.create('Rd.view.meshes.cmbMeshEntryPoints',{
            labelClsExtra   : 'lblRdReq'
        });

		var hide_cp = true;
		if(me.type == 'captive_portal'){
			hide_cp = false;
		}
 
        cmbConnectWith.getStore().getProxy().setExtraParam('mesh_id',me.meshId);
        cmbConnectWith.getStore().getProxy().setExtraParam('exit_id',me.exitId);
        cmbConnectWith.getStore().load();
 
        var frmData = Ext.create('Ext.form.Panel',{
            border:     false,
            layout:     'fit',
            itemId:     'scrnData',
            autoScroll: true,
            fieldDefaults: {
                msgTarget   : 'under',
                labelClsExtra: 'lblRd',
                labelAlign  : 'left',
                labelSeparator: '',
                labelClsExtra: 'lblRd',
                labelWidth  : Rd.config.labelWidth,
                maxWidth    : Rd.config.maxWidth, 
                margin      : Rd.config.fieldMargin
            },
            defaultType: 'textfield',
            buttons : [
                {
                    itemId: 'save',
                    text: i18n('sOK'),
                    scale: 'large',
                    iconCls: 'b-btn_ok',
                    glyph   : Rd.config.icnYes,
                    formBind: true,
                    margin: Rd.config.buttonMargin
                }
            ],
            items:[
                {
                    xtype   : 'tabpanel',
                    layout  : 'fit',
                    xtype   : 'tabpanel',
                    margins : '0 0 0 0',
                    plain   : false,
                    tabPosition: 'bottom',
                    border  : false,
                    items   : [
                        { 
                            'title'     : i18n('sCommon_settings'),
                            'layout'    : 'anchor',
                            itemId      : 'tabRequired',
                            defaults    : {
                                anchor: '100%'
                            },
                            autoScroll:true,
                            items       : [
                                {
                                    itemId  : 'mesh_id',
                                    xtype   : 'textfield',
                                    name    : "mesh_id",
                                    hidden  : true,
                                    value   : me.meshId
                                }, 
                                {
                                    itemId  : 'type',
                                    xtype   : 'textfield',
                                    name    : 'type',
                                    hidden  : true
                                }, 
                                {
                                    itemId  : 'id',
                                    xtype   : 'textfield',
                                    name    : 'id',
                                    hidden  : true
                                }, 
                                {
                                    xtype       : 'checkbox',      
                                    fieldLabel  : i18n('sAuto_detect'),
                                    name        : 'auto_detect',
                                    inputValue  : 'auto_detect',
                                    checked     : true,
                                    labelClsExtra: 'lblRdReq'
                                },
                                {
                                    xtype       : 'numberfield',
                                    name        : 'vlan',
                                    itemId      : 'vlan',
                                    fieldLabel  : i18n('sVLAN_number'),
                                    value       : 0,
                                    maxValue    : 4095,
                                    step        : 1,
                                    minValue    : 0,
                                    labelClsExtra: 'lblRdReq',
                                    allowBlank  : false,
                                    blankText   : i18n("sSupply_a_value")
                                },
                                cmbConnectWith,
                                {
                                    itemId      : 'cmbOpenVpnServers',
                                    xtype       : 'cmbOpenVpnServers',
                                    labelClsExtra: 'lblRdReq',
                                    allowBlank  : false
                                }
                            ]
                        },
                        //---- Captive Protal ----
                        { 
                            title       : i18n('sCaptive_Portal_settings'),
                            layout      : 'fit',
                            disabled    : true,
                            itemId      : 'tabCaptivePortal',
                            hidden		: hide_cp,
                            items       : [ 
                                {
                                    xtype   : 'tabpanel',
                                    layout  : 'fit',
                                    xtype   : 'tabpanel',
                                    margins : '0 0 0 0',
                                    plain   : true,
                                    tabPosition: 'top',
                                    cls     : 'subTab',
                                    border  : false,
                                    items   :  [
                                        {
                                            title       : 'Basic',
                                            layout      : 'anchor',
                                            defaults    : {
                                                anchor: '100%'
                                            },
                                            autoScroll:true,
                                            items       :[
                                                {
                                                    xtype       : 'textfield',
                                                    fieldLabel  : i18n('sRADIUS_server1'),
                                                    name        : 'radius_1',
                                                    allowBlank  : false,
                                                    blankText   : i18n('sSupply_a_value'),
                                                    labelClsExtra: 'lblRdReq'
                                                },
                                                {
                                                    xtype       : 'textfield',
                                                    fieldLabel  : i18n('sRADIUS_server2'),
                                                    name        : 'radius_2',
                                                    allowBlank  : true,
                                                    labelClsExtra: 'lblRd'
                                                },
                                                {
                                                    xtype       : 'textfield',
                                                    fieldLabel  : i18n('sRADIUS_secret'),
                                                    name        : 'radius_secret',
                                                    allowBlank  : false,
                                                    labelClsExtra: 'lblRdReq'
                                                },
                                                 {
                                                    xtype       : 'textfield',
                                                    fieldLabel  : i18n('sRADIUS_NASID'),
                                                    name        : 'radius_nasid',
                                                    allowBlank  : false,
                                                    labelClsExtra: 'lblRdReq'
                                                },
                                                {
                                                    xtype       : 'textfield',
                                                    fieldLabel  : i18n('sUAM_URL'),
                                                    name        : 'uam_url',
                                                    allowBlank  : false,
                                                    labelClsExtra: 'lblRdReq'
                                                },
                                                {
                                                    xtype       : 'textfield',
                                                    fieldLabel  : i18n('sUAM_Secret'),
                                                    name        : 'uam_secret',
                                                    allowBlank  : false,
                                                    labelClsExtra: 'lblRdReq'
                                                },
                                                {
                                                    xtype       : 'textareafield',
                                                    grow        : true,
                                                    fieldLabel  : i18n('sWalled_garden'),
                                                    name        : 'walled_garden',
                                                    anchor      : '100%',
                                                    allowBlank  : true,
                                                    labelClsExtra: 'lblRd'
                                                 },
                                                 {
                                                    xtype       : 'checkbox',      
                                                    fieldLabel  : i18n('sSwap_octets'),
                                                    name        : 'swap_octet',
                                                    inputValue  : 'swap_octet',
                                                    checked     : true,
                                                    labelClsExtra: 'lblRdReq'
                                                },
                                                {
                                                    xtype       : 'checkbox',      
                                                    fieldLabel  : i18n('sMAC_authentication'),
                                                    name        : 'mac_auth',
                                                    inputValue  : 'mac_auth',
                                                    checked     : true,
                                                    labelClsExtra: 'lblRdReq'
                                                }
                                            ]
                                        },
                                        {
                                            title       : 'DNS',
                                            itemId      : 'tabDns',
                                            layout      : 'anchor',
                                            defaults    : {
                                                    anchor: '100%'
                                            },
                                            autoScroll:true,
                                            items       :[
                                                {
                                                    itemId      : 'chkDnsOverride',
                                                    xtype       : 'checkbox',      
                                                    fieldLabel  : 'Enable Override',
                                                    name        : 'dns_manual',
                                                    inputValue  : 'dns_manual',
                                                    checked     : false,
                                                    labelClsExtra: 'lblRd',
                                                    listeners   : {
											            change  : 'onChkDnsOverrideChange'
											        }
                                                },
                                                {
                                                    itemId      : 'txtDns1',
                                                    xtype       : 'textfield',
                                                    fieldLabel  : 'DNS-1',
                                                    name        : 'dns1',
                                                    allowBlank  : false,
                                                    labelClsExtra: 'lblRdReq',
                                                    disabled    : true
                                                },
                                                {
                                                    itemId      : 'txtDns2',
                                                    xtype       : 'textfield',
                                                    fieldLabel  : 'DNS-2',
                                                    name        : 'dns2',
                                                    allowBlank  : true,
                                                    labelClsExtra: 'lblRd',
                                                    disabled    : true
                                                },
                                                {
                                                    itemId      : 'chkAnyDns',
                                                    xtype       : 'checkbox',      
                                                    fieldLabel  : 'Allow Any DNS',
                                                    name        : 'uamanydns',
                                                    inputValue  : 'uamanydns',
                                                    checked     : true,
                                                    labelClsExtra: 'lblRd'
                                                },
                                                {
                                                    xtype       : 'checkbox',      
                                                    fieldLabel  : 'DNS Paranoia',
                                                    name        : 'dnsparanoia',
                                                    inputValue  : 'dnsparanoia',
                                                    checked     : false,
                                                    labelClsExtra: 'lblRd'
                                                },
                                                {
                                                    itemId      : 'chkDnsDesk',
                                                    xtype       : 'checkbox',      
                                                    fieldLabel  : 'Use DNSdesk',
                                                    name        : 'dnsdesk',
                                                    inputValue  : 'dnsdesk',
                                                    checked     : false,
                                                    labelClsExtra: 'lblRd',
                                                    listeners   : {
											            change  : 'onChkDnsDeskChange',
											            beforerender : 'onDnsDeskBeforeRender'
											        }
                                                }
                                            ]
                                        }, 
                                        {
                                            title       : 'Proxy',
                                            itemId      : 'tabProxy',
                                            layout      : 'anchor',
                                            defaults    : {
                                                    anchor: '100%'
                                            },
                                            autoScroll:true,
                                            items       :[
                                                {
                                                    itemId      : 'chkProxyEnable',
                                                    xtype       : 'checkbox',      
                                                    fieldLabel  : 'Enable',
                                                    name        : 'proxy_enable',
                                                    inputValue  : 'proxy_enable',
                                                    checked     : false,
                                                    labelClsExtra: 'lblRdReq'
                                                },
                                                {
                                                    xtype       : 'textfield',
                                                    fieldLabel  : 'Upstream proxy',
                                                    name        : 'proxy_ip',
                                                    allowBlank  : false,
                                                    labelClsExtra: 'lblRdReq',
                                                    disabled    : true
                                                },
                                                {
                                                    xtype       : 'textfield',
                                                    fieldLabel  : 'Upstream port',
                                                    name        : 'proxy_port',
                                                    allowBlank  : false,
                                                    labelClsExtra: 'lblRdReq',
                                                    disabled    : true
                                                },
                                                {
                                                    xtype       : 'textfield',
                                                    fieldLabel  : 'Auth name',
                                                    name        : 'proxy_auth_username',
                                                    allowBlank  : true,
                                                    labelClsExtra: 'lblRd',
                                                    disabled    : true
                                                },
                                                {
                                                    xtype       : 'textfield',
                                                    fieldLabel  : 'Auth password',
                                                    name        : 'proxy_auth_password',
                                                    allowBlank  : true,
                                                    labelClsExtra: 'lblRd',
                                                    disabled    : true
                                                }
                                            ]
                                        }, 
                                        {
                                            title       : 'Coova Specific',
                                            layout      : 'anchor',
                                            defaults    : {
                                                    anchor: '100%'
                                            },
                                            autoScroll:true,
                                            items       :[
                                                {
                                                    xtype       : 'textareafield',
                                                    grow        : true,
                                                    fieldLabel  : 'Optional config items',
                                                    name        : 'coova_optional',
                                                    anchor      : '100%',
                                                    allowBlank  : true,
                                                    labelClsExtra: 'lblRd'
                                                 }
                                            ]
                                        }
                                    ]
                                } 
                            ]
                        }
                        //--- End Captive Portal --- 
                    ]
                }
            ]
        });

        //Should we enable or disable the captive portal tab
        var tab_capt= frmData.down('#tabCaptivePortal');
        if(me.type == 'captive_portal'){
            tab_capt.setDisabled(false);
        }else{
            tab_capt.setDisabled(true); 
        }

        me.items = frmData;
        me.callParent(arguments);
    }
});
