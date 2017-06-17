Ext.define('Rd.view.components.gridNote' ,{
    extend:'Ext.grid.Panel',
    alias : 'widget.gridNote',
    multiSelect: true,
    border: false,
    noteForId   : '',
    noteForGrid : '',
    requires: ['Rd.model.mNote'],
    tbar:   [
        { xtype: 'button',  iconCls: 'b-reload',    glyph   : Rd.config.icnReload,    scale: 'large', itemId: 'reload',   tooltip:    i18n('sReload')},              
        { xtype: 'button',  iconCls: 'b-add',       glyph   : Rd.config.icnAdd,     scale: 'large', itemId: 'add',      tooltip:    i18n('sAdd')   },
        { xtype: 'button',  iconCls: 'b-delete',    glyph   : Rd.config.icnDelete,     scale: 'large', itemId: 'delete',   tooltip:    i18n('sDelete'), disabled: true}
    ],
    bbar: [
        {   xtype: 'component', itemId: 'count',   tpl: i18n('sResult_count_{count}'),   style: 'margin-right:5px', cls: 'lblYfi' }
    ],
    columns: [
        {xtype: 'rownumberer'},
        { 
            text        : i18n('sNote'),   
            dataIndex   : 'note',    
            tdCls       : 'multiLine', 
            flex        : 1,
            xtype       :  'templatecolumn', 
            tpl:        new Ext.XTemplate(
                            "<tpl if='available_to_siblings == true'><div class=\"hasRight\">{note}</div></tpl>",
                            "<tpl if='available_to_siblings == false'><div class=\"noRight\">{note}</div></tpl>"
                        )
        },
        { text: i18n('sOwner'),  dataIndex: 'owner',   tdCls: 'gridTree', width: 70}
    ],
    initComponent: function(){
        var me      = this;  
        me.store    = Ext.create(Ext.data.Store,{
            model: 'Rd.model.mNote',
            extend: 'Ext.data.Store',
            proxy: {
                type    : 'ajax',
                format  : 'json',
                batchActions: true, 
                url     : '/cake2/rd_cake/' + me.noteForGrid + '/note_index.json',
                extraParams: { 'for_id' : me.noteForId },
                reader: {
                    type: 'json',
                    rootProperty: 'items',
                    messageProperty: 'message'
                },
                api: {
                    destroy  : '/cake2/rd_cake/' + me.noteForGrid + '/note_del.json'
                }
            },
            autoLoad: true
        });
        me.getStore().addListener('load',me.onStoreNoteLoaded, me); 
        me.callParent(arguments);
    },
    onStoreNoteLoaded: function() {
        var me      = this;
        console.log(me.getStore().getTotalCount());
        var count   = me.getStore().getTotalCount();
        me.down('#count').update({count: count});
    }
});
