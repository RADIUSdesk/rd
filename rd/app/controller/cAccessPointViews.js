Ext.define('Rd.controller.cAccessPointViews', {
    extend: 'Ext.app.Controller',
    views:  [
        'aps.pnlAccessPointView', 
        'charts.pnlChartApMain',		
        'aps.gridApViewEntries'
    ],
    config      : {  
        urlApChildCheck				: '/cake3/rd_cake/access-providers/child-check.json',
		urlMapPrefView				: '/cake2/rd_cake/ap_profiles/map_pref_view.json',
		urlOverviewGoogleMap		: '/cake2/rd_cake/ap_reports/overview_google_map.json',
		urlRestartNodes				: '/cake2/rd_cake/ap_reports/restart_nodes.json',
		urlApProfileAddApAction     : '/cake2/rd_cake/ap_actions/add.json'
    },
    refs: [
        {  ref: 'tabAccessPoints',  selector: '#tabAccessPoints'      } 
    ],
    init: function() {
        var me = this;
        
        if (me.inited) {
            return;
        }
        me.inited = true;
            
        me.control({
			//==== MESHdesk View related ====
            'pnlApView gridAccessPointViewEntries #reload' : {
                click: me.reloadViewEntry
            },
            'pnlApView gridAccessPointViewEntries button' : {
                toggle: me.viewEntryTimeToggle
            },
            'pnlApView #tabAccessPointViewEntries': {
               // activate:       me.tabAccessPointViewEntriesActivate
            },
            'pnlApView gridAccessPointViewEntries #reload menuitem[group=refresh]'   : {
                click:      function(menu){
                    var me = this;
                    me.autoRefresh(menu,'entries');
                }
            },
            'pnlApView': {
                beforeshow:      me.pnlViewClose,
                destroy   :      me.pnlViewClose
            }
        });
    },
    actionIndex: function(ap_id,name){
		var me      = this;
        var id		= 'tabAccessPointView'+ ap_id;
        var tabAps  = me.getTabAccessPoints();
        var newTab  = tabAps.items.findBy(
            function (tab){
                return tab.getItemId() === id;
            });
         
        if (!newTab){
            newTab = tabAps.add({
                glyph   : Rd.config.icnView, 
                title   : name,
                closable: true,
                layout  : 'fit',
                xtype   : 'pnlAccessPointView',
                itemId  : id,
                ap_id   : ap_id
            });
        }    
        tabAps.setActiveTab(newTab);    
    },
	viewEntryTimeToggle: function(button,pressed){
        var me = this;
        if(pressed){
            me.reloadViewEntry(button);  
        }
    },
    reloadViewEntry: function(button){
        var me      = this;
        var pnl     = button.up("pnlMeshView");
        var entGrid = pnl.down("gridMeshViewEntries");
        var day     = entGrid.down('#day');
        var week    = entGrid.down('#week');
        var span    = 'hour';
        if(day.pressed){
            span='day';
        }
        if(week.pressed){
            span='week';
        }
        entGrid.getStore().getProxy().setExtraParam('timespan',span);
        entGrid.getStore().reload();
    },
    viewNodeTimeToggle: function(button,pressed){
        var me = this;
        if(pressed){
            me.reloadViewNode(button);  
        }
    },
    reloadViewNode: function(button){
        var me      = this;
        var pnl     = button.up("pnlMeshView");
        var entGrid = pnl.down("gridMeshViewNodes");
        var day     = entGrid.down('#day');
        var week    = entGrid.down('#week');
        var span    = 'hour';
        if(day.pressed){
            span='day';
        }
        if(week.pressed){
            span='week';
        }
        entGrid.getStore().getProxy().setExtraParam('timespan',span);
        entGrid.getStore().reload();
    },
	viewNodeNodesTimeToggle: function(button,pressed){
        var me = this;
        if(pressed){
            me.reloadViewNodeNodes(button);  
        }
    },
	reloadViewNodeNodes: function(button){
        var me      = this;
        var pnl     = button.up("pnlMeshView");
        var entGrid = pnl.down("gridMeshViewNodeNodes");
        var day     = entGrid.down('#day');
        var week    = entGrid.down('#week');
        var span    = 'hour';
        if(day.pressed){
            span='day';
        }
        if(week.pressed){
            span='week';
        }
        entGrid.getStore().getProxy().setExtraParam('timespan',span);
        entGrid.getStore().reload();
    },
    tabMeshViewNodesActivate: function(tab){
        var me = this;
        var b = tab.down('#reload');
        me.reloadViewNode(b);
    },
    tabMeshViewEntriesActivate: function(tab){
        var me = this;
        var b = tab.down('#reload');
        me.reloadViewEntry(b);
    },
	tabMeshViewNodeNodesActivate: function(tab){
        var me = this;
        var b = tab.down('#reload');
        me.reloadViewNodeNodes(b);
    },
	tabMeshViewNodeDetailsActivate: function(tab){
        var me = this;
        var b = tab.down('#reload');
        me.reloadViewNodeDetails(b);
    },
    autoRefresh: function(menu_item,item){
        var me      = this;
        var n       = menu_item.getItemId();
        var b       = menu_item.up('button'); 
        var interval= 30000; //default
        clearInterval(me.autoRefresInterval);   //Always clear
        b.setGlyph(Rd.config.icnTime);

        if(n == 'mnuRefreshCancel'){
            b.setGlyph(Rd.config.icnReload);
            return;
        }
        
        if(n == 'mnuRefresh1m'){
           interval = 60000
        }

        if(n == 'mnuRefresh5m'){
           interval = 360000
        }
        me.autoRefresInterval = setInterval(function(){ 
            if(item == 'nodes'){
                me.reloadViewNode(b);
            }

			 if(item == 'node_nodes'){
                me.reloadViewNodeNodes(b);
            }  

            if(item == 'entries'){
                me.reloadViewEntry(b);
            }       
        },  interval);  

    },
    pnlViewClose:   function(){
        var me = this;
        if(me.autoRefresInterval != undefined){
            clearInterval(me.autoRefresInterval);   //Always clear
        }
    }
});
