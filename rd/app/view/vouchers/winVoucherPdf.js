Ext.define('Rd.view.vouchers.winVoucherPdf', {
    extend: 'Ext.window.Window',
    alias : 'widget.winVoucherPdf',
    title : i18n('sGenerate_pdf'),
    layout: 'fit',
    autoShow: false,
    width:    450,
    height:   350,
    iconCls: 'pdf',
    glyph   : Rd.config.icnPdf,
    requires: [
        'Rd.view.vouchers.cmbPdfFormats',
        'Rd.view.components.vLanguagesCmb',
        'Rd.store.sPdfFormats',
        'Rd.model.mPdfFormat'
    ],
    initComponent: function() {
        var me 			= this;
		var orientation = Ext.create('Ext.data.Store', {
			fields: ['id', 'name'],
			data : [
				{"id":"P", "name":"Portrait"},
				{"id":"L", "name":"Landscape"}
			]
		});


		var basic_controlls = [
				{ 
					xtype           : 'cmbPdfFormats', 
					name            : 'format',
					labelClsExtra   : 'lblRdReq',
					allowBlank      : false 
				},
				{ 
					xtype           : 'cmbLanguages', 
					fieldLabel      : i18n('sLanguage'),  
					name            : 'language',
					allowBlank      : false,
					labelClsExtra   : 'lblRdReq',
					allowBlank      : false
				}  
		    ];

		 if(me.selecteds == true){
            Ext.Array.push(basic_controlls, {
                xtype           : 'checkbox',      
                fieldLabel      : i18n('sOnly_selected'),
                name            : 'selected_only',
                inputValue      : 'selected_only',
				itemId			: 'selected_only',
                checked         : true,
                labelClsExtra   : 'lblRd'
            });
        }

		var controlls = [{
                    xtype   : 'tabpanel',
                    layout  : 'fit',
                    xtype   : 'tabpanel',
                    margins : '0 0 0 0',
                    plain   : true,
                    tabPosition: 'bottom',
                    border  : false,
                    cls     : 'subTab',
                    items   : [
                        { 
                            title     	: 'Basic',
                            itemId      : 'tabBasic',
							layout		: 'anchor',
							defaults: {
						        anchor: '100%'
						    },
                            autoScroll	: true,
                            items       : basic_controlls
                        },
						{
							title       : 'Advanced',
                            itemId      : 'tabAdvanced',
							layout		: 'anchor',
							defaults: {
						        anchor: '100%'
						    },
                            autoScroll	:true,
                            items       : [
								{
									xtype			: 'combobox',
									fieldLabel		: 'Orientation',
									store			: orientation,
									queryMode		: 'local',
									displayField	: 'name',
									valueField		: 'id',
									name			: 'orientation'
								},
								{
									xtype           : 'checkbox',      
									fieldLabel      : 'Include QR code',
									name            : 'q_r',
									inputValue      : 'q_r',
									checked         : true,
									labelClsExtra   : 'lblRd'
								},
								{
									xtype           : 'checkbox',      
									fieldLabel      : 'Include date',
									name            : 'date',
									inputValue      : 'date',
									checked         : true,
									labelClsExtra   : 'lblRd'
								},
								{
									xtype           : 'checkbox',      
									fieldLabel      : 'Include T&C',
									name            : 't_and_c',
									inputValue      : 't_and_c',
									checked         : true,
									labelClsExtra   : 'lblRd'
								},
								{
									xtype           : 'checkbox',      
									fieldLabel      : 'Social media links',
									name            : 'social_media',
									inputValue      : 'social_media',
									checked         : true,
									labelClsExtra   : 'lblRd'
								},
								{
									xtype           : 'checkbox',      
									fieldLabel      : 'Realm detail',
									name            : 'realm_detail',
									inputValue      : 'realm_detail',
									checked         : true,
									labelClsExtra   : 'lblRd'
								},
								{
									xtype           : 'checkbox',      
									fieldLabel      : 'Profile detail',
									name            : 'profile_detail',
									inputValue      : 'profile_detail',
									checked         : true,
									labelClsExtra   : 'lblRd'
								}
                            ]
                        }
					]
			}];

        me.items = [
            {
                xtype		: 'form',
                border		: false,
                layout		: 'fit',
                autoScroll	: true,
                fieldDefaults: {
                    msgTarget		: 'under',
                    labelClsExtra	: 'lblRd',
                    labelAlign		: 'left',
                    labelSeparator	: '',
                    margin          : 15,
                    labelWidth      : 170
                },
                defaultType: 'textfield',
                items: controlls,
                buttons: [
                    {
                        itemId		: 'save',
                        text		: i18n('sOK'),
                        formBind	: true,
                        scale		: 'large',
                        iconCls		: 'b-next',
                        glyph   	: Rd.config.icnYes,
                        formBind	: true,
                        margin		: '0 10 20 0'
                    }
                ]
            }
        ];
        this.callParent(arguments);
    }
});
