(function() {
	var hasVotes = function(steps){
		if(steps.value !== undefined) return true;
		for(var i = 0; i < steps['subpuntos'].length; ++i){
			if(hasVotes(steps['subpuntos'][i]))
				return true;
		}
		return false;
	};

	var getVote = function(steps,data){
		if(steps.value !== undefined){
			var punto = {
				punto_id:      steps.id,
				opcionVoto_id: steps.value
			};
			data.push(punto);
			return data;
		}
		if(steps['subpuntos'].length > 0){
			angular.forEach(steps['subpuntos'], function(subpunto){
				data = getVote(subpunto,data);
			});
		}
		return data;
	};

	'use strict';
	angular.module('workspaceControllers'
	).controller('previousModalCtrl',[
		"$scope","$modalInstance","$location","votoUtils","loadAccion","$http","logger",
		function($scope,$modalInstance,$location,votoUtils,loadAccion,$http,logger){
			$scope.anular = function(){
				$http.post(ApiBase + '/anulacions').success(function(){
					logger.logUpperSuccess("id00257_app:vote:modal:anular:success");

				}).then(function(){
					votoUtils.leave();
					$modalInstance.close();
				});
			};

			$scope.continuar = function(){
				//load previousVote
				$modalInstance.close();
				loadAccion();
			};

			$scope.cancelar = function(){
				votoUtils.leave();
				$modalInstance.close();
			};
		}
	]).controller('previousAvCheckModalCtrl',[
		"$scope","$modalInstance","$location","votoUtils","loadAccion","$http","logger",
		function($scope,$modalInstance,$location,votoUtils,loadAccion,$http,logger) {
			$scope.continuar = function(){
				//load previousVote
				$modalInstance.close();
				loadAccion();
			};

			$scope.cancelar = function(){
				votoUtils.leave();
				$modalInstance.close();
			};
		}
	]).controller('arbitraryStepCtrl',[
		"$scope","accionistasManager","localize",'$localStorage',
		function($scope, accionistasManager,localize, $localStorage){
			var i18n = localize.getLocalizedString;
                        $scope.isVirtualAssistance = $localStorage["Config.vote.show.virtualAssistance"] == 1;
                        /*$scope.loadTemplateMail = function(template, option, userto) {
                            $scope.template = $scope.templates[template];
                            $scope.userto = userto;
                            $scope.option = option;
                        };*/
			//$scope.title    = i18n("id00291_app:voto:welcome:title");
			//$scope.subtitle = i18n("id00292_app:voto:welcome:subtitle") + $scope.main.brand;
			//$scope.body     = "a";
                        
            //$scope.$watch('body',function(body){
                //$scope.body = body;
            //});
		}
	]).controller('newVaCtrl',[
                
		'$scope','mailsManager','mailEventOut','logger','localize', '$location','$localStorage', function($scope,mailsManager,mailEventOut,logger,localize, $location,$localStorage){	
			$scope.sendMail = function(){
                            var i18n = localize.getLocalizedString;
                            var option = 'admin';
                            var data = {
                                subject: i18n("id00493_app:voto:welcome:assistance:desertion:mail:title"),
                                body:    i18n("id00494_app:voto:welcome:assistance:desertion:mail:body"),
                            };

                            mailsManager.createMail(data, option).then(function() {
                                mailEventOut.update();
                                logger.logSuccess("id00213_app:logger:mail-success");
                            },function(reason) {
                                logger.logError("id00214_app:logger:mail-error");
                            });
			};
                        
                        $scope.streaming = function() {
                            //var url = "https://";
                            var url = $localStorage["Config.Av.live.address"];
                            window.open(url, '_blank');
                        };
                        
                        $scope.question = function(){
                            $location.path("/app/header/mail/mail").search("mai_id","compose");
			};

		}
        
        ]).controller('sharesStepCtrl',[
		"$scope","accionistasManager","jsUtils","$localStorage","logger","votoUtils",
		function($scope,accionistasManager,jsUtils,$localStorage,logger,votoUtils){

			$scope.data = $scope.data || {};
			$scope.data.accionista = {};

			var previousShares = false;
			votoUtils.getPreviousAction().then(function(accion){
                        if (accion['lastAccion'].length != 0 && accion['lastAccion'][0].shares_num)
                        {
                            previousShares = accion['lastAccion'][0].shares_num;
                            $scope.data.accionista.shares_num = jsUtils.formatNumber(previousShares);
                        }
			})

			accionistasManager.getAccionista().then(function(accionista){
				$scope.data.accionista.shares_num = jsUtils.formatNumber(previousShares || accionista.shares_num);
				$scope.data.accionista.shares_min = jsUtils.formatNumber($localStorage['Config.vote.minShares'] || $localStorage['Config.accionista.accionesMin']);
			});
                        
                        $scope.blockShares = $localStorage['Config.shares.sharesBlock'] == 1;

			
		}
	]).controller('voteDelegateCtrl',[
		"$scope",
		function($scope){
			$scope = $scope.$parent;
			$scope.choice = 'vote';
                        

			$scope.onChoice = function(){
				registerStep.inform('voteDelSel',$scope.choice);
			}
                        
		}
	]).controller('intentionDelegationVoteCtrl',[
		"$scope","stepsManager","jsUtils", "votoUtils","localize","$localStorage","$rootScope",
		function($scope, stepsManager, jsUtils, votoUtils, localize, $localStorage,$rootScope){
			$scope.i18n = localize.getLocalizedString;
			$scope.docTypes = {
				nif: "id00155_app:doctype:nif",
				cif: "id00156_app:doctype:cif",
				nie: "id00157_app:doctype:nie",
				otros: "id00158_app:doctype:other"
			};

			$scope.data = $scope.data || {};
			$scope.data.choiceVoteDelegation = $scope.data.choiceVoteDelegation || -1;
			$scope.data.sustitucion          = $scope.data.sustitucion  || false;
			$scope.data.onChoiceInstructions = $scope.data.onChoiceInstructions || -1;
			$scope.showSubstitution = $localStorage['Config.vote.show.substitution'] == 1;
                        
                        
			
			($scope.onChoiceVoteDelegation = function(){
				/*var voteSteps = stepsManager.getStepsByPiece("vote");
				angular.forEach(voteSteps,function(voteStep){
					voteStep.scope.resetPuntos();
				});*/

				switch($scope.data.choiceVoteDelegation){
					case 'vote':
						stepsManager.hideStepByPiece('delegation');
						stepsManager.hideStepByPiece('instructions');
						stepsManager.showStepByPiece('vote');

						break;
					case 'delegation':
						stepsManager.showStepByPiece('delegation');
						stepsManager.showStepByPiece('instructions');

						$scope.onChoiceInstructions();

						break;
				}
			})();

			votoUtils.configDelegation  ($scope);
			votoUtils.configInstructions($scope);
			votoUtils.configTargetNext  ($scope, true, true, true);
			votoUtils.configConditions  ($scope);
			votoUtils.configPrevious    ($scope);
			votoUtils.configFinish      ($scope);
		}
	]).controller('nonIntentionDelegationNonVoteCtrl',[
		"$scope","stepsManager","votoUtils","localize","$localStorage",
		function($scope, stepsManager,votoUtils,localize,$localStorage){
			$scope.i18n = localize.getLocalizedString;
			$scope.docTypes = {
				nif: "id00155_app:doctype:nif",
				cif: "id00156_app:doctype:cif",
				nie: "id00157_app:doctype:nie",
				otros: "id00158_app:doctype:other"
			};

			$scope.data = $scope.data || {};
			$scope.data.choiceVoteDelegation = $scope.data.choiceVoteDelegation || -1;
			$scope.data.sustitucion  = $scope.data.sustitucion  || false;
			$scope.data.onChoiceInstructions = $scope.data.onChoiceInstructions || -1;
			$scope.showSubstitution = $localStorage['Config.vote.show.substitution'] == 1;

			$scope.onChoiceDelegation = function(){
				if($scope.data.choiceDelegation == 'chairman'){
					$scope.data.person = {name: 'Presidente del consejo'};
				}
				else{
					$scope.data.person = {
						name: '',
						secondname: '',
						dni:  ''
					}
				}
			}

			votoUtils.configDelegation  ($scope);
			votoUtils.configInstructions($scope);
			votoUtils.configTargetNext  ($scope, true, false, false);
			votoUtils.configConditions  ($scope);
			votoUtils.configPrevious    ($scope);
			votoUtils.configFinish      ($scope);
		}
	]).controller('nonIntentionDelegationVoteCtrl', [
		"$scope","stepsManager","localize","$localStorage","votoUtils",
		function($scope, stepsManager, localize, $localStorage, votoUtils){
			$scope.i18n = localize.getLocalizedString;
			$scope.docTypes = {
				nif: "id00155_app:doctype:nif",
				cif: "id00156_app:doctype:cif",
				nie: "id00157_app:doctype:nie",
				otros: "id00158_app:doctype:other"
			};

			$scope.data = $scope.data || {};
			$scope.data.choiceVoteDelegation = $scope.data.choiceVoteDelegation || -1;
			$scope.data.sustitucion  = $scope.data.sustitucion  || false;
			$scope.data.onChoiceInstructions = $scope.data.onChoiceInstructions || -1;
			$scope.showSubstitution = $localStorage['Config.vote.show.substitution'] == 1;

			($scope.onChoiceVoteDelegation = function(){
                            
				switch($scope.data.choiceVoteDelegation){
					case 'vote':
						stepsManager.hideStepByPiece('delegation');
						stepsManager.showStepByPiece('vote');
						break;
					case 'delegation':
						stepsManager.showStepByPiece('delegation');
						stepsManager.hideStepByPiece('vote');
						break;
				}
			})();

			votoUtils.configDelegation  ($scope);
			votoUtils.configInstructions($scope);
			votoUtils.configTargetNext  ($scope, true, false, true);
			votoUtils.configConditions  ($scope);
			votoUtils.configPrevious    ($scope);
			votoUtils.configFinish      ($scope);
		}
	]).controller('intentionDelegationNonVoteCtrl', [
		"$scope","stepsManager","votoUtils","localize","$localStorage",
		function($scope, stepsManager,votoUtils,localize,$localStorage){
			$scope.i18n = localize.getLocalizedString;
			$scope.docTypes = {
				nif: "id00155_app:doctype:nif",
				cif: "id00156_app:doctype:cif",
				nie: "id00157_app:doctype:nie",
				otros: "id00158_app:doctype:other"
			};

			$scope.data = $scope.data || {};
			$scope.data.choiceVoteDelegation = $scope.data.choiceVoteDelegation || -1;
			$scope.data.sustitucion  = $scope.data.sustitucion  || false;
			$scope.showSubstitution = $localStorage['Config.vote.show.substitution'] == 1;

			votoUtils.configDelegation  ($scope);
			votoUtils.configInstructions($scope);
			votoUtils.configTargetNext  ($scope, true, true, false);
			votoUtils.configConditions  ($scope);
			votoUtils.configPrevious    ($scope);
			votoUtils.configFinish      ($scope);
		}
	]).controller('nonIntentionNonDelegationVoteCtrl',[
		"$scope","stepsManager","votoUtils", "localize", "$localStorage",
		function($scope, stepsManager,votoUtils,localize,$localStorage){
			$scope.data = $scope.data || {};
			$scope.data.choiceVoteDelegation = "vote";
			$scope.data.sustitucion  = $scope.data.sustitucion  || false;
			$scope.showSubstitution = $localStorage['Config.vote.show.substitution'] == 1;

			votoUtils.configDelegation  ($scope);
			votoUtils.configInstructions($scope);
			votoUtils.configTargetNext  ($scope, false, false, true);
			votoUtils.configConditions  ($scope);
			votoUtils.configPrevious    ($scope);
			votoUtils.configFinish      ($scope);
		}
	]).controller('VoteAvCtrl',[
		"$scope","stepsManager","votoUtils", "localize", "$localStorage",
		function($scope, stepsManager,votoUtils,localize,$localStorage){
			$scope.data = $scope.data || {};
			$scope.data.choiceVoteDelegation = "av";
			$scope.data.sustitucion  = $scope.data.sustitucion  || false;
			$scope.showSubstitution = $localStorage['Config.vote.show.substitution'] == 1;

			//votoUtils.configDelegation  ($scope);
			//votoUtils.configInstructions($scope);
			votoUtils.configTargetNext  ($scope, false, false, true);
			votoUtils.configConditions  ($scope);
			votoUtils.configPrevious    ($scope);
			votoUtils.configFinish      ($scope);
		}
	]).controller('voteStepCtrl',[
		"$scope","stepsManager","$localStorage","localize","logger","jsUtils","votoUtils",
		function($scope,stepsManager,$localStorage,localize,logger,jsUtils,votoUtils){
			$scope = $scope.$parent;
			$scope.firstLoadVote = true;
                        $scope.showGlobalVote = $localStorage['Config.vote.showGlobalVote'] || true;
			var i18n = localize.getLocalizedString;

			var loadPrevious = $localStorage['Config.vote.loadPreviousVote'] == 1;

			if(loadPrevious && $scope.data.data != undefined){
				var previousVote = $scope.data.data.previousVote;
			}

			var loadPreviousVote = function(){
				if(previousVote == undefined) return;

				for(var k in previousVote){
					getPuntoById(previousVote[k]['punto'].id).value = previousVote[k]['opcion_voto'].id;
				}
				for(var i in $scope.abs_adicionals){
					for(var j in $scope.data.data.abs_adicional){
						if($scope.abs_adicionals[i].id == $scope.data.data.abs_adicional[j].abs_adicional.id){
							$scope.abs_adicionals[i].value = $scope.data.data.abs_adicional[j].value;
						}
					}
				}

				$scope.$apply();
			};

			var getPuntoById = function(id){
				var recursive = function(punto,id){
					if(punto.id == id){
						return punto;
					}
					var res;
					for(var j in punto['subpuntos']){
						res = recursive(punto['subpuntos'][j],id);
						if(res !== false){
							return res;
						}
					}
					return false;
				};

				var res;
				for(var i in $scope.puntos){
					res = recursive($scope.puntos[i],id);
					if(res !== false){
						return res;
					}
				}
				return false;
			};

			//Devuelve el número total de puntos
			var countPuntos = function(){
				var count = function(punto,c){
					if(punto['subpuntos'].length === 0){
						c++;
					}
					for(var i = 0; i < punto['subpuntos'].length; ++i){
						c = count(punto['subpuntos'][i],c);
					}
					return c;
				};

				var c = 0;
				for(var i = 0; i < $scope.puntos.length; ++i){
					c = count($scope.puntos[i],c);
				}
				return c;
			};

			//Devuelve el número de puntos votados
			var countVotes = function(){
				var count = function(punto,c){
					if(punto['subpuntos'].length === 0){
						if(punto.value != undefined)
							c++;
					}
					for(var i = 0; i < punto['subpuntos'].length; ++i){
						c = count(punto['subpuntos'][i],c);
					}
					return c;
				}

				var voted = 0;
				for(var i = 0; i < $scope.puntos.length; ++i){
					voted = count($scope.puntos[i],voted);
				}
				return voted;
			};

			var maxvotes = $scope.type['max_votos'];
			var minvotes = $scope.type['min_votos'];

			//Ejecutado al votar un punto
			$scope.onVote = function(reference){
				$scope;
			};

			$scope.resetPuntos = function(){
				$scope.globalPointClear();
			};
			
			//Ejecutado al hacer un voto global
			$scope.globalPointUpdate = function(){
				var value = $scope.globalPoint;

				if(value != undefined && $localStorage['Config.vote.maxVotesBlock'] == 1 && countPuntos() > maxvotes){
					logger.logUpper(i18n("id00247_app:vote:votemax1") + maxvotes + i18n("id00248_app:vote:votemax2"),true);
					$scope.globalPoint = -1;
					return;
				}

				for(var i = 0; i < $scope.puntos.length; ++i){
					setValue($scope.puntos[i],value);
				}
			}

			//Borrar todos los puntos (se les asigna undefined al value)
			$scope.globalPointClear = function(){
				$scope.globalPoint = undefined;
				for(var i = 0; i < $scope.puntos.length; ++i){
					setValue($scope.puntos[i],'clear');
				}
			}

			//Asigna un valor a un punto determinado, especificado con un string
			//que indica el recorrido para llegar al punto
			var setValueByReference = function(reference,value){
				reference = reference.split("-");

				var set;
				(set = function(punto,reference,value){
					if(reference.length == 0){
						punto.value = value;
						return;
					}
					set(punto['subpuntos'][reference[0]],reference.slice(1),value);
				})($scope.puntos[reference[0]],reference.slice(1),value);
			}

			//Se asigna un valor a todos los puntos, o se borran si value = 'clear'
			var setValue = function(punto,value){
				if(punto['subpuntos'].length === 0 && punto.informativo == 0){
					for(var i = 0; i < punto['grupos_o_v']['opciones_voto'].length; ++i){
						if(value == 'clear'){
							punto.value = undefined;
							break;
						}
						if(punto['grupos_o_v']['opciones_voto'][i].id == value){
							punto.value = value;
							break;
						}
					}
					return;
				}
				for(var i = 0; i < punto['subpuntos'].length; ++i){
					setValue(punto['subpuntos'][i],value);
				}
			};

			$scope.onStepChangingNext = function(index, data){
				//Máximo de votos
				if(($localStorage['Config.vote.maxVotesBlock']         == 1 && data.choiceVoteDelegation == "vote") || 
				   ($localStorage['Config.instructions.maxVotesBlock'] == 1 && data.choiceVoteDelegation == "delegation")){
					if(maxvotes != -1 && countVotes() > maxvotes){
						logger.logUpper(i18n("id00247_app:vote:votemax1") + maxvotes + i18n("id00248_app:vote:votemax2"),true);
						return false;
					}
				}

				//Mínimo de votos
				if(($localStorage['Config.vote.minVotesBlock']         == 1 && data.choiceVoteDelegation == "vote") ||
				   ($localStorage['Config.instructions.minVotesBlock'] == 1 && data.choiceVoteDelegation == "delegation")){
					if(countVotes() < minvotes){
						logger.logUpper(i18n("id00250_app:vote:votemin1") + minvotes + i18n("id00251_app:vote:votemin2"),true);
						return false;
					}
				}

				votoUtils.enableNextButton();
				return true;
			};

			$scope.onStepChangingTargetNext = function(index, data){
				if(index == "vote"){
					if(loadPrevious && $scope.firstLoadVote){
						loadPreviousVote();
					}

					var shares = Number(jsUtils.cleanNumber(data.accionista.shares_num));
					if(($localStorage['Config.vote.minSharesBlock']         == 1 && data.choiceVoteDelegation == "vote") ||
					   ($localStorage['Config.instructions.minSharesBlock'] == 1 && data.choiceVoteDelegation == "delegation")){
						if(Number($localStorage['Config.vote.minShares']) > shares){
							logger.logUpper("id00254_app:vote:sharesmin:error");
							return false;
						}
					}
					$scope.firstLoadVote = false;

					votoUtils.disableNextButton();
				}
				return true;
			};

			$scope.onStepChangingTargetPrevious = function(index,data){
				votoUtils.disableNextButton();
				return true;
			};

			$scope.onStepChangingPrevious = function(index,data){
				votoUtils.enableNextButton();
				return true;
			};
		}
	]);
}).call(this);