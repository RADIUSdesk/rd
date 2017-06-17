Ext.define('Rd.view.meshes.vcMeshNodeGeneric', {
    extend  : 'Ext.app.ViewController',
    alias   : 'controller.vcMeshNodeGeneric',
    config : {
        urlAdvancedSettingsForModel : '/cake2/rd_cake/meshes/advanced_settings_for_model.json'
    },
    init: function() {
        var me = this;
    },
     
    onCmbHardwareOptionsChange: function(cmb){
		var me      = this;
        var form    = cmb.up('form');
        var key     = form.down('#key');
        var voip    = form.down('#tabVoip');
        var adv     = form.down('#tabVoipAdvanced');
		var radio	= form.down('#tabRadio');
        var val     = cmb.getValue();
        
        var r_count = 1;  
        var record  = cmb.getSelection();
        if(record != null){
            r_count =record.get('radios');
        }

        var tabAdvRadio1    = form.down('#tabAdvWifiRadio1');
        var window          = cmb.up('window');
        
        if(window.getItemId() != 'winMeshEditNodeMain'){
             var params     = {model:val};
        }else{
            var params      = {model:val,node_id:window.nodeId};
        }
             
        //Load the advanced settings for this hardware...
        form.load({
            url     : me.getUrlAdvancedSettingsForModel(), 
            method  : 'GET',
            params  : params,
            success : function(a,b,c){

                if(b.result.data.device_type == 'ac'){
                    //Determine the 5G radio
                    if(b.result.data.radio0_band == 5){
                        me.addAcHtMode(0);
                        me.removeAcHtMode(1);
                    }
                    if(b.result.data.radio1_band == 5){
                        me.addAcHtMode(1);
                        me.removeAcHtMode(0);
                    }
                }else{
                    me.removeAcHtMode(0);
                    me.removeAcHtMode(1);
                }   
            },
            listeners       : {
                actioncomplete  : function(){
                    console.log("Action is complete");
                }
            } 
        });
        
        if(r_count == 2){
	        radio.setDisabled(false);	
	        radio.tab.show();
            tabAdvRadio1.setDisabled(false);
            tabAdvRadio1.tab.show();
        }else{
	        radio.setDisabled(true);
	        radio.tab.hide();
            tabAdvRadio1.setDisabled(true);
            tabAdvRadio1.tab.hide();
        }
        
        
	},
	onChkRadioEnableChange: function(chk){
		var me 		= this;
		var fs    	= chk.up('panel');//fs
        var value   = chk.getValue();
		var fields_voip = Ext.ComponentQuery.query('field',fs);
		Ext.Array.forEach(fields_voip,function(item){
			if(item != chk){
				item.setDisabled(!value);
			}
		});
	},
	onChkRadioMeshChange: function(chk){
		var me 		= this;
		var fs    	= chk.up('panel');//fs
		var t_band	= fs.down('#radio24');
		var n_t		= fs.down('#numRadioTwoChan');
		var n_v		= fs.down('#numRadioFiveChan');

		if(chk.getValue() == false){
			if(t_band.getValue()){	//2.4 selected... show it
				n_t.setVisible(true);
				n_t.setDisabled(false);
				n_v.setVisible(false);
				n_v.setDisabled(true);
			}else{
				n_t.setVisible(false);
				n_t.setDisabled(true);
				n_v.setVisible(true);
				n_v.setDisabled(false);
			}
		}else{
			//hide and disable both
			n_t.setVisible(false);
			n_t.setDisabled(true);
			n_v.setVisible(false);
			n_v.setDisabled(true);
		}		
	},
    onRadio_0_BandChange: function(rb){
        var me      = this;
        var band    = rb.getValue();      
        var fs      = rb.up('panel');//fs   
        var mesh    = fs.down('#chkRadio0Mesh');
        var t_band	= fs.down('#radio24');
        var n_t		= fs.down('#numRadioTwoChan');
		var n_v		= fs.down('#numRadioFiveChan');
        if(mesh.getValue() == false){
            if(t_band.getValue()){	//2.4 selected... show it
				n_t.setVisible(true);
				n_t.setDisabled(false);
				n_v.setVisible(false);
				n_v.setDisabled(true);
			}else{
				n_t.setVisible(false);
				n_t.setDisabled(true);
				n_v.setVisible(true);
				n_v.setDisabled(false);
			}
        }
    },
    onRadio_1_BandChange: function(rb){
        var me      = this;
        var band    = rb.getValue();      
        var fs      = rb.up('panel');//fs    
        var mesh    = fs.down('#chkRadio1Mesh');
        var t_band	= fs.down('#radio24');
        var n_t		= fs.down('#numRadioTwoChan');
		var n_v		= fs.down('#numRadioFiveChan');

        if(mesh.getValue() == false){
            if(t_band.getValue()){	//2.4 selected... show it
				n_t.setVisible(true);
				n_t.setDisabled(false);
				n_v.setVisible(false);
				n_v.setDisabled(true);
			}else{
				n_t.setVisible(false);
				n_t.setDisabled(true);
				n_v.setVisible(true);
				n_v.setDisabled(false);
			}
        }
    },
    addAcHtMode: function(radio_number){
        var me = this;
        var w  = me.getView();
        if(radio_number == 0){
            w.down('#radio0_htmode_vht20').setVisible(true);
            w.down('#radio0_htmode_vht40').setVisible(true);
            w.down('#radio0_htmode_vht80').setVisible(true);
            w.down('#radio0_htmode_vht160').setVisible(true);
        }
        
        if(radio_number == 1){
            w.down('#radio1_htmode_vht20').setVisible(true);
            w.down('#radio1_htmode_vht40').setVisible(true);
            w.down('#radio1_htmode_vht80').setVisible(true);
            w.down('#radio1_htmode_vht160').setVisible(true);
        }
    },
    removeAcHtMode: function(radio_number){
        var me = this;
        var w  = me.getView();
        if(radio_number == 0){
            w.down('#radio0_htmode_vht20').setVisible(false);
            w.down('#radio0_htmode_vht40').setVisible(false);
            w.down('#radio0_htmode_vht80').setVisible(false);
            w.down('#radio0_htmode_vht160').setVisible(false);
        }
        
        if(radio_number == 1){
            w.down('#radio1_htmode_vht20').setVisible(false);
            w.down('#radio1_htmode_vht40').setVisible(false);
            w.down('#radio1_htmode_vht80').setVisible(false);
            w.down('#radio1_htmode_vht160').setVisible(false);
        }
    }
});
