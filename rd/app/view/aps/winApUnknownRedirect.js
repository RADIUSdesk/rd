Ext.define('Rd.view.aps.winApUnknownRedirect', {
    extend		: 'Ext.window.Window',
    alias 		: 'widget.winApUnknownRedirect',
    title 		: 'Redirect To Another Server',
    layout		: 'fit',
    autoShow	: false,
    width		: 350,
    height		: 250,
    glyph		: Rd.config.icnRedirect,
	unknownApId : '',
    new_server  : '',
    initComponent: function() {
        var me  = this;
        me.items = [
            {
                xtype: 'form',
                border:     false,
                layout:     'anchor',
                autoScroll: true,
                defaults: {
                    anchor: '100%'
                },
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
                items: [
                     {
			            itemId  	: 'unknown_node_id',
			            xtype   	: 'textfield',
			            name    	: "id",
			            hidden  	: true,
			            value   	: me.unknownApId
			        },
                    {
			            xtype       : 'textfield',
			            fieldLabel  : 'IP Address / FQDN',
			            name        : "new_server",
			            allowBlank  : false,
			            labelClsExtra: 'lblRdReq',
                        value       : me.new_server
			        }  
                ],
                buttons: [
                    
                    {
                        itemId  : 'save',
                        text    : i18n("sOK"),
                        scale   : 'large',
                        formBind: true,
                        glyph   : Rd.config.icnYes,
                        margin  : Rd.config.buttonMargin
                    }
                ]
            }
        ];
        this.callParent(arguments);
    }
});
