Ext.define('Rd.view.meshes.winMeshUnknownRedirect', {
    extend		: 'Ext.window.Window',
    alias 		: 'widget.winMeshUnknownRedirect',
    title 		: 'Redirect To Another Server',
    layout		: 'fit',
    autoShow	: false,
    width		: 350,
    height		: 250,
    glyph		: Rd.config.icnRedirect,
	unknownNodeId   : '',
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
                    margin          : 15
                },
                defaultType: 'textfield',
                items: [
                     {
			            itemId  	: 'unknown_node_id',
			            xtype   	: 'textfield',
			            name    	: "id",
			            hidden  	: true,
			            value   	: me.unknownNodeId
			        },
                    {
			            xtype       : 'textfield',
			            fieldLabel  : 'IP Address / FQDN',
			            name        : "new_server",
			            allowBlank  : false,
			            blankText   : i18n('sSupply_a_value'),
			            labelClsExtra: 'lblRdReq',
                        value       : me.new_server
			        }  
                ],
                buttons: [
                    
                    {
                        itemId: 'save',
                        text: i18n('sOK'),
                        scale: 'large',
                        iconCls: 'b-save',
                        glyph: Rd.config.icnYes,
                        formBind: true,
                        margin: '0 20 40 0'
                    }
                ]
            }
        ];
        this.callParent(arguments);
    }
});
