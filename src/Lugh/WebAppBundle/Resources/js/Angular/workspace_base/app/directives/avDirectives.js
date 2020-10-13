(function() {
	'use strict';
    
    var loadStep = function(view,object,piece,id,attrs,ele,$scope,stepsManager,eleId,data){
		var stepData                = {};
		stepData.type               = attrs['stepId'];
		stepData.id                 = id;
		stepData.title              = attrs['title'];
		stepData.title_body         = attrs['titleBody'];
		stepData.subtitle_body      = attrs['subtitleBody'];
		stepData.body               = attrs['body'];
		stepData.piece              = piece;
		stepData.path               = view;
		stepData.view               = ele;

		if(eleId !== undefined){
			var element = $('<div id="' + eleId + stepData.id + '"></div>');
			element.appendTo(ele);
			stepData.view = element;
		}

		for(var d in data){
			if(data[d] !== undefined)
				stepData[d] = data[d];
		}

		var instance = new object(stepData,$scope);
		stepsManager.setStep(instance);
	};
	
	var loadConfirmAndEnd = function(objectConfirm, objectEnd, attrs, eleId, ele,$scope,stepsManager)
	{
		var id = attrs['stepId'] + '-' + 'ConfirmStep';
		var view = '/workspace/views/app/av/voto/Confirm.html';
		loadStep(view,objectConfirm,'confirm',id,attrs,ele,$scope,stepsManager, 
			{
				eleId: eleId,
				title: 'Confirm'
			}
		);
		id = attrs['stepId'] + '-' + 'EndStep';
		view = '/workspace/views/app/av/voto/Finish.html';
		loadStep(view,objectEnd,'end',id,attrs,ele,$scope,stepsManager, 
			{
				eleId: eleId,
				title: 'Finish'
			}
		);
	}
	
	var loadVote = function(object,attrs, eleId, ele,$scope,stepsManager, $http, $q, /*[assocArray]*/ data)
	{
		var deferred = $q.defer();

		$http.get(ApiBase+'/tipovotos').success(function(types){
			types = types['tipoVotos'];
			var view = '/workspace/views/app/av/voto/Vote.html';

			var count = 0;
			for(var k in types){
				count++;
				var id = attrs['stepId'] + '-' + types[k].id;
				var dt = data.fullObjectize();

				dt.votoIndex = count;
				dt.type = types[k];
				loadStep(view,object,'vote',id,attrs,ele,$scope.$new(false,$scope),stepsManager,eleId,
					{
						data:     dt,
						voteType: types[k],
						title:    types[k].tag
					}
				);
			}
			deferred.resolve();
		});

		return deferred.promise;
	}

	var getVoteDelegateData = function(accionistasManager, $modal, $q, assocArray,$localStorage,localize, votoUtils){
		var deferred = $q.defer();
		var stepData = new assocArray();
		
		stepData.push('data', new assocArray());
		stepData.data.push('data', {}); //es para que el data llegue al scope del controlador y al stepsManager
		votoUtils.getPreviousAction().then(function(accion){
			if (accion['lastAccion'].error !== undefined)
			{
				deferred.reject(accion['lastAccion'].error);
				return;
			}
			if(accion['lastAccion'].length != 0){
				
                                // No mostramos mas el modal, directamente el paso de votacion
                                deferred.resolve(stepData);
                               
                                /*if(accion['lastAccion'][0].discr != 2){
					//deferred.reject(stepData);
					deferred.resolve(stepData);
				}
				else{
					var previousAccion = accion['lastAccion'][0];

					var previousVote  = previousAccion.votacion;
					var previousAbsAd = previousAccion.voto_abs_adicional;

					var loadPreviousVotes = function(){
						if($localStorage['Config.vote.loadPreviousVote'] != 1){
							deferred.resolve(new assocArray());
							return;
						}
						
						stepData.data.data.abs_adicional = previousAbsAd;
						for(var i in stepData.data.data.abs_adicional){
							stepData.data.data.abs_adicional[i].value = stepData.data.data.abs_adicional[i].opcion_voto.id;
						}

						if(previousVote){

							stepData.data.data.previousVote = previousVote;
						}

						deferred.resolve(stepData);
					}

					var modalInstance = $modal.open({
						templateUrl:WebDefault + "/workspace/views/app/av/voto/modal.html",
						controller: 'previousModalAvCtrl',
						backdrop: 'static',
						resolve: {
							loadAccion: function(){ return loadPreviousVotes; }
						}
					});
				}*/
			} else {
				deferred.resolve(stepData);
			}
		}).catch(function(accion){
			deferred.reject(accion);
		});

		return deferred.promise;
	}
		
	var leave = function($localStorage, AppService, $location){
			/*@TODO: solo voto o voto con más aplicaciones*/
			if(!$localStorage.foro && !$localStorage.derecho && !$localStorage.av){
				AppService.doLogout();
			} else {
				$location.path("/");
			}
	};
    
	angular.module('workspaceControllers'
	).directive('uiAvWizardForm',[
		"$compile","$timeout","$rootScope","assocArray","stepsManager","votoUtils","typeAvVotoPersistence","logger",
		function($compile, $timeout,$rootScope,assocArray,stepsManager,votoUtils,  typeVotoPersistence, logger){
			var directives = {
				"arbitrary-av-view":"arbitrary-av-step",
				"shares":           "shares-step",
				"vote-delegate":    "vote-delegate-step",
				"DCICV":            "intention-delegation-vote-step",
				"DSISV":            "non-intention-delegation-non-vote-step",
				"DSICV":            "non-intention-delegation-vote-step",
				"DCISV":            "intention-delegation-non-vote-step",
				"vote":             "vote-step",
                "vote-av":          "vote-av-step",
				"confirm":          "confirm-step",
				"end":              "end-step"
			};

			var loadConfig = function(config){
				var code = '<div>';
				var directive,title,data,id;
				for(var i in config){
					directive = directives[config[i].type];
					title     = config[i].title;
					data      = config[i].data;
					id        = config[i].id;

					code += '<' + directive + ' ';

					for(var j in data){
						code += j + '="' + data[j] + '" ';
					}

					code += 'title="'+ title +'" step-id="' + id + '"></' + directive + '>\n';
				}
				code += "</div>";
				return code;
			};

			return {
				//controller: "wizardVotoCtrl",
				link: function($scope,ele,attrs,controller){
					votoUtils.init();
					var wizard = "";
					typeVotoPersistence.getPatternVoto().then(function(vototypeData){
						$scope.config = vototypeData;
						stepsManager.init(ele);

						wizard = loadConfig($scope.config);
						ele.html($(wizard).contents());
						$compile(ele.contents())($scope);

						$timeout(
							function() {
								stepsManager.directiveFinish();
							}
						);
					}).catch(function(){
						logger.logError("id00201_app:logger:server-error");
					});
				}
			}
		}
	]).directive('arbitraryAvStep',[
		"stepsManager","ArbitraryStep",
		function(stepsManager, ArbitraryStep){
			return {
				restrict: 'E',
				controller: 'arbitraryStepCtrl',
				scope: true,
				link: function($scope,ele,attrs,controller){
					var view = '/workspace/views/app/av/voto/Arbitrary.html';
					loadStep(view,ArbitraryStep,'arbitrary',attrs['stepId'],attrs,ele,$scope,stepsManager);
				}
			}
		}
	]).directive('voteAvStep',[
		"stepsManager","VoteStep","$http","ConfirmStep","EndStep", "$q","accionistasManager","$modal","localize","assocArray","$localStorage", "logger", "AppService", "$location","votoUtils",
		function(stepsManager, VoteStep,$http, ConfirmStep, EndStep, $q,accionistasManager, $modal, localize,assocArray,$localStorage, logger, AppService, $location,votoUtils){
			return {
				restrict: 'E',
				controller: 'VoteAvCtrl',
				scope: true,
				link: function($scope,ele,attrs,controller){
					$scope.stepID = attrs['stepId'];

					ele.html("");
					var eleId = 'voteStep';
					stepsManager.wait();
					getVoteDelegateData(accionistasManager,$modal,$q,assocArray,$localStorage,localize,votoUtils).then(function(data){
						loadVote(VoteStep,attrs, eleId, ele,$scope,stepsManager, $http, $q,data).then(function(){
							loadConfirmAndEnd(ConfirmStep,EndStep,attrs, eleId, ele,$scope,stepsManager);
							stepsManager.signal();
							stepsManager.directiveFinish();
							ele.replaceWith(ele.contents());
						}); 
					}).catch(function(error){
						logger.logError(error);
						leave($localStorage, AppService, $location)
					});
				}
			}
		}
	]).directive('acreditacionButton', [
        'juntaManager','$modal','accionistasManager', '$rootScope', 'intervalService',
        function (juntaManager, $modal, accionistasManager, $rootScope, intervalService) {
            return{
                restrict: 'A',
                scope: true,
                link: function ($scope, element, attrs, ctrl) {
                    var acreditacionHideJunta = function(junta){
                        if (junta.acreditacion_enabled) {
                            accionistasManager.getAccionista().then(function(accionista){
                                if (accionista.acreditado === false) {
                                    $scope.isHide = false;
                                }
                                else {
                                    $scope.isHide = true;
                                }
                            });
                        }
                        else
                        {
                            $scope.isHide = true;
                        }
                    };

                    var acreditacionHide = function(){
                        $scope.isHide = true;
                        juntaManager.getJunta().then(function(junta) {
                            acreditacionHideJunta(junta);
                        });
                    };
                    
                    $scope.isHide = true;

                    //acreditacionHide();
                    var broadName = intervalService.subscribe('Junta');
                    
                    $rootScope.$on(broadName,function(event, args) { 
                          acreditacionHideJunta(args);
                    });

                    element.bind('click', function (event) {
                        var modalInstance = $modal.open({
                            templateUrl:WebDefault + "/workspace/sharedviews/modalInstance.html",
                            controller: 'modalAcreditacionAvCtrl',
                            backdrop: 'static',
                            resolve: {
                                acreditacionHide: function(){ return acreditacionHide; }
                            }
                        });
                        modalInstance.result.then(function() {
						  //if('success')
						});
                    });
                    element.on('$destroy', function() {
                        intervalService.unsubscribe('Junta');
                    });
                }
            };
        }

    ]).directive('questionsButton', [
        'intervalService','$rootScope',
        function (intervalService, $rootScope) {
            return{
                restrict: 'A',
                scope: true,
                link: function ($scope, element, attrs, ctrl) {
                    var broadName = intervalService.subscribe('Junta');
                    $scope.isDisabled = true;
                    
                    $rootScope.$on(broadName,function(event, args) { 
                          $scope.isDisabled = args.preguntas_enabled == false;
                    });
                    element.on('$destroy', function() {
                        intervalService.unsubscribe('Junta');
                    });
                }
            };
        }

    ]).directive('votoButton', [
        'intervalService','$rootScope',
        function (intervalService, $rootScope) {
            return{
                restrict: 'A',
                scope: true,
                link: function ($scope, element, attrs, ctrl) {
                    var broadName = intervalService.subscribe('Junta');
                    $scope.isDisabled = true;
                    
                    $rootScope.$on(broadName,function(event, args) { 
                          $scope.isDisabled = args.votacion_enabled == false;
                    });
                    element.on('$destroy', function() {
                        intervalService.unsubscribe('Junta');
                    });
                }
            };
        }

    ]).directive('desertionButton', [
        'intervalService','$rootScope',
        function (intervalService, $rootScope) {
            return{
                restrict: 'A',
                scope: true,
                link: function ($scope, element, attrs, ctrl) {
                    var broadName = intervalService.subscribe('Junta');
                    $scope.isDisabled = true;
                    
                    $rootScope.$on(broadName,function(event, args) { 
                          $scope.isDisabled = args.abandono_enabled == false;
                    });
                    element.on('$destroy', function() {
                        intervalService.unsubscribe('Junta');
                    });
                }
            };
        }

    ])/*.directive('liveButton', [
        'intervalService','$rootScope', 'paramRequest', '$localStorage',
        function (intervalService, $rootScope, paramRequest, $localStorage) {
            return{
                restrict: 'A',
                scope: true,
                link: function ($scope, element, attrs, ctrl) {
                    var broadName = intervalService.subscribe('Junta');
                    $scope.isDisabled = true;
                    
                    $rootScope.$on(broadName,function(event, args) { 
                          $scope.isDisabled = args.live_enabled == false;
                    });
                    element.on('$destroy', function() {
                        intervalService.unsubscribe('Junta');
                    });
                    element.bind('click', function (event) {
                        var url = $localStorage["Av.live.address"];
                        window.open(url, '_blank');
                    });
                }
            };
        }

    ])*/.directive('questionsButtonRacc', [
		'intervalService','$rootScope','juntaManager', 'logger',
		function (intervalService, $rootScope, juntaManager, logger) {
			return{
				restrict: 'A',
				scope: true,
				link: function ($scope, element, attrs, ctrl) {
					element.bind('click', function (event) {
						juntaManager.getJunta().then(function(junta) {
							if (!junta.preguntas_enabled) {
								logger.logError("id00506_app:logger:blocked-by-server")
								return false;
							}
							else {
								window.location.href ='#/app/av/crear-ruego';
							}
						});
					});
				}
			};
		}

	]).directive('votoButtonRacc', [
		'intervalService','$rootScope','juntaManager', 'logger',
		function (intervalService, $rootScope, juntaManager, logger) {
			return{
				restrict: 'A',
				scope: true,
				link: function ($scope, element, attrs, ctrl) {
					element.bind('click', function (event) {
						juntaManager.getJunta().then(function(junta) {
							if (!junta.votacion_enabled) {
								logger.logError("id00506_app:logger:blocked-by-server")
								return false;
							}
							else {
								window.location.href ='#/app/av/voto';
							}
						});
					});
				}
			};
		}

	]);
}).call(this);