Ext.define('Rd.view.dynamicDetails.pnlDynamicDetailPhoto', {
    extend  : 'Ext.panel.Panel',
    alias   : 'widget.pnlDynamicDetailPhoto',
    border  : false,
    frame   : false,
    layout  : 'hbox',
    store   : undefined,
    dynamic_detail_id: null,
    bodyStyle: {backgroundColor : Rd.config.panelGrey },
    requires: [
        'Rd.view.components.ajaxToolbar'
    ],
    urlMenu: '/cake2/rd_cake/dynamic_details/menu_for_photos.json',
    initComponent: function(){
        var me = this;

        //Create the view for the wallpapers:
        var imageTpl = new Ext.XTemplate(
            '<tpl for=".">',
                '<div class="thumb-wrap">',
                    '<div>',
                        '<div><h1>{title}</h1></div>',
                            '<img src="{img}" />',
                        '<div class="description">{description}</div>',
                        '<tpl if="Ext.isEmpty(url)">', //If the url is not empty add the link
                            '<div></div>',
                        '<tpl else>',
                            '<div><a href="{url}" target="_blank">{url}</a></div>',
                        '</tpl>',
                    '</div>',
                '</div>',
            '</tpl>'
        );

        me.store = Ext.create(Ext.data.Store,{
            model: 'Rd.model.mDynamicPhoto',
            proxy: {
                type  :'ajax',
                url   : '/cake2/rd_cake/dynamic_details/index_photo.json',
                extraParams : { 'dynamic_detail_id' : me.dynamic_detail_id},
                format  : 'json',
                reader: {
                    type: 'json',
                    rootProperty: 'items'
                },
                api: {
                    destroy  : '/cake2/rd_cake/dynamic_details/delete_photo.json'
                }
            },
            listeners: {
                load: function(store, records, successful) {
                    if(!successful){
                        Ext.ux.Toaster.msg(
                            'Error encountered',
                            store.getProxy().getReader().rawData.message.message,
                            Ext.ux.Constants.clsWarn,
                            Ext.ux.Constants.msgWarn
                        );
                    }else{
                        var count       = me.down('dataview').getStore().getTotalCount();
                        me.down('#count').update({count: count});
                    }   
                },
                scope: this
            }
        });

        var v = Ext.create('Ext.view.View', {
            store       : me.store,
            multiSelect : true,
            tpl         : imageTpl,
            itemSelector: 'div.thumb-wrap',
            emptyText   : i18n('sNo_images_available')
        });

        me.items =  {
                xtype       : 'panel',
                frame       : false,
                height      : '100%', 
                width       :  550,
                layout: {
                    type: 'vbox',
                    align: 'stretch'
                },
                items       : v,
                autoScroll  : true,
                tbar        : Ext.create('Rd.view.components.ajaxToolbar',{'url': me.urlMenu}),
                bbar: [
                    {   xtype: 'component', itemId: 'count',   tpl: i18n('sResult_count_{count}'),   style: 'margin-right:5px', cls: 'lblYfi'  }
                ]
        };
        me.callParent(arguments);
    }
});
