Ext.define('Rd.view.desktop.pnlDesktop', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.pnlDesktop',
    requires:   [
        'Rd.view.components.compWallpaper'
    ],
    border: false,
    html: '&#160;',
    layout: 'fit',   
    shortcutItemSelector: 'div.ux-desktop-shortcut',
    shortcuts: null,
    shortcutTpl: [
        '<tpl for=".">',
            '<div class="ux-desktop-shortcut" id="{name}-shortcut">',
                '<div class="ux-desktop-shortcut-icon {iconCls}">',
                    '<img src="',Ext.BLANK_IMAGE_URL,'" title="{name}">',
                '</div>',
                '<span class="ux-desktop-shortcut-text">{name}</span>',
            '</div>',
        '</tpl>',
        '<div class="x-clear"></div>'
    ],
    initComponent: function () {
        var me = this;
        me.items    = [{'xtype' : 'compWallpaper','url' : me.url},me.createDataView()];
        this.callParent();
    },
    createDataView: function () {
        var me = this;
        return {
            xtype: 'dataview',
            overItemCls: 'x-view-over',
            trackOver: true,
            itemSelector: me.shortcutItemSelector,
            store: 'sDesktopShortcuts',
            style: {
                position: 'absolute'
            },
            x: 0, y: 0,
            tpl: new Ext.XTemplate(me.shortcutTpl)
        };
    }  
});
