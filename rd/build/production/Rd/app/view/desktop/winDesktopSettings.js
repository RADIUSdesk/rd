Ext.define('Rd.view.desktop.winDesktopSettings', {
    extend  : 'Ext.window.Window',
    alias   : 'widget.winDesktopSettings',
    title   : i18n('sSettings'),
    layout  : 'fit',
    autoShow: false,
    width   : 350,
    height  : 350,
    glyph   : Rd.config.icnSpanner,
    initComponent: function() {
        var me = this;

        //Create the view for the wallpapers:
        var imageTpl = new Ext.XTemplate(
            '<tpl for=".">',
                '<div class="thumb-wrap">',
                    '<img src="{img}" />',
                    '<br/><span>{file}</span>',
                '</div>',
            '</tpl>'
        );

        var v = Ext.create('Ext.view.View', {
            store: 'sWallpapers',
            tpl: imageTpl,
            itemSelector: 'div.thumb-wrap',
            emptyText: 'No images available'
        });

        me.items = [
            {
                xtype   : 'tabpanel',
                layout  : 'fit',
                xtype   : 'tabpanel',
                margins : '0 0 0 0',
                plain   : true,
                tabPosition: 'top',
                border  : false,
                items   : [
                    { 
                        'title'     : i18n('sWallpaper'),
                        itemId      : 'tabWallpaper',
                        autoScroll  : true,
                        items       :  v
                    }   
                ]
            }
        ];
        this.callParent(arguments);
    }
});
