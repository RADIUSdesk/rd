Ext.define('Rd.view.components.winCsvColumnSelect', {
    extend: 'Ext.window.Window',
    alias : 'widget.winCsvColumnSelect',
    title : i18n('sCSV_export'),
    layout: 'fit',
    autoShow: false,
    width:    350,
    height:   400,
    iconCls: 'list',
    glyph   : Rd.config.icnCsv,
    columns: [],
    initComponent: function() {
        var me = this;
        this.items = [
            {
                xtype: 'form',
                border:     false,
                layout:     'anchor',
                autoScroll: true,
                defaults: {
                    anchor: '100%'
                },
                fieldDefaults: {
                    msgTarget: 'under',
                    labelClsExtra: 'lblRd',
                    labelAlign: 'left',
                    labelSeparator: '',
                    margin: 15
                },
                defaultType: 'textfield',
                tbar: [
                    { xtype: 'tbtext', text: i18n('sSelect_columns_to_include_in_CSV_list'), cls: 'lblWizard' }
                ],
                items: [
                    {
                        xtype       : 'fieldcontainer',
                        fieldLabel  : i18n('sColumns'),
                        defaultType : 'checkboxfield',
                        items:      me.columns
                    }
                ],
                buttons: [
                    {
                        itemId: 'save',
                        text: i18n('sOK'),
                        scale: 'large',
                        iconCls: 'b-next',
                        glyph   : Rd.config.icnNext,
                        formBind: true,
                        margin: '0 20 40 0'
                    }
                ]
            }
        ];
        this.callParent(arguments);
    }
});
