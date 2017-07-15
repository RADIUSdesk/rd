Ext.define('Rd.view.aps.winAccessPointEditAp', {
    extend      : 'Ext.window.Window',
    alias       : 'widget.winAccessPointEditAp',
    closable    : true,
    draggable   : true,
    resizable   : true,
    title       : 'Edit Device',
    width       : 450,
    height      : 450,
    plain       : true,
    border      : false,
    layout      : 'fit',
    glyph       : Rd.config.icnEdit,
    autoShow    : false,
    apProfileId : '',
	apProfileName	: '',
    defaults    : {
            border: false
    },
    requires: [
        'Ext.tab.Panel',
        'Ext.form.Panel',
        'Ext.form.field.Text',
		'Rd.view.components.cmbApProfile',
		'Rd.view.aps.cmbApHardwareModels',
        'Rd.view.components.cmbFiveGigChannels' 
    ],
     initComponent: function() {
        var me 	= this;
 
		var cmb = Ext.create('Rd.view.components.cmbApProfile',{'itemId' : 'ap_profile_id', labelClsExtra: 'lblRdReq'});
		cmb.getStore().loadData([],false); //Wipe it
		cmb.getStore().loadData([{'id' : me.apProfileId, 'name' : me.apProfileName}],true);//Add it
		cmb.setValue(me.apProfileId);//Show it

        var frmData = Ext.create('Ext.form.Panel',{
            border:     false,
            layout:     'fit',
            itemId:     'scrnData',
            autoScroll: true,
            fieldDefaults: {
                msgTarget       : 'under',
                labelClsExtra   : 'lblRd',
                labelAlign      : 'left',
                labelSeparator  : '',
                labelWidth      : Rd.config.labelWidth,
                maxWidth        : Rd.config.maxWidth, 
                margin          : Rd.config.fieldMargin
            },
            defaultType: 'textfield',
            buttons : [
                {
                    itemId  : 'save',
                    text    : i18n("sOK"),
                    scale   : 'large',
                    formBind: true,
                    glyph   : Rd.config.icnYes,
                    margin  : Rd.config.buttonMargin
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
                            title     : 'Basic',
                            glyph     : Rd.config.icnStar,
                            layout    : 'anchor',
                            itemId    : 'tabRequired',
                            defaults    : {
                                anchor: '100%'
                            },
                            autoScroll:true,
                            items       : [
                                {
						            itemId  : 'ap_id',
						            xtype   : 'textfield',
						            name    : "id",
						            hidden  : true,
						            value   : me.apId
						        },
								cmb,
						        {
						            xtype       : 'textfield',
						            fieldLabel  : i18n("sMAC_address"),
						            name        : "mac",
						            allowBlank  : false,
						            blankText   : i18n("sSupply_a_value"),
						            labelClsExtra: 'lblRdReq',
						            vtype       : 'MacAddress',
						            fieldStyle  : 'text-transform:lowercase'
						           // value       : 'A8-40-41-13-60-E3'
						        },
						        {
						            xtype       : 'textfield',
						            fieldLabel  : i18n("sName"),
						            name        : "name",
						            allowBlank  : false,
						            blankText   : i18n("sSupply_a_value"),
						            labelClsExtra: 'lblRdReq'
						        },
						        {
						            xtype       : 'textfield',
						            fieldLabel  : i18n("sDescription"),
						            name        : "description",
						            allowBlank  : true,
						            labelClsExtra: 'lblRd'
						        },
						        {
						            xtype           : 'cmbApHardwareModels',
						            labelClsExtra   : 'lblRdReq',
						            allowBlank      : false 
						        }  
                            ]
                        },
						{
							title       : 'Radios',
							glyph       : Rd.config.icnWifi,
							layout      : 'fit',
                            itemId      : 'tabRadio',
                            autoScroll	:true,
                            items       : [ {
                                layout  : 'fit',
                                xtype   : 'tabpanel',
                                margins : '0 0 0 0',
                                plain   : true,
                                cls     : 'subTab',
                                tabPosition: 'top',
                                border  : false,
                                items   :  [
                                    {
                                        title       : i18n("sRadio_zero"),
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
											        fieldLabel  : i18n("sEnable"),
											        itemId      : 'chkRadio0Enable',
											        name        : 'radio0_enable',
											        inputValue  : 'radio0_enable',
											        checked     : true,
											        labelClsExtra: 'lblRdReq'
								
										        },
										        {
                                                    xtype      : 'fieldcontainer',
                                                    fieldLabel : 'Band',
                                                    defaultType: 'radiofield',
                                                    labelClsExtra: 'lblRd',
                                                    layout      : 'hbox',
                                                    items: [
                                                        {
                                                            boxLabel  : i18n("sTwo_point_four_gig"),
                                                            name      : 'radio0_band',
                                                            inputValue: '24',
                                                            itemId    : 'radio24',
                                                            margin    : Rd.config.radioMargin
                                                        }, 
                                                        {
                                                            boxLabel  : i18n("sFive_gig"),
                                                            name      : 'radio0_band',
                                                            inputValue: '5',
                                                            itemId    : 'radio5',
                                                            margin    : Rd.config.radioMargin
                                                        }
                                                    ]
                                                },
										        {
										            xtype       : 'numberfield',
										            anchor      : '100%',
										            name        : 'radio0_channel_two',
										            fieldLabel  : i18n("s2_pt_4G_Channel"),
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
										            name        : 'radio0_channel_five',
										            fieldLabel  : i18n("s5G_Channel"),
											        hidden		: true,
											        disabled	: true,
											        itemId		: 'numRadioFiveChan'
                                                }   	         
                                        ]
                                    },
                                    {
                                        title       : i18n("sRadio_one"),
                                        xtype       : 'panel',
                                        baseCls     : 'tabRadio',
                                        layout      : 'anchor',
                                        hidden      : 'true',
                                        itemId      : 'tabRadiosRadio1',
                                        defaults    : {
                                            anchor: '100%'
                                        },
                                        autoScroll:true,
                                        items       :[
                                            {
											        xtype       : 'checkbox',      
											        fieldLabel  : i18n("sEnable"),
											        itemId      : 'chkRadio1Enable',
											        name        : 'radio1_enable',
											        inputValue  : 'radio1_enable',
											        checked     : true,
											        labelClsExtra: 'lblRdReq'
								
										        },
										        {
                                                    xtype      : 'fieldcontainer',
                                                    fieldLabel : 'Band',
                                                    defaultType: 'radiofield',
                                                    labelClsExtra: 'lblRd',
                                                    layout: {
                                                        type: 'hbox',
                                                        align: 'begin',
                                                        pack: 'start'
                                                    },
                                                    items: [
                                                        {
                                                            boxLabel  : i18n("sTwo_point_four_gig"),
                                                            name      : 'radio1_band',
                                                            inputValue: '24',
                                                            itemId    : 'radio24',
                                                            margin    : Rd.config.radioMargin
                                                        }, 
                                                        {
                                                            boxLabel  : i18n("sFive_gig"),
                                                            name      : 'radio1_band',
                                                            inputValue: '5',
                                                            itemId    : 'radio5',
                                                            margin    : Rd.config.radioMargin
                                                        }
                                                    ]
                                                },
										        {
										            xtype       : 'numberfield',
										            anchor      : '100%',
										            name        : 'radio1_channel_two',
										            fieldLabel  : i18n("s2_pt_4G_Channel"),
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
										            name        : 'radio1_channel_five',
										            fieldLabel  : i18n("s5G_Channel"),
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
                                cls     : 'subTab',
                                tabPosition: 'top',
                                border  : false,
                                items   :  [
                                    {
                                        title       : i18n("sRadio_zero"),
                                        xtype       : 'panel',
                                        baseCls     : 'tabRadio',
                                        layout      : 'anchor',
                                        defaults    : {
                                            anchor: '100%'
                                        },
                                        autoScroll:true,
                                        items       :[
                                            {
                                                xtype      : 'fieldcontainer',
                                                fieldLabel : 'HT-mode',
                                                defaultType: 'radiofield',
                                                labelClsExtra: 'lblRd',
                                                layout: {
                                                    type: 'hbox',
                                                    align: 'begin',
                                                    pack: 'start'
                                                },
                                                items: [
                                                    {
                                                        boxLabel  : 'HT20',
                                                        name      : 'radio0_htmode',
                                                        inputValue: 'HT20',
                                                        checked   : true,
                                                        margin    : Rd.config.radioMargin
                                                    }, 
                                                    {
                                                        boxLabel  : 'HT40',
                                                        name      : 'radio0_htmode',
                                                        inputValue: 'HT40',
                                                        margin    : Rd.config.radioMargin
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
                                        title       : i18n("sRadio_one"),
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
                                                xtype      : 'fieldcontainer',
                                                fieldLabel : 'HT-mode',
                                                defaultType: 'radiofield',
                                                labelClsExtra: 'lblRd',
                                                layout: {
                                                    type: 'hbox',
                                                    align: 'begin',
                                                    pack: 'start'
                                                },
                                                items: [
                                                    {
                                                        boxLabel  : 'HT20',
                                                        name      : 'radio1_htmode',
                                                        inputValue: 'HT20',
                                                        checked   : true,
                                                        margin    : Rd.config.radioMargin
                                                    }, 
                                                    {
                                                        boxLabel  : 'HT40',
                                                        name      : 'radio1_htmode',
                                                        inputValue: 'HT40',
                                                        margin    : Rd.config.radioMargin
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
