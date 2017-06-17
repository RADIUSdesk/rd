Ext.define('Rd.view.meshes.winMeshView', {
    extend      : 'Ext.window.Window',
    alias       : 'widget.winMeshView',
    width           : Rd.config.winWidth,
    height          : Rd.config.winHeight,
    iconCls     : 'mesh',
    glyph       : Rd.config.icnView,
    animCollapse: false,
    border      : false,
    isWindow    : true,
    minimizable : true,
    maximizable : true,
    constrainHeader:true,
    layout      : 'border',
    stateful    : true,
    autoShow    : false,
    meshId      : '',
    initComponent: function() {
        var me      = this; 
        me.items    = [
            {
                region: 'north',
                xtype:  'pnlBanner',
                heading: me.title,
                image:  'resources/images/48x48/mesh_view.png'
            },
            {
                region  : 'center',
                xtype   : 'panel',
                layout  : 'fit',
                border  : false,
                items   : [
                    {
                        xtype   : 'tabpanel',
                        layout  : 'fit',
                        margins : '0 0 0 0',
                        border  : false,
                        plain   : true,
                        cls     : 'subTab',
                        items   : [
								{
                                    title   : 'Overview',
                                    itemId  : 'tabMeshViewOverwiew',
									xtype	: 'pnlMeshViewNodes',
                                    meshId  : me.meshId
                                },
                                {
                                    title   : 'SSID &#8660; Device',
                                    itemId  : 'tabMeshViewEntries',
                                    xtype   : 'gridMeshViewEntries',
                                    meshId  : me.meshId
                                },
                                {
                                    title   : 'Node &#8660; Device',
                                    itemId  : 'tabMeshViewNodes',
                                    xtype   : 'gridMeshViewNodes',
                                    meshId  : me.meshId
                                },
								{
                                    title   : 'Node &#8660; Nodes',
                                    itemId  : 'tabMeshViewNodeNodes',
									xtype   : 'gridMeshViewNodeNodes',
                                    meshId  : me.meshId
                                },
								{
                                    title   : 'Nodes',
                                    itemId  : 'tabMeshViewNodeDetails',
									xtype   : 'gridMeshViewNodeDetails',
                                    meshId  : me.meshId
                                }
                            ]
                        }
                    ]
            }
        ];
        me.callParent(arguments);
    }
});
