(function() {
    'use strict';

    angular.module('adminWorkspaceControllers'
    ).controller('allThreadsCtrl',[
        '$scope','itemsManager','stateService', '$location', '$filter','localize', function($scope, itemsManager, stateService, $location, $filter,localize){
            var init;
            $scope.numPerPageOpt     = [3, 5, 10, 20, 100];
            $scope.numPerPage        = $scope.numPerPageOpt[2];
            $scope.stores            = [];
            $scope.currentPageStores = [];
            $scope.filteredStores    = [];
            $scope.searchKeywords    = '';
            $scope.row               = '';
            $scope.currentPage       = 1;

            $scope.states = stateService.getIDStates();
            $scope.states['0'] = 'todo';
            $scope.filterState     = '0';

            $scope.i18n        = localize.getLocalizedString;
            $scope.statesTrans    = stateService.getThreadIDTags();
            $scope.statesTrans[0] = "id00080_app:foro:nav:all";

            itemsManager.getByType("threads").then(function(itemList){
                $scope.stores = itemList['threads'];
                init();
            });

            $scope.select = function(page) {
                var end, start;
                start = (page - 1) * $scope.numPerPage;
                end = start + $scope.numPerPage;
                return $scope.currentPageStores = $scope.filteredStores.slice(start, end);
            };

            $scope.onFilterChange = function() {
                $scope.select(1);
                $scope.currentPage = 1;
                return $scope.row = '';
            };

            $scope.onNumPerPageChange = function() {
                $scope.select(1);
                return $scope.currentPage = 1;
            };

            $scope.onOrderChange = function() {
                $scope.select(1);
                return $scope.currentPage = 1;
            };

            $scope.search = function() {
                $scope.filteredStores = $filter('filter')($scope.stores, $scope.searchKeywords);
                return $scope.onFilterChange();
            };

            $scope.filterByState = function(){
                if($scope.filterState == 0)
                    $scope.filteredStores = $scope.stores;
                else
                    $scope.filteredStores = $filter('filter')($scope.stores,{state: $scope.filterState});
                return $scope.onFilterChange();
            };

            $scope.order = function(rowName) {
                if ($scope.row === rowName) {
                    return;
                }
                $scope.row = rowName;
                $scope.filteredStores = $filter('orderBy')($scope.stores, rowName);
                return $scope.onOrderChange();
            };

            init = function() {
                $scope.search();
                return $scope.select($scope.currentPage);
            };

            $scope.goToDetails = function(thread){
                $location.path("/app/derecho/solicitud").search("id",thread.id);
            };

            return init();
        }
    ]).controller('pendingThreadsCtrl',[
        '$scope','itemsManager','stateService', '$location', '$filter', function($scope, itemsManager, stateService, $location, $filter){
            var init;
            $scope.numPerPageOpt     = [3, 5, 10, 20, 100];
            $scope.numPerPage        = $scope.numPerPageOpt[2];
            $scope.stores            = [];
            $scope.currentPageStores = [];
            $scope.filteredStores    = [];
            $scope.searchKeywords    = '';
            $scope.row               = '';
            $scope.currentPage       = 1;

            $scope.states = stateService.getIDStates();
            $scope.states['0'] = 'todo';
            $scope.filterState     = '0';

            itemsManager.getByTypeState("threads","pending").then(function(itemList){
                $scope.stores = itemList['threads'];
                init();
            });

            $scope.select = function(page) {
                var end, start;
                start = (page - 1) * $scope.numPerPage;
                end = start + $scope.numPerPage;
                return $scope.currentPageStores = $scope.filteredStores.slice(start, end);
            };

            $scope.onFilterChange = function() {
                $scope.select(1);
                $scope.currentPage = 1;
                return $scope.row = '';
            };

            $scope.onNumPerPageChange = function() {
                $scope.select(1);
                return $scope.currentPage = 1;
            };

            $scope.onOrderChange = function() {
                $scope.select(1);
                return $scope.currentPage = 1;
            };

            $scope.search = function() {
                $scope.filteredStores = $filter('filter')($scope.stores, $scope.searchKeywords);
                return $scope.onFilterChange();
            };

            $scope.order = function(rowName) {
                if ($scope.row === rowName) {
                    return;
                }
                $scope.row = rowName;
                $scope.filteredStores = $filter('orderBy')($scope.stores, rowName);
                return $scope.onOrderChange();
            };

            init = function() {
                $scope.search();
                return $scope.select($scope.currentPage);
            };

            $scope.goToDetails = function(thread){
                $location.path("/app/derecho/solicitud").search("id",thread.id);
            };

            return init();
        }
    ]).controller('threadsByUserCtrl',[
        '$scope', 'itemsManager', '$routeParams','stateService','$filter','$location', function($scope,itemsManager,$routeParams,stateService,$filter,$location){
            var init;
            $scope.numPerPageOpt     = [3, 5, 10, 20, 100];
            $scope.numPerPage        = $scope.numPerPageOpt[2];
            $scope.stores            = [];
            $scope.currentPageStores = [];
            $scope.filteredStores    = [];
            $scope.searchKeywords    = '';
            $scope.row               = '';
            $scope.currentPage       = 1;

            $scope.states = stateService.getIDStates();
            $scope.states['0'] = 'todo';
            $scope.filterState     = '0';

            itemsManager.getAllByAccionista($routeParams.id,"threads").then(function(itemList){
                $scope.stores = itemList["threads"];
                init();
            });

            $scope.select = function(page) {
                var end, start;
                start = (page - 1) * $scope.numPerPage;
                end = start + $scope.numPerPage;
                return $scope.currentPageStores = $scope.filteredStores.slice(start, end);
            };

            $scope.onFilterChange = function() {
                $scope.select(1);
                $scope.currentPage = 1;
                return $scope.row = '';
            };

            $scope.onNumPerPageChange = function() {
                $scope.select(1);
                return $scope.currentPage = 1;
            };

            $scope.onOrderChange = function() {
                $scope.select(1);
                return $scope.currentPage = 1;
            };

            $scope.search = function() {
                $scope.filteredStores = $filter('filter')($scope.stores, $scope.searchKeywords);
                return $scope.onFilterChange();
            };

            $scope.filterByState = function(){
                if($scope.filterState == 0)
                    $scope.filteredStores = $scope.stores;
                else
                    $scope.filteredStores = $filter('filter')($scope.stores,{state: $scope.filterState});
                return $scope.onFilterChange();
            };

            $scope.order = function(rowName) {
                if ($scope.row === rowName) {
                    return;
                }
                $scope.row = rowName;
                $scope.filteredStores = $filter('orderBy')($scope.stores, rowName);
                return $scope.onOrderChange();
            };

            init = function() {
                $scope.search();
                return $scope.select($scope.currentPage);
            };

            $scope.goToDetails = function(thread){
                $location.path("/app/derecho/solicitud").search("id",thread.id);
            };

            return init();
        }
    ]).controller('solicitudAdminCtrl',[
        '$scope', 'itemsManager', '$routeParams', '$route', 'Item', 'logger', 'stateService', 'uploaderProvider', '$location', 'anonymousToken', '$http', function($scope,itemsManager,$routeParams,$route,Item,logger,stateService,uploaderProvider,$location, anonymousToken, $http){
            if($routeParams.id == undefined)
                $location.path("/404").replace();

            $scope.thread = new Item({
                subject: '',
                body: '',
                messages: [],
                autor: []
            });
            
            $scope.initialState = -1; 
            itemsManager.getByTypeID("threads", $routeParams.id).then(function(itemList){
                $scope.thread = itemList["threads"][0];
                $scope.initialState  = $scope.thread.state;
                $scope.thread.locked = $scope.thread.locked.toString();
                $scope.thread.message = '';

                $scope.thread.retornateThread = ($scope.thread.state != 2 && $scope.thread.state != 3) ? '1' : '0';
            }).catch(function(){
                $location.path("/404").replace();
            });
            
            var reload = function(){
                $scope.thread.reload().then(function(){
                    $scope.initialState  = $scope.thread.state;
                    $scope.thread.locked = $scope.thread.locked.toString();

                    $scope.thread.retornateThread = ($scope.thread.state != 2 && $scope.thread.state != 3) ? '1' : '0';

                    //Clean file queue
                    $scope.uploader.clearQueue();
                }).catch(function(){
                    $location.path("/404").replace();
                });
            };

            $scope.goToMessage = function(message){
                $location.path("/app/header/edit-message").search("id",message.id);
            };

            $scope.sendMessage = function(message){
                var token = $scope.uploader.getToken();
                if($scope.thread.retornateThread == 1 && $scope.thread.state != 2 && $scope.thread.state != 3)
                {
                    $scope.thread.token = token;
                    $scope.thread.message = message;
                    $scope.thread.state = 3;
                    $scope.thread.update().then(function(item){
                    if (item.error !== undefined)
                    {
                        logger.logError(item.error);
                    }
                    else
                    {
                        logger.logSuccess("Respuesta enviada Correctamente");
                    }
                        reload();
                    }).catch(function(){
                        logger.logError("Se ha producido un error al enviar. Inténtelo de nuevo.");
                    });
                    $scope.message = ""; 
                    anonymousToken.reset();
                    uploaderProvider.renew($scope.uploaderID,anonymousToken.newToken());
                }
                else
                {
                    $scope.thread.sendComment(message,token).then(function(){
                        logger.logSuccess("Respuesta enviada correctamente");
                        reload();
                    }).catch(function(){
                        logger.logError("Se ha producido un error al enviar. Inténtelo de nuevo.");
                    });
                    $scope.thread.message = ""; 
                    anonymousToken.reset();
                    uploaderProvider.renew($scope.uploaderID,anonymousToken.newToken());
                }
            };

            $scope.setState = function(){
                var state = -1;
                if($scope.thread.state == $scope.initialState){
                    state = ($scope.thread.locked == '1') ? 'locked' : 'unlocked';
                }
                else{
                    state = stateService.getAliasState($scope.thread.state);
                }

                $scope.thread.token = $scope.uploader.getToken();
                $scope.thread.update($scope.thread,state)
                    .then(function(){
                        logger.logSuccess("Estado cambiado correctamente");
                        reload();
                        
                    }).catch(function(){
                        logger.logError("Se ha producido un error al enviar. Inténtelo de nuevo.");
                    });
                $scope.message = ""; 
                anonymousToken.reset();
                uploaderProvider.renew($scope.uploaderID,anonymousToken.newToken());
            };

            $scope.uploaderID = "threadUploader"+$routeParams.id;
            $scope.uploader = uploaderProvider.get($scope.uploaderID,$scope,anonymousToken.newToken());

            $scope.downloadDocument = function(id){
                window.location.href = ApiBase + "/documents/" + id;
            };
        }
    ]).controller('newCommuniqueCtrl',[
        '$scope','$http','logger','uploaderProvider','anonymousToken','$location',function($scope,$http,logger,uploaderProvider,anonymousToken,$location){
            ($scope.clear = function(){
                $scope.communique = {
                    subject: '',
                    body: '',
                    enabled: false
                };
            })();

            $scope.isValid = function(){
                return $scope.communique.subject !== '' && $scope.communique.body != '';
            }

            $scope.submit = function(communique){
                communique.enabled = (communique.enabled === 'true');
                communique.token = $scope.uploader.getToken();
                $http.post(ApiBase + '/communiques', communique)
                    .success(function(communiqueData){
                        logger.logSuccess('Comunicado creado correctamente.');
                        $location.path("/app/derecho/comunicado").search("id",communiqueData.success.id);
                    })
                    .error(function(){
                        logger.logError('Se ha producido un error al enviar. Inténtelo de nuevo');
                    });
                uploaderProvider.renew($scope.uploaderID,anonymousToken.newToken());
            }

            $scope.uploaderID = "newCommuniqueUploader"+anonymousToken.newToken();
            $scope.uploader = uploaderProvider.get($scope.uploaderID,$scope,anonymousToken.newToken());
        }
    ]).controller('communiquesCtrl',[
        '$scope','$http','$location', '$filter', function($scope,$http,$location,$filter){
            var init;
            $scope.numPerPageOpt     = [3, 5, 10, 20, 100];
            $scope.numPerPage        = $scope.numPerPageOpt[2];
            $scope.stores            = [];
            $scope.currentPageStores = [];
            $scope.filteredStores    = [];
            $scope.searchKeywords    = '';
            $scope.row               = '';
            $scope.currentPage       = 1;

            $http.get(ApiBase+'/communiques')
            .success(function(communiquesList){
                $scope.stores = communiquesList['communiques'];
                init();
            });

            $scope.select = function(page) {
                var end, start;
                start = (page - 1) * $scope.numPerPage;
                end = start + $scope.numPerPage;
                return $scope.currentPageStores = $scope.filteredStores.slice(start, end);
            };

            $scope.onFilterChange = function() {
                $scope.select(1);
                $scope.currentPage = 1;
                return $scope.row = '';
            };

            $scope.onNumPerPageChange = function() {
                $scope.select(1);
                return $scope.currentPage = 1;
            };

            $scope.onOrderChange = function() {
                $scope.select(1);
                return $scope.currentPage = 1;
            };

            $scope.search = function() {
                $scope.filteredStores = $filter('filter')($scope.stores, $scope.searchKeywords);
                return $scope.onFilterChange();
            };

            $scope.order = function(rowName) {
                if ($scope.row === rowName) {
                    return;
                }
                $scope.row = rowName;
                $scope.filteredStores = $filter('orderBy')($scope.stores, rowName);
                return $scope.onOrderChange();
            };

            init = function() {
                $scope.search();
                return $scope.select($scope.currentPage);
            };

            $scope.goToDetails = function(communique){
                $location.path("/app/derecho/comunicado").search("id",communique.id);
            };

            return init();
        }
    ]).controller('communiqueCtrl',[
        '$scope','$http','$routeParams','logger','$window','uploaderProvider','anonymousToken','$location',function($scope,$http,$routeParams,logger,$window,uploaderProvider,anonymousToken,$location){
            if($routeParams.id == undefined)
                $location.path("/404").replace();

            var initialBody, initialSubject, initialEnabled;
            ($scope.clear = function(){
                $scope.communique = {
                    subject: '',
                    body: ''
                }; 
                initialBody = initialSubject = '';
                initialEnabled = "false";
            })();

            $scope.isValid = function(){
                return $scope.communique.subject  !== '' && $scope.communique.body !== '' &&
                       ($scope.communique.subject !== initialSubject || 
                       $scope.communique.body     !== initialBody    ||
                       $scope.communique.enabled != initialEnabled)  ||
                       $scope.uploader.queue != 0;
            };

            $http.get(ApiBase + '/communiques/' + $routeParams.id)
                .success(function(communiqueData){
                    if(communiqueData.error != undefined){
                        $location.path("/404").replace();
                        return;
                    }

                    $scope.communique = communiqueData['communiques'];
                    $scope.communique.enabled = $scope.communique.enabled.toString();
                    initialSubject = $scope.communique.subject;
                    initialBody    = $scope.communique.body;
                    initialEnabled = $scope.communique.enabled.toString();

                    $scope.uploader.setAdditional($scope.communique.documents);
                }).catch(function(){
                    $location.path("/404").replace();
                });

            $scope.submit = function(communique){
                communique.enabled = (communique.enabled === 'true');
                communique.token = $scope.uploader.getToken();
                $http.put(ApiBase + '/communiques/' + communique.id, communique)
                    .success(function(){
                        logger.logSuccess('Comunicado modificado correctamente');
                    })
                    .error(function(){
                        logger.logError('Se ha producido un error al enviar el comunicado. Por favor, inténtelo de nuevo.');
                    });

                $window.history.back();
                uploaderProvider.renew($scope.uploaderID,anonymousToken.newToken());
            };

            $scope.uploaderID = "CommuniqueUploader"+$routeParams.id;
            $scope.uploader = uploaderProvider.get($scope.uploaderID,$scope,anonymousToken.newToken());
        }
    ]);

}).call(this);