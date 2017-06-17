/**
 * The main application class. An instance of this class is created by app.js when it
 * calls Ext.application(). This is the ideal place to handle application launch and
 * initialization details.
 */
Ext.define('Rd.Application', {
    extend: 'Ext.app.Application',
    
    name: 'Rd',

    controllers: [
        'cStartup',
        'cLogin'
    ],
    
    desktopData : null,  //Data on how the desktop will look like which will be returned after login
    languages   : null,
    selLanguage : null,
    autoCreateViewport: true,
    init: function() {
        var me = this;
        me.addConstants();
        me.addUx();
        Ext.tip.QuickTipManager.init();
        Ext.state.Manager.setProvider(Ext.create('Ext.state.LocalStorageProvider'));
        me.applyVtypes();
    },
    
    launch: function () {
        // TODO - Launch the application
        var me   = this;
        me.runAction('cStartup','Index');
    },
    
    runAction:function(controllerName, actionName,a,b){
        var me          = this;
        var controller  = me.getController(controllerName);
        controller.init(me); //Initialize the contoller
        return controller['action'+actionName](a,b);
    },
    
    setDesktopData: function(data){
        var me          = this;
        me.desktopData  = data;
    },

    getDesktopData: function(){
        var me          = this;
        return me.desktopData;
    },

    setLanguages: function(data){
        var me = this;
        me.languages = data;
    },
    getLanguages: function(data){
        var me = this;
        return me.languages;
    },

    setSelLanguage: function(data){
        var me =this;
        me.selLanguage = data;
    },
    getSelLanguage: function(data){
        var me = this;
        return me.selLanguage;
    },
    
    applyVtypes: function(){

        Ext.apply(Ext.form.field.VTypes, {

            //__IP Address__
            IPAddress:  function(v) {
                return (/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/).test(v);
            },
            IPAddressText:  i18n('sExample') + ': 192.168.1.1',
            IPAddressMask: /[\d\.]/i,
         
            //__ MAC Address __
            MacAddress: function(v) {
                return (/^([a-fA-F0-9]{2}-){5}[a-fA-F0-9]{2}$/).test(v);
            },
            MacAddressMask: /[a-fA-F0-9\-]/,
            MacAddressText: i18n('sExample') + ': 01-23-45-67-89-AB',
         
            //__ Hostname __
            DnsName: function(v) {
                return (/^(([a-zA-Z]|[a-zA-Z][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z]|[A-Za-z][A-Za-z0-9\-]*[A-Za-z0-9])$/).test(v);
            },
            DnsNameText: i18n('This is not a valid DNS name'),
            
            //__ Password match __
            PasswordMatch: function(a,b){
                var me  = this;
                var f   = b.up('form');
                var pwd = f.down('#password');
                if(pwd != null){
                    if(a != pwd.getValue()){
                        return false;
                    }else{
                        return true;
                    }   
                }
                return true;
            },
            PasswordMatchText: i18n('sPasswords_does_not_match'),

            //__ Numeric __
            Numeric : function(){
				  var objRegExp  =  /[0-9]/;
				  return function(strValue){
					  //check for numeric characters
					  return objRegExp.test(strValue);
				  }
		    }(),
		    NumericText: 'Only numbers are allowed',
            NumericMask: /[0-9]/

            //__ Voucher batch required __

        });
    },
    
    addConstants: function(){

        Ext.namespace('Rd.config');
        //Declare some constants
        Ext.namespace('Rd').config = {
            //buttonMargin    : '0 20 40 0',
            buttonMargin    : '10 15 10 15',
            fieldMargin     : 15,
            labelWidth      : 150,
            maxWidth        : 400,
			numWidth		: 30,
            panelGrey       : '#e5e6ef',
            'icnSignIn'     : 'xf090@FontAwesome',
            'icnSignOut'    : 'xf08b@FontAwesome',
            'icnLock'       : 'xf023@FontAwesome',
            'icnYes'        : 'xf00c@FontAwesome',
            'icnMenu'       : 'xf0c9@FontAwesome',
            'icnInfo'       : 'xf129@FontAwesome',
            'icnPower'      : 'xf011@FontAwesome',
            'icnSpanner'    : 'xf0ad@FontAwesome',
            'icnHome'       : 'xf015@FontAwesome',
            'icnDynamic'    : 'xf0d0@FontAwesome',
            'icnVoucher'    : 'xf145@FontAwesome',
            'icnNext'       : 'xf061@FontAwesome',
            'icnBack'       : 'xf060@FontAwesome',
            'icnReload'     : 'xf021@FontAwesome',
            'icnAdd'        : 'xf067@FontAwesome',
            'icnEdit'       : 'xf040@FontAwesome',
            'icnDelete'     : 'xf1f8@FontAwesome',
            'icnPdf'        : 'xf1c1@FontAwesome',
            'icnCsv'        : 'xf1c3@FontAwesome',
            'icnRadius'     : 'xf10c@FontAwesome',
            'icnLight'      : 'xf204@FontAwesome',
            'icnNote'       : 'xf08d@FontAwesome',
            'icnKey'        : 'xf084@FontAwesome',
            'icnRealm'      : 'xf17d@FontAwesome',
            'icnNas'        : 'xf1cb@FontAwesome',
            'icnTag'        : 'xf02b@FontAwesome',
            'icnProfile'    : 'xf1b3@FontAwesome',
            'icnComponent'  : 'xf12e@FontAwesome',
            'icnActivity'   : 'xf0e7@FontAwesome',
            'icnLog'        : 'xf044@FontAwesome',
            'icnTranslate'  : 'xf0ac@FontAwesome',
            'icnConfigure'  : 'xf0ad@FontAwesome',
            'icnUser'       : 'xf007@FontAwesome',
            'icnDevice'     : 'xf10a@FontAwesome',
            'icnMesh'       : 'xf20e@FontAwesome',
            'icnBug'        : 'xf188@FontAwesome',
            'icnMobile'     : 'xf10b@FontAwesome',
            'icnDesktop'    : 'xf108@FontAwesome',
            'icnView'       : 'xf002@FontAwesome',
            'icnMeta'       : 'xf0cb@FontAwesome',
            'icnMap'        : 'xf041@FontAwesome',
            'icnConnect'    : 'xf0c1@FontAwesome',
            'icnGraph'      : 'xf080@FontAwesome',
            'icnKick'       : 'xf1e6@FontAwesome',
            'icnClose'      : 'xf00d@FontAwesome',
            'icnFinance'    : 'xf09d@FontAwesome',
            'icnOnlineShop' : 'xf07a@FontAwesome',
            'icnEmail'      : 'xf0e0@FontAwesome',
            'icnAttach'     : 'xf0c6@FontAwesome',
            'icnCut'        : 'xf0c4@FontAwesome',
            'icnTopUp'      : 'xf0f4@FontAwesome',
            'icnSubtract'   : 'xf068@FontAwesome',
            'icnWatch'      : 'xf017@FontAwesome',
            'icnStar'       : 'xf005@FontAwesome',
            'icnGrid'       : 'xf00a@FontAwesome',
            'icnFacebook'   : 'xf082@FontAwesome',
            'icnGoogle'     : 'xf1a0@FontAwesome',
            'icnTwitter'    : 'xf099@FontAwesome',
            'icnWifi'       : 'xf012@FontAwesome',
            'icnIP'         : 'xf1c0@FontAwesome',
            'icnThumbUp'    : 'xf087@FontAwesome',
            'icnThumbDown'  : 'xf088@FontAwesome',
            'icnCPU'        : 'xf085@FontAwesome',
            'icnCamera'     : 'xf030@FontAwesome',
            'icnFolder'     : 'xf07b@FontAwesome',
            'icnSnapshot'   : 'xf030@FontAwesome',
            'icnTime'       : 'xf017@FontAwesome',
            'icnExpand'     : 'xf065@FontAwesome',
            'icnOperator'   : 'xf19d@FontAwesome',
            'icnRadiusClient': 'xf1ce@FontAwesome',
            'icnHeart'      : 'xf004@FontAwesome',
            'icnGears'      : 'xf085@FontAwesome',
            'icnSite'       : 'xf19c@FontAwesome',
            'icnSsid'       : 'xf1eb@FontAwesome',
            'icnOverview'   : 'xf015@FontAwesome',
            'icnData'       : 'xf1c0@FontAwesome',
            'icnAccessPoint': 'xf0b2@FontAwesome',
            'icnChain'      : 'xf0c1@FontAwesome',
            'icnChainBroken': 'xf127@FontAwesome',
            'icnNetwork'    : 'xf1e0@FontAwesome',
            'icnAngleLeft'  : 'xf100@FontAwesome',
            'icnAngleRight' : 'xf101@FontAwesome',
            'icnRedirect'   : 'xf074@FontAwesome',
            'icnClear'      : 'xf0c4@FontAwesome',
            'icnStart'      : 'xf05d@FontAwesome',
            'icnStop'       : 'xf00d@FontAwesome',
            'icnInfo'       : 'xf033@FontAwesome',
            'icnCopy'       : 'xf0c5@FontAwesome'
        };
    },
    addUx: function(){

        Ext.namespace('Ext.ux'); 

        //-- Constants -->

        Ext.ux.Constants = {
                msgWarn : 4000, //Timeout values for toaster message
                msgInfo : 2000,
                msgError: 8000,
                clsWarn : 'warn', //Classes for the message types
                clsInfo : 'info',
                clsError: 'error'
        }
        //<-- Constants --

        //--- Toaster --->

        //We create a toaster to inform people of our actions
        //This utility can be called the following ways:
        //Ext.ux.Toaster.msg('Buiding added','Adding went fine');
        //Ext.ux.Toaster.msg('Color Selected', 'You choose red',Ext.ux.Constants.clsInfo );
        //Ext.ux.Toaster.msg('Color Selected', 'You choose red',Ext.ux.Constants.clsError, Ext.ux.Constants.msgError );
        //The 3rd and 4th arguments are optional
                
        Ext.ux.Toaster = function(){
            var msgCt;
            var defaultTimeout = 500;
            function createBox(t, s){
                    return '<div class="msg">'+
                                '<h3>' + t + '</h3>'+
                                '<p>'  + s + '</p>' +
                            '</div>';
            }
            //This is a new thing, but valid. JS allows you to return an object, so when we call
            //Ext.toaster.msg('a','b'); we do in fact to the following by chaining
            // var t = Ext.toaster
            // t.msg('a','b');
            //Note that the context in which variables are called in the return object is in the falled function
            //This is why we can refer to msgCt as a local variable defined in this closure.
            return {
                msg : function(title, content, type, timeout){
                    //So this first part check if there is already a msgCt element, if not adds it to the document
                    //Using the Ext.DomHelper which will add a 'div' element by default, now with msg-div as id
                    //The original specified the return of an Ext.Element (true) but it works fine by returning a
                    //dom element (false)
                    if(!msgCt){
                        msgCt = Ext.DomHelper.insertFirst(document.body, {id:'msg-div'}, false);
                    }
                    //Here the 'true' is important to get Ext.Element to do animation on
                    var m = Ext.DomHelper.append(msgCt, createBox(title, content), true);
                    //Add a class if required
                    if(type !== undefined){
                        m.addCls(type);
                    }
                    //Change the timeout if required
                    if(timeout === undefined){
                        timeout = defaultTimeout;   
                    }
                    m.on('click',function(){ //Allow the user to destroy the message (that't typically their natural reaction)
                        m.destroy();
                    })
                    //Here we hide the newly created element first
                    m.hide();  
                    //Then we slide it in (default 2000ms), chained by a ghost effect of 500ms that will remove the message
                    m.slideIn('t').ghost("t", { delay: timeout, remove: true}); 
                }
            };
        }();

        //<-- Toaster --

        //--- Form Fail message --->
        Ext.ux.formFail = function(form,action){
            switch (action.failureType) {
            case Ext.form.action.Action.CLIENT_INVALID:
                Ext.ux.Toaster.msg(
                    i18n('sFailure'),
                    i18n('Form fields may not be submitted with invalid values'),
                    Ext.ux.Constants.clsWarn,
                    Ext.ux.Constants.msgWarn
                );
            break;
            case Ext.form.action.Action.CONNECT_FAILURE:
                Ext.ux.Toaster.msg(
                    i18n('sFailure'),
                    i18n('Ajax communication failed'),
                    Ext.ux.Constants.clsWarn,
                    Ext.ux.Constants.msgWarn
                );
            break;
            case Ext.form.action.Action.SERVER_INVALID:
                Ext.ux.Toaster.msg(
                    i18n('sFailure'),
                    action.result.message.message,
                    Ext.ux.Constants.clsWarn,
                    Ext.ux.Constants.msgWarn
                );
            }
        }

        //<-- Form Fail message

        //-- Format to a readable unit --->
        Ext.ux.bytesToHuman = function (fileSizeInBytes) {

            if((fileSizeInBytes == 0)||(fileSizeInBytes == null)){
                return '0 kb';
            }
            var i = -1;
            var byteUnits = [' kb', ' Mb', ' Gb', ' Tb', 'Pb', 'Eb', 'Zb', 'Yb'];
            do {
                fileSizeInBytes = fileSizeInBytes / 1024;
                i++;
            } while (fileSizeInBytes >= 1024);

            return Math.max(fileSizeInBytes, 0.1).toFixed(1) + byteUnits[i];
        };

        //-- Format to a readable time -->
        Ext.ux.secondsToHuman = function(seconds) {
            var numdays     = Math.floor(seconds / 86400); 
            var numhours    = Math.floor((seconds % 86400) / 3600);
            var numminutes  = Math.floor(((seconds  % 86400) % 3600) / 60);
            var numseconds  = ((seconds % 86400) % 3600) % 60;
            return  padDigits(numdays,2) + ":" + padDigits(numhours,2) + ":" + padDigits(numminutes,2) + ":" + padDigits(numseconds,2);

            function padDigits(number, digits) {
                return Array(Math.max(digits - String(number).length + 1, 0)).join(0) + number;
            }
        }

        //-- Format to a readable amount -->
        Ext.ux.centsToHuman = function(cents) {
            return (cents/100).toFixed(2); 
        }

    },
    

    onAppUpdate: function () {
        Ext.Msg.confirm('Application Update', 'This application has an update, reload?',
            function (choice) {
                if (choice === 'yes') {
                    window.location.reload();
                }
            }
        );
    }
});
