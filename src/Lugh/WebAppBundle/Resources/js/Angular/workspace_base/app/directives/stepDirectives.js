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
		var view = '/workspace/views/app/voto/juntas/Confirm.html';
		loadStep(view,objectConfirm,'confirm',id,attrs,ele,$scope,stepsManager,
			{
				eleId: eleId,
				title: 'Confirm'
			}
		);
		id = attrs['stepId'] + '-' + 'EndStep';
		view = '/workspace/views/app/voto/juntas/Finish.html';
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
			var view = '/workspace/views/app/voto/juntas/Vote.html';

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
	};

	var getVoteDelegateData = function(accionistasManager, $modal, $q, assocArray,$localStorage,localize, votoUtils){
		var deferred = $q.defer();
		var stepData = new assocArray();

		var avPetitionCheck = (function(){
			accionistasManager.getAccionistaUpdated().then(function(accionista){
				if(accionista.getApp('av').state == 1 || accionista.getApp('av').state == 2)
				{

					var modalInstance = $modal.open({
						templateUrl:WebDefault + "/workspace/views/app/voto/juntas/modalavcheck.html",
						controller: 'previousAvCheckModalCtrl',
						backdrop: 'static',
						resolve: {
							loadAccion: function(){ return previousActionCheck; }
						}
					});

				}else{
					previousActionCheck();
				}

			});
		}).bind(this);

		var previousActionCheck = (function(){

			stepData.push('data', new assocArray());
			stepData.data.push('data', {}); //es para que el data llegue al scope del controlador y al stepsManager
			votoUtils.getPreviousAction().then(function(accion){
				if (accion['lastAccion'].error !== undefined)
				{
					deferred.reject(accion['lastAccion'].error);
					return;
				}
				if(accion['lastAccion'].length != 0){

					if(accion['lastAccion'][0].discr != 0 && accion['lastAccion'][0].discr != 1){
						//deferred.reject(stepData);
						deferred.resolve(stepData);
					}
					else{
						var previousAccion = accion['lastAccion'][0];

						var previousVote  = previousAccion.votacion;
						var previousDeleg = previousAccion.delegado;
						var previousSust  = previousAccion.sustitucion;
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

							if(previousDeleg){
								stepData.data.data.choiceVoteDelegation = "delegation";
								stepData.data.data.sustitucion   = previousSust;

								if(previousDeleg['is_director']){
									stepData.data.data.choiceDelegation = "chairman";
									stepData.data.data.person = {nombre: localize.getLocalizedString("id00396_app:voto:delegation:chairman")};;
								} else if(previousDeleg['is_secretary']){
									stepData.data.data.choiceDelegation = "secretary";
									stepData.data.data.person = {nombre: localize.getLocalizedString("id00422_app:voto:delegation:secretary")};;
								} else if(previousDeleg['is_conseller']){
									stepData.data.data.choiceDelegation = "list";
									stepData.data.data.conseller = previousDeleg;
								} else {
									stepData.data.data.choiceDelegation = "person";
									stepData.data.data.person = previousDeleg;
									stepData.data.data.person.documentNum = stepData.data.data.person['document_num'];
									stepData.data.data.person.documentType= stepData.data.data.person['document_type'];
								}
							} else {
								stepData.data.data.choiceVoteDelegation = "vote";
							}

							if(previousVote){
								if(previousDeleg && previousVote.length > 0){
									stepData.data.data.choiceInstructions = 'yes';
								}
								if(previousDeleg && previousVote.length === 0){
									stepData.data.data.choiceInstructions = 'no';
								}

								stepData.data.data.previousVote = previousVote;
							}

							deferred.resolve(stepData);
						};

						var modalInstance = $modal.open({
							templateUrl:WebDefault + "/workspace/views/app/voto/juntas/modal.html",
							controller: 'previousModalCtrl',
							backdrop: 'static',
							resolve: {
								loadAccion: function(){ return loadPreviousVotes; }
							}
						});
					}
				} else {
					deferred.resolve(stepData);
				}
			}).catch(function(accion){
				deferred.reject(accion);
			});

		}).bind(this);

		//force refresh local storage => then:
                // 2020 - First Voto, then AV, not necesary check avPetitionCheck()
		//if($localStorage.platforms.av == true){
		//	avPetitionCheck();
		//}else
			previousActionCheck();

		return deferred.promise;
	};

	var leave = function($localStorage, AppService, $location){
		/*@TODO: solo voto o voto con más aplicaciones*/
		if(!$localStorage.foro && !$localStorage.derecho && !$localStorage.av){
			AppService.doLogout();
		} else {
			$location.path("/");
		}
	};

	angular.module('workspaceControllers'
	).directive('arbitraryStep',[
		"stepsManager","ArbitraryStep",
		function(stepsManager, ArbitraryStep){
			return {
				restrict: 'E',
				controller: 'arbitraryStepCtrl',
				scope: true,
				link: function($scope,ele,attrs,controller){
					var view = '/workspace/views/app/voto/juntas/Arbitrary.html';
					loadStep(view,ArbitraryStep,'arbitrary',attrs['stepId'],attrs,ele,$scope,stepsManager);
				}
			}
		}
	]).directive('sharesStep',[
		"SharesStep","stepsManager",
		function(SharesStep,stepsManager){
			return {
				restrict: 'E',
				controller: 'sharesStepCtrl',
				scope: true,
				link: function($scope,ele,attrs,controller){
					var view = '/workspace/views/app/voto/juntas/Shares.html';
					loadStep(view,SharesStep,'shares',attrs['stepId'],attrs,ele,$scope,stepsManager);
				}
			};
		}
	]).directive('intentionDelegationVoteStep',[
		"VoteDelegationStep","DelegationStep","InstructionsStep","stepsManager", "$http","VoteStep","ConfirmStep", "EndStep", "$q","accionistasManager","$modal","assocArray","$localStorage","localize","logger","$location","AppService","votoUtils",
		function(VoteDelegationStep,DelegationStep,InstructionsStep,stepsManager, $http, VoteStep, ConfirmStep,EndStep,$q, accionistasManager, $modal, assocArray,$localStorage,localize,logger,$location, AppService, votoUtils){
			return {
				restrict: 'E',
				controller: 'intentionDelegationVoteCtrl',
				scope: true,
				link: function($scope,ele,attrs,controller){

					$scope.stepID = attrs['stepId'];

					var views = [
						'/workspace/views/app/voto/juntas/Vote-or-Delegation.html',
						'/workspace/views/app/voto/juntas/Delegation.html',
						'/workspace/views/app/voto/juntas/Instructions.html'
					];

					var pieces = [
						'voteDelegation',
						'delegation',
						'instructions'
					];

					var objects = [
						VoteDelegationStep,
						DelegationStep,
						InstructionsStep
					];

					ele.html("");
					stepsManager.wait();
					getVoteDelegateData(accionistasManager,$modal,$q,assocArray,$localStorage,localize,votoUtils).then(function(data){
						var eleId = 'intentionDelegationVoteStep';
						for(var k in objects){
							var id = attrs['stepId'] + '-' + k.toString();
							loadStep(views[k],objects[k],pieces[k],id,attrs,ele,$scope,stepsManager,eleId,data.fullObjectize());
						}

						loadVote(VoteStep,attrs, eleId, ele,$scope,stepsManager, $http, $q, data).then(function(){
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
	]).directive('nonIntentionDelegationNonVoteStep',[
		"DelegationStep","stepsManager", "ConfirmStep","EndStep","accionistasManager","$modal","$q","assocArray","$localStorage","localize","logger", "AppService", "$location","votoUtils",
		function(DelegationStep,stepsManager,ConfirmStep, EndStep, accionistasManager,$modal,$q,assocArray,$localStorage,localize, logger, AppService, $location, votoUtils){
			return {
				restrict: 'E',
				controller: 'nonIntentionDelegationNonVoteCtrl',
				scope: true,
				link: function($scope,ele,attrs,controller){

					$scope.stepID = attrs['stepId'];

					var views = [
						'/workspace/views/app/voto/juntas/Delegation.html'
					];

					var pieces = [
						'delegation'
					];

					var objects = [
						DelegationStep
					];

					ele.html("");
					stepsManager.wait();
					getVoteDelegateData(accionistasManager,$modal,$q,assocArray,$localStorage,localize,votoUtils).then(function(data){
						var eleId = 'nonIntentionDelegationNonVoteStep';
						for(var k in objects){
							var id = attrs['stepId'] + '-' + k.toString();
							loadStep(views[k],objects[k],pieces[k],id,attrs,ele,$scope,stepsManager,eleId,data);
						}
						loadConfirmAndEnd(ConfirmStep,EndStep,attrs, eleId, ele,$scope,stepsManager);
						stepsManager.signal();
						stepsManager.directiveFinish();
						ele.replaceWith(ele.contents());
					}).catch(function(error){
						logger.logError(error);
						leave($localStorage, AppService, $location)
					});

				}
			}
		}
	]).directive('nonIntentionDelegationVoteStep',[
		"VoteDelegationStep","DelegationStep","stepsManager", "$http","VoteStep","ConfirmStep","EndStep", "$q","accionistasManager","$modal","assocArray","$localStorage","localize", "logger", "AppService", "$location","votoUtils",
		function(VoteDelegationStep,DelegationStep,stepsManager, $http, VoteStep, ConfirmStep, EndStep, $q,accionistasManager, $modal, assocArray,$localStorage,localize, logger, AppService, $location, votoUtils){
			return {
				restrict: 'E',
				controller: 'nonIntentionDelegationVoteCtrl',
				scope: true,
				link: function($scope,ele,attrs,controller){

					$scope.stepID = attrs['stepId'];

					var views = [
						'/workspace/views/app/voto/juntas/Vote-or-Delegation.html',
						'/workspace/views/app/voto/juntas/Delegation.html'
					];

					var pieces = [
						'voteDelegation',
						'delegation',
					];

					var objects = [
						VoteDelegationStep,
						DelegationStep
					];

					ele.html("");
					stepsManager.wait();
					getVoteDelegateData(accionistasManager,$modal,$q,assocArray,$localStorage,localize,votoUtils).then(function(data){
						var eleId = 'nonIntentionDelegationVoteStep';
						for(var k in objects){
							var id = attrs['stepId'] + '-' + k.toString();
							loadStep(views[k],objects[k],pieces[k],id,attrs,ele,$scope,stepsManager,eleId,data);
						}

						loadVote(VoteStep,attrs, eleId, ele,$scope,stepsManager, $http, $q, data).then(function(){
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
	]).directive('intentionDelegationNonVoteStep',[
		"$compile","VoteDelegationStep","DelegationStep","InstructionsStep","stepsManager", "$http","VoteStep","EndStep", "$q","ConfirmStep","accionistasManager","$modal","assocArray","$localStorage", "localize", "logger", "AppService", "$location","votoUtils",
		function($compile, VoteDelegationStep,DelegationStep,InstructionsStep,stepsManager, $http, VoteStep,EndStep, $q, ConfirmStep, accionistasManager, $modal, assocArray, $localStorage, localize, logger, AppService, $location,votoUtils){
			return {
				restrict: 'E',
				controller: 'intentionDelegationNonVoteCtrl',
				scope: true,
				link: function($scope,ele,attrs,controller){

					$scope.stepID = attrs['stepId'];

					var views = [
						'/workspace/views/app/voto/juntas/Delegation.html',
						'/workspace/views/app/voto/juntas/Instructions.html'
					];

					var pieces = [
						'delegation',
						'instructions'
					];

					var objects = [
						DelegationStep,
						InstructionsStep
					];

					ele.html("");
					stepsManager.wait();
					getVoteDelegateData(accionistasManager,$modal,$q,assocArray,$localStorage,localize,votoUtils).then(function(data){
						var eleId = 'intentionDelegationNonVoteStep';
						for(var k in objects){
							var id = attrs['stepId'] + '-' + k.toString();
							loadStep(views[k],objects[k],pieces[k],id,attrs,ele,$scope,stepsManager,eleId,data);
						}

						loadVote(VoteStep,attrs, eleId, ele,$scope,stepsManager, $http, $q, data).then(function(){
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
	]).directive('voteStep',[
		"stepsManager","VoteStep","$http","ConfirmStep","EndStep", "$q","accionistasManager","$modal","localize","assocArray","$localStorage", "logger", "AppService", "$location","votoUtils",
		function(stepsManager, VoteStep,$http, ConfirmStep, EndStep, $q,accionistasManager, $modal, localize,assocArray,$localStorage, logger, AppService, $location,votoUtils){
			return {
				restrict: 'E',
				controller: 'nonIntentionNonDelegationVoteCtrl',
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
	]).directive('confirmStep',[
		"stepsManager","ConfirmStep",
		function(stepsManager, ConfirmStep){
			return {
				restrict: 'E',
				controller: 'confirmCtrl',
				scope: true,
				link: function($scope,ele,attrs,controller){

					var stepData    = {};
					stepData.type   = attrs['stepId'];
					stepData.id     = attrs['stepId'];
					stepData.title  = attrs['title'];
					stepData.body   = attrs['body'];
					stepData.piece  = 'confirm';
					stepData.path   = '/workspace/views/app/voto/juntas/Confirm.html';
					stepData.view   = ele;

					var instance = new ConfirmStep(stepData,$scope);
					stepsManager.setStep(instance);
				}
			}
		}
	]).directive('endStep',[
		"stepsManager","EndStep",
		function(stepsManager, EndStep){
			return {
				restrict: 'E',
				controller: 'arbitraryStepCtrl',
				scope: true,
				link: function($scope,ele,attrs,controller){

					var stepData    = {};
					stepData.type   = attrs['stepId'];
					stepData.id     = attrs['stepId'];
					stepData.title  = attrs['title'];
					stepData.body   = attrs['body'];
					stepData.piece  = 'end';
					stepData.path   = '/workspace/views/app/voto/juntas/Finish.html';
					stepData.view   = ele;

					var instance = new EndStep(stepData,$scope);
					stepsManager.setStep(instance);
				}
			}
		}
	]);
}).call(this);