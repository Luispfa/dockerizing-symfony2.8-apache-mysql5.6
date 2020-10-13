(function() {
	'use strict';
	var types = {
		"proposals":  "propuesta",
		"requests":   "peticion",
		"initiatives":"iniciativa",
		"offers":     "oferta"
	};
        var action = {
                '-1': "",
                 '1': "3",
                 '2': "3",
                 '3': "1",
                 '4': ""
        };
        


	var clearAdhesions = function(adhesions){
		for(var i in adhesions){
			if(adhesions[i] != 2)
				adhesions[i].shares_num = '--'
			if(adhesions[i].state == 3)
				adhesions.splice(i,1);
		}
		return adhesions;
	}

	angular.module('workspaceControllers'
	).controller('propuestaPersonalCtrl',[
		"$scope","$routeParams","itemsManager","logger","$route","Item","$location","localize","$modal","stateService",
		function($scope,$routeParams,itemsManager,logger,$route,Item,$location,localize,$modal,stateService){
			$scope.adhesions = {};
			$scope.loading = false;
                        $scope.noclick = false;
			$scope.i18n = localize.getLocalizedString;
			$scope.adhesions = [];

			$scope.states = stateService.getIDTags();

			itemsManager.getByTypeID("proposals",$routeParams.id).then(function(items){
                                var item = items["proposals"][0];
                                var initState = item.state;
				item.state = action[initState];
				item.testUpdate().then(function(data){
                                    item.state = initState;
                                    $scope.item = item;
                                    if(data.error != undefined) 
                                        $scope.noclick = true;
				}).catch(function(data){
					logger.logError("id00201_app:logger:server-error");
				});
                                
				($scope.updateAdhesions = function(){
					item.getAdhesions().then(function(adhesions){
						$scope.adhesions = clearAdhesions(adhesions);
						$scope.adhesions.unshift({accionista: item.autor, owner: true});
						$scope.total_shares = 0;


						adhesions.forEach(function(adh){if(adh.owner || adh.state ==2){ $scope.total_shares += adh.accionista.shares_num;}});
					});
				})();
                                
			}).catch(function(){
				$location.path('/404').replace();
			});

			$scope.goToAdhesion = function(adhesion){
				if(adhesion.id)
					$location.path('/app/foro/adhesion/proposal').search('id',adhesion.id);
			};

			($scope.clear = function(){
				$scope.item = new Item({
					title: '',
					description: '',
					message: '',
					state: -1
				}, "proposals");
			})();

			$scope.allowItem = function(){
				return $scope.item.title         != "" &&
					   $scope.item.justification != "" &&
					   $scope.item.description   != "";
			};

			$scope.confirm = function(item){
				$scope.loading = true;

				var data = {
					title:         $scope.item.title,
					justification: $scope.item.justification,
					description:   $scope.item.description
				};

				$scope.publish(item);
			};

			$scope.publish = function(item){
				$scope.loading = true;

				var initState = item.state;
				item.state = 1;
				item.update().then(function(data){
					$scope.loading = false;
					if(data.error != undefined){
						item.state = initState;
						logger.logError(data.error);
						return;
					}
					logger.logSuccess("id00197_app:logger:mod-proposal-success");
				}).catch(function(data){
					logger.logError("id00201_app:logger:server-error");
				});
			};

			$scope.cancel = function(item){
				var modalInstance = $modal.open({
					templateUrl:WebDefault + "/home/sharedviews/textareaModal.html",
					controller: 'textareaModalCtrl',
					backdrop:'static',
					resolve: {
						title: function() {
							return localize.getLocalizedString("id00238_app:modal:message");
						},
						body: function() {
							return localize.getLocalizedString("id00237_app:modal:cancel-item-message");
						}
					}
				});

				modalInstance.result.then(function(message){
					$scope.loading = true;
					item.update(item,"retornate",message).then(function(){
						$scope.updateAdhesions();
						$scope.loading = false;
						logger.logSuccess("id00198_app:logger:mod-proposal-cancel");
						item.message = '';
					}).catch(function(message){
						logger.logError(message);
					});
				}, function(){});
			};
		}
	]).controller('iniciativaPersonalCtrl',[
		"$scope","$routeParams","itemsManager","logger","$route","Item","$location","localize","$modal","stateService",
		function($scope,$routeParams,itemsManager,logger,$route,Item,$location,localize,$modal,stateService){
			$scope.adhesions = {};
			$scope.loading = false;
                        $scope.noclick = false;
			$scope.i18n = localize.getLocalizedString;
			$scope.adhesions = [];

			$scope.states = stateService.getIDTags();

			itemsManager.getByTypeID("initiatives",$routeParams.id).then(function(items){
                                var item = items["initiatives"][0];
                                var initState = item.state;
				item.state = action[initState];
				item.testUpdate().then(function(data){
                                    item.state = initState;
                                    $scope.item = item;
                                    if(data.error != undefined) 
                                        $scope.noclick = true;
				}).catch(function(data){
					logger.logError("id00201_app:logger:server-error");
				});
				($scope.updateAdhesions = function(){
					item.getAdhesions().then(function(adhesions){
						$scope.adhesions = clearAdhesions(adhesions);
						$scope.adhesions.unshift({accionista: item.autor, owner: true});
						$scope.total_shares = 0;


						adhesions.forEach(function(adh){if(adh.owner || adh.state ==2){ $scope.total_shares += adh.accionista.shares_num;}});
					});
				})();
			}).catch(function(){
				$location.path('/404').replace();
			});

			$scope.goToAdhesion = function(adhesion){
				if(adhesion.id)
					$location.path('/app/foro/adhesion/initiative').search('id',adhesion.id);
			};

			($scope.clear = function(){
				$scope.item = new Item({
					title: '',
					description: '',
					message: '',
					state: -1
				}, "initiatives");
			})();

			$scope.allowItem = function(){
				return $scope.item.title         != "" &&
					   $scope.item.justification != "" &&
					   $scope.item.description   != "";
			};

			$scope.confirm = function(item){
				$scope.loading = true;

				var data = {
					title:         $scope.item.title,
					justification: $scope.item.justification,
					description:   $scope.item.description
				};

				$scope.publish(item);
			};

			$scope.publish = function(item){
				$scope.loading = true;

				var initState = item.state;
				item.state = 1;
				item.update().then(function(data){
					$scope.loading = false;
					if(data.error != undefined){
						item.state = initState;
						logger.logError(data.error);
						return;
					}
					logger.logSuccess("id00195_app:logger:mod-initiative-success");
				}).catch(function(){
					logger.logError("id00201_app:logger:server-error");
				});
			};

			$scope.cancel = function(item){
				var modalInstance = $modal.open({
					templateUrl:WebDefault + "/home/sharedviews/textareaModal.html",
					controller: 'textareaModalCtrl',
					backdrop:'static',
					resolve: {
						title: function() {
							return localize.getLocalizedString("id00238_app:modal:message");
						},
						body: function() {
							return localize.getLocalizedString("id00237_app:modal:cancel-item-message");
						}
					}
				});

				modalInstance.result.then(function(message){
					$scope.loading = true;
					item.update(item,"retornate",message).then(function(){
						$scope.updateAdhesions();
						$scope.loading = false;
						logger.logSuccess("id00196_app:logger:mod-initiative-cancel");
						item.message = '';
					}).catch(function(message){
						logger.logError(message);
					});
				}, function(){});
			};
		}
	]).controller('ofertaPersonalCtrl',[
		"$scope","$routeParams","itemsManager","logger","$route","Item","$location","localize","$modal","stateService",
		function($scope,$routeParams,itemsManager,logger,$route,Item,$location,localize,$modal,stateService){
			$scope.adhesions = {};
			$scope.loading = false;
                        $scope.noclick = false;
			$scope.i18n = localize.getLocalizedString;
			$scope.adhesions = [];

			$scope.states = stateService.getIDTags();

			itemsManager.getByTypeID("offers",$routeParams.id).then(function(items){
				var item = items["offers"][0];
                                var initState = item.state;
				item.state = action[initState];
				item.testUpdate().then(function(data){
                                    item.state = initState;
                                    $scope.item = item;
                                    if(data.error != undefined) 
                                        $scope.noclick = true;
				}).catch(function(data){
					logger.logError("id00201_app:logger:server-error");
				});

				($scope.updateAdhesions = function(){
					item.getAdhesions().then(function(adhesions){
						$scope.adhesions = clearAdhesions(adhesions);
						$scope.adhesions.unshift({accionista: item.autor, owner: true});
						$scope.total_shares = 0;


						adhesions.forEach(function(adh){if(adh.owner || adh.state ==2){ $scope.total_shares += adh.accionista.shares_num;}});
					});
				})();
			}).catch(function(){
				$location.path('/404').replace();
			});

			$scope.goToAdhesion = function(adhesion){
				if(adhesion.id)
					$location.path('/app/foro/adhesion/offer').search('id',adhesion.id);
			};

			($scope.clear = function(){
				$scope.item = new Item({
					title: '',
					description: '',
					message: '',
					state: -1
				}, "offers");
			})();

			$scope.allowItem = function(){
				return $scope.item.title         != "" &&
					   $scope.item.justification != "" &&
					   $scope.item.description   != "";
			};

			$scope.confirm = function(item){
				$scope.loading = true;

				var data = {
					title:         $scope.item.title,
					justification: $scope.item.justification,
					description:   $scope.item.description
				};

				$scope.publish(item);
			};

			$scope.publish = function(item){
				$scope.loading = true;

				var initState = item.state;
				item.state = 1;
				item.update().then(function(data){
					$scope.loading = false;
					if(data.error != undefined){
						item.state = initState;
						logger.logError(data.error);
						return;
					}
					logger.logSuccess("id00193_app:logger:mod-offer-success");
				}).catch(function(){
					logger.logError("id00201_app:logger:server-error");
				});
			};

			$scope.cancel = function(item){
				var modalInstance = $modal.open({
					templateUrl:WebDefault + "/home/sharedviews/textareaModal.html",
					controller: 'textareaModalCtrl',
					backdrop:'static',
					resolve: {
						title: function() {
							return localize.getLocalizedString("id00238_app:modal:message");
						},
						body: function() {
							return localize.getLocalizedString("id00237_app:modal:cancel-item-message");
						}
					}
				});

				modalInstance.result.then(function(message){
					$scope.loading = true;
					item.update(item,"retornate",message).then(function(){
						$scope.updateAdhesions();
						$scope.loading = false;
						logger.logSuccess("id00194_app:logger:mod-offer-cancel");
						item.message = '';
					}).catch(function(message){
						logger.logError(message);
					});
				}, function(){});
			};
		}
	]).controller('peticionPersonalCtrl',[
		"$scope","$routeParams","itemsManager","logger","$route","Item","$location","localize","$modal","stateService",
		function($scope,$routeParams,itemsManager,logger,$route,Item,$location,localize,$modal,stateService){
			$scope.adhesions = {};
			$scope.loading = false;
                        $scope.noclick = false;
			$scope.i18n = localize.getLocalizedString;
			$scope.adhesions = [];

			$scope.states = stateService.getIDTags();

			itemsManager.getByTypeID("requests",$routeParams.id).then(function(items){
				var item = items["requests"][0];
                                var initState = item.state;
				item.state = action[initState];
				item.testUpdate().then(function(data){
                                    item.state = initState;
                                    $scope.item = item;
                                    if(data.error != undefined) 
                                        $scope.noclick = true;
				}).catch(function(data){
					logger.logError("id00201_app:logger:server-error");
				});

				($scope.updateAdhesions = function(){
					item.getAdhesions().then(function(adhesions){
						$scope.adhesions = clearAdhesions(adhesions);
						$scope.total_shares = 0;


						adhesions.forEach(function(adh){if(adh.state == 2){ $scope.total_shares += adh.accionista.shares_num;}});
					});
				})();
			}).catch(function(){
				$location.path('/404').replace();
			});

			$scope.goToAdhesion = function(adhesion){
				if(adhesion.id)
					$location.path('/app/foro/adhesion/request').search('id',adhesion.id);
			};

			($scope.clear = function(){
				$scope.item = new Item({
					title: '',
					description: '',
					message: '',
					state: -1
				}, "requests");
			})();

			$scope.allowItem = function(){
				return $scope.item.title         != "" &&
					   $scope.item.justification != "" &&
					   $scope.item.description   != "";
			};

			$scope.confirm = function(item){
				$scope.loading = true;

				var data = {
					title:         $scope.item.title,
					justification: $scope.item.justification,
					description:   $scope.item.description
				};

				$scope.publish(item);
			};

			$scope.publish = function(item){
				$scope.loading = true;

				var initState = item.state;
				item.state = 1;
				item.update().then(function(data){
					$scope.loading = false;
					if(data.error != undefined){
						item.state = initState;
						logger.logError(data.error);
						return;
					}
					logger.logSuccess("id00199_app:logger:mod-request-success");
				}).catch(function(){
					logger.logError("id00201_app:logger:server-error");
				});
			};

			$scope.cancel = function(item){
				var modalInstance = $modal.open({
					templateUrl:WebDefault + "/home/sharedviews/textareaModal.html",
					controller: 'textareaModalCtrl',
					backdrop:'static',
					resolve: {
						title: function() {
							return localize.getLocalizedString("id00238_app:modal:message");
						},
						body: function() {
							return localize.getLocalizedString("id00237_app:modal:cancel-item-message");
						}
					}
				});

				modalInstance.result.then(function(message){
					$scope.loading = true;
					item.update(item,"retornate",message).then(function(){
						$scope.updateAdhesions();
						$scope.loading = false;
						logger.logSuccess("id00200_app:logger:mod-request-cancel");
						item.message = '';
					}).catch(function(message){
						logger.logError(message);
					});
				}, function(){});
			};
		}
	]).controller('propuestaPublicaCtrl',[
		"$scope","$routeParams","itemsManager","logger","accionistasManager","Item","$location", function($scope,$routeParams,itemsManager,logger,accionistasManager,Item,$location){
			$scope.item = new Item({
					title: '',
					description: '',
					message: ''
				}, "proposals");

			$scope.adherido = -1;
			$scope.adherible = true;

			var checkAdhesion = function(item){
				accionistasManager.getAccionista().then(function(accionista){
					//Estas 2 variables son un semaphore improvisado:
					$scope.adheribleReady = false;
					$scope.adheridoReady = false;

					item.adherido(accionista).then(function(adhesion){
						$scope.adheridoReady = (adhesion == 0) ? 0 : adhesion.state;
						
						if($scope.adheribleReady !== false)
							$scope.adherido = $scope.adheridoReady;

						$scope.adhesion = adhesion;
					});

					item.adherible(accionista).then(function(adherible){
						if($scope.adheridoReady !== false)
							$scope.adherido = $scope.adheridoReady;
						$scope.adherible = adherible;
						$scope.adheribleReady = true;
					});

					$scope.adherir = function(item){
						var before = $scope.adherido;
						$scope.adherido = -1;
						item.adherir(accionista).then(function(stat){
							if(!stat){
								$scope.adherido = before;
								return;
							}
							checkAdhesion(item);
							logger.logSuccess("id00202_app:logger:adhesion-success");
						}).catch(function(){
							$scope.adherido = before;
							logger.logError("id00201_app:logger:server-error");
						});
					};
				});
			};

			itemsManager.getByTypeID("proposals",$routeParams.id).then(function(items){
				$scope.item = items["proposals"][0];

				checkAdhesion($scope.item);
			}).catch(function(){
				$location.path("/404").replace();
			});

			$scope.cancelarAdhesion = function(item){
				$scope.adherido = -1;
				item.cancelarAdhesion($scope.adhesion.id).then(function(){
					logger.logSuccess("id00203_app:logger:adhesion-cancel");
					checkAdhesion(item);
				}).catch(function(){
					logger.logError("id00201_app:logger:server-error");
				});
			};
		}
	]).controller('iniciativaPublicaCtrl',[
		"$scope","$routeParams","itemsManager","logger","accionistasManager","Item","$location", function($scope,$routeParams,itemsManager,logger,accionistasManager,Item,$location){
			$scope.item = new Item({
					title: '',
					description: '',
					message: ''
				}, "initiatives");

			$scope.adherido = -1;
			$scope.adherible = true;

			var checkAdhesion = function(item){
				accionistasManager.getAccionista().then(function(accionista){
					//Estas 2 variables son un semaphore improvisado:
					$scope.adheribleReady = false;
					$scope.adheridoReady = false;

					item.adherido(accionista).then(function(adhesion){
						$scope.adheridoReady = (adhesion == 0) ? 0 : adhesion.state;
						
						if($scope.adheribleReady !== false)
							$scope.adherido = $scope.adheridoReady;

						$scope.adhesion = adhesion;
					});

					item.adherible(accionista).then(function(adherible){
						if($scope.adheridoReady !== false)
							$scope.adherido = $scope.adheridoReady;
						$scope.adherible = adherible;
						$scope.adheribleReady = true;
					});

					$scope.adherir = function(item){
						var before = $scope.adherido;
						$scope.adherido = -1;
						item.adherir(accionista).then(function(stat){
							if(!stat){
								$scope.adherido = before;
								return;
							}
							checkAdhesion(item);
							logger.logSuccess("id00202_app:logger:adhesion-success");
						}).catch(function(){
							$scope.adherido = before;
							logger.logError("id00201_app:logger:server-error");
						});
					};
				});
			};

			itemsManager.getByTypeID("initiatives",$routeParams.id).then(function(items){
				$scope.item = items["initiatives"][0];

				checkAdhesion($scope.item);
			}).catch(function(){
				$location.path("/404").replace();
			});

			

			$scope.cancelarAdhesion = function(item){
				$scope.adherido = -1;
				item.cancelarAdhesion($scope.adhesion.id).then(function(){
					logger.logSuccess("id00203_app:logger:adhesion-cancel");
					checkAdhesion(item);
				}).catch(function(){
					logger.logError("id00201_app:logger:server-error");
				});
			};
		}
	]).controller('ofertaPublicaCtrl',[
		"$scope","$routeParams","itemsManager","logger","accionistasManager","Item","$location", function($scope,$routeParams,itemsManager,logger,accionistasManager,Item,$location){
			$scope.item = new Item({
					title: '',
					description: '',
					message: ''
				}, "offers");

			$scope.adherido = -1;
			$scope.adherible = true;

			var checkAdhesion = function(item){
				accionistasManager.getAccionista().then(function(accionista){
					//Estas 2 variables son un semaphore improvisado:
					$scope.adheribleReady = false;
					$scope.adheridoReady = false;

					item.adherido(accionista).then(function(adhesion){
						$scope.adheridoReady = (adhesion == 0) ? 0 : adhesion.state
						
						if($scope.adheribleReady !== false)
							$scope.adherido = $scope.adheridoReady;

						$scope.adhesion = adhesion;
					});

					item.adherible(accionista).then(function(adherible){
						if($scope.adheridoReady !== false)
							$scope.adherido = $scope.adheridoReady;
						$scope.adherible = adherible;
						$scope.adheribleReady = true;
					});

					$scope.adherir = function(item){
						var before = $scope.adherido;
						$scope.adherido = -1;
						item.adherir(accionista).then(function(stat){
							if(!stat){
								$scope.adherido = before;
								return;
							}
							checkAdhesion(item);
							logger.logSuccess("id00202_app:logger:adhesion-success");
						}).catch(function(){
							$scope.adherido = before;
							logger.logError("id00201_app:logger:server-error");
						});
					};
				});
			};

			itemsManager.getByTypeID("offers",$routeParams.id).then(function(items){
				$scope.item = items["offers"][0];

				checkAdhesion($scope.item);
			}).catch(function(){
				$location.path("/404").replace();
			});

			

			$scope.cancelarAdhesion = function(item){
				$scope.adherido = -1;
				item.cancelarAdhesion($scope.adhesion.id).then(function(){
					logger.logSuccess("id00203_app:logger:adhesion-cancel");
					checkAdhesion(item);
				}).catch(function(){
					logger.logError("id00201_app:logger:server-error");
				});
			};
		}
	]).controller('peticionPublicaCtrl',[
		"$scope","$routeParams","itemsManager","logger","accionistasManager","Item","$location", function($scope,$routeParams,itemsManager,logger,accionistasManager,Item,$location){
			$scope.item = new Item({
					title: '',
					description: '',
					message: ''
				}, "requests");

			$scope.adherido = -1;
			$scope.adherible = true;

			var checkAdhesion = function(item){
				accionistasManager.getAccionista().then(function(accionista){
					//Estas 2 variables son un semaphore improvisado:
					$scope.adheribleReady = false;
					$scope.adheridoReady = false;

					item.adherido(accionista).then(function(adhesion){
						$scope.adheridoReady = (adhesion == 0) ? 0 : adhesion.state;
						
						if($scope.adheribleReady !== false)
							$scope.adherido = $scope.adheridoReady;

						$scope.adhesion = adhesion;
					});

					item.adherible(accionista).then(function(adherible){
						if($scope.adheridoReady !== false)
							$scope.adherido = $scope.adheridoReady;
						$scope.adherible = adherible;
						$scope.adheribleReady = true;
					});

					$scope.adherir = function(item){
						var before = $scope.adherido;
						$scope.adherido = -1;
						item.adherir(accionista).then(function(stat){
							if(!stat){
								$scope.adherido = before;
								return;
							}
							checkAdhesion(item);
							logger.logSuccess("id00202_app:logger:adhesion-success");
						}).catch(function(){
							$scope.adherido = before;
							logger.logError("id00201_app:logger:server-error");
						});
					};
				});
			};

			itemsManager.getByTypeID("requests",$routeParams.id).then(function(items){
				$scope.item = items["requests"][0];

				checkAdhesion($scope.item);
			}).catch(function(){
				$location.path("/404").replace();
			});

			

			$scope.cancelarAdhesion = function(item){
				$scope.adherido = -1;
				item.cancelarAdhesion($scope.adhesion.id).then(function(){
					logger.logSuccess("id00203_app:logger:adhesion-cancel");
					checkAdhesion(item);
				}).catch(function(){
					logger.logError("id00201_app:logger:server-error");
				});
			};
		}
	]).controller('newProposalCtrl',[
		"$scope","itemsManager","logger","$route","Item","$location", function($scope,itemsManager,logger,$route,Item,$location){
			$scope.acceptTerms = false;

			($scope.clear = function(){
				$scope.item = new Item({
					title: '',
					justification: '',
					description: '',
					message: ''
				}, "proposals");
			})();

			$scope.allowItem = function(){
				return $scope.item.title         != "" &&
					   $scope.item.justification != "" &&
					   $scope.item.description   != "";
			};

			itemsManager.testNew("proposals").then(function(postable){
				if (postable.error !== undefined)
                                {
                                    logger.logError(postable.error);
                                    $location.path('/app/foro/dashboard-foro');
                                }
                                else if(!postable){
					logger.logError("id00399_app:foro:newitem:test");
					$location.path('/app/foro/dashboard-foro');
				}
			});

			$scope.submit = function(){
				$scope.loading = true;
				itemsManager.newItem($scope.item).then(function(data){
					$scope.loading = false;
					if(data.error === undefined){
						$location.path("/app/foro/actividad-personal/"+types[$scope.item.type]).search("id",$scope.item.id);
						logger.logSuccess("id00204_app:logger:new-proposal-success");
					}else{
						logger.logError(data.error);
					}
				}).catch(function(){
					logger.logError("id00201_app:logger:server-error");
				});
			}

			$scope.clear();
		}
	]).controller('newInitiativeCtrl',[
		"$scope","itemsManager","logger","$route","Item","$location", function($scope,itemsManager,logger,$route,Item,$location){
			$scope.acceptTerms = false;

			($scope.clear = function(){
				$scope.item = new Item({
					title: '',
					description: '',
					message: ''
				}, "initiatives");
			})();

			$scope.allowItem = function(){
				return $scope.item.title         != "" &&
					   $scope.item.justification != "" &&
					   $scope.item.description   != "";
			};

			itemsManager.testNew("initiatives").then(function(postable){
				if (postable.error !== undefined)
                                {
                                    logger.logError(postable.error);
                                    $location.path('/app/foro/dashboard-foro');
                                }
                                else if(!postable){
					logger.logError("id00399_app:foro:newitem:test");
					$location.path('/app/foro/dashboard-foro');
				}
			});

			$scope.submit = function(){
				$scope.loading = true;
				itemsManager.newItem($scope.item).then(function(data){
					$scope.loading = false;
					if(data.error === undefined){
						$location.path("/app/foro/actividad-personal/"+types[$scope.item.type]).search("id",$scope.item.id);
						logger.logSuccess("id00205_app:logger:new-initiative-success");
					}else{
						logger.logError(data.error);
					}
				}).catch(function(){
					logger.logError("id00201_app:logger:server-error");
				});
			}

			$scope.clear();
		}
	]).controller('newRepresentationCtrl',[
		"$scope","itemsManager","logger","$route","Item","$location","accionistasManager","$modal","localize", 
		function($scope,itemsManager,logger,$route,Item,$location,accionistasManager,$modal,localize){
			$scope.acceptTerms = false;

			($scope.clear = function(){
				$scope.item = new Item({
					title: '',
					description: '',
					message: ''
				},"offers");
				$scope.item.type = -1;
			})();

			$scope.allowItem = function(){
				return $scope.item.title         != "" &&
					   $scope.item.justification != "" &&
					   $scope.item.description   != "";
			}

			$scope.postableoffers   = true;
			$scope.postablerequests = true;

			var postable = true;
			itemsManager.testNew("offers").then(function(postableoffers){
                                postableoffers = postableoffers.error === undefined ?  true : false;
				$scope.postableoffers = postableoffers;
				postable = postableoffers;
				itemsManager.testNew("requests").then(function(postablerequests){
                                        postablerequests = postablerequests.error === undefined ?  true : false;
					$scope.postablerequests = postablerequests;
					postable = postable || postablerequests;

					if(!$scope.postableoffers && $scope.postablerequests){
						$scope.item.type = "requests";
					} else {
						$scope.item.type = "offers";
					}

					if(!postable){
						var modalInstance = $modal.open({
							templateUrl:WebDefault + "/home/sharedviews/modalInstance.html",
							controller: 'representationModalCtrl',
							backdrop:'static',
							resolve: {
								title: function() {
									return localize.getLocalizedString("id00299_app:representation:atencion");
								},
								body: function() {
									return localize.getLocalizedString("id00232_app:representation:error-body");
								}
							}
						});
					}
				});		
			});

			$scope.submit = function(){
				$scope.loading = true;
				itemsManager.newItem($scope.item).then(function(success){
					$scope.loading = false;
					if(success.error === undefined){
						$location.path("/app/foro/actividad-personal/"+types[$scope.item.type]).search("id",$scope.item.id);
						if($scope.item.type == "offers"){
							logger.logSuccess("id00185_app:logger:new-offer-success");
						} else if($scope.item.type == "requests"){
							logger.logSuccess("id00187_app:logger:new-request-success");
						}
					}else{
						logger.logError(success.error);
					}
				}).catch(function(){
					$scope.loading = false;
					logger.logError("id00186_app:logger:new-representation-error");
				});
			}			
		}
	]).controller('representationModalCtrl',[
		'$scope','$modalInstance','body','title','$location',
		function($scope,$modalInstance,body,title,$location){
			$scope.body = body;
			$scope.title= title;

			$scope.ok = function(){
				$modalInstance.close();
				$location.path('/app/foro/dashboard-foro');
			}
		}
	]).controller('textareaModalCtrl',[
		'$scope','$modalInstance','body','title',
		function($scope,$modalInstance,body,title){
			$scope.body   = body;
			$scope.title  = title;
			$scope.message= '';

			$scope.ok = function(){
				if($scope.message !== '')
					$modalInstance.close($scope.message);
			};
			$scope.cancel = function(){
				$modalInstance.dismiss(false);
			}
		}
	]).controller('adhesionModalCtrl',[
		'$scope','$modalInstance',
		function($scope,$modalInstance){
			$scope.ok = function(){
				$modalInstance.close();
			}
		}
	]);

}).call(this);