Ext.define('Rd.view.login.pnlAboutMenu', {
    extend      : 'Ext.panel.Panel',
    alias       : 'widget.pnlAboutMenu',
    layout      : 'fit',
    width       : 300,
    glyph       : 'xf129@FontAwesome',
    initComponent: function() {

        var me      = this;// menu = me.menu;
        me.title    = i18n('sAbout_RADIUSdesk');
        me.html     =   "<div class='thumb-wrap'>"+
                        "<h1>"+i18n("sA_Modern_Webtop_front-end_to_FreeRADIUS")+"</h1>"+
                        "<div class='description'>"+
                            "<a href='http://sourceforge.net/projects/radiusdesk/' target='_blank'>http://sourceforge.net/projects/radiusdesk/</a>"
                        "</div>"+
                    "</div>";

        me.callParent();
    }
}); 

