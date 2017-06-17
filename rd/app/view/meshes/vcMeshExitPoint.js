Ext.define('Rd.view.meshes.vcMeshExitPoint', {
    extend  : 'Ext.app.ViewController',
    alias   : 'controller.vcMeshExitPoint',
    config : {
        urlCheckExperimental : '/cake2/rd_cake/meshes/mesh_experimental_check.json'
    },
    init: function() {
        var me = this;
    },    
	onChkDnsOverrideChange: function(chk){
		var me 		= this;
		var form    = chk.up('form');
		var d1      = form.down('#txtDns1');
		var d2      = form.down('#txtDns2');
		var desk    = form.down('#chkDnsDesk');
		if(chk.getValue()){
		    d1.enable();
		    d2.enable();
		    desk.setValue(false);
		    desk.disable(); 
		}else{
		    d1.disable();
		    d2.disable();
		    desk.enable(); 
		}
	},
	onChkDnsDeskChange: function(chk){
	    var me 		= this;
		var form    = chk.up('form');
		var override= form.down('#chkDnsOverride');
		var any     = form.down('#chkAnyDns');
		if(chk.getValue()){
		    any.setValue(false);
		    any.disable();
		    override.setValue(false);
		    override.disable();  
		}else{
		    any.enable();
		    override.enable();  
		}
	},
	onDnsDeskBeforeRender : function(chk){
	    var me = this; 
	    Ext.Ajax.request({
            url: me.getUrlCheckExperimental(),
            method: 'GET',
            success: function(response){
                var jsonData    = Ext.JSON.decode(response.responseText);
                if(jsonData.success){                      
                    if(jsonData.active){
                        chk.show();
                        chk.enable();  
                    }else{
                        chk.hide();
                        chk.disable(); 
                    }
                }   
            },
            scope: me
        });
	}
});
