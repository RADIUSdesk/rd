Ext.define('Rd.view.meshes.pnlMeshView', {
    extend      : 'Ext.tab.Panel',
    alias       : 'widget.pnlMeshView',
    meshId      : undefined,
    meshName    : undefined,
    plain       : true,
    tabPosition : 'top',
    cls         : 'subTab',
    initComponent: function() {
        var me      = this;     
        me.items    = [
		    {
                title   : i18n("sOverview"),
                itemId  : 'tabMeshViewOverwiew',
			    xtype	: 'pnlMeshViewNodes',
                meshId  : me.mesh_id
            },
            {
                title   : 'SSID &#8660; Device',
                itemId  : 'tabMeshViewEntries',
                xtype   : 'gridMeshViewEntries',
                meshId  : me.mesh_id
            },
            {
                title   : 'Node &#8660; Device',
                itemId  : 'tabMeshViewNodes',
                xtype   : 'gridMeshViewNodes',
                meshId  : me.mesh_id
            },
		    {
                title   : 'Node &#8660; Nodes',
                itemId  : 'tabMeshViewNodeNodes',
			    xtype   : 'gridMeshViewNodeNodes',
                meshId  : me.mesh_id
            },
		    {
                title   : i18n("sNodes"),
                itemId  : 'tabMeshViewNodeDetails',
			    xtype   : 'gridMeshViewNodeDetails',
                meshId  : me.mesh_id
            }
        ];
        me.callParent(arguments);
    }
});
