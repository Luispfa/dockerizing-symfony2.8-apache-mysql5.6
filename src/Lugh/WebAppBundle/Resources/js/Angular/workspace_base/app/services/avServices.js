
(function () {
    'use strict';

    angular.module('appServices'
            ).factory('liveWindowManager', ['VWSWindow', '$localStorage',
            function (VWSWindow, $localstorage) {
            var liveWindowManager = {
                instances: {},
                //instances: [],
                options: {
                    width : 1032,
                    height : 640,
                    toolbar : 0,
                    location : 0,
                    directories : 0,
                    status : 0,
                    menubar : 0,
                    scrollbars : 0,
                    resizable : 1,
                    url : "",
                    name : "name",
                    target: ""
                },
                
                _create: function(element){		
                    // remember this instance
                    //this.instances.push(new VWSWindow(element));
                    this.instances[element] = new VWSWindow(element);
                },

                _init: function(){

                },
                _getInstance : function (element) {
                    /*var vwsw;
                    this.instances.forEach(function(instance) {
                        if(instance.element == element)
                            vwsw = instance;
                    });
                    return vwsw;*/

                    if(this.instances && Object.keys(this.instances).length > 0){
                        return this.instances[element];
                    }
                    throw new Exception('');
                    return null;
                },
                _getOtherInstances: function(element){
                    return $.grep(this.instances, function(el){
                        return el !== element;
                    });
                },
                create: function(element){
                  this._create(element);
                },
                open: function(element, target, url){
                    // trigger beforeopen event.  if beforeopen returns false,
                    // prevent bail out of this method. 
                    /*if( this._trigger("beforeopen") === false ){
                        return null;
                    }*/

                    // more open related code here
                    this._setOption('target', target);
                    this._setOption('url', url);
                    var w = this._openWindow(element);
                    // trigger open event
                    //this._trigger("open");

                    return w;
                },
                
                getVWSWindow: function(element) {
                    return this._getInstance(element);
                },
                
                /*destroy: function(){
                    // remove this instance from $.ui.mywidget.instances
                    var instance = this._getInstance,
                    position = $.inArray(instance, this.instances);

                    // if this instance was found, splice it off
                    if(position > -1){
                        this.instances.splice(position, 1);
                    }

                    // call the original destroy method since we overwrote it
                    //$.Widget.prototype.destroy.call( this );
                },*/
                destroy: function(element){
                    if(this.instances && this.instances[element]){
                        delete this.instances[element];
                        return true;
                    }
                    return false;
                },
                setFocus: function(instance)
                {
                    instance.window.focus();
                },
                
                _setOption: function(key, value){
                    this.options[key] = value;
                },
                
                _openWindow: function (element){

                    var instance = this._getInstance(element);

                    if($localstorage['Config.Av.opentargetmode'] == 1){
                        instance.window = window.open(this.options["url"],this.options["target"]);
                        //var node = document.querySelector('a[live-button="74aa3939-9842-11e5-987b-00221952a7e5"]');
                    }else {
                        var w = this.options["width"];
                        var h = this.options["height"];
                        var y = (screen.height-h)/2;
                        var x = (screen.width-w)/2;
                        var opts="toolbar="+this.options["toolbar"]+",location="+this.options["location"]+",directories="+this.options["directories"]+", status="+this.options["status"]+", menubar="+this.options["menubar"]+",scrollbars="+this.options["scrollbars"]+",resizable="+this.options["resizable"]+",innerHeight="+h+",height="+h+",innerWidth="+w+",width="+w+",top="+y+",left="+x;

                        instance.window = window.open(this.options["url"],this.options["target"],opts);
                    }

                    //instance.window.focus();
                    return instance.window;
                }

            };
            return liveWindowManager;

    }]).factory('VWSWindow', [ 
        function() {
            function VWSWindow(elem){
                this.element = elem;
            }
            VWSWindow.prototype.element = null;
            VWSWindow.prototype.window = null;
            
            return VWSWindow;
        }
    ]).service('liveService', ['liveWindowManager', '$http','$q', 'logger','$rootScope','intervalService',
        function (liveWindowManager, $http, $q, logger, $rootScope, intervalService) {
            var _credentials;
            var interval        = null;
            var liveInterval    = null;
            var liveCount       = 0;


            var _getCredentials = function()
            {
                var deferred = $q.defer();
                if (_credentials === undefined)
                {
                    //$http.get(ApiBase + '/av/credentials')
                    $http.get(ApiBase + '/av/streaming')
                    .success(function(credentials){
                        _credentials = credentials;
                        deferred.resolve(credentials);
                    })
                    .error(function(data, status, headers, config) {
                        deferred.reject();
                    });
                }
                else
                {
                    deferred.resolve(_credentials);
                }
                
                return deferred.promise;
            }

            this.createInterval = function() {
                var self = this;
                if (interval == null){
                    interval = intervalService.subscribe('Junta');
                    liveInterval = $rootScope.$on(interval,function(event, args) { 
                        self.getStatus(args.live_enabled);
                    }); 
                }
                liveCount++;
                
            }

            this.removeInterval = function() {
                liveCount--;
                if(liveCount <= 0){
                    intervalService.unsubscribe('Junta');
                    if(liveInterval !== null)  liveInterval();
                    interval        = null;
                    liveInterval    = null;
                    
                }
            }

            
            this.openWindow = function (element, target) {
                
                var VWSWindow = liveWindowManager.getVWSWindow(element) ;
                if (VWSWindow && VWSWindow.window !== undefined && VWSWindow.window !== null && VWSWindow.window.closed === false)
                {
                    liveWindowManager.setFocus(VWSWindow);
                }
                else
                {
                    _getCredentials().then(function(credentials){
                        //var mapForm = document.createElement("form");
                        //mapForm.action = credentials[element]['url'];
                        //mapForm.method = "POST"; // or "post" if appropriate
                        //mapForm.target = target;
                        

                        
                        //var data = credentials[element]['data'];
                        //for(var key in data){
                        //    var value = data[key];
                        //    var mapInput = document.createElement("input");
                        //    mapInput.type = "hidden";
                        //    mapInput.name = key;
                        //    mapInput.value = value;
                        //    mapForm.appendChild(mapInput);
                        //}
                        //document.body.appendChild(mapForm);
                        try{
                            var map = liveWindowManager.open(element, target, credentials[element]['url']);
                        }catch(e){
                            console.error('Error opening window: '+e);
                        }
                        
                        if (map) {
                            //mapForm.submit();
                        } else {
                            alert('You must allow popups for this map to work.');
                        }
                        //document.body.removeChild(mapForm);
                        
                    }).catch(function(e){
                        logger.logError("id00201_app:logger:server-error")
                    });
                }
            };
            this.create = function (element) {
                liveWindowManager.create(element);
            };
            
            this.getStatus = function (enabled) {
                $http.get(ApiBase + '/av/lives')
                .success(function(data) {
                    if(typeof data.success !== 'undefined'){
                        var lives = data.success;
                        for(var key in lives){
                            var live = lives[key];
                            if (live && enabled == true && live.enabled == true && (live.session_live_status == "on" || live.session_od_status == 'published'))
                            {
                                $rootScope.$broadcast('avlive'+live['id'], true);
                            }
                            else
                            {
                                $rootScope.$broadcast('avlive'+live['id'], false);
                            }  
                        }
                    }
                    else if(data.error !== undefined)
                    {

                    }
                })
                .error(function() {
                });
            };
            
            
        }
    ]).directive('liveButton', [
        'intervalService','liveService', '$http','$rootScope',
        function (intervalService, liveService, $http, $rootScope) {
            var ons = [];
            return{
                restrict: 'A',
                scope: true,
                link: function ($scope, element, attrs, ctrl) {
                    liveService.create(attrs.liveButton);
                    
                    var broadName = intervalService.subscribe('Junta');
                    $scope.isDisabled = true;
                    
                    ons[attrs.liveButton] = $rootScope.$on(broadName,function(event, args) { 
                          $scope.isDisabled = args.live_enabled == false;
                    });
                     
                     
                    /*ons[attrs.liveButton] = $rootScope.$on('avlive'+attrs.liveButton,function(event, args) { 
                        $scope.isDisabled = !args;   
                    });*/

                    //liveService.createInterval();

                    element.bind('click', function (event) {
                        liveService.openWindow(attrs.liveButton, '_blank');
                    });
                    element.on('$destroy', function() {
                        intervalService.unsubscribe('Junta');
                        //liveService.removeInterval();
                        if (ons[attrs.liveButton] !== null && ons[attrs.liveButton] !== undefined) ons[attrs.liveButton]();
                        ons[attrs.liveButton] = null;
                    });
                }
            };
        }

    ]).directive('liveButtonRacc', [
        'intervalService','liveService', '$http','$rootScope','juntaManager', 'logger',
        function (intervalService, liveService, $http, $rootScope, juntaManager, logger) {
            var ons = [];
            return{
                restrict: 'A',
                scope: true,
                link: function ($scope, element, attrs, ctrl) {
                    liveService.create(attrs.liveButtonRacc);

                    element.bind('click', function (event) {
                        juntaManager.getJunta().then(function(junta) {
                            if (!junta.live_enabled) {
                                logger.logError("id00506_app:logger:blocked-by-server")
                                return false;
                            }
                            else {
                                liveService.openWindow(attrs.liveButtonRacc, '_blank');
                            }
                        });
                    });
                    element.on('$destroy', function() {
                        if (ons[attrs.liveButtonRacc] !== null && ons[attrs.liveButtonRacc] !== undefined) ons[attrs.liveButtonRacc]();
                        ons[attrs.liveButtonRacc] = null;
                    });
                }
            };
        }

    ]);
}).call(this);
