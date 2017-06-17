Ext.override(Ext.grid.column.Column, {
    constructor: function (config) {
        Ext.apply(config, { align: 'left' });
        this.callParent([config]);
    }
});
