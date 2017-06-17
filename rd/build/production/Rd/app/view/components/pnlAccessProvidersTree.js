Ext.define('Rd.view.components.pnlAccessProvidersTree', {
    extend      :'Ext.tree.Panel',
    alias       :'widget.pnlAccessProvidersTree',
    useArrows   : true,
    store       : 'sAccessProvidersTree',
    rootVisible : true,
    rowLines    : true,
    layout      : 'fit',
    stripeRows  : true,
    border      : false,
    requires    : [   
        'Rd.store.sAccessProvidersTree'
    ],
    tbar: [
        { xtype: 'tbtext', text: i18n('sSelect_the_owner'), cls: 'lblWizard' }
    ],
    columns: [
        {
            xtype: 'treecolumn', //this is so we know which column will show the tree
            text: i18n('sOwner'),
            sortable: true,
            flex: 1,
            dataIndex: 'username',
            tdCls: 'gridTree'
        }
    ],
    buttons: [
            {
                itemId: 'btnTreeNext',
                text: i18n('sNext'),
                scale: 'large',
                iconCls: 'b-next',
                glyph   : Rd.config.icnNext,
                margin: '0 20 40 0'
            }
        ],
    listeners : {
        beforerender: function(pnl){
            console.log(" Before render");
            pnl.getStore().load();
        }
    }
});




