Ext.define('Rd.view.components.winCsvColumnSelect', {
    extend  : 'Ext.window.Window',
    alias   : 'widget.winCsvColumnSelect',
    title   : i18n('sCSV_export'),
    layout  : 'fit',
    autoShow: false,
    width   : 450,
    height  : 400,
    glyph   : Rd.config.icnCsv,
    columns : [],
    initComponent: function() {
        var me = this;
        this.items = [
            {
                xtype       : 'form',
                border      : false,
                layout      : 'anchor',
                autoScroll  : true,
                defaults    : {
                    anchor: '100%'
                },
                fieldDefaults: {
                    msgTarget       : 'under',
                    labelClsExtra   : 'lblRd',
                    labelAlign      : 'left',
                    labelSeparator  : '',
                    margin          : Rd.config.fieldMargin
                },
                defaultType: 'textfield',
               // tbar: [
               //     { xtype: 'tbtext', text: i18n('sSelect_columns_to_include_in_CSV_list'), cls: 'lblWizard' }
               // ],
                items: [
                    {
                        xtype       : 'checkboxgroup',
                        columns     : 2,
                        vertical    : true,
                        items       : me.columns
                    }
                ],
                buttons: [
                    {
                        itemId      : 'save',
                        text        : i18n('sOK'),
                        scale       : 'large',
                        glyph       : Rd.config.icnNext,
                        formBind    : true,
                        margin      : Rd.config.buttonMargin
                    }
                ]
            }
        ];
        this.callParent(arguments);
    }
});
