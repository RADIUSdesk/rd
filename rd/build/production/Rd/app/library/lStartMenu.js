/**
 * Ext JS Library
 * Copyright(c) 2006-2014 Sencha Inc.
 * licensing@sencha.com
 * http://www.sencha.com/license
 * @class Ext.ux.desktop.StartMenu
 */
Ext.define('Rd.library.lStartMenu', {
    extend      : 'Ext.menu.Menu',
    baseCls     : Ext.baseCSSPrefix + 'panel',
    cls         : 'x-menu ux-start-menu',
    bodyCls     : 'ux-start-menu-body',
    defaultAlign: 'bl-tl',
    iconCls     : 'user',
    bodyBorder  : true,
    
    alias       : 'widget.pnlStartMenu',
    
    width       : 320,

    initComponent: function() {
        var me = this;
        me.layout.align = 'stretch';
        me.items = me.menu;
        me.callParent();

        me.toolbar = new Ext.toolbar.Toolbar(Ext.apply({
            dock: 'right',
            cls: 'ux-start-menu-toolbar',
            vertical: true,
            width: 100,
            layout: {
                align: 'stretch'
            }
        }, me.toolConfig));
        me.addDocked(me.toolbar);
        delete me.toolItems;
    },

    addMenuItem: function() {
        var cmp = this.menu;
        cmp.add.apply(cmp, arguments);
    },

    addToolItem: function() {
        var cmp = this.toolbar;
        cmp.add.apply(cmp, arguments);
    }
}); // StartMenu
