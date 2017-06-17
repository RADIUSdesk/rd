Ext.define('Rd.view.accessProviders.vcAccessProviderDetails', {
    extend  : 'Ext.app.ViewController',
    alias   : 'controller.vcAccessProviderDetails',
    init: function() {
    
    },  
    onChkWlActiveChange: function(chk){
        var me       = this;
        var pnl    = chk.up('panel');
         if(chk.getValue()){ 
         
            pnl.down('#txtWlHeader').setDisabled(false);
            pnl.down('#clrWlHeaderBg').setDisabled(false);
            pnl.down('#clrWlHeaderFg').setDisabled(false); 
            pnl.down('#txtWlFooter').setDisabled(false); 
            pnl.down('#chkWlImgActive').setDisabled(false); 
            pnl.down('#flWlImgFileUpload').setDisabled(false);
          
         }else{
         
            pnl.down('#txtWlHeader').setDisabled(true);
            pnl.down('#clrWlHeaderBg').setDisabled(true);
            pnl.down('#clrWlHeaderFg').setDisabled(true); 
            pnl.down('#txtWlFooter').setDisabled(true); 
            pnl.down('#chkWlImgActive').setDisabled(true); 
            pnl.down('#flWlImgFileUpload').setDisabled(true);
         }  
    },
    onChkWlImgActiveChange: function(chk){
        var me      = this;
        var pnl     = chk.up('panel');
        var value   = chk.getValue();
        if(value){   
            pnl.down('#flWlImgFileUpload').setDisabled(false);        
        }else{
            pnl.down('#flWlImgFileUpload').setDisabled(true);
        }
    }
});
