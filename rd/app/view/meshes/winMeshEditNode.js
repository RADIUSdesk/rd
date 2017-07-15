Ext.define('Rd.view.meshes.winMeshEditNode', {
    extend      : 'Ext.window.Window',
    alias       : 'widget.winMeshEditNode',
    closable    : true,
    draggable   : true,
    resizable   : true,
    title       : 'Edit mesh node',
    width       : 450,
    height      : 550,
    plain       : true,
    border      : false,
    layout      : 'fit',
    iconCls     : 'add',
    glyph       : Rd.config.icnEdit,
    autoShow    : false,
    nodeId      : '',
	meshName	: '',
	meshId		: '',
    defaults: {
            border: false
    },
    requires: [
        'Ext.tab.Panel',
        'Ext.form.Panel',
        'Ext.form.field.Text',
        'Rd.view.meshes.cmbHardwareOptions',
		'Rd.view.components.cmbMesh',
        'Rd.view.components.cmbFiveGigChannels',
        'Rd.view.meshes.vcMeshNodeGeneric',
        'Rd.view.meshes.tagStaticEntries'
    ],
    controller  : 'vcMeshNodeGeneric',
    initComponent: function() {
        var me 		= this; 
		var cmb = Ext.create('Rd.view.components.cmbMesh',
		    { 
		        itemId          : 'mesh_id', 
		        labelClsExtra   : 'lblRdReq',
		        listeners       : {
                        change : 'onCmbMeshChange'
                }  
		    });
		cmb.getStore().loadData([],false); //Wipe it
		cmb.getStore().loadData([{'id' : me.meshId, 'name' : me.meshName}],true);//Add it
		//cmb.setValue(me.meshId);//Show it (We don't need to show it.... the view just need to specify it as an INTEGER and NOT string  in JSON)

        var frmData = Ext.create('Ext.form.Panel',{
            border:     false,
            layout:     'fit',
            defaults: {
                anchor: '100%'
            },
            itemId:     'scrnData',
            autoScroll: true,
            fieldDefaults: {
                msgTarget       : 'under',
                labelClsExtra   : 'lblRd',
                labelAlign      : 'left',
                labelSeparator  : '',
                labelClsExtra   : 'lblRd',
                labelWidth      : Rd.config.labelWidth,
                maxWidth        : Rd.config.maxWidth, 
                margin          : Rd.config.fieldMargin
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
            items: [

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
                            'title'     : 'Basic',
                            'layout'    : 'anchor',
                            glyph       : Rd.config.icnStar,
                            itemId      : 'tabRequired',
                            defaults    : {
                                anchor: '100%'
                            },
                            autoScroll:true,
                            items       : [
                                {
						            itemId  : 'node_id',
						            xtype   : 'textfield',
						            name    : "id",
						            hidden  : true,
						            value   : me.nodeId
						        },
						        {
						            itemId      : 'ac_device',
						            xtype       : 'textfield',
						            name        : 'device_type',
						            hidden      : true,
						            value       : 'standard'
						        },
								cmb,
						        {
						            xtype       : 'textfield',
						            fieldLabel  : i18n('sMAC_address'),
						            name        : "mac",
						            allowBlank  : false,
						            blankText   : i18n('sSupply_a_value'),
						            labelClsExtra: 'lblRdReq',
						            vtype       : 'MacAddress',
						            fieldStyle  : 'text-transform:lowercase',
						            value       : 'A8-40-41-13-60-E3'
						        },
						        {
						            xtype       : 'textfield',
						            fieldLabel  : i18n('sName'),
						            name        : "name",
						            allowBlank  : false,
						            blankText   : i18n('sSupply_a_value'),
						            labelClsExtra: 'lblRdReq'
						        },
						        {
						            xtype       : 'textfield',
						            fieldLabel  : i18n('sDescription'),
						            name        : "description",
						            allowBlank  : true,
						            labelClsExtra: 'lblRd'
						        },
						        {
						            xtype           : 'cmbHardwareOptions',
						            labelClsExtra   : 'lblRdReq',
						            allowBlank      : false,
						            listeners       : {
                                            change : 'onCmbHardwareOptionsChange'
                                    } 
						        },
						        {
						            xtype       : 'tagStaticEntries',
						            meshId      : me.meshId
						        },
						        {
						            xtype       : 'cmbStaticExits',
						            meshId      : me.meshId,
						            nodeId      : me.nodeId
						        }
                            ]
                        },
						{
							title       : 'Radios',
                            disabled    : true,
							layout      : 'fit',
                            itemId      : 'tabRadio',
                            glyph       : Rd.config.icnWifi,
                            autoScroll	:true,
							hidden		: true,
                            items       : [ {
                                layout  : 'fit',
                                xtype   : 'tabpanel',
                                margins : '0 0 0 0',
                                plain   : true,
                                tabPosition: 'top',
                                border  : false,
                                cls     : 'subTab',
                                items   :  [
                                    {
                                        title       : 'Radio0',
                                        xtype       : 'panel',
                                        baseCls     : 'tabRadio',
                                        layout      : 'anchor',
                                        defaults    : {
                                            anchor: '100%'
                                        },
                                        autoScroll:true,
                                        items       :[
                                                {
											        xtype       : 'checkbox',      
											        fieldLabel  : 'Enable',
											        itemId      : 'chkRadio0Enable',
											        name        : 'radio0_enable',
											        inputValue  : 'radio0_enable',
											        checked     : true,
											        labelClsExtra: 'lblRdReq',
											        listeners   : {
											            change  : 'onChkRadioEnableChange'
											        }
								
										        },
										        {
											        xtype       : 'checkbox',      
											        fieldLabel  : 'Mesh',
											        itemId      : 'chkRadio0Mesh',
											        name        : 'radio0_mesh',
											        inputValue  : 'radio0_mesh',
											        checked     : true,
											        labelClsExtra: 'lblRd',
											        listeners   : {
											            change  : 'onChkRadioMeshChange'
											        }
								
										        },
										        {
											        xtype       : 'checkbox',      
											        fieldLabel  : 'Entry point',
											        itemId      : 'chkRadio0Entry',
											        name        : 'radio0_entry',
											        inputValue  : 'radio0_entry',
											        checked     : true,
											        labelClsExtra: 'lblRd'
										        },
										        {
											        xtype       : 'radio',
											        fieldLabel  : '2.4G',
											        name      	: 'radio0_band',
											        inputValue	: '24',
											        itemId      : 'radio24',
											        labelClsExtra: 'lblRd',
											        //checked		: true,
											        listeners   : {
											            change  : 'onRadio_0_BandChange'
											        }
										        }, 
										        {
											        xtype       : 'radio',
											        fieldLabel  : '5G',
											        name      	: 'radio0_band',
											        inputValue	: '5',
											        itemId      : 'radio5',
											        labelClsExtra: 'lblRd',
											        listeners   : {
											            change  : 'onRadio_0_BandChange'
											        }
										        },
										        {
										            xtype       : 'numberfield',
										            anchor      : '100%',
										            name        : 'radio0_two_chan',
										            fieldLabel  : i18n('s2_pt_4G_Channel'),
										            value       : 5,
										            maxValue    : 14,
										            minValue    : 1,
											        hidden		: true,
											        disabled	: true,
											        itemId		: 'numRadioTwoChan'
										        },
                                                {
                                                    xtype       : 'cmbFiveGigChannels',
                                                    anchor      : '100%',
										            name        : 'radio0_five_chan',
										            fieldLabel  : i18n('s5G_Channel'),
											        hidden		: true,
											        disabled	: true,
											        itemId		: 'numRadioFiveChan'
                                                }   	         
                                        ]
                                    },
                                    {
                                        title       : 'Radio1',
                                        xtype       : 'panel',
                                        baseCls     : 'tabRadio',
                                        layout      : 'anchor',
                                        defaults    : {
                                            anchor: '100%'
                                        },
                                        autoScroll:true,
                                        items       :[
                                            {
											        xtype       : 'checkbox',      
											        fieldLabel  : 'Enable',
											        itemId      : 'chkRadio1Enable',
											        name        : 'radio1_enable',
											        inputValue  : 'radio1_enable',
											        checked     : true,
											        labelClsExtra: 'lblRdReq',
											        listeners   : {
											            change  : 'onChkRadioEnableChange'
											        }
								
										        },
										        {
											        xtype       : 'checkbox',      
											        fieldLabel  : 'Mesh',
											        itemId      : 'chkRadio1Mesh',
											        name        : 'radio1_mesh',
											        inputValue  : 'radio1_mesh',
											        checked     : true,
											        labelClsExtra: 'lblRd',
											        listeners   : {
											            change  : 'onChkRadioMeshChange'
											        }
								
										        },
										        {
											        xtype       : 'checkbox',      
											        fieldLabel  : 'Entry point',
											        itemId      : 'chkRadio1Entry',
											        name        : 'radio1_entry',
											        inputValue  : 'radio1_entry',
											        checked     : true,
											        labelClsExtra: 'lblRd'
										        },
										        {
											        xtype       : 'radio', 
											        fieldLabel  : '2.4G',
											        name      	: 'radio1_band',
											        inputValue	: '24',
											        itemId      : 'radio24',
											        labelClsExtra: 'lblRd',
											        listeners   : {
											            change  : 'onRadio_1_BandChange'
											        }
										        }, 
										        {
											        xtype       : 'radio',
											        fieldLabel  : '5G',
											        name      	: 'radio1_band',
											        inputValue	: '5',
											        itemId      : 'radio5',
											        //checked		: true,
											        labelClsExtra: 'lblRd',
											        listeners   : {
											            change  : 'onRadio_1_BandChange'
											        }
										        },
										        {
										            xtype       : 'numberfield',
										            anchor      : '100%',
										            name        : 'radio1_two_chan',
										            fieldLabel  : i18n('s2_pt_4G_Channel'),
										            value       : 5,
										            maxValue    : 14,
										            minValue    : 1,
											        hidden		: true,
											        disabled	: true,
											        itemId		: 'numRadioTwoChan'
										        },
                                                {
                                                    xtype       : 'cmbFiveGigChannels',
                                                    anchor      : '100%',
										            name        : 'radio1_five_chan',
										            fieldLabel  : i18n('s5G_Channel'),
											        hidden		: true,
											        disabled	: true,
											        itemId		: 'numRadioFiveChan'
                                                }
                                        ]
                                    }
                                ]}
                            ]
                        },
                        { 
                            'title'     : 'Advanced',
                            'layout'    : 'anchor',
                            glyph       : Rd.config.icnSpanner,
                            itemId      : 'tabAdvanced',
                            defaults    : {
                                anchor: '100%'
                            },
                            autoScroll:true,
                            items       : [ {
                                layout  : 'fit',
                                xtype   : 'tabpanel',
                                margins : '0 0 0 0',
                                plain   : true,
                                tabPosition: 'top',
                                border  : false,
                                cls     : 'subTab',
                                items   :  [
                                    {
                                        title       : 'Radio0',
                                        xtype       : 'panel',
                                        baseCls     : 'tabRadio',
                                        layout      : 'anchor',
                                        defaults    : {
                                            anchor: '100%'
                                        },
                                        autoScroll:true,
                                        items       :[
                                             {
                                                xtype       : 'radiogroup',
                                                fieldLabel  : 'HT-mode',
                                                columns     : 2,
                                                vertical    : false,
                                                items       : [
                                                    {
                                                        boxLabel  : 'HT20',
                                                        name      : 'radio0_htmode',
                                                        inputValue: 'HT20'
                                                    }, 
                                                    {
                                                        boxLabel  : 'HT40',
                                                        name      : 'radio0_htmode',
                                                        inputValue: 'HT40'
                                                    },
                                                    {
                                                        boxLabel  : 'VHT20',
                                                        name      : 'radio0_htmode',
                                                        itemId    : 'radio0_htmode_vht20',
                                                        inputValue: 'VHT20'
                                                    },
                                                    {
                                                        boxLabel  : 'VHT40',
                                                        name      : 'radio0_htmode',
                                                        itemId    : 'radio0_htmode_vht40',
                                                        inputValue: 'VHT40'
                                                    },
                                                    {
                                                        boxLabel  : 'VHT80',
                                                        name      : 'radio0_htmode',
                                                        itemId    : 'radio0_htmode_vht80',
                                                        inputValue: 'VHT80'
                                                    },
                                                    {
                                                        boxLabel  : 'VHT160',
                                                        name      : 'radio0_htmode',
                                                        itemId    : 'radio0_htmode_vht160',
                                                        inputValue: 'VHT160'
                                                    }
                                                ]
                                            },
                                            {
										        xtype       : 'checkbox',      
										        fieldLabel  : 'Disable 802.11b',
                                                boxLabel    : '* Recommended on 2.4G',
										        name        : 'radio0_disable_b',
										        inputValue  : 'radio0_disable_b',
										        labelClsExtra: 'lblRd'
									        }, 
                                            {
										        xtype       : 'checkbox',      
										        fieldLabel  : 'Diversity',
										        name        : 'radio0_diversity',
										        inputValue  : 'radio0_diversity',
										        labelClsExtra: 'lblRd'
									        }, 
                                            {
										        xtype       : 'checkbox',      
										        fieldLabel  : 'Noscan',
										        name        : 'radio0_noscan',
										        inputValue  : 'radio0_noscan',
										        labelClsExtra: 'lblRd'
									        },
                                            {
										        xtype       : 'checkbox',      
										        fieldLabel  : 'LDPC (Low Density Parity Check)',
										        name        : 'radio0_ldpc',
										        inputValue  : 'radio0_ldpc',
										        labelClsExtra: 'lblRd'
									        },
                                            {
									            xtype       : 'numberfield',
									            anchor      : '100%',
									            name        : 'radio0_txpower',
									            fieldLabel  : 'TX Power(dBm)',
									            value       : 15,
									            maxValue    : 35,
									            minValue    : 0
									        },
                                            {
									            xtype       : 'numberfield',
									            anchor      : '100%',
									            name        : 'radio0_beacon_int',
									            fieldLabel  : 'Beacon Interval',
									            value       : 100,
									            maxValue    : 65535,
									            minValue    : 15
									        },
                                            {
									            xtype       : 'numberfield',
									            anchor      : '100%',
									            name        : 'radio0_distance',
									            fieldLabel  : 'Distance',
									            value       : 300,
									            maxValue    : 3000,
									            minValue    : 1
									        },
                                            {
                                                xtype       : 'textareafield',
                                                grow        : true,
                                                fieldLabel  : 'HT Capabilities',
                                                name        : 'radio0_ht_capab',
                                                anchor      : '100%',
                                                allowBlank  : true,
                                                labelClsExtra: 'lblRd'
                                             }      
                                        ]
                                    },
                                    {
                                        title       : 'Radio1',
                                        xtype       : 'panel',
                                        baseCls     : 'tabRadio',
                                        layout      : 'anchor',
                                        itemId      : 'tabAdvWifiRadio1',
                                        defaults    : {
                                            anchor: '100%'
                                        },
                                        autoScroll:true,
                                        items       :[
                                            {
                                                xtype       : 'radiogroup',
                                                fieldLabel  : 'HT-mode',
                                                columns     : 2,
                                                vertical    : false,
                                                items       : [
                                                    {
                                                        boxLabel  : 'HT20',
                                                        name      : 'radio1_htmode',
                                                        inputValue: 'HT20'
                                                    }, 
                                                    {
                                                        boxLabel  : 'HT40',
                                                        name      : 'radio1_htmode',
                                                        inputValue: 'HT40'
                                                    },
                                                    {
                                                        boxLabel  : 'VHT20',
                                                        name      : 'radio1_htmode',
                                                        itemId    : 'radio1_htmode_vht20',
                                                        inputValue: 'VHT20'
                                                    },
                                                    {
                                                        boxLabel  : 'VHT40',
                                                        name      : 'radio1_htmode',
                                                        itemId    : 'radio1_htmode_vht40',
                                                        inputValue: 'VHT40'
                                                    },
                                                    {
                                                        boxLabel  : 'VHT80',
                                                        name      : 'radio1_htmode',
                                                        itemId    : 'radio1_htmode_vht80',
                                                        inputValue: 'VHT80'
                                                    },
                                                    {
                                                        boxLabel  : 'VHT160',
                                                        name      : 'radio1_htmode',
                                                        itemId    : 'radio1_htmode_vht160',
                                                        inputValue: 'VHT160'
                                                    }
                                                ]
                                            },
                                            {
										        xtype       : 'checkbox',      
										        fieldLabel  : 'Disable 802.11b',
                                                boxLabel    : '* Recommended on 2.4G',
										        name        : 'radio1_disable_b',
										        inputValue  : 'radio1_disable_b',
										        labelClsExtra: 'lblRd'
									        }, 
                                            {
										        xtype       : 'checkbox',      
										        fieldLabel  : 'Diversity',
										        name        : 'radio1_diversity',
										        inputValue  : 'radio1_diversity',
										        labelClsExtra: 'lblRd'
									        }, 
                                            {
										        xtype       : 'checkbox',      
										        fieldLabel  : 'Noscan',
										        name        : 'radio1_noscan',
										        inputValue  : 'radio1_noscan',
										        labelClsExtra: 'lblRd'
									        },
                                            {
										        xtype       : 'checkbox',      
										        fieldLabel  : 'LDPC (Low Density Parity Check)',
										        name        : 'radio1_ldpc',
										        inputValue  : 'radio1_ldpc',
										        labelClsExtra: 'lblRd'
									        },
                                            {
									            xtype       : 'numberfield',
									            anchor      : '100%',
									            name        : 'radio1_txpower',
									            fieldLabel  : 'TX Power(dBm)',
									            value       : 15,
									            maxValue    : 35,
									            minValue    : 0
									        },
                                            {
									            xtype       : 'numberfield',
									            anchor      : '100%',
									            name        : 'radio1_beacon_int',
									            fieldLabel  : 'Beacon Interval',
									            value       : 100,
									            maxValue    : 65535,
									            minValue    : 15
									        },
                                            {
									            xtype       : 'numberfield',
									            anchor      : '100%',
									            name        : 'radio1_distance',
									            fieldLabel  : 'Distance',
									            value       : 300,
									            maxValue    : 3000,
									            minValue    : 1
									        },
                                            {
                                                xtype       : 'textareafield',
                                                grow        : true,
                                                fieldLabel  : 'HT Capabilities',
                                                name        : 'radio1_ht_capab',
                                                anchor      : '100%',
                                                allowBlank  : true,
                                                labelClsExtra: 'lblRd'
                                             }      
                                        ]
                                    },
                                    {
                                        title       : 'LEDs',
                                        xtype       : 'panel',
                                        baseCls     : 'tabRadio',
                                        layout      : 'anchor',
                                        defaults    : {
                                            anchor: '100%'
                                        },
                                        autoScroll:true,
                                        items       :[
                                            {
                                                xtype       : 'displayfield',
                                                fieldLabel  : '(Future dev)'
                                            },
                                            {
										        xtype       : 'checkbox',      
										        fieldLabel  : 'Neighbor Count',
										        name        : 'led_neighbor',
										        inputValue  : 'led_neighbor',
										        labelClsExtra: 'lblRd',
										        disabled     : true
									        }, 
                                            {
										        xtype       : 'checkbox',      
										        fieldLabel  : 'Mesh Traffic',
										        name        : 'led_mesh',
										        inputValue  : 'led_mesh',
										        labelClsExtra: 'lblRd',
										        disabled     : true
									        },
                                            {
										        xtype       : 'checkbox',      
										        fieldLabel  : 'Internet On/Off',
										        name        : 'led_internet',
										        inputValue  : 'led_internet',
										        labelClsExtra: 'lblRd',
										        disabled     : true
									        }    
                                        ]
                                    }
                                ]}
                            ]
                        } 
                    ]
                }                      
            ]
        });

        me.items = frmData;
        me.callParent(arguments);
    }
});
