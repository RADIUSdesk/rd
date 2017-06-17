Ext.define('Rd.view.vouchers.gridVoucherPrivate' ,{
    extend:'Ext.grid.Panel',
    alias : 'widget.gridVoucherPrivate',
    multiSelect: true,
    stateful: true,
    stateId: 'StateGridVoucherPrivate',
    stateEvents:['groupclick','columnhide'],
    border: false,
    viewConfig: {
        loadMask:true
    },
    plugins     : 'gridfilters',  //*We specify this
    bbar: [
        {   xtype: 'component', itemId: 'count',   tpl: i18n('sResult_count_{count}'),   style: 'margin-right:5px', cls: 'lblYfi' }
    ],
    tbar: [
        { xtype: 'buttongroup', title: i18n('sAction'),items : [ 
            {   xtype: 'button',  iconCls: 'b-reload', glyph   : Rd.config.icnReload,   scale: 'large',   itemId: 'reload',    tooltip:    i18n('sReload')},
            {   xtype: 'button',  iconCls: 'b-delete', glyph   : Rd.config.icnDelete,   scale: 'large',   itemId: 'delete',    disabled: true, tooltip:    i18n('sDelete')}
        ]}, 
        { xtype: 'buttongroup', title: i18n('sSelection'),items : [
            {   xtype: 'cmbVendor'     , itemId:'cmbVendor',    emptyText: i18n('sSelect_a_vendor') },
            {   xtype: 'cmbAttribute'  , itemId:'cmbAttribute', emptyText: i18n('sSelect_an_attribute') },
            {   xtype: 'button',  iconCls: 'b-add',    glyph   : Rd.config.icnAdd, scale: 'large', itemId: 'add',       tooltip:    i18n('sAdd')}
        ]}        
    ],
    plugins: [
        Ext.create('Ext.grid.plugin.CellEditing', {
            clicksToEdit: 1
        })
    ],
    username: 'nobody', //dummy value
    initComponent: function(){
        var me      = this;

        //Very important to avoid weird behaviour:
        me.plugins = [Ext.create('Ext.grid.plugin.CellEditing', {
                clicksToEdit: 1
        })];

        me.columns = [
            {xtype: 'rownumberer',stateId: 'StateGridVoucherPrivate1'},
            {
                header: i18n('sType'),
                dataIndex: 'type',
                width: 130,
                editor: {
                    xtype: 'combobox',
                    typeAhead: true,
                    triggerAction: 'all',
                    selectOnTab: true,
                    store: [
                        ['check','Check'],
                        ['reply','Reply']
                    ],
                    lazyRender: true,
                    listClass: 'x-combo-list-small'
                },
                renderer: function(value,metaData,record){
                    if(record.get('edit') != false){
                        metaData.tdCls = 'grdEditable';
                    }else{
                        metaData.tdCls = 'gridTree';
                    }
                    if(value == "check"){
                        return i18n('sCheck');
                    }else{
                        return i18n('sReply');
                    }
                },
                stateId: 'StateGridVoucherPrivate2'
            },
            { text: i18n('sAttribute_name'),    dataIndex: 'attribute', tdCls: 'gridTree', flex: 1, stateId: 'StateGridVoucherPrivate3'},
            {
                header: i18n('sOperator'),
                dataIndex: 'op',
                width: 100,
                editor: {
                    allowBlank: false,
                    xtype: 'combobox',
                    typeAhead: true,
                    triggerAction: 'all',
                    selectOnTab: true,
                    store: [
                        ['=' ,  '=' ],
                        [':=',  ':='],
                        ['+=',  '+='],
                        ['==',  '=='],
                        ['-=',  '-='],
                        ['<=',  '<='],
                        ['>=',  '>='],
                        ['!*',  '!*']
                    ],
                    lazyRender: true,
                    listClass: 'x-combo-list-small'
                },
                renderer: function(value,metaData,record){
                    if(record.get('edit') != false){
                        metaData.tdCls = 'grdEditable';
                    }else{
                        metaData.tdCls = 'gridTree';
                    }
                    return value;
                },
                stateId: 'StateGridVoucherPrivate4'
            },
            { 
                text: i18n('sValue'),        dataIndex: 'value', flex: 1,
                editor: { xtype: 'textfield',    allowBlank: false},
                renderer: function(value,metaData,record){
                    if(record.get('edit') != false){
                        metaData.tdCls = 'grdEditable';
                    }else{
                        metaData.tdCls = 'gridTree';
                    }
                    return value;
                },
                stateId: 'StateGridVoucherPrivate5'
            }
        ];

        //Create a store specific to this Access Provider
        me.store = Ext.create(Ext.data.Store,{
            model: 'Rd.model.mPrivateAttribute',
            proxy: {
                type        : 'ajax',
                format      : 'json',
                batchActions: true,
                extraParams : { 'username' : me.username },
                reader      : {
                    type            : 'json',
                    rootProperty    : 'items',
                    messageProperty : 'message'
                },
                api         : {
                    create      : '/cake2/rd_cake/vouchers/private_attr_add.json',
                    read        : '/cake2/rd_cake/vouchers/private_attr_index.json',
                    update      : '/cake2/rd_cake/vouchers/private_attr_edit.json',
                    destroy     : '/cake2/rd_cake/vouchers/private_attr_delete.json'
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
                        //console.log(store.getProxy().getReader().rawData.message.message);
                    }else{
                        var count   = me.getStore().getTotalCount();
                        me.down('#count').update({count: count});
                    }   
                },
                update: function(store, records, success, options) {
                    store.sync({
                        success: function(batch,options){
                            Ext.ux.Toaster.msg(
                                i18n('sUpdated_item'),
                                i18n('sItem_has_been_updated'),
                                Ext.ux.Constants.clsInfo,
                                Ext.ux.Constants.msgInfo
                            ); 
                            store.load();  
                        },
                        failure: function(batch,options){
                            Ext.ux.Toaster.msg(
                                i18n('sProblems_updating_the_item'),
                                i18n('sItem_could_not_be_updated'),
                                Ext.ux.Constants.clsWarn,
                                Ext.ux.Constants.msgWarn
                            );
                            store.load();
                        }
                    });
                },
                scope: this
            },
            autoLoad: false    
        });
 
        me.callParent(arguments);
    }
});
