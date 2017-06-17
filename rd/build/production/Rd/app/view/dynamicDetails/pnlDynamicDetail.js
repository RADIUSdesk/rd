Ext.define('Rd.view.dynamicDetails.pnlDynamicDetail', {
    extend: 'Ext.tab.Panel',
    alias: 'widget.pnlDynamicDetail',
    border: false,
    dynamic_detail_id: null,
    plain   : true,
    cls     : 'subTab',
    initComponent: function(){
        var me = this;
        me.items = [
            {   
                title:  i18n('sDetail'),
                xtype:  'pnlDynamicDetailDetail',
                itemId: 'tabDetail'
                   
            },
            { 
                title   : i18n('sLogo'),
                itemId  : 'tabLogo',
                xtype   : 'pnlDynamicDetailLogo',
                margin  : 5
            },
            { 
                title   : i18n('sPhotos'),
                itemId  : 'tabPhoto',
                xtype   : 'pnlDynamicDetailPhoto',
                dynamic_detail_id : me.dynamic_detail_id
            },
            { 
                title   : i18n('sOwn_pages'),
                itemId  : 'tabPages',
                xtype   : 'gridDynamicDetailPages',
                dynamic_detail_id : me.dynamic_detail_id
            },
            { 
                title   : i18n('sDynamic_keys'),
                itemId  : 'tabPairs',
                xtype   : 'gridDynamicDetailPairs',
                dynamic_detail_id : me.dynamic_detail_id
            },
            { 
                title   : 'Settings',
                itemId  : 'tabSettings',
                xtype   : 'pnlDynamicDetailSettings',
                dynamic_detail_id : me.dynamic_detail_id
            },
            { 
                title   : 'Click to connect',
                itemId  : 'tabClickToConect',
                xtype   : 'pnlDynamicDetailClickToConnect',
                dynamic_detail_id : me.dynamic_detail_id
            },
			{ 
                title   : 'Social login',
                itemId  : 'tabSocialLogin',
                xtype   : 'pnlDynamicDetailSocialLogin',
                dynamic_detail_id : me.dynamic_detail_id
            }

        ]; 
        me.callParent(arguments);
    }
});
