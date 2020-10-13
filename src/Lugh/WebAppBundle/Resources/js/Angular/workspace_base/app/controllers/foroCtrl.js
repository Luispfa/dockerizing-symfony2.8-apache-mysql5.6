(function() {
	'use strict';

	var actividadPersonalURL = "/app/foro/actividad-personal/";
	var actividadPublicaURL  = "/app/foro/actividad-publica/";

	angular.module('workspaceControllers'
	).controller('foroPublicAll',[
		"$scope","itemsManager","$location","accionistasManager","typeService","localize","ItemsList", function($scope, itemsManager, $location, accionistasManager, typeService, localize,ItemsList){
			var types = typeService.getTypesToURL();
			$scope.i18n = localize.getLocalizedString;

			$scope.items = new ItemsList();
			$scope.types = typeService.getShortTags(true);
			$scope.types['todo'] = 'id00080_app:foro:nav:all';
			$scope.filterType    = 'todo';

			itemsManager.getByState("public").then(function(itemList){
				if(itemList.threads){
					itemList.threads = [];
				}
				$scope.items = itemList;
			});

			$scope.loadItem = function(item){
				accionistasManager.getAccionista().then(function(accionista){
					if(accionista.id === item.autor.id){
						$location.path(actividadPersonalURL+types[item.type]).search("id",item.id);
					}
					else{
						$location.path(actividadPublicaURL +types[item.type]).search("id",item.id);
					}
				});
			};
		}
	]).controller('foroPublicProposals',[
		"$scope","itemsManager","$location","accionistasManager","typeService","ItemsList",function($scope, itemsManager, $location, accionistasManager, typeService, ItemsList){
			var types = typeService.getTypesToURL();
			$scope.items = new ItemsList();

			itemsManager.getByTypeState("proposals","public").then(function(itemList){
				$scope.items = itemList;
			});

			$scope.loadItem = function(item){
				accionistasManager.getAccionista().then(function(accionista){
					if(accionista.id === item.autor.id){
						$location.path(actividadPersonalURL+types[item.type]).search("id",item.id);
					}
					else{
						$location.path(actividadPublicaURL +types[item.type]).search("id",item.id);
					}
				});
			};
		}
	]).controller('foroPublicInitiatives',[
		"$scope","itemsManager","$location","accionistasManager","typeService","ItemsList",function($scope, itemsManager, $location, accionistasManager, typeService,ItemsList){
			var types = typeService.getTypesToURL();
			$scope.items = new ItemsList();

			itemsManager.getByTypeState("initiatives","public").then(function(itemList){
				$scope.items = itemList;
			});

			$scope.loadItem = function(item){
				accionistasManager.getAccionista().then(function(accionista){
					if(accionista.id === item.autor.id){
						$location.path(actividadPersonalURL+types[item.type]).search("id",item.id);
					}
					else{
						$location.path(actividadPublicaURL +types[item.type]).search("id",item.id);
					}
				});
			};
		}
	]).controller('foroPublicOffers',[
		"$scope","itemsManager","$location","accionistasManager","typeService","ItemsList",function($scope, itemsManager, $location, accionistasManager, typeService,ItemsList){
			var types = typeService.getTypesToURL();
			$scope.items = new ItemsList();

			itemsManager.getByTypeState("offers","public").then(function(itemList){
				$scope.items = itemList;
			});

			$scope.loadItem = function(item){
				accionistasManager.getAccionista().then(function(accionista){
					if(accionista.id === item.autor.id){
						$location.path(actividadPersonalURL+types[item.type]).search("id",item.id);
					}
					else{
						$location.path(actividadPublicaURL +types[item.type]).search("id",item.id);
					}
				});
			};
		}
	]).controller('foroPublicRequests',[
		"$scope","itemsManager","$location","accionistasManager","typeService","ItemsList",function($scope, itemsManager, $location, accionistasManager, typeService,ItemsList){
			var types = typeService.getTypesToURL();
			$scope.items = new ItemsList();

			itemsManager.getByTypeState("requests","public").then(function(itemList){
				$scope.items = itemList;
			});

			$scope.loadItem = function(item){
				accionistasManager.getAccionista().then(function(accionista){
					if(accionista.id === item.autor.id){
						$location.path(actividadPersonalURL+types[item.type]).search("id",item.id);
					}
					else{
						$location.path(actividadPublicaURL +types[item.type]).search("id",item.id);
					}
				});
			};
		}
	]).controller('foroPersonalAll',[
		"$scope","itemsManager","$location","accionistasManager", "stateService", "localize","typeService","ItemsList", function($scope, itemsManager, $location, accionistasManager, stateService, localize, typeService,ItemsList){
			var types = typeService.getTypesToURL();

			$scope.i18n        = localize.getLocalizedString;
			$scope.states      = stateService.getIDTags();
			$scope.states[0]   = "id00080_app:foro:nav:all";
			$scope.filterState = "0";
			$scope.tags = stateService.getIDTags();

			$scope.items = new ItemsList();
			accionistasManager.getAccionista().then(function(accionista){
				accionista.getItems().then(function(itemList){
					$scope.items = itemList;
				});
			});

			$scope.loadItem = function(item){
				$location.path(actividadPersonalURL+types[item.type]).search("id",item.id);
			};
		}
	]).controller('foroPersonalProposals',[
		"$scope","itemsManager","$location","accionistasManager", "stateService", "localize","typeService","ItemsList", function($scope, itemsManager, $location, accionistasManager, stateService, localize, typeService,ItemsList){
			var types = typeService.getTypesToURL();

			$scope.i18n        = localize.getLocalizedString;
			$scope.states      = stateService.getIDTags();
			$scope.states[0]   = "id00080_app:foro:nav:all";
			$scope.filterState = "0";
			$scope.tags = stateService.getIDTags();

			$scope.items = new ItemsList();
			accionistasManager.getAccionista().then(function(accionista){
				accionista.getProposals().then(function(itemList){
					$scope.items = itemList;
				});
			});

			$scope.loadItem = function(item){
				$location.path(actividadPersonalURL+types[item.type]).search("id",item.id);
			};
		}
	]).controller('foroPersonalInitiatives',[
		"$scope","itemsManager","$location","accionistasManager", "stateService", "localize","typeService","ItemsList", function($scope, itemsManager, $location, accionistasManager, stateService, localize, typeService,ItemsList){
			var types = typeService.getTypesToURL();

			$scope.i18n        = localize.getLocalizedString;
			$scope.states      = stateService.getIDTags();
			$scope.states[0]   = "id00080_app:foro:nav:all";
			$scope.filterState = "0";
			$scope.tags = stateService.getIDTags();
			
			$scope.items = new ItemsList();
			accionistasManager.getAccionista().then(function(accionista){
				accionista.getInitiatives().then(function(itemList){
					$scope.items = itemList;
				});
			});

			$scope.loadItem = function(item){
				$location.path(actividadPersonalURL+types[item.type]).search("id",item.id);
			};
		}
	]).controller('foroPersonalOffers',[
		"$scope","itemsManager","$location","accionistasManager", "stateService", "localize","typeService","ItemsList", function($scope, itemsManager, $location, accionistasManager, stateService, localize, typeService,ItemsList){
			var types = typeService.getTypesToURL();

			$scope.i18n        = localize.getLocalizedString;
			$scope.states      = stateService.getIDTags();
			$scope.states[0]   = "id00080_app:foro:nav:all";
			$scope.filterState = "0";
			$scope.tags = stateService.getIDTags();
			
			$scope.items = new ItemsList();
			accionistasManager.getAccionista().then(function(accionista){
				accionista.getOffers().then(function(itemList){
					$scope.items = itemList;
				});
			});

			$scope.loadItem = function(item){
				$location.path(actividadPersonalURL+types[item.type]).search("id",item.id);
			};
		}
	]).controller('foroPersonalRequests',[
		"$scope","itemsManager","$location","accionistasManager", "stateService", "localize","typeService","ItemsList", function($scope, itemsManager, $location, accionistasManager, stateService, localize, typeService,ItemsList){
			var types = typeService.getTypesToURL();

			$scope.i18n        = localize.getLocalizedString;
			$scope.states      = stateService.getIDTags();
			$scope.states[0]   = "id00080_app:foro:nav:all";
			$scope.filterState = "0";
			$scope.tags = stateService.getIDTags();
			
			$scope.items = new ItemsList();
			accionistasManager.getAccionista().then(function(accionista){
				accionista.getRequests().then(function(itemList){
					$scope.items = itemList;
				});
			});

			$scope.loadItem = function(item){
				$location.path(actividadPersonalURL+types[item.type]).search("id",item.id);
			};
		}
	]).controller('foroPersonalPendientes',[
		"$scope","itemsManager","$location","accionistasManager", "stateService", "typeService","ItemsList","adhesionsManager", 
                function($scope, itemsManager, $location, accionistasManager, stateService, typeService,ItemsList,adhesionsManager){
			var types = typeService.getTypesToURL();
			$scope.tags = stateService.getIDTags();

			$scope.items = new ItemsList();
			/*itemsManager.getByState("pending").then(function(itemsList){
				$scope.items.merge(itemsList);
			});*/

			itemsManager.getByState("retornate").then(function(itemsList){
				$scope.items.merge(itemsList);
			});

			$scope.adhesions = {};
                        accionistasManager.getAccionista().then(function(accionista){
                            adhesionsManager.loadAllByState(1).then(function(adhesions){
                                
                                    for(var i in adhesions){
                                        for (var j in adhesions[i]){
                                            if(adhesions[i][j].accionista.id == accionista.id)
                                                adhesions[i].splice(j,1);
                                        }
                                    }
                                    $scope.adhesions = adhesions;
                                    var length = 0;
                                    for(var i in $scope.adhesions){
                                            length+=$scope.adhesions[i].length;
                                    }
                                    $scope.adhesions.length = length;
                            });
                        });
			

			$scope.loadAdhesion = function(adhesion,type){
				$location.path('/app/foro/adhesion/'+type).search('id',adhesion.id);
			};

			$scope.loadItem = function(item,type){
				$location.path(actividadPersonalURL+types[item.type || type]).search("id",item.id);
			};
		}
	]).controller('foroPersonalAdhesions',[
		"$scope","itemsManager","$location","accionistasManager","stateService","typeService","localize","ItemsList", "logger",
                function($scope,itemsManager,$location,accionistasManager,stateService,typeService,localize,ItemsList, logger){
			var types = typeService.getTypesToURL();

			$scope.items = new ItemsList();
			$scope.i18n  = localize.getLocalizedString;
			$scope.types         = typeService.getShortTags(true);
			$scope.types['todo'] = 'id00080_app:foro:nav:all';
			$scope.filterType    = 'todo';
			$scope.tags = stateService.getIDTags();

			accionistasManager.getAccionista().then(function(accionista){
				itemsManager.getAdheredByAccionista(accionista.id).then(function(itemsList){
                                    /*for(var i in itemsList){
                                            if(itemsList[i].state == 4)
                                                    itemsList.splice(i,1);
                                    }*/
					$scope.items = itemsList;
				});
			});

			$scope.loadItem = function(item){
				accionistasManager.getAccionista().then(function(accionista){
                                    if (item.state != 3 && item.state != 4)
                                    {
                                        if(accionista.id === item.autor.id){
						$location.path(actividadPersonalURL+types[item.type]).search("id",item.id);
					}
					else{
						$location.path(actividadPublicaURL +types[item.type]).search("id",item.id);
					}
                                    }
                                    else
                                    {
                                        logger.logError("id00394_app:app:nolink");
                                    }
				});
			};
		}
	]);
}).call(this);