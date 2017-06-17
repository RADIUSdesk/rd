Ext.define('Rd.controller.cMeshViews', {
    extend: 'Ext.app.Controller',
    views:  [
        'components.pnlBanner', 	'meshes.winMeshView', 		'meshes.gridMeshViewEntries',	
		'meshes.gridMeshViewNodes',	'meshes.pnlMeshViewNodes',	'meshes.gridMeshViewNodeNodes',
		'meshes.gridMeshViewNodeDetails',						'meshes.pnlMeshViewGMap',
		'meshes.gridMeshViewNodeActions',						'meshes.winMeshAddNodeAction'
    ],
    stores      : [	
		'sMeshViewEntries', 'sMeshViewNodeNodes', 'sMeshViewNodes', 'sNodeDetails'
    ],
    models      : [

    ],
    config      : {  
        urlApChildCheck				: '/cake2/rd_cake/access_providers/child_check.json',
		urlMapPrefView				: '/cake2/rd_cake/meshes/map_pref_view.json',
		urlOverviewGoogleMap		: '/cake2/rd_cake/mesh_reports/overview_google_map.json',
		urlRestartNodes				: '/cake2/rd_cake/mesh_reports/restart_nodes.json',
		urlMeshAddNodeAction		: '/cake2/rd_cake/node_actions/add.json',
		urlBlueMark 				: 'resources/images/map_markers/mesh_blue_node.png',
		urlRedNode 					: 'resources/images/map_markers/mesh_red_node.png',
		urlRedGw 					: 'resources/images/map_markers/mesh_red_gw.png',
		urlGreenNode 				: 'resources/images/map_markers/mesh_green_node.png',
		urlGreenGw	 				: 'resources/images/map_markers/mesh_green_gw.png', //Now also supporting phones!
		urlPhoneGreenGw				: 'resources/images/map_markers/phone_green_gw.png',
		urlPhoneGreenNode			: 'resources/images/map_markers/phone_green.png',
		urlPhoneRedGw				: 'resources/images/map_markers/phone_red_gw.png',
		urlPhoneRedNode			    : 'resources/images/map_markers/phone_red.png',
		urlPhoneBlueGw				: 'resources/images/map_markers/phone_blue_gw.png',
		urlPhoneBlueNode			: 'resources/images/map_markers/phone_blue.png'
    },
    refs: [
       
    ],
    init: function() {
        var me = this;
        if (me.inited) {
            return;
        }
        me.inited = true;

        me.control({
			//==== MESHdesk View related ====
            'winMeshView gridMeshViewEntries #reload' : {
                click: me.reloadViewEntry
            },
            'winMeshView gridMeshViewEntries button' : {
                toggle: me.viewEntryTimeToggle
            },
            'winMeshView gridMeshViewNodes #reload' : {
                click: me.reloadViewNode
            },
            'winMeshView gridMeshViewNodes button' : {
                toggle: me.viewNodeTimeToggle
            },
            'winMeshView #tabMeshViewEntries': {
                activate:       me.tabMeshViewEntriesActivate
            },
            'winMeshView #tabMeshViewNodes': {
                activate:       me.tabMeshViewNodesActivate
            },
			'winMeshView #tabMeshViewNodeNodes': {
                activate:       me.tabMeshViewNodeNodesActivate
            },
			'winMeshView #tabMeshViewNodeDetails': {
                activate:       me.tabMeshViewNodeDetailsActivate
            },
            'winMeshView gridMeshViewEntries #reload menuitem[group=refresh]'   : {
                click:      function(menu){
                    var me = this;
                    me.autoRefresh(menu,'entries');
                }
            },
            'winMeshView gridMeshViewNodes #reload menuitem[group=refresh]'   : {
                click:      function(menu){
                    me.autoRefresh(menu,'nodes');
                }
            },
			'winMeshView gridMeshViewNodeNodes #reload menuitem[group=refresh]'   : {
                click:      function(menu){
                    me.autoRefresh(menu,'node_nodes');
                }
            }, 
            'winMeshView': {
                beforeshow:      me.winViewClose,
                destroy   :      me.winViewClose
            },
			'winMeshView pnlMeshViewNodes':	{
				activate:		function(pnl){
					pnl.getData()
				}
			},
			'#pnlMapsNodeInfo #restart': {
				click:	me.mapRestart
			},
			'winMeshView pnlMeshViewNodes #reload':	{
				click:		function(button){
					var me 	= this;
					var pnl = button.up("pnlMeshViewNodes");
					pnl.getData()
				}
			},
			'winMeshView gridMeshViewNodeNodes #reload':	{
				click: me.reloadViewNodeNodes
			},
			'winMeshView gridMeshViewNodeNodes button' : {
                toggle: me.viewNodeNodesTimeToggle
            },
			'gridMeshViewNodeDetails #map' : {
                click: 	me.mapLoadApi
            },
			'winMeshView #mapTab'		: {
				activate: function(pnl){
					me.reloadMap(pnl);
				}
			},
			'pnlMeshViewGMap #reload'	: {
				click:	function(b){
					var me = this;
					me.reloadMap(b.up('pnlMeshViewGMap'));
				}
			},
			'winMeshView gridMeshViewNodeDetails #reload' : {
				click	: me.reloadViewNodeDetails
			},
			'winMeshView gridMeshViewNodeDetails #execute' : {
				click	: me.execute
			},
			'winMeshView gridMeshViewNodeDetails #history' : {
				click	: me.history
			},
			'winMeshView gridMeshViewNodeDetails #restart' : {
				click	: me.restart
			},
			'winMeshAddNodeAction #save' : {
				click	: me.commitExecute
			},
			'winMeshView gridMeshViewNodeActions #reload' : {
				click	: me.reloadNodeActions
			},
			'winMeshView gridMeshViewNodeActions #add' : {
				click	: me.addNodeActions
			},
			'winMeshView gridMeshViewNodeActions #delete' : {
				click	: me.deleteNodeActions
			},
			'winMeshView gridMeshViewNodeActions' : {
				activate: me.activateNodeActions
			}
        });
    },
    actionIndex: function(mesh_id,name){
        var me      = this;
		var id      = 'winMeshView'+mesh_id; 
        if(!me.application.runAction('cDesktop','AlreadyExist',id)){
            var w = Ext.widget('winMeshView',{id:id, name:name, stateId:id,title: 'MESHdesk view '+name, meshId: mesh_id});
            me.application.runAction('cDesktop','Add',w);      
        }
    },
	viewEntryTimeToggle: function(button,pressed){
        var me = this;
        if(pressed){
            me.reloadViewEntry(button);  
        }
    },
    reloadViewEntry: function(button){
        var me      = this;
        var win     = button.up("winMeshView");
        var entGrid = win.down("gridMeshViewEntries");
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
        var win     = button.up("winMeshView");
        var entGrid = win.down("gridMeshViewNodes");
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
        var win     = button.up("winMeshView");
        var entGrid = win.down("gridMeshViewNodeNodes");
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
        b.setIconCls('b-reload_time');
        b.setGlyph(Rd.config.icnTime);

        if(n == 'mnuRefreshCancel'){
            b.setGlyph(Rd.config.icnReload);
            b.setIconCls('b-reload');
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
    winViewClose:   function(){
        var me = this;
        if(me.autoRefresInterval != undefined){
            clearInterval(me.autoRefresInterval);   //Always clear
        }
    },
	//____ MAP ____
    mapLoadApi:   function(button){
        var me 	= this;
		Ext.ux.Toaster.msg(
	        'Loading Google Maps API',
	        'Please be patient....',
	        Ext.ux.Constants.clsInfo,
	        Ext.ux.Constants.msgInfo
	    );
	    
	    Ext.Loader.loadScript({
            url: 'https://www.google.com/jsapi',                    // URL of script
            scope: this,                   // scope of callbacks
            onLoad: function() {           // callback fn when script is loaded
                google.load("maps", "3", {
                    other_params:"sensor=false",
                    callback : function(){
                    // Google Maps are loaded. Place your code here
                        me.mapCreatePanel(button);
                }
            });
            },
            onError: function() {          // callback fn if load fails 
                console.log("Error loading Google script");
            } 
        });
    },
    mapCreatePanel : function(button){
        var me = this
        var tp          = button.up('tabpanel');
        var map_tab_id  = 'mapTab';
        var nt          = tp.down('#'+map_tab_id);
        if(nt){
            tp.setActiveTab(map_tab_id); //Set focus on  Tab
            return;
        }

        var map_tab_name = i18n("sGoogle_Maps");
		var win 		= tp.up('winMeshView');
		var mesh_id		= win.meshId;

        //We need to fetch the Preferences for this user's Google Maps map
        Ext.Ajax.request({
            url		: me.getUrlMapPrefView(),
            method	: 'GET',
			params	: {
				mesh_id	: mesh_id
			},
            success: function(response){
                var jsonData    = Ext.JSON.decode(response.responseText);
                if(jsonData.success){     
                   	//console.log(jsonData);
					//___Build this tab based on the preferences returned___
                    tp.add({ 
                        title 		: map_tab_name,
                        itemId		: map_tab_id,
                        closable	: true,
                        glyph		: Rd.config.icnMap, 
                        layout		: 'fit', 
                        xtype		: 'pnlMeshViewGMap',
                        mapOptions	: {zoom: jsonData.data.zoom, mapTypeId: google.maps.MapTypeId[jsonData.data.type] },	//Required for map
                       	centerLatLng: {lat:jsonData.data.lat,lng:jsonData.data.lng},										//Required for map
                       	markers		: [],
						meshId		: mesh_id
                    });
                    tp.setActiveTab(map_tab_id); //Set focus on Add Tab
                    //____________________________________________________   
                }   
            },
			failure: function(batch,options){
                Ext.ux.Toaster.msg(
                    'Problems getting the map preferences',
                    'Map preferences could not be fetched',
                    Ext.ux.Constants.clsWarn,
                    Ext.ux.Constants.msgWarn
                );
            },
			scope: me
        });
    },
	reloadMap: function(map_panel){
		var me = this;
		//console.log("Reload markers");
		map_panel.setLoading(true);
		map_panel.clearMarkers();
		map_panel.clearPolyLines();
		var mesh_id = map_panel.meshId;

		Ext.Ajax.request({
            url		: me.getUrlOverviewGoogleMap(),
            method	: 'GET',
			params	: {
				mesh_id: mesh_id
			},
            success: function(response){
                var jsonData    = Ext.JSON.decode(response.responseText);
                if(jsonData.success){

					Ext.each(jsonData.items, function(i){
						var icon 		= me.getUrlBlueMark();
						
						//Phones
						var phone_flag 	= false;
						if((i.hardware == 'mp2_basic')||(i.hardware == 'mp2_phone')){
							phone_flag = true; 
						}

						if(phone_flag){
							icon 		= me.getUrlPhoneBlueNode();
						}

						if(i.state == 'down'){
							if(phone_flag){
								icon = me.getUrlPhoneRedNode();
							}else{
								icon = me.getUrlRedNode()
							}
						}

						if((i.state == 'down')&(i.gateway == 'yes')){
							if(phone_flag){
								icon = me.getUrlPhoneRedGw();
							}else{
								icon = me.getUrlRedGw()
							}
						}

						if(i.state == 'up'){
							if(phone_flag){
								icon = me.getUrlPhoneGreenNode();
							}else{
								icon = me.getUrlGreenNode()
							}
						}

						if((i.state == 'up')&(i.gateway == 'yes')){
							if(phone_flag){
								icon = me.getUrlPhoneGreenGw();
							}else{
								icon = me.getUrlGreenGw()
							}
						}
						
						var sel_marker = map_panel.addMarker({
		                    lat			: i.lat, 
		                    lng			: i.lng,
		                    icon		: icon,
		                    title		: i.name,
		                    listeners: {
								click: function(e,f){
		                            //console.log(record);
		                            me.markerClick(i,map_panel,sel_marker);   
		                        }
		                    }
		                })
					});
					//Add the poly lines
					Ext.each(jsonData.connections, function(c){
						var pl = map_panel.addPolyLine(c);
					});

					map_panel.setLoading(false);
                }   
            },
            scope: me
        });
	},
	reloadViewNodeDetails: function(button){
        var me      = this;
        var win     = button.up("winMeshView");
        var grid    = win.down("gridMeshViewNodeDetails"); 
        grid.getStore().reload();
    },
	markerClick: function(item,map_panel,sel_marker){
    	var me = this;
        map_panel.marker_data = item;
        map_panel.infowindow.open(map_panel.gmap,sel_marker); 
    },
	execute:   function(button){
        var me      = this; 
		var win		= button.up('window')
		var grid	= win.down('gridMeshViewNodeDetails');
		var mesh_id = grid.meshId;   
        //Find out if there was something selected
        if(grid.getSelectionModel().getCount() == 0){
             Ext.ux.Toaster.msg(
                        i18n('sSelect_an_item'),
                        'Select an item on which to execute the command',
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
            );
        }else{
        	//console.log("Show window for command content")
			if(!me.application.runAction('cDesktop','AlreadyExist','winMeshAddNodeActionId')){
                var w = Ext.widget('winMeshAddNodeAction',{id:'winMeshAddNodeActionId',grid : grid});
                me.application.runAction('cDesktop','Add',w);         
            }
        }
    },
	commitExecute:  function(button){
        var me      = this;
        var win     = button.up('winMeshAddNodeAction');
        var form    = win.down('form');

		var selected    = win.grid.getSelectionModel().getSelection();
		var list        = [];
        Ext.Array.forEach(selected,function(item){
            var id = item.getId();
            Ext.Array.push(list,{'id' : id});
        });

        form.submit({
            clientValidation	: true,
            url					: me.getUrlMeshAddNodeAction(),
			params				: list,
            success: function(form, action) {       
                win.grid.getStore().reload();
				win.close();
                Ext.ux.Toaster.msg(
                    i18n('sNew_item_created'),
                    i18n('sItem_created_fine'),
                    Ext.ux.Constants.clsInfo,
                    Ext.ux.Constants.msgInfo
                );
            },
            failure: Ext.ux.formFail
        });
    },
	history:   function(button){

		var me 			= this
		var win			= button.up('winMeshView');
        var tp          = button.up('tabpanel');
		var grid		= win.down('gridMeshViewNodeDetails');
  
        //Find out if there was something selected
        if(grid.getSelectionModel().getCount() == 0){
             Ext.ux.Toaster.msg(
                        i18n('sSelect_an_item'),
                        'Select an item to view the history',
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
            );
        }else{
			var selected    	= grid.getSelectionModel().getSelection();
            Ext.Array.forEach(selected,function(item){
                var id 			= item.getId();
				var n			= item.get('name');
				var h_tab_id    = 'hTab_'+id;
				var h_tab_name  = 'History for '+n;
				var nt          = tp.down('#'+h_tab_id);
				if(nt){
				    tp.setActiveTab(h_tab_id); //Set focus on  Tab
				}else{
					tp.add({ 
                        title 		: h_tab_name,
                        itemId		: h_tab_id,
                        closable	: true,
                        glyph		: Rd.config.icnWatch, 
                        layout		: 'fit', 
         				xtype		: 'gridMeshViewNodeActions',
						nodeId		: id
                    });
                    tp.setActiveTab(h_tab_id); //Set focus on Add Tab
				}

            });
        }
    },
	restart:   function(button){
        var me      = this; 
		var win		= button.up('window');
		var grid	= win.down('gridMeshViewNodeDetails');
		var mesh_id = grid.meshId;
    
        //Find out if there was something selected
        if(grid.getSelectionModel().getCount() == 0){
             Ext.ux.Toaster.msg(
                        i18n('sSelect_an_item'),
                        'First select an item to restart',
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
            );
        }else{
            Ext.MessageBox.confirm(i18n('sConfirm'), i18n('sAre_you_sure_you_want_to_do_that_qm'), function(val){
                if(val== 'yes'){

                    var selected    = grid.getSelectionModel().getSelection();
                    var list        = [];
                    Ext.Array.forEach(selected,function(item){
                        var id = item.getId();
                        Ext.Array.push(list,{'id' : id});
                    });

                    Ext.Ajax.request({
                        url: me.getUrlRestartNodes(),
                        method: 'POST',          
                        jsonData: {nodes: list, mesh_id: mesh_id},
                        success: function(batch,options){
                            Ext.ux.Toaster.msg(
                                'Restart command queued',
                                'Command queued for execution',
                                Ext.ux.Constants.clsInfo,
                                Ext.ux.Constants.msgInfo
                            );
                            grid.getStore().reload();
                        },                                    
                        failure: function(batch,options){
                            Ext.ux.Toaster.msg(
                                'Problems restarting device',
                                batch.proxy.getReader().rawData.message.message,
                                Ext.ux.Constants.clsWarn,
                                Ext.ux.Constants.msgWarn
                            );
                            grid.getStore().reload();
                        }
                    });
                }
            });
        }
    },
	mapRestart: function(b){
		var me 		= this;
		var pnl		= b.up('#pnlMapsNodeInfo');
		var node_id	= pnl.nodeId;
		var mesh_id = pnl.meshId;
		Ext.Ajax.request({
            url: me.getUrlRestartNodes(),
            method: 'POST',          
            jsonData: {nodes: [{'id': node_id}], mesh_id: mesh_id},
            success: function(batch,options){
                Ext.ux.Toaster.msg(
                    'Restart command queued',
                    'Command queued for execution',
                    Ext.ux.Constants.clsInfo,
                    Ext.ux.Constants.msgInfo
                );c
            },                                    
            failure: function(batch,options){
                Ext.ux.Toaster.msg(
                    'Problems restarting device',
                    batch.proxy.getReader().rawData.message.message,
                    Ext.ux.Constants.clsWarn,
                    Ext.ux.Constants.msgWarn
                );
            }
        });
	},
	activateNodeActions: function(grid){
		var me = this;
		grid.getStore().reload();
	},
	reloadNodeActions: function(b){
		var me 		= this;
		var grid 	= b.up('gridMeshViewNodeActions');
		grid.getStore().reload();

	},
	addNodeActions: function(b){
		var me 		= this;
		var grid 	= b.up('gridMeshViewNodeActions');
		var nodeId	= grid.nodeId;

		if(!me.application.runAction('cDesktop','AlreadyExist','winMeshAddNodeAction_'+nodeId)){
            var w = Ext.widget('winMeshAddNodeAction',{id:'winMeshAddNodeAction_'+nodeId,grid : grid,nodeId: nodeId});
            me.application.runAction('cDesktop','Add',w);         
        }
	},
	deleteNodeActions:   function(b){
        var me 		= this;
		var grid 	= b.up('gridMeshViewNodeActions');
		var nodeId	= grid.nodeId;
   
        //Find out if there was something selected
        if(grid.getSelectionModel().getCount() == 0){
             Ext.ux.Toaster.msg(
                        i18n('sSelect_an_item'),
                        i18n('sFirst_select_an_item_to_delete'),
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
            );
        }else{
            Ext.MessageBox.confirm(i18n('sConfirm'), i18n('sAre_you_sure_you_want_to_do_that_qm'), function(val){
                if(val== 'yes'){
                    grid.getStore().remove(grid.getSelectionModel().getSelection());
                    grid.getStore().sync({
                        success: function(batch,options){
                            Ext.ux.Toaster.msg(
                                i18n('sItem_deleted'),
                                i18n('sItem_deleted_fine'),
                                Ext.ux.Constants.clsInfo,
                                Ext.ux.Constants.msgInfo
                            );
                            grid.getStore().load(); //Reload from server since the sync was not good  
                        },
                        failure: function(batch,options,c,d){
                            Ext.ux.Toaster.msg(
                                i18n('sProblems_deleting_item'),
                                batch.proxy.getReader().rawData.message.message,
                                Ext.ux.Constants.clsWarn,
                                Ext.ux.Constants.msgWarn
                            );
                            grid.getStore().load(); //Reload from server since the sync was not good
                        }
                    });
                }
            });
        }
    }

});
