/* 
This controller is the starting point. 
It checks if a user is logged in
If not it will load the Login controller and call it's Index action
If they are logged in it will load the Desktop controller and call it's Index action
*/

Ext.define('Rd.controller.cStartup', {
    extend: 'Ext.app.Controller',
    config: {
        urlLocalizedStrings:    '/cake2/rd_cake/phrase_values/get_language_strings.json',
        urlCheckToken:          '/cake2/rd_cake/desktop/check_token.json'
    },
   actionIndex: function(){
        //Declare some scoped variables
        var me          = this;

        //This is to determine the language before we log on
        var language    = '';
        if(Ext.state.Manager.get('rdLanguage') != ''){
            language =  Ext.state.Manager.get('rdLanguage');
        }

        //Fetch the languages first before trying to check whether we are authenticated or not
        Ext.Ajax.request({
            url: me.getUrlLocalizedStrings(),
            params: {
                language: language
            },
            method: 'GET',
            success: function(response){
                var jsonData = Ext.JSON.decode(response.responseText);
                //Set the phrases
                if(jsonData.success){

                    me.application.setLanguages(jsonData.data.languages);
                    me.application.setSelLanguage(jsonData.data.selLanguage);

                    //Set the ajax extra params
                    Local.localizedStrings      = jsonData.data.phrases;

                    //Set the sel_language for the login screen
                    //The extraParams should only be set in three places. 
                    // 1.) Here BEFORE login in order to configue the php phrases for the login screen's POST return values
                    Ext.Ajax.setExtraParams({'sel_language': me.application.getSelLanguage()});

                    //After we have our language, we can check if we have an existing token to continuie with
                    me.checkToken();
                }
            }
        });
    },
    
    checkToken: function(){

        //Check if we have an existing token and try to get user info for this token
        var me = this;
        var token = Ext.util.Cookies.get("Token"); //No token?
        if(token == null){
            me.application.runAction('cLogin','Index');
        }else{

            //Check if the back-end likes our token
            Ext.Ajax.request({
                url: me.getUrlCheckToken(),
                params: {
                    token: token
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
                        me.application.setDesktopData(jsonData.data);
                        me.application.runAction('cDesktop','Index');

                    }else{

                        //Token failed - Clear it first
                        me.application.runAction('cLogin','Exit');
                    }
                }
            });
        }
    }
});
