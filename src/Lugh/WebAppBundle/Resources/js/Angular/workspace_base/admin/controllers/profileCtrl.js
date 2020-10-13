(function() {
	'use strict';
	angular.module('adminWorkspaceControllers'
	).controller("profileAppsCtrl",[
		"$scope","$http","accionistasAdminManager", "$routeParams", "$location",
		function($scope,$http, accionistasManager, $routeParams, $location){
                    $scope.apps = {
				foro: true,
				voto: true,
				derecho: true,
                                av: true,
			};
                    accionistasManager.getAccionista($routeParams.id).then(function(accionista){
				$scope.apps = accionista.apps;
			}).catch(function(){
				$location.path("/404").replace();
			});

			$scope.submit = function(){
                                var data = {};
                                data.apps = $scope.apps;
				$scope.accionista.appChange(data)
			}
		}
	]).controller("profileStateCtrl",[
		"$scope","$routeParams","accionistasAdminManager","logger","$location", 
		function($scope,$routeParams,accionistasManager,logger,$location) {

			$scope.state        = -1;
			$scope.initialState = -1;
			$scope.message  = "";
			$scope.sendMail = true;
			accionistasManager.getAccionista($routeParams.id).then(function(accionista){
				$scope.accionista = accionista;
				$scope.initialState = accionista.item_accionista.state;
				$scope.state        = accionista.item_accionista.state;
			}).catch(function(){
				$location.path("/404").replace();
			});

			$scope.submit = function() {
				$scope.accionista.stateChange($scope.state,$scope.message,$scope.sendMail).then(function(accionista){
					$scope.initialState = accionista.item_accionista.state;
					$scope.state        = accionista.item_accionista.state;
					$scope.message = "";
					logger.logSuccess("Se ha cambiado el estado satisfactoriamente");
				});
			};

			$scope.canSubmit = function() {
				return $scope.state != $scope.initialState;
			};
		}

	]).controller("profileVotoCtrl",[
		"$scope","$routeParams","accionistasAdminManager","logger","$location", 
		function($scope,$routeParams,accionistasManager,logger,$location) {

			$scope.state        = -1;
			$scope.initialState = -1;
			$scope.message  = "";
			$scope.sendMail = true;
			accionistasManager.getAccionista($routeParams.id).then(function(accionista){
				$scope.accionista = accionista;
                for (var i in accionista.app)
                {
                    if (accionista.app[i].discr == 0) {
                        $scope.initialState = accionista.app[i].state;
                        $scope.state        = accionista.app[i].state; 
                    }
                }
			}).catch(function(){
				$location.path("/404").replace();
			});

			$scope.submit = function() {
				$scope.accionista.appStateChange('voto',$scope.state,$scope.message,$scope.sendMail).then(function(accionista){
					for (var i in accionista.app)
                    {
                        if (accionista.app[i].discr == 0) {
                            $scope.initialState = accionista.app[i].state;
                            $scope.state        = accionista.app[i].state; 
                        }
                    }
					$scope.message = "";
					logger.logSuccess("Se ha cambiado el estado satisfactoriamente");
				});
			};

			$scope.canSubmit = function() {
				return $scope.state != $scope.initialState;
			};
		}

	]).controller("profileForoCtrl",[
		"$scope","$routeParams","accionistasAdminManager","logger","$location", 
		function($scope,$routeParams,accionistasManager,logger,$location) {

			$scope.state        = -1;
			$scope.initialState = -1;
			$scope.message  = "";
			$scope.sendMail = true;
			accionistasManager.getAccionista($routeParams.id).then(function(accionista){
				$scope.accionista = accionista;
				for (var i in accionista.app)
                {
                    if (accionista.app[i].discr == 1) {
                        $scope.initialState = accionista.app[i].state;
                        $scope.state        = accionista.app[i].state; 
                    }
                }
			}).catch(function(){
				$location.path("/404").replace();
			});

			$scope.submit = function() {
				$scope.accionista.appStateChange('foro',$scope.state,$scope.message,$scope.sendMail).then(function(accionista){
					for (var i in accionista.app)
                    {
                        if (accionista.app[i].discr == 1) {
                            $scope.initialState = accionista.app[i].state;
                            $scope.state        = accionista.app[i].state; 
                        }
                    }
					$scope.message = "";
					logger.logSuccess("Se ha cambiado el estado satisfactoriamente");
				});
			};

			$scope.canSubmit = function() {
				return $scope.state != $scope.initialState;
			};
		}

	]).controller("profileDerechoCtrl",[
		"$scope","$routeParams","accionistasAdminManager","logger","$location", 
		function($scope,$routeParams,accionistasManager,logger,$location) {

			$scope.state        = -1;
			$scope.initialState = -1;
			$scope.message  = "";
			$scope.sendMail = true;
			accionistasManager.getAccionista($routeParams.id).then(function(accionista){
				$scope.accionista = accionista;
				for (var i in accionista.app)
                {
                    if (accionista.app[i].discr == 2) {
                        $scope.initialState = accionista.app[i].state;
                        $scope.state        = accionista.app[i].state; 
                    }
                }
			}).catch(function(){
				$location.path("/404").replace();
			});

			$scope.submit = function() {
				$scope.accionista.appStateChange('derecho',$scope.state,$scope.message,$scope.sendMail).then(function(accionista){
					for (var i in accionista.app)
                    {
                        if (accionista.app[i].discr == 2) {
                            $scope.initialState = accionista.app[i].state;
                            $scope.state        = accionista.app[i].state; 
                        }
                    }
					$scope.message = "";
					logger.logSuccess("Se ha cambiado el estado satisfactoriamente");
				});
			};

			$scope.canSubmit = function() {
				return $scope.state != $scope.initialState;
			};
		}

	]).controller("profileAVCtrl",[
		"$scope","$routeParams","accionistasAdminManager","logger","$location", 
		function($scope,$routeParams,accionistasManager,logger,$location) {

			$scope.state        = -1;
			$scope.initialState = -1;
			$scope.message  = "";
			$scope.sendMail = true;
			accionistasManager.getAccionista($routeParams.id).then(function(accionista){
				$scope.accionista = accionista;
				for (var i in accionista.app)
                {
                    if (accionista.app[i].discr == 3) {
                        $scope.initialState = accionista.app[i].state;
                        $scope.state        = accionista.app[i].state; 
                    }
                }
			}).catch(function(){
				$location.path("/404").replace();
			});

			$scope.submit = function() {
				$scope.accionista.appStateChange('av',$scope.state,$scope.message,$scope.sendMail).then(function(accionista){
					for (var i in accionista.app)
                    {
                        if (accionista.app[i].discr == 3) {
                            $scope.initialState = accionista.app[i].state;
                            $scope.state        = accionista.app[i].state; 
                        }
                    }
					$scope.message = "";
					logger.logSuccess("Se ha cambiado el estado satisfactoriamente");
				});
			};

			$scope.canSubmit = function() {
				return $scope.state != $scope.initialState;
			};
		}

	]).controller('profileCtrl',[
		'$scope','accionistasAdminManager','$localStorage', '$routeParams', "$location","$http","logger", function($scope,accionistasManager,$localStorage,$routeParams,$location,$http,logger) {
			$scope.foro    = {
				"propuestas" : 0,
				"iniciativas": 0,
				"ofertas"    : 0,
				"peticiones" : 0
			};
			$scope.voto    = "Sin acción";
			$scope.derecho = 0;
                        $scope.av = "Sin acción";

			var updateView = function(accionista){
				$scope.accionista = accionista;
				
				if($localStorage.foro == 1){
					accionista.getItems().then(function(accionista){
						$scope.foro = {
							"propuestas" : accionista.proposals.length,
							"iniciativas": accionista.initiatives.length,
							"ofertas"    : accionista.offers.length,
							"peticiones" : accionista.requests.length
						};
					});
				}
				
				if($localStorage.voto == 1){
					accionista.getAccions().then(function(accionista){
						var last = accionista.accion.slice(-1)[0];
						if(last !== undefined){
							if(last.discr === 1){
								$scope.voto = "Delegación en: " + last.delegado.nombre;
							}
							if(last.discr === 0){
								$scope.voto = "Votación"
							}
						}

						$scope.goToAccion = function(){
							$location.path('app/voto/accion').search('id',last.id);
						};
					});
				}
				
				if($localStorage.derecho == 1){
					accionista.getThreads().then(function(accionista){
						$scope.derecho = accionista.threads.length;
					});
				}
                                
                                if($localStorage.av == 1){
					accionista.getAccions().then(function(accionista){
						var last = accionista.accion.slice(-1)[0];
						if(last !== undefined){
							if(last.discr === 2){
								$scope.av = "Votación";
							}
							if(last.discr === 100){
								$scope.av = "Anulación"
							}
						}

						$scope.goToAccion = function(){
							$location.path('app/av/accion').search('id',last.id);
						};
					});
				}

				$scope.goToItemList = function(type){
					$location.path('/app/foro/'+type).search('id',accionista.id);
				};

				$scope.goToThreads = function(){
					$location.path('app/derecho/user-solicitudes').search('id',accionista.id);
				};
			};

			$scope.password = function() {
				$http.put(ApiBase + '/accionistas/' + $scope.accionista.user.id + '/password').success(function(data){
					if(data.error !== undefined){
						logger.logError("Se ha producido un error al cambiar la contraseña.");
					} else {
						logger.logSuccess("Contraseña cambiada satisfactoriamente.");
					}
				}).catch(function(){
					logger.logError("Se ha producido un error de comunicación con el servidor.");
				});
			};

			accionistasManager.getAccionista($routeParams.id).then(function(accionista){
				updateView(accionista);

				$scope.contact = function(){
					$location.path("/app/header/contact-user").search('email',accionista.user.email);
				};
			}).catch(function(){
				$location.path("/404").replace();
			});

			$scope.downloadDocument = function(id){
				window.location.href = ApiBase + "/documents/" + id;
			};

		}

	]);
}).call(this);