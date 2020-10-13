(function() {
	'use strict';

	angular.module('workspaceControllers'
	).controller('NavAvCtrl', [
		'$scope','$rootScope','accionistasManager','$localStorage', function($scope, $rootScope, accionistasManager, $localStorage) {
                    
                        $scope.isQuestion = $localStorage["Config.Av.showquestions"] == 1;
                        $scope.isDesertion = $localStorage["Config.Av.showdesertion"] == 1;

			accionistasManager.getAccionista().then(function(accionista){
				accionista.myLives().then(function(accionistaLives){
					$scope.lives = accionistaLives.success;
				}).catch(function(){});
			}).catch(function(){});

		}]).controller('newAvCtrl',[
		'$scope','$http', 'Item','logger','$location','uploaderProvider','anonymousToken', function($scope,$http ,Item,logger,$location,uploaderProvider,anonymousToken){
			$scope.isValid = function(){
				return $scope.acceptTerms && $scope.question.subject !== '' && $scope.question.body !== '';
			};

			$scope.confirm = function(question){
				
				question.token = $scope.uploader.getToken();
				$http.post(ApiBase + '/questions', question)
					.success(function(response){
						if(response.error != undefined){
							logger.logError("id00506_app:logger:blocked-by-server")
						}else{
							logger.logSuccess("id00439_app:logger:new-question-success");
							$location.path('/app/av/ruego').search("id", response.success.id);
						}
					})
					.error(function(){
						logger.logError("id00201_app:logger:server-error")
					});
				uploaderProvider.renew($scope.uploaderID,anonymousToken.newToken());

			};

			($scope.clear = function(){
				$scope.question = new Item({
					subject: '',
					body: ''
				}, "questions");

				$scope.acceptTerms = false;
			})();

			$scope.uploaderID = "newQuestionUploader"+anonymousToken.newToken();
			$scope.uploader = uploaderProvider.get($scope.uploaderID,$scope,anonymousToken.newToken());
		}
	]).controller('personalAVsCtrl',[
		'$scope', 'ItemsList', 'accionistasManager','localize','stateService','$location',function($scope,ItemsList,accionistasManager,localize,stateService,$location){
			$scope.items = [];

			accionistasManager.getAccionista().then(function(accionista){
				accionista.getQuestions().then(function(itemList){
					$scope.items = itemList["questions"];
				});
			});

			$scope.i18n        = localize.getLocalizedString;
			$scope.states      = stateService.getQuestionIDTags();
			$scope.states[0]   = "id00080_app:foro:nav:all";

			$scope.goToQuestion = function(question){
				$location.path("/app/av/ruego").search("id",question.id);
			};
		}
	]).controller('publicAvsCtrl',[
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
	]).controller('questionCtrl',[
		'$scope','$http','itemsManager','$routeParams','$route', 'Item', 'logger', 'accionistasManager', 'localize',
		function($scope,$http,itemsManager,$routeParams,$route, Item, logger, accionistasManager, localize){

			$scope.question = new Item({
				subject: '',
				body: '',
				messages: [],
				autor: [],
				locked: 1
			}, "questions");

			$scope.i18n = localize.getLocalizedString;

			itemsManager.getByTypeID("questions", $routeParams.id).then(function(itemList){
				$scope.question = itemList["questions"][0];
				//$scope.thread.locked = $scope.thread.locked.toString();
				$scope.question.message = '';
			});

			accionistasManager.getAccionista().then(function(accionista){
				$scope.owner = function(){
					return accionista.id == $scope.question.autor.id;
				};
			});

			var reload = function(){
				$scope.question.reload().then(function(){
					//$scope.question.locked = $scope.question.locked.toString();
					$scope.question.message = "";
				});
			};

			$scope.sendMessage = function(message){
				if($scope.owner() && $scope.question.state == 3){
					$scope.question.state = 1;
					$scope.question.update().then(function(item){
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
					$scope.question.sendComment(message).then(function(){
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
	]).controller('modalAcreditacionAvCtrl',[
		'$scope','$modalInstance','acreditacionHide','accionistasManager',"localize", function($scope,$modalInstance,acreditacionHide, accionistasManager, localize){
			var i18n = localize.getLocalizedString;
			$scope.title = i18n("id00477_app:av:acreditacion:modal_tittle");
			$scope.body = i18n("id00478_app:av:acreditacion:modal_body");
			$scope.ok = function() {
				accionistasManager.getAccionista().then(function(accionista){
					accionista.acreditar(1).then(function(a){
						acreditacionHide();
						$modalInstance.close();
					})
				});
			}
			$scope.cancel = function() {
				$modalInstance.close();
			}

		}
	]).controller('previousModalAvCtrl',[
		"$scope","$modalInstance","$location","votoUtils","loadAccion","$http","logger","AppService","$timeout",
		function($scope,$modalInstance,$location,votoUtils,loadAccion,$http,logger,AppService, $timeout){
			$scope.anular = function(){
				$http.post(ApiBase + '/anulacionavs').success(function(response){
					if(response.success) {
						logger.logUpperSuccess("id00257_app:vote:modal:anular:success");
						$timeout(
							function() {
								votoUtils.leave();
								//AppService.doClose();
							},3000
						);
					}

					else {
						logger.logError("id00201_app:logger:server-error");
						$timeout(
							function() {
								AppService.doClose();
							},3000
						);
					}
				}).error(function(data, status, headers, config) {
					logger.logError("id00201_app:logger:server-error");
					$timeout(
						function() {
							votoUtils.leave();
							//AppService.doClose();
						},3000
					);
				});
				$modalInstance.close();
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
	]).controller('voteAvStepCtrl',[
		"$scope","stepsManager","$localStorage","localize","logger","jsUtils","votoUtils",
		function($scope,stepsManager,$localStorage,localize,logger,jsUtils,votoUtils){
			$scope = $scope.$parent;
			$scope.firstLoadVote = true;
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
				if($localStorage['Config.vote.maxVotesBlock'] == 1){
					if(maxvotes != -1 && countVotes() > maxvotes){
						logger.logUpper(i18n("id00247_app:vote:votemax1") + maxvotes + i18n("id00248_app:vote:votemax2"),true);
						return false;
					}
				}

				//Mínimo de votos
				if($localStorage['Config.vote.minVotesBlock']){
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

					/*var shares = Number(jsUtils.cleanNumber(data.accionista.shares_num));
					 if($localStorage['Config.vote.minSharesBlock']){
					 if(Number($localStorage['Config.vote.minShares']) > shares){
					 logger.logUpper("id00254_app:vote:sharesmin:error");
					 return false;
					 }
					 }*/
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
	]).controller('newLvCtrl',[
		'$scope','$http', 'Item','logger','AppService', function($scope,$http ,Item,logger,AppService){
			$scope.isValid = function(){
				return $scope.acceptTerms;
			};
                        
          		$scope.confirm = function(){
				
				$http.post(ApiBase + '/desertions')
					.success(function(response){
						if(response.error != undefined) {
                                                    logger.logError("id00506_app:logger:blocked-by-server")
                                                }
                                                else {
                                                    logger.logSuccess("id00502_app:logger:new-leave-success");
                                                    AppService.doLogout();
                                                }
						
					})
					.error(function(){
						logger.logError("id00201_app:logger:server-error")
					});
			};
		}
	]).controller('AvLogCtrl',[
		'$scope','$http', 'Item','logger','AppService', 'accionistasManager', function($scope,$http ,Item,logger,AppService, accionistasManager){
			accionistasManager.getAccionista().then(function(accionista){
				accionista.logAccess('av');
			}).catch(function(){});
		}
	]);

}).call(this);