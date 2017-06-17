/* This file has a lot of content but there are actually only a few places which are key and important to know 
These places will be marked with //@@ comments
*/

Ext.define('Rd.controller.cDesktop', {
    extend: 'Ext.app.Controller',
    views: ['desktop.pnlDesktop','desktop.winDesktopSettings', 'desktop.winPasswordChanger'],
    config: {
        urlWallpaper                : 'resources/images/wallpapers/2.jpg',
        urlSaveWallpaperSelection   : '/cake2/rd_cake/desktop/save_wallpaper_selection.json',
        urlChangePassword           : '/cake2/rd_cake/desktop/change_password.json'
    },
    models: ['mDesktopShortcut', 'mWallpaper'],
    requires: [
        'Ext.util.MixedCollection',
        'Ext.menu.Menu',
        'Ext.view.View', // dataview
        'Ext.window.Window',
        'Rd.library.lTaskBar'
    ],
    stores: ['sDesktopShortcuts', 'sWallpapers'],  //This must be pulled from the back-end
    refs: [
        {   ref: 'viewP',  selector: 'viewP',   xtype: 'viewP',    autoCreate: true},
        {   ref: 'pnlDesktop', selector: 'pnlDesktop', xtype: 'pnlDesktop' }

    ],
    //Stuff specific to the Desktop which was moved to the Desktop controller...
    activeWindowCls:    'ux-desktop-active-win',
    inactiveWindowCls:  'ux-desktop-inactive-win',
    lastActiveWindow:   null,

    xTickSize: 1,
    yTickSize: 1,

    desktopView: null,  //@@ This is a reference to the desktop which is a subclass of Ext.panel.panel

    init: function() {
        var me      = this;
        if (me.inited) {
            return;
        }
        me.inited = true; 
        me.contextMenu  = new Ext.menu.Menu(me.createDesktopMenu());
        me.windows      = new Ext.util.MixedCollection();
        this.control({
            'pnlDesktop': {
                'show': this.onDesktopMenu
            },
            'pnlStartMenu menuitem': {
                'click': me.onMenuItem
            },
            '#tabWallpaper dataview': {
                'select' : me.onSelectWallpaper
            },
            'winPasswordChanger #save': {
                'click' : me.onChangePassword
            }
        });
    },
    actionIndex: function(){
        var me      = this;
        var dd      = me.application.getDesktopData();
        var user    = dd.user.group+'::'+dd.user.username;
        var cls     = dd.user.cls;

        //@@ This is important to create the structure for the taskbar with it's accompanying start menu
        me.taskbarConfig = {
            quickStart: [
             //   { name: 'Logout', iconCls: 'exit', module: 'acc-win' }
            ],
            trayItems: [
               // { xtype: 'trayclock', flex: 1 } we are leaving the clock out - 2 much desktop
            ],
            application: me.application,        //Feed the taskbar with the application
            dock: 'bottom',
            startConfig: {
                title: user,
                iconCls: cls,
                glyph: Rd.config.icnBug,
                height: 380,
                menu: dd.menu,
                toolConfig: {
                    width: 120,
                    items: [
                        {   text:i18n('sLogout'),      glyph : Rd.config.icnPower,      handler: me.onLogout,   scope: me   },'-',
                        {   text:i18n('sSettings'),    glyph : Rd.config.icnSpanner ,   handler: me.onSettings, scope: me   },
                        {   text:i18n('sPassword'),    glyph : Rd.config.icnLock ,      handler: me.onPassword, scope: me   }
                    ]
                }
            }
        };

        //Build the configuration for the desktop....
       var dtConfig = {};

        me.windowMenu           = new Ext.menu.Menu(me.createWindowMenu());
        me.taskbar              = new Rd.library.lTaskBar(me.taskbarConfig);  //This is a very important part
        me.taskbar.windowMenu   = me.windowMenu;

       /// dtConfig.shortcuts = Ext.create('Ext.data.Store', {
        ///        model: 'NAC.model.modelShortcut',
        ///        data: dd.shortcuts
        ///    });
      
        //We first create a plain desktop  
        var dt = me.getView('desktop.pnlDesktop').create({url: dd.urlWallpaper}); 

        //@@ Now we add the tasbar with it's accompanying menu
        dt.addDocked(me.taskbar);
        var vp = me.getViewP();
        vp.removeAll(true);
        vp.add([dt]);

        //This one is to catch right click events on the desktop
        var el = dt.getEl();
        //console.log(el);
        el.on('contextmenu', me.onDesktopMenu, me);

        //Catch the item click on the dataview....
        dt.items.getAt(1).on('itemclick', me.onShortcutItemClick, me);

        //Add a reference to the desktop in the controller
        me.desktopView = dt;

        //Set the menu button text:
        var tb = me.getPnlDesktop();
        tb.down('#startButton').setText(i18n('sMenu'));
    },

    createDesktopMenu: function () {
        var me = this, ret = {
            items: me.contextMenuItems || []
        };

        if (ret.items.length) {
            ret.items.push('-');
        }

        ret.items.push(
                { text: i18n('sTile'), handler: me.tileWindows, scope: me, minWindows: 1 },
                { text: i18n('sCascade'), handler: me.cascadeWindows, scope: me, minWindows: 1 })

        return ret;
    },

    createWindowMenu: function () {
        var me = this;
        return {
            defaultAlign: 'br-tr',
            items: [
                { text: i18n('sRestore'), handler: me.onWindowMenuRestore, scope: me },
                { text: i18n('sMinimize'), handler: me.onWindowMenuMinimize, scope: me },
                { text: i18n('sMaximize'), handler: me.onWindowMenuMaximize, scope: me },
                '-',
                { text: i18n('sClose'), handler: me.onWindowMenuClose, scope: me }
            ],
            listeners: {
                beforeshow: me.onWindowMenuBeforeShow,
                hide: me.onWindowMenuHide,
                scope: me
            }
        };
    },

    //------------------------------------------------------
    // Event handler methods

    onDesktopMenu: function (e) {
        var me = this, menu = me.contextMenu;
        e.stopEvent();
        if (!menu.rendered) {
            menu.on('beforeshow', me.onDesktopMenuBeforeShow, me);
        }
        menu.showAt(e.getXY());
        menu.doConstrain();
    },

    onDesktopMenuBeforeShow: function (menu) {
        var me = this, count = me.windows.getCount();

        menu.items.each(function (item) {
            var min = item.minWindows || 0;
            item.setDisabled(count < min);
        });
    },

    //@@ This will call the application's runAction to source the controller and show it's window
    onShortcutItemClick: function (dataView, record) {
        var me = this;
        me.getPnlDesktop().setLoading(true); //Mask it
        //Call the controller's Index method
        var win = me.application.runAction(record.get('controller'),'Index');
    },

    onWindowClose: function(win) {
        var me = this;
        me.windows.remove(win);
        me.taskbar.removeTaskButton(win.taskButton);
        me.updateActiveWindow();
    },

    //------------------------------------------------------
    // Window context menu handlers

    onWindowMenuBeforeShow: function (menu) {
        var me = this;
        var items = menu.items.items, win = menu.theWin;
        me.menuWin = win; //Add this since me.windowMenu.theWin did now work correct with ExtJs 4.2.x
        items[0].setDisabled(win.maximized !== true && win.hidden !== true); // Restore
        items[1].setDisabled(win.minimized === true); // Minimize
        items[2].setDisabled(win.maximized === true || win.hidden === true); // Maximize
    },

    onWindowMenuClose: function (m) {
        var me = this;
        me.menuWin.close();
    },

    onWindowMenuHide: function (menu) {
        menu.theWin = null;
    },

    onWindowMenuMaximize: function () {
        var me = this;
        me.menuWin.maximize();
        me.menuWin.toFront();
    },

    onWindowMenuMinimize: function () {
        var me = this;
        me.menuWin.minimize();
    },

    onWindowMenuRestore: function () {
        var me = this;
        me.restoreWindow(me.menuWin);
    },

    //------------------------------------------------------
    // Dynamic (re)configuration methods
/*
    getWallpaper: function () {
        return this.wallpaper.wallpaper;
    },
*/

    setTickSize: function(xTickSize, yTickSize) {
        var me = this,
            xt = me.xTickSize = xTickSize,
            yt = me.yTickSize = (arguments.length > 1) ? yTickSize : xt;

        me.windows.each(function(win) {
            var dd = win.dd, resizer = win.resizer;
            dd.xTickSize = xt;
            dd.yTickSize = yt;
            resizer.widthIncrement = xt;
            resizer.heightIncrement = yt;
        });
    },
/*
    setWallpaper: function (wallpaper, stretch) {
        this.wallpaper.setWallpaper(wallpaper, stretch);
        return this;
    },
*/

    //------------------------------------------------------
    // Window management methods

    cascadeWindows: function() {
        var x = 0, y = 0,
            zmgr = this.getDesktopZIndexManager();

        zmgr.eachBottomUp(function(win) {
            if (win.isWindow && win.isVisible() && !win.maximized) {
                win.setPosition(x, y);
                x += 20;
                y += 20;
            }
        });
    },

    //@@ This is very important to have a base window set-up for all those windows which will be created through this method.
    createWindow: function(config, cls) {
        var me = this, win, cfg = Ext.applyIf(config || {}, {
                stateful: false,
                isWindow: true,
                constrainHeader: true,
                minimizable: true,
                maximizable: true
            });

        cls = cls || Ext.window.Window;
        //win = me.desktopView.add(new cls(cfg));
        var win = me.desktopView.add(Ext.create(cls,cfg));  //The preferred way to instantiate a class => Ext.create(Class, Config);

        me.windows.add(win);

        win.taskButton = me.taskbar.addTaskButton(win);
        win.animateTarget = win.taskButton.el;

        win.on({
            activate: me.updateActiveWindow,
            beforeshow: me.updateActiveWindow,
            deactivate: me.updateActiveWindow,
            minimize: me.minimizeWindow,
            destroy: me.onWindowClose,
            scope: me
        });

        win.on({
            boxready: function () {
                win.dd.xTickSize = me.xTickSize;
                win.dd.yTickSize = me.yTickSize;

                if (win.resizer) {
                    win.resizer.widthIncrement = me.xTickSize;
                    win.resizer.heightIncrement = me.yTickSize;
                }
            },
            single: true
        });

        // replace normal window close w/fadeOut animation:
        win.doClose = function ()  {
            win.doClose = Ext.emptyFn; // dblclick can call again...
            win.el.disableShadow();
            win.el.fadeOut({
                listeners: {
                    afteranimate: function () {
                        win.destroy();
                    }
                }
            });
        };

        me.getPnlDesktop().setLoading(false);
        return win;
    },

    //@@ This is very important to have a base window set-up for all those windows which will be created through this method.
    actionAdd: function(win) {
        var me = this;

        var id      = win.getId();
        var exist   = me.getWindow(id);

        //We do not need to add one
        if(exist){
            me.restoreWindow(win);
            return win;
        }

        me.desktopView.add(win);  //The preferred way to instantiate a class => Ext.create(Class, Config);
        me.windows.add(win);

        win.taskButton = me.taskbar.addTaskButton(win);
        win.animateTarget = win.taskButton.el;

        win.on({
            activate: me.updateActiveWindow,
            beforeshow: me.updateActiveWindow,
            deactivate: me.updateActiveWindow,
            minimize: me.minimizeWindow,
            destroy: me.onWindowClose,
            scope: me
        });

        win.on({
            boxready: function () {
                if(win.isDraggable()){
                    win.dd.xTickSize = me.xTickSize;
                    win.dd.yTickSize = me.yTickSize;
                }

                if (win.resizer) {
                    win.resizer.widthIncrement = me.xTickSize;
                    win.resizer.heightIncrement = me.yTickSize;
                }
            },
            single: true
        });

        // replace normal window close w/fadeOut animation:
        win.doClose = function ()  {
            win.doClose = Ext.emptyFn; // dblclick can call again...
            win.el.disableShadow();
            win.el.fadeOut({
                listeners: {
                    afteranimate: function () {
                        win.destroy();
                    }
                }
            });
        };
        me.restoreWindow(win);
        return win;
    },
    actionAlreadyExist: function(id){
        var me    = this;
        var win   = me.getWindow(id);

        //We do not need to add one
        if(win){
            me.restoreWindow(win);
            return win;
        }else{    
            return false;
        } 
    },

    getActiveWindow: function () {
        var win = null,
            zmgr = this.getDesktopZIndexManager();

        if (zmgr) {
            // We cannot rely on activate/deactive because that fires against non-Window
            // components in the stack.

            zmgr.eachTopDown(function (comp) {
                if (comp.isWindow && !comp.hidden) {
                    win = comp;
                    return false;
                }
                return true;
            });
        }

        return win;
    },

    getDesktopZIndexManager: function () {
        var windows = this.windows;
        // TODO - there has to be a better way to get this...
        return (windows.getCount() && windows.getAt(0).zIndexManager) || null;
    },

    getWindow: function(id) {
        return this.windows.get(id);
    },

    minimizeWindow: function(win) {
        win.minimized = true;
        win.hide();
    },

    restoreWindow: function (win) {
        var me = this;
        if (win.isVisible()) {
            win.restore();
            win.toFront();
        } else {
            win.show();
        }
        me.getPnlDesktop().setLoading(false);
        return win;
    },

    tileWindows: function() {
        var me = this, availWidth = me.desktopView.body.getWidth(true);
        var x = me.xTickSize, y = me.yTickSize, nextY = y;

        me.windows.each(function(win) {
            if (win.isVisible() && !win.maximized) {
                var w = win.el.getWidth();

                // Wrap to next row if we are not at the line start and this Window will
                // go off the end
                if (x > me.xTickSize && x + w > availWidth) {
                    x = me.xTickSize;
                    y = nextY;
                }

                win.setPosition(x, y);
                x += w + me.xTickSize;
                nextY = Math.max(nextY, y + win.el.getHeight() + me.yTickSize);
            }
        });
    },

    updateActiveWindow: function () {
        var me = this, activeWindow = me.getActiveWindow(), last = me.lastActiveWindow;
        if (activeWindow === last) {
            return;
        }

        if (last) {
            if (last.el.dom) {
                last.addCls(me.inactiveWindowCls);
                last.removeCls(me.activeWindowCls);
            }
            last.active = false;
        }

        me.lastActiveWindow = activeWindow;

        if (activeWindow) {
            activeWindow.addCls(me.activeWindowCls);
            activeWindow.removeCls(me.inactiveWindowCls);
            activeWindow.minimized = false;
            activeWindow.active = true;
        }

        me.taskbar.setActiveButton(activeWindow && activeWindow.taskButton);
    },
    onLogout: function(b){
        var me = this;
        b.up('panel').close();
        me.getViewP().removeAll(true);
        me.application.runAction('cLogin','Exit');
    },
    onSettings: function(b){
        var me = this;
        if(!me.application.runAction('cDesktop','AlreadyExist','winDesktopSettingsId')){
            var w = Ext.widget('winDesktopSettings',{
                id  :'winDesktopSettingsId'
            });
            me.application.runAction('cDesktop','Add',w);         
        }
       // me.setWallpaper('resources/images/wallpapers/7.jpg');
    },
    onMenuItem: function(memuItem){
        var me      = this;
        var itemId  = memuItem.getItemId();  
        //If the itemId of the menuitem is not set; it will reply with the id which
        //will be menuitem-<something>
        var m=itemId.match(/menuitem-/g);
        if(m == null){
            me.getPnlDesktop().setLoading(true); //Mask it
            me.application.runAction(itemId,'Index');
        }  
    },

    //Rd add on for clearing all windows
    closeAllWindows: function(){
        var me =this;
        if(me.windows != undefined){
            me.windows.each(function(win){
                win.close();
            })
        }
    },

    setWallpaper: function (wallpaper) {
        var me = this;
        var dt = me.getPnlDesktop();
        var wp = dt.down("compWallpaper");
        var imgEl;
        if (wp.rendered) {
            imgEl = wp.el.dom.firstChild;
            imgEl.src = wallpaper;
            Ext.fly(imgEl).setStyle({
                width: '100%',
               height: '100%'
            }).show();
        }
    },

    onSelectWallpaper: function(a, record, c){
        var me = this;
        var wp = record.get('r_dir')+record.get('file');
        
        //Update this user's preferences:
        Ext.Ajax.request({
            url: me.getUrlSaveWallpaperSelection(),
            method: 'GET',
            params: {wallpaper: wp},
            success: function(response){
                var jsonData    = Ext.JSON.decode(response.responseText);
                //Only change if success == true
                if(jsonData.success == true){
                    me.setWallpaper(wp); 
                    Ext.ux.Toaster.msg(
                        i18n('sWalpaper_changed'),
                        i18n('sWalpaper_changed_fine'),
                        Ext.ux.Constants.clsInfo,
                        Ext.ux.Constants.msgInfo
                    );
                }

                if(jsonData.success == false){ 
                    Ext.ux.Toaster.msg(
                        i18n('sError_encountered'),
                        jsonData.message.message,
                        Ext.ux.Constants.clsWarn,
                        Ext.ux.Constants.msgWarn
                    );
                }
            },
            scope: me
        });
    },
    onPassword: function(b){
        var me = this;
        if(!me.application.runAction('cDesktop','AlreadyExist','winPasswordChangerId')){
            var w = Ext.widget('winPasswordChanger',{
                id  :'winPasswordChangerId'
            });
            me.application.runAction('cDesktop','Add',w);         
        }
       // me.setWallpaper('resources/images/wallpapers/7.jpg');
    },
    onChangePassword: function(button){
        var me      = this;
        var form    = button.up('form');
        var win     = button.up('window');

        form.submit({
            clientValidation: true,
            url: me.getUrlChangePassword(),
            success: function(form, action) {
                //Important to update the token for the next requests
                var token = action.result.data.token; 
                Ext.Ajax.setExtraParams({token : token});
                win.close();
                Ext.ux.Toaster.msg(
                    i18n('sItem_updated'),
                    i18n('sItem_updated_fine'),
                    Ext.ux.Constants.clsInfo,
                    Ext.ux.Constants.msgInfo
                );
            },
            failure: Ext.ux.formFail
        });
    }

});
