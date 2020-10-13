(function() {
    'use strict';

    angular.module('workspaceControllers'
    ).controller('newThreadCtrl',[
        '$scope','Item','logger','$location','itemsManager', function($scope,Item,logger,$location,itemsManager){
            $scope.isValid = function(){
                return $scope.acceptTerms && $scope.thread.subject !== '' && $scope.thread.body !== '';
            };
            
            itemsManager.testNew("threads").then(function(postable){
                    if (postable.error !== undefined)
                    {
                        logger.logError(postable.error);
                        $location.path('/app/derecho/dashboard-derecho');
                    }
                    else if(!postable){
                            logger.logError("id00399_app:foro:newitem:test");
                            $location.path('/app/derecho/dashboard-derecho');
                    }
            });

            $scope.confirm = function(thread){
                thread.create().then(function(){
                    logger.logSuccess("id00215_app:logger:new-request-success");
                    $location.path('/app/derecho/solicitud').search("id",thread.id);
                }).catch(function(){
                    logger.logError("id00201_app:logger:server-error")
                });
            };

            ($scope.clear = function(){
                $scope.thread = new Item({
                    subject: '',
                    body: ''
                }, "threads");

                $scope.acceptTerms = false;
            })();
        }
    ]).controller('personalThreadsCtrl',[
        '$scope', 'ItemsList', 'accionistasManager','localize','stateService','$location',function($scope,ItemsList,accionistasManager,localize,stateService,$location){
            $scope.items = [];

            accionistasManager.getAccionista().then(function(accionista){
                accionista.getThreads().then(function(itemList){
                    $scope.items = itemList["threads"];
                });
            });

            $scope.i18n        = localize.getLocalizedString;
            $scope.states      = stateService.getThreadIDTags();
            $scope.states[0]   = "id00080_app:foro:nav:all";

            $scope.goToThread = function(thread){
                $location.path("/app/derecho/solicitud").search("id",thread.id);
            };
        }
    ]).controller('publicThreadsCtrl',[
        '$scope', 'itemsManager', 'ItemsList', '$location', 'localize', 'stateService', function($scope,itemsManager,ItemsList,$location,localize,stateService){
            $scope.items = [];

            itemsManager.getByTypeState("threads","public").then(function(itemList){
                $scope.items = itemList["threads"];
            });

            $scope.i18n        = localize.getLocalizedString;
            $scope.states      = stateService.getThreadIDTags();
            $scope.states[0]   = "id00080_app:foro:nav:all";

            $scope.goToThread = function(thread){
                $location.path("/app/derecho/solicitud").search("id",thread.id);
            };
        }
    ]).controller('threadCtrl',[
        '$scope','itemsManager','$routeParams','$route', 'Item', 'logger', 'accionistasManager', 'localize', function($scope,itemsManager,$routeParams,$route, Item, logger, accionistasManager, localize){

            $scope.thread = new Item({
                subject: '',
                body: '',
                messages: [],
                autor: [],
                locked: 1
            }, "threads");

            $scope.i18n = localize.getLocalizedString;

            itemsManager.getByTypeID("threads", $routeParams.id).then(function(itemList){
                $scope.thread = itemList["threads"][0];
                $scope.thread.locked = $scope.thread.locked.toString();
                $scope.thread.message = '';
            });

            accionistasManager.getAccionista().then(function(accionista){
                $scope.owner = function(){
                    return accionista.id == $scope.thread.autor.id;
                };
            });

            var reload = function(){
                $scope.thread.reload().then(function(){
                    $scope.thread.locked = $scope.thread.locked.toString();
                    $scope.thread.message = "";
                });
            };

            $scope.sendMessage = function(message){
                if($scope.owner() && $scope.thread.state == 3){
                    $scope.thread.state = 1;
                    $scope.thread.update().then(function(item){
                        if (item.error !== undefined)
                        {
                            logger.logError(item.error);
                        }
                        else
                        {
                            logger.logSuccess("id00216_app:logger:message-sent-success");
                        }
                        reload();
                    }).catch(function(){
                        logger.logError("id00201_app:logger:server-error");
                    });
                }
                else{
                    $scope.thread.sendComment(message).then(function(){
                        logger.logSuccess("id00216_app:logger:message-sent-success");
                        reload();
                    }).catch(function(){
                        logger.logError("id00201_app:logger:server-error");
                    });
                }
            };

            $scope.downloadDocument = function(id){
                window.location.href = ApiBase + "/documents/" + id;
            };
        }
    ]).controller('communiquesCtrl',[
        '$scope','$http','$location',function($scope,$http,$location){
            $scope.communiques = [];

            $http.get(ApiBase + '/communiques')
                .success(function(communiquesData){
                    $scope.communiques = communiquesData['communiques'];
                });

            $scope.goToDetails = function(communique){
                $location.path('/app/derecho/comunicado').search('id',communique.id);
            };
        }
    ]).controller('communiqueCtrl',[
        '$scope','$http','$routeParams','$location',function($scope,$http,$routeParams,$location){
            ($scope.clear = function(){
                $scope.communique = {
                    subject: '',
                    body: ''
                };
            })();

            $http.get(ApiBase + '/communiques/' + $routeParams.id)
                .success(function(communiqueData){
                    if(communiqueData.error === undefined){
                        $scope.communique = communiqueData['communiques'];
                    }else{
                        $location.path("/404").replace();
                    }
                }).catch(function(){
                    $location.path("/404").replace();
                });
            $scope.downloadDocument = function(id){
                window.location.href = ApiBase + "/documents/" + id;
            };
        }
    ]);

}).call(this);