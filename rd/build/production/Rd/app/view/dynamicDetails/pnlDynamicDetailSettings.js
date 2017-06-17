Ext.define('Rd.view.dynamicDetails.pnlDynamicDetailSettings', {
    extend  : 'Ext.panel.Panel',
    alias   : 'widget.pnlDynamicDetailSettings',
    border  : false,
    dynamic_detail_id: null,
    layout  : 'hbox',
    bodyStyle: {backgroundColor : Rd.config.panelGrey },
    initComponent: function(){
        var me = this;

        me.items =  { 
                xtype   :  'form',
                height  : '100%', 
                width   :  450,
                layout  : 'fit',
                autoScroll:true,
                frame   : true,
                fieldDefaults: {
                    msgTarget       : 'under',
                    labelClsExtra   : 'lblRd',
                    labelAlign      : 'left',
                    labelSeparator  : '',
                    margin          : Rd.config.fieldMargin,
                    labelWidth      : Rd.config.labelWidth,
                    maxWidth        : Rd.config.maxWidth  
                },
                items       : [
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
                                'title'     : 'Theme',
                                'layout'    : 'anchor',
                                itemId      : 'tabTheme',
                                defaults    : {
                                    anchor: '100%'
                                },
                                autoScroll:true,
                                items       : [
									{
								        xtype       : 'textfield',
								        name        : "id",
								        hidden      : true
								    },
									{ 
										xtype       : 'cmbThemes', 
										labelClsExtra : 'lblRdReq',
										allowBlank  : false 
									},
									{
								        xtype       : 'checkbox',      
								        fieldLabel  : 'Slideshow',
								        itemId      : 'chkSlideshow',
								        name        : 'slideshow_check',
								        inputValue  : 'slideshow_check',
								        checked     : false,
								        labelClsExtra: 'lblRdReq'
								    },
								    {
								        xtype       : 'numberfield',
								        name        : 'seconds_per_slide',
								        fieldLabel  : 'Seconds per slide',
								        itemId      : 'nrSecondsPerSlide',
								        value       : 30,
								        maxValue    : 300,
								        minValue    : 10,
								        disabled    : true
								    }
                                ]
                            },
                            { 
                                'title'     : 'Login',
                                'layout'    : 'anchor',
                                itemId      : 'tabUsersVouchers',
                                defaults    : {
                                    anchor: '100%'
                                },
                                autoScroll:true,
                                items       : [  
									{
								        xtype       : 'checkbox',      
								        fieldLabel  : 'User login',
								        itemId      : 'chkUserLogin',
								        name        : 'user_login_check',
								        inputValue  : 'user_login_check',
								        checked     : true,
								        labelClsExtra: 'lblRdReq'
								    },
									{
								        xtype       : 'checkbox',      
								        fieldLabel  : 'Auto-add suffix',
								        itemId      : 'chkAutoSuffix',
								        name        : 'auto_suffix_check',
								        inputValue  : 'auto_suffix_check',
								        checked     : true,
								        labelClsExtra: 'lblRd'
								    },
									{
								        xtype       : 'textfield',
								        fieldLabel  : 'Suffix',
								        itemId      : 'txtSuffix',
								        name        : 'auto_suffix',
								        disabled    : true
								    },
									{
								        xtype       : 'checkbox',      
								        fieldLabel  : 'User registration',
								        itemId      : 'chkRegisterUsers',
								        name        : 'register_users',
								        inputValue  : 'register_users',
								        labelClsExtra: 'lblRd'
								    },
									{
								        xtype       : 'checkbox',      
								        fieldLabel  : 'Lost password',
								        itemId      : 'chkLostPassword',
								        name        : 'lost_password',
								        inputValue  : 'lost_password',
								        labelClsExtra: 'lblRd'
								    },
									{
								        xtype       : 'checkbox',      
								        fieldLabel  : 'Voucher login',
								        itemId      : 'chkVoucherLogin',
								        name        : 'voucher_login_check',
								        inputValue  : 'voucher_login_check',
								        checked     : true,
								        labelClsExtra: 'lblRdReq'
								    }          
                                ]
                            },
                            { 
                                'title'     : 'T&Cs',
                                'layout'    : 'anchor',
                                itemId      : 'tabTandC',
                                defaults    : {
                                    anchor: '100%'
                                },
                                autoScroll:true,
                                items       : [
									{
								        xtype       : 'checkbox',      
								        fieldLabel  : 'Agree to T&C',
								        itemId      : 'chkTc',
								        name        : 't_c_check',
								        inputValue  : 't_c_check',
								        checked     : false,
								        labelClsExtra: 'lblRdReq'
								    },
								    {
								        xtype       : 'textfield',
								        fieldLabel  : 'T&C URL',
								        itemId      : 'txtTcUrl',
								        name        : "t_c_url",
								        disabled    : true,
								        allowBlank  : false,
								        vtype       : 'url'
								    }              
                                ]
                            },
							{ 
                                'title'     : 'Redirect',
                                'layout'    : 'anchor',
                                itemId      : 'tabRedirect',
                                defaults    : {
                                    anchor: '100%'
                                },
                                autoScroll:true,
                                items       : [
									 {
								        xtype       : 'checkbox',      
								        fieldLabel  : 'Redirect after connect',
								        itemId      : 'chkRedirect',
								        name        : 'redirect_check',
								        inputValue  : 'redirect_check',
								        checked     : false,
								        labelClsExtra: 'lblRdReq'
								    },
								    {
								        xtype       : 'textfield',
								        fieldLabel  : 'Redirect to URL',
								        itemId      : 'txtRedirectUrl',
								        name        : "redirect_url",
								        disabled    : true,
								        allowBlank  : false,
								        vtype       : 'url'
								    },
									{
								        xtype       : 'checkbox',      
								        fieldLabel  : 'Show usage',
								        itemId      : 'chkUsage',
								        name        : 'usage_show_check',
								        inputValue  : 'usage_show_check',
								        checked     : false,
								        labelClsExtra: 'lblRdReq'
								    },
								    {
								        xtype       : 'numberfield',
								        name        : 'usage_refresh_interval',
								        fieldLabel  : 'Refresh every (seconds)',
								        itemId      : 'nrUsageRefresh',
								        value       : 120,
								        maxValue    : 600,
								        minValue    : 60,
								        disabled    : true
								    }	      
                                ]
                            }
                        ]
                    }
                ],
                buttons: [
                    {
                        itemId		: 'save',
                        formBind	: true,
                        text		: i18n('sSave'),
                        scale		: 'large',
                        iconCls		: 'b-save',
                        glyph		: Rd.config.icnYes,
                        margin		: Rd.config.buttonMargin
                    }
                ]
            };

        me.callParent(arguments);
    }
});
