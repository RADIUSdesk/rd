Ext.define('Rd.view.meshes.pnlMeshEdit', {
   extend       : 'Ext.tab.Panel',
    alias       : 'widget.pnlMeshEdit',
    border      : true,
    meshId      : undefined,
    meshName    : undefined,
    plain       : true,
    tabPosition : 'top',
    cls         : 'subTab',
    initComponent: function() {
        var me      = this;
        
        console.log("Mesh ID is "+me.meshId);   
        me.items    = [
            {
                title   : i18n("sEntry_points"),
                itemId  : 'tabEntryPoints',
                xtype   : 'gridMeshEntries',
                meshId  : me.meshId
            },
            {
                title   :  i18n("sMesh_settings"),
                itemId  : 'tabMeshSettings',
                xtype   : 'pnlMeshSettings',
                meshId  : me.meshId
            },
            {
                title   :  i18n("sExit_points"),
                itemId  : 'tabExitPoints',
                xtype   : 'gridMeshExits',
                meshId  : me.meshId
            },
            {
                title   : i18n("sNode_settings"),
                itemId  : 'tabNodeCommonSettings',
                xtype   : 'pnlNodeCommonSettings',
                meshId  : me.meshId 
            },
            {
                title   : i18n("sNodes"),
                itemId  : 'tabNodes',
                xtype   : 'gridNodes',
                meshId  : me.meshId    
            }
        ];
        me.callParent(arguments);
    }
});
