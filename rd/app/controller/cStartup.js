/* 
This controller is the starting point. 
It checks if a user is logged in
If not it will load the Login controller and call it's Index action
If they are logged in it will load the Dashboard controller and call it's Index action
*/

Ext.define('Rd.controller.cStartup', {
    extend: 'Ext.app.Controller',
    config: {
        urlCheckToken:          '/cake3/rd_cake/dashboard/check_token.json'
    },
   actionIndex: function(){
        //Declare some scoped variables
        var me          = this;     
        me.application.setSelLanguage(Rd.config.selLanguage); //We hardcode the language since it is not very efficient to store the phrases in DB
        Ext.Ajax.setExtraParams({'sel_language': me.application.getSelLanguage()});
        me.checkToken();
    },
    
    checkToken: function(){

        //Check if we have an existing token and try to get user info for this token
        var me = this;
        var token = Ext.util.Cookies.get("Token"); //No token?
        if(token == null){
            me.application.runAction('cLogin','Index');
        }else{

            var screen_width = Ext.getBody().getViewSize().width;
            var auto_compact = false;
            if(screen_width < 1000){ //Smaller screens -> Auto compact
                auto_compact = true;
            }
            
            //Check if the back-end likes our token
            Ext.Ajax.request({
                url: me.getUrlCheckToken(),
                params: {
                    token       : token,
                    auto_compact: auto_compact
                },
                method: 'GET',
                success: function(response){
                    var jsonData = Ext.JSON.decode(response.responseText);
                    //Set the phrases
                    if(jsonData.success){ //Token is ok, let us continiue

                        //Apply phrases to the cusom VTypes to include language:
                        me.application.applyVtypes();
   
                        //Set extra params to token's value
                        //This is the second place of three where we set the extraParams. The token is valid 3rt blace in cLogin.js
                        Ext.Ajax.setExtraParams({'token': token,'sel_language': me.application.getSelLanguage()});
                        me.application.setDashboardData(jsonData.data);
                        me.application.runAction('cDashboard','Index');

                    }else{

                        //Token failed - Clear it first
                        me.application.runAction('cLogin','Exit');
                    }
                }
            });
        }
    }
});
