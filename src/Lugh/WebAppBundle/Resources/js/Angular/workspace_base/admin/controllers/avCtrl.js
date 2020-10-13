(function() {
    'use strict';

    angular.module('adminWorkspaceControllers'
    ).controller('allQuestionsCtrl',[
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
            var states = stateService.getIDStates();
            delete states[2];
            $scope.states = states;

            $scope.states['0'] = 'todo';
            $scope.filterState     = '0';

            $scope.i18n        = localize.getLocalizedString;
            $scope.statesTrans    = stateService.getQuestionIDTags();
            $scope.statesTrans[0] = "id00080_app:foro:nav:all";
            $scope.getNameState = stateService.getNameState;

            itemsManager.getByType("questions").then(function(itemList){
                $scope.stores = itemList['questions'];
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

            $scope.loadUserProfile = function(id) {
                $location.path("/av/userlives").search("id",id);
            };

            init = function() {
                $scope.search();
                return $scope.select($scope.currentPage);
            };

            $scope.goToDetails = function(question){
                $location.path("/app/av/ruego").search("id",question.id);
            };

            return init();
        }
    ]).controller('pendingQuestionsCtrl',[
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

            itemsManager.getByTypeState("questions","pending").then(function(itemList){
                $scope.stores = itemList['questions'];
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

            $scope.goToDetails = function(question){
                $location.path("/app/av/ruego").search("id",question.id);
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
    ]).controller('questionAdminCtrl',[
        '$scope', 'itemsManager', '$routeParams', '$route', 'Item', 'logger', 'stateService', 'uploaderProvider', '$location', 'anonymousToken', '$http',
        function($scope,itemsManager,$routeParams,$route,Item,logger,stateService,uploaderProvider,$location, anonymousToken, $http){
            if($routeParams.id == undefined)
                $location.path("/404").replace();

            $scope.question = new Item({
                subject: '',
                body: '',
                messages: [],
                autor: []
            });
            
            $scope.initialState = -1; 
            itemsManager.getByTypeID("questions", $routeParams.id).then(function(itemList){
                $scope.question = itemList["questions"][0];
                $scope.initialState  = $scope.question.state;
                $scope.question.message = '';

                $scope.question.retornateQuestion = ($scope.question.state != 2 && $scope.question.state != 3) ? '1' : '0';
            }).catch(function(){
                $location.path("/404").replace();
            });
            
            var reload = function(){
                $scope.question.reload().then(function(){
                    $scope.initialState  = $scope.question.state;

                    $scope.question.retornateQuestion = ($scope.question.state != 2 && $scope.question.state != 3) ? '1' : '0';

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
                if($scope.question.retornateQuestion == 1 && $scope.question.state != 2 && $scope.question.state != 3)
                {
                    $scope.question.token = token;
                    $scope.question.message = message;
                    $scope.question.state = 3;
                    $scope.question.update().then(function(item){
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
                    $scope.question.sendComment(message,token).then(function(){
                        logger.logSuccess("Respuesta enviada correctamente");
                        reload();
                    }).catch(function(){
                        logger.logError("Se ha producido un error al enviar. Inténtelo de nuevo.");
                    });
                    $scope.question.message = ""; 
                    anonymousToken.reset();
                    uploaderProvider.renew($scope.uploaderID,anonymousToken.newToken());
                }
            };

            $scope.setState = function(){
                var state = -1;
                if($scope.question.state == $scope.initialState){
                    //state = ($scope.question.locked == '1') ? 'locked' : 'unlocked';
                }
                else{
                    state = stateService.getAliasState($scope.question.state);
                }

                $scope.question.token = $scope.uploader.getToken();
                $scope.question.update($scope.question,state)
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

            $scope.uploaderID = "questionUploader"+$routeParams.id;
            $scope.uploader = uploaderProvider.get($scope.uploaderID,$scope,anonymousToken.newToken());

            $scope.downloadDocument = function(id){
                window.location.href = ApiBase + "/documents/" + id;
            };
        }
    ]).controller('accionistasLivesTableCtrl',
    ['$scope', '$filter','$location','accionistasAdminManager','stateService', 
    function($scope,$filter,$location,AccionistasManager,stateService)
    {

        var init;
            $scope.numPerPageOpt     = [3, 5, 10, 20];
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
            
            $scope.getNameState = stateService.getNameState;

            //Cargar lista de accionistas
            AccionistasManager.loadAllAccionistas().then(function(accionistas){
                $scope.stores = accionistas;
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
                    $scope.filteredStores = $filter('filter')($scope.stores,{item_accionista:{state: $scope.filterState}});
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

            $scope.loadUserLives = function(id) {
                $location.path("/app/av/userlives").search("id",id);
            };

            return init();

    }])
    .controller('accionistaLivesCtrl',[
    '$scope', 'accionistasAdminManager' , '$routeParams', '$location'
    ,function($scope, accionistasManager, $routeParams, $location  ){
        if($routeParams.id == undefined)
            $location.path("/404").replace();
        
        $scope.accionista = null;
        $scope.lives = [];

        accionistasManager.getAccionista($routeParams.id).then(function(accionista) {
            $scope.accionista = accionista;
            accionista.getLives().then(function(accionistaLives) {

                $scope.lives = accionistaLives;

            }); 

        });

        $scope.loadAccionista = function(id){
            //$location.path("/app/profile-admin").search("id",id);
            $location.path("/app/users/profile").search("id",id);
        }

        $scope.loadHistorial = function(id){
            $location.path("/app/av/historial").search("id",id);
        }
        
    
    }
    ]).controller('usersAvCtrl',[
        '$scope','itemsManager','stateService', '$location', '$filter','localize', function($scope, itemsManager, stateService, $location, $filter,localize){
            var init;
            $scope.numPerPageOpt     = [3, 5, 10, 20];
            $scope.numPerPage        = $scope.numPerPageOpt[2];
            $scope.stores            = [];
            $scope.currentPageStores = [];
            $scope.filteredStores    = [];
            $scope.searchKeywords    = '';
            $scope.row               = '';
            $scope.currentPage       = 1;
            
            itemsManager.getByType("desertions").then(function(itemList){
                $scope.stores = itemList['desertions'];
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

            $scope.loadUserProfile = function(id) {
                $location.path("/app/users/profile").search("id",id);
            };

            init = function() {
                $scope.search();
                return $scope.select($scope.currentPage);
            };
            
            $scope.excel = function(){
		window.location.href = ApiBase + "/desertions/excel";
            }
            $scope.total = function(){
                window.location.href = ApiBase + "/desertions/total";
            }
            $scope.last = function(){
                window.location.href = ApiBase + "/desertions/last";
            }
            

            return init();
        }
    ]).controller('votosAvCtrl',[
		"$scope","actionsManager","$filter","$location",
		function($scope,actionsManager,$filter,$location){
			var init;
			$scope.numPerPageOpt     = [3, 5, 10, 20, 100];
			$scope.numPerPage        = $scope.numPerPageOpt[2];
			$scope.stores            = [];
			$scope.currentPageStores = [];
			$scope.filteredStores    = [];
			$scope.searchKeywords    = '';
			$scope.row               = '';
			$scope.currentPage       = 1;

			actionsManager.getAccionesAv().then(function(accions){
				$scope.stores = accions;
				init();
			});
                        
                        $scope.type = {
                             'def': 'Acción',
				 2: 'Asistencia Virtual',
                                100: 'Anulación'
			};

			$scope.loadAccion = function(id){
				$location.path("app/av/accion").search("id",id);
			};

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
                        
                        $scope.excel = function(){
				window.location.href = ApiBase + "/accions/excel/av";
			}
                        
                        $scope.total = function(){
				window.location.href = ApiBase + "/accions/total/av";
			}
                        
                        $scope.last = function(){
				window.location.href = ApiBase + "/accions/last/av";
			}

			return init();
		}
	]).controller('accionAvCtrl',[
		'$scope', '$routeParams', '$location', 'actionsManager',
		function($scope,$routeParams,$location,actionsManager){
			if($routeParams.id == undefined)
				$location.path("/404").replace();

			$scope.type = {
			 'def': 'Acción',
				 2: 'Asistencia Virtual',
                               100: 'Anulación'
			};

			$scope.action = {discr: 'def'};
			actionsManager.getAccion($routeParams.id).then(function(action){
				$scope.action = action;
			});

			$scope.loadAccionista = function(id){
                            //$location.path("/app/profile-admin").search("id",id);
                            $location.path("/app/users/profile").search("id",id);
			}

			$scope.loadHistorial = function(id){
				$location.path("/app/av/historial").search("id",id);
			}
		}
	]).controller('historialAvCtrl',[
		'$scope', '$routeParams', '$location', 'accionistasAdminManager', '$filter',
		function($scope,$routeParams,$location,accionistasAdminManager, $filter){
			if($routeParams.id == undefined)
				$location.path("/404").replace();

			$scope.type = {
			 'def': 'Acción',
				 2: 'Asistencia Virtual',
			       100: 'Anulación'
			};

                        $scope.selectedTypes = [2,100];
                        $scope.filterByTypes = function(acciones) {
                            return ($scope.selectedTypes.indexOf(acciones.discr) !== -1);
                        };
                        
			accionistasAdminManager.getAccionista($routeParams.id).then(function(accionista){
				$scope.accionista = accionista;
				accionista.getAccions().then(function(actions){
					$scope.actions = actions.accion;
                                        $scope.actions = $filter('orderBy')($scope.actions, 'date_time_create')
				});
			});

			$scope.loadAction = function(id){
				$location.path("app/av/accion").search("id",id);
			}
                        $scope.loadAccionista = function(id) {
                            $location.path("/app/users/profile").search("id",id);
                        };
		}
	]).controller('accionistasAvCtrl',[
		'$scope', '$routeParams', '$location', 'accesosManager', '$filter',
		function($scope,$routeParams,$location,accesosManager, $filter){
			var init;
			$scope.numPerPageOpt     = [3, 5, 10, 20, 100];
			$scope.numPerPage        = $scope.numPerPageOpt[2];
			$scope.stores            = [];
			$scope.currentPageStores = [];
			$scope.filteredStores    = [];
			$scope.searchKeywords    = '';
			$scope.row               = '';
			$scope.currentPage       = 1;

			accesosManager.getAvs().then(function(accesos){
				$scope.stores = accesos;
				init();
			});
                        
                        $scope.loadDetalle = function(id) {
                            //$location.path("/app/users/profile").search("id",id);
                            $location.path("app/av/detalle").search("id",id);
                        };

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
                        
                        $scope.excel = function(){
				window.location.href = ApiBase + "/accesos/excel/av";
			}
                        $scope.total = function(){
				window.location.href = ApiBase + "/accesos/total/av";
			}
                        $scope.last = function(){
				window.location.href = ApiBase + "/accesos/last/av";
			}


			return init();
		}
	]).controller('detalleAvCtrl',[
		'$scope', '$routeParams', '$location', 'accionistasAdminManager', '$filter',
		function($scope,$routeParams,$location,accionistasAdminManager, $filter){
			if($routeParams.id == undefined)
				$location.path("/404").replace();
                            
                        $scope.selectedTypes = [3];
                        $scope.filterByTypes = function(accesos) {
                            return ($scope.selectedTypes.indexOf(accesos.discr) !== -1);
                        };

			accionistasAdminManager.getAccionista($routeParams.id).then(function(accionista){
				$scope.accionista = accionista;
				accionista.getAccesos('av').then(function(accesos){
					$scope.accesos = accesos.accesos;
                                        $scope.accesos = $filter('orderBy')($scope.accesos, 'date_time')
				});
			});
                        
                        $scope.loadAccionista = function(id) {
                            $location.path("/app/users/profile").search("id",id);
                        };

		}
	]);
        
        

}).call(this);