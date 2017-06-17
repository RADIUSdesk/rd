Ext.define('Rd.view.components.vCmbLanguages', {
    extend			: 'Ext.form.ComboBox',
    alias 			: 'widget.cmbLanguages',
    fieldLabel		: i18n('sChoose_a_language'),
    labelSeparator	: '',
    store			: 'sLanguages',
    queryMode		: 'local',
    valueField		: 'id',
    displayField	: 'text',
    typeAhead		: true,
    mode			: 'local',
    itemId			: 'cmbLanguage',
	tpl				: Ext.create('Ext.XTemplate',
        '<tpl for=".">',
            '<div class="x-boundlist-item">',
				'<div class="combo-wrapper"><img src="{icon_file}" />',
					'<div class="combo-country">{country}</div>',
               		'<div class="combo-language"> {language}</div>',
               	'</div>',
			'</div>',
        '</tpl>'
    ),
    displayTpl		: Ext.create('Ext.XTemplate',
        '<tpl for=".">',
            '{country} - {language}',
        '</tpl>'
    )
});
