Ext.define('Rd.view.dynamicDetails.cmbThemes', {
    extend          : 'Ext.form.ComboBox',
    alias           : 'widget.cmbThemes',
    fieldLabel      : 'Theme',
    labelSeparator  : '',
    store           : 'sThemes',
    queryMode       : 'local',
    valueField      : 'id',
    displayField    : 'name',
    allowBlank      : false,
    editable        : false,
    mode            : 'local',
    itemId          : 'theme',
    name            : 'theme',
    value           : 'Default',
    labelClsExtra   : 'lblRd'
});
