Ext.define('Rd.view.meshes.pnlMeshViewNodes', {
    extend  	: 'Ext.panel.Panel',
    alias   	: 'widget.pnlMeshViewNodes',
    border  	: false,
	urlOverview	:   '/cake2/rd_cake/mesh_reports/overview.json',
	meshId		: '',
	viewConfig	: {
        loadMask:true
    },
    tbar: [
        { xtype: 'buttongroup', title: i18n('sAction'), items : [
            { xtype: 'button',  iconCls: 'b-reload',    glyph: Rd.config.icnReload ,scale: 'large', itemId: 'reload',   tooltip:    i18n('sReload')}
        ]}    
    ],
    initComponent: function(){
        var me 	= this;
		me.html	= "<div id='n_t_n_"+me.meshId+"' style='width:100%;height:100%;background-color:#aaf6be;'></div>";
		me.buffered = Ext.Function.createBuffered(function(){			
				me.fd.computeIncremental({
					iter	: 40,
					property: ['end'],
					onStep: function(perc){
					  //console.log(perc + '% loaded...');
					},
					onComplete: function(){
					  //console.log('done');
					  me.fd.animate({
						modes: ['linear'],
						transition: $jit.Trans.Elastic.easeOut,
						duration: 2500
					  });
					}
				});
			},1000,me);
		
		me.listeners= {
		    afterrender: function(a,b,c){
				//console.log("afterrender....");
				me.initCanvas();
		    },
			afterlayout: function(a,b,c){
				//console.log("afterlayout....");
				var me = this;
				var w  = me.getWidth();
				var h  = me.getHeight()-90; //90 is the space taken up by the top toolbar
				me.fd.canvas.resize(w,h);	
				me.buffered();
		    },
			scope: me
		}
		me.callParent(arguments);
    },
	getData: function(){
		var me = this
		me.setLoading(true);
		Ext.Ajax.request({
            url: me.urlOverview,
            method: 'GET',
			params: {
				mesh_id: me.meshId
			},
            success: function(response){
                var jsonData    = Ext.JSON.decode(response.responseText);
                if(jsonData.success){
                	//console.log(jsonData)
					me.fd.loadJSON(jsonData.data);
					//re-layout
					me.buffered()
					me.setLoading(false);
                }   
            },
            scope: me
        });
	},
	initCanvas: function(){
		var me = this;
		//console.log("Init the canvas");
		var labelType, useGradients, nativeTextSupport, animate;

		(function() {
		  var ua = navigator.userAgent,
			  iStuff = ua.match(/iPhone/i) || ua.match(/iPad/i),
			  typeOfCanvas = typeof HTMLCanvasElement,
			  nativeCanvasSupport = (typeOfCanvas == 'object' || typeOfCanvas == 'function'),
			  textSupport = nativeCanvasSupport 
				&& (typeof document.createElement('canvas').getContext('2d').fillText == 'function');
		  //I'm setting this based on the fact that ExCanvas provides text support for IE
		  //and that as of today iPhone/iPad current text support is lame
		  labelType = (!nativeCanvasSupport || (textSupport && !iStuff))? 'Native' : 'HTML';
		  nativeTextSupport = labelType == 'Native';
		  useGradients = nativeCanvasSupport;
		  animate = !(iStuff || !nativeCanvasSupport);
		})();


		var i  = 'n_t_n_'+me.meshId;
		// init ForceDirected
		var fd = new $jit.ForceDirected({
			Canvas: {
				height : 500,
        		width  : 200
			},
			//id of the visualization container
			injectInto: i,
			//Enable zooming and panning
			//by scrolling and DnD
			Navigation: {
			  enable: true,
			  //Enable panning events only if we're dragging the empty
			  //canvas (and not a node).
			  panning: 'avoid nodes',
			  zooming: 10 //zoom speed. higher is more sensible
			},
			// Change node and edge styles such as
			// color and width.
			// These properties are also set per node
			// with dollar prefixed data-properties in the
			// JSON structure.
			Node: {
			  	overridable: true          
			},
			Edge: {
			  overridable: true,
			  color: 'red',
			  lineWidth: 0.4
			},
			//Native canvas text styling
			Label: {
			  type	: labelType, //Native or HTML
			  size	: 15,
			  style	: 'bold',
			  color	: '#434446'
			},
			//Add Tips
			Tips: {
			  enable: true,
			  onShow: function(tip, node) {
				//count connections
				var count = 0;
				node.eachAdjacency(function(a,b,c) { 
					console.log(a)
					console.log(b)
					console.log(c)
					count++; 
				});
				//display node info in tooltip
				tip.innerHTML = "<div class='divTip'>"+
					"<div class='tip-title'>"+ node.name + "</div>"+
					"<label class='lblListS'>MAC</label><label class='lblValueS'>"+node.data.mac+"</label>"+
                    "<div style='clear:both;'></div>"+
					"<label class='lblListS'>Description</label><label class='lblValueS'>"+node.data.description+"</label>"+
                    "<div style='clear:both;'></div>"+
					"<label class='lblListS'>Hardware</label><label class='lblValueS'>"+node.data.hw_human+"</label>"+
                    "<div style='clear:both;'></div>"+
					"<label class='lblListS'>IP Address</label><label class='lblValueS'>"+node.data.ip+"</label>"+
                    "<div style='clear:both;'></div>"+
					"<label class='lblListS'>Last contact</label><label class='lblValueS'>"+node.data.last_contact_human+"</label>"+
                    "<div style='clear:both;'></div>"+
					"</div>";
			  }
			},
			// Add node events
			Events: {
			  enable: true,
			  type: 'Native',
			  //Change cursor style when hovering a node
			  onMouseEnter: function() {
				fd.canvas.getElement().style.cursor = 'move';
			  },
			  onMouseLeave: function() {
				fd.canvas.getElement().style.cursor = '';
			  },
			  //Update node positions when dragged
			  onDragMove: function(node, eventInfo, e) {
				  var pos = eventInfo.getPos();
				  node.pos.setc(pos.x, pos.y);
				  fd.plot();
			  },
			  //Implement the same handler for touchscreens
			  onTouchMove: function(node, eventInfo, e) {
				$jit.util.event.stop(e); //stop default touchmove event
				this.onDragMove(node, eventInfo, e);
			  },
			  //Add also a click handler to nodes
			  onClick: function(node) {
				  if(!node) return;
				  console.log(node);
				  node.data["$color"] = "#FF0000";
				  fd.plot();
			  }
			},
			//Number of iterations for the FD algorithm
			iterations			: 50,
			//Edge length
			levelDistance		: 130,
			// Add text to the labels. This method is only triggered
			// on label creation and only for DOM labels (not native canvas ones).
			onCreateLabel: function(domElement, node){
			  domElement.innerHTML 	= node.name;
			  var style 			= domElement.style;
			  style.fontSize 		= "1.8em";
			  style.color 			= "black";
			}
		});

		//Some dummy date to start with (else it spitz out an error)
		var json = [
			{
				id: "graphnode1",
				name: ".",
				data: {
				    $color: "#117c25",
				    $type: "circle",
				    $dim: 1
				}
			}
		];
		fd.loadJSON(json);
		me.fd 			= fd;
	}
});
