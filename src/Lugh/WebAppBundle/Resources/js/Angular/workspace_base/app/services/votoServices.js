(function() {
	'use strict';
	angular.module('workspaceControllers'
	).service('votoUtils',[
		"$http","logger","stepsManager","jsUtils","$q","$localStorage","$location","AppService","$timeout","localize","$modal", "documentValidation","accionistasManager",
		function($http,logger,stepsManager,jsUtils,$q,$localStorage,$location,AppService,$timeout,localize, $modal, documentValidation, accionistasManager){
			var scope = this;
			var previousAction = false;

			this.init = function(){
				scope.firstLoadDelegation = true;
				scope.firstLoadVoteDelegation = true;
			};

			this.hasVotes = function(steps){
				if(steps.value !== undefined) return true;
				for(var i = 0; i < steps['subpuntos'].length; ++i){
					if(this.hasVotes(steps['subpuntos'][i]))
						return true;
				}
				return false;
			};

			this.getVote = function(steps,data){
				if(steps.value !== undefined && steps.value !== false){
					var punto = {
						punto_id:      steps.id,
						opcionVoto_id: steps.value
					};
					data.push(punto);
					return data;
				}
				if(steps['subpuntos'].length > 0){
					angular.forEach(steps['subpuntos'], function(subpunto){
						data = scope.getVote(subpunto,data);
					});
				}
				return data;
			};

			this.sendVote = function(data, $scope){
				var tipos = stepsManager.getStepsByPiece("vote");

				var votaciones = [];
				var abs_adicionales = [];
				angular.forEach(tipos,function(tipo){
					var puntos = tipo.getPuntos();
					var abs_adicionals = tipo.getAbsAdicional();
					angular.forEach(puntos,function(steps){
						//No se está sobreescribiendo votaciones, mirar bien getVote
						votaciones = scope.getVote(steps,votaciones);
					});
					
					angular.forEach(abs_adicionals,function(abs_adicional){
						if(abs_adicional && abs_adicional.value && abs_adicional.value != '' && abs_adicional['vote_proxy'] == 1){
							abs_adicionales.push({
								absAdicional_id: abs_adicional.id,
								opcionVoto_id: abs_adicional.value
							});
						}
					});
				});

				var action = {
					sharesnum: Number(jsUtils.cleanNumber(data.accionista.shares_num)),
					votacion: votaciones,
					abs_adicional: abs_adicionales
				};

				var modalInstance = $modal.open({
					templateUrl: "myModalContent.html",
					backdrop: 'static'
				});

				$http.post(ApiBase + '/votos', action).success(function(response){
					if(response.success) {
                                            scope.setFinish(response,$scope);
                                            $scope.readyFinish = true;
                                            modalInstance.dismiss('Valid');
                                            previousAction = false;
                                                                    logger.logUpperSuccess("id00258_app:vote:sent:success");
                                        }   
                                        else {
                                            logger.logUpper("id00259_app:vote:sent:error");
                                            modalInstance.dismiss('Valid');
                                            previousAction = false;
                                            scope.leave($scope);
                                        }
				});
			};
            
            this.sendAv = function(data, $scope){
				var tipos = stepsManager.getStepsByPiece("vote");

				var votaciones = [];
				var abs_adicionales = [];
				angular.forEach(tipos,function(tipo){
					var puntos = tipo.getPuntos();
					var abs_adicionals = tipo.getAbsAdicional();
					angular.forEach(puntos,function(steps){
						//No se está sobreescribiendo votaciones, mirar bien getVote
						votaciones = scope.getVote(steps,votaciones);
					});
					
					angular.forEach(abs_adicionals,function(abs_adicional){
						if(abs_adicional && abs_adicional.value && abs_adicional.value != '' && abs_adicional['vote_proxy'] == 1){
							abs_adicionales.push({
								absAdicional_id: abs_adicional.id,
								opcionVoto_id: abs_adicional.value
							});
						}
					});
				});

				var action = {
					//sharesnum: Number(jsUtils.cleanNumber(data.accionista.shares_num)),
					votacion: votaciones,
					abs_adicional: abs_adicionales
				};

				var modalInstance = $modal.open({
					templateUrl: "myModalContent.html",
					backdrop: 'static'
				});
                
				$http.post(ApiBase + '/avs', action)
                                    .success(function(response){
					if(response.error != undefined) {
                                            modalInstance.dismiss('Valid');
                                            previousAction = true;
                                            logger.logError("id00506_app:logger:blocked-by-server")
                                        }
					else {
                                            scope.setFinish(response,$scope);
                                            $scope.readyFinish = true;
                                            modalInstance.dismiss('Valid');
                                            previousAction = false;
                                            logger.logUpperSuccess("id00258_app:vote:sent:success");
                                        }
                                    })
                                    .error(function(){
                                        modalInstance.dismiss('Valid');
                                        previousAction = true;
                                        logger.logUpper("id00259_app:vote:sent:error");
                                    });
			};

			this.getDelegadoID = function(data){
				var deferred = $q.defer();

				if(data.choiceDelegation == 'chairman'){
					$http.get(ApiBase + '/delegados/director').success(function(delegado){
						deferred.resolve(delegado.id);
					});
				}
                                if(data.choiceDelegation == 'secretary'){
					$http.get(ApiBase + '/delegados/secretary').success(function(delegado){
						deferred.resolve(delegado.id);
					});
				}
				if(data.choiceDelegation == 'list'){
					deferred.resolve(data.conseller.id);
				}
				if(data.choiceDelegation == 'person'){
					$http.post(ApiBase + '/delegados',data.person).success(function(response){
						deferred.resolve(response.success.id);
					});
				}

				return deferred.promise;
			};

			this.sendDelegation = function(data, $scope){
				var tipos = stepsManager.getStepsByPiece("vote");
				
				var Data = data;
				this.getDelegadoID(Data).then(function(id){
					var votaciones = [];
					var abs_adicionales = [];
					if(Data['choiceInstructions'] == 'yes') {
						angular.forEach(tipos, function (tipo) {
							var puntos = tipo.getPuntos();
							var abs_adicionals = tipo.getAbsAdicional();
							angular.forEach(puntos, function (steps) {
								votaciones = scope.getVote(steps, votaciones);
							});
							
							angular.forEach(abs_adicionals,function(abs_adicional){
								if(abs_adicional && abs_adicional.value && abs_adicional.value != '' && abs_adicional['vote_proxy'] == 2){
									abs_adicionales.push({
											absAdicional_id: abs_adicional.id,
											opcionVoto_id: abs_adicional.value
									});
								}
							});
						});
					}

					var action = {
						sharesnum: Number(jsUtils.cleanNumber(Data.accionista.shares_num)),
						votacion:  votaciones,
						delegado_id: id,
						observaciones: Data.observaciones,
						sustitucion:   Data.sustitucion,
						abs_adicional: abs_adicionales
					};

					var modalInstance = $modal.open({
						templateUrl: "myModalContent.html",
						backdrop: 'static'
					});

					$http.post(ApiBase + '/delegacions', action).success(function(response){
						if(response.success) {
                            scope.setFinish(response,$scope);
                            $scope.readyFinish = true;
                            modalInstance.dismiss('Valid');
                            previousAction = false;
                            logger.logUpperSuccess("id00258_app:vote:sent:success");
                        }   
                        else {
                            logger.logUpper("id00259_app:vote:sent:error");
                            modalInstance.dismiss('Valid');
                            previousAction = false;
                            scope.leave($scope);
                        }
					});
				});
			};

			this.setConfirm = function($scope){
				$scope.tipos = stepsManager.getStepsByPiece("vote");
				$scope.confirmPuntos = [];
				$scope.confirmAbsAdicional = [];
				angular.forEach($scope.tipos,function(tipo,key){
					var puntos = tipo.getPuntos();
					var abs_adicionales = tipo.getAbsAdicional();
					
					$scope.confirmPuntos[key] = [];
					$scope.confirmAbsAdicional[key] = [];
					angular.forEach(puntos,function(steps){
						if(scope.hasVotes(steps))
							$scope.confirmPuntos[key].push(steps);
					});

					angular.forEach(abs_adicionales,function(abs_adicional){
						if(abs_adicional && abs_adicional.value != undefined && abs_adicional.value != ''){
							var opcion = -1;
							for(var i in abs_adicional.grupos_o_v.opciones_voto){
								if(abs_adicional.value == abs_adicional.grupos_o_v.opciones_voto[i].id){
									opcion = abs_adicional.grupos_o_v.opciones_voto[i].nombre;
								}
							}
							abs_adicional['option'] = opcion;
							$scope.confirmAbsAdicional[key].push(abs_adicional);
						}
					});
				});

				$scope.$apply();
			};

			this.getVotesByTipo = function(votes,type){
				var res = [];
				angular.forEach(votes,function(vote,key){
					if(vote['punto']['tipo_voto'].id == type['voteType'].id){
						vote['punto'].value = vote['opcion_voto'].id;
						res.push(vote['punto']);
					}
				});
				return res;
			}

			this.setFinish = function(response,$scope){
				$scope.tipos = stepsManager.getStepsByPiece("vote");
				$scope.confirmPuntos = [];
				angular.forEach($scope.tipos,function(tipo,key){
					var puntos = scope.getVotesByTipo(response.success.votacion,tipo);
					$scope.confirmPuntos[key] = [];

					angular.forEach(puntos,function(steps){
						$scope.confirmPuntos[key].push(steps);
					});
				});

				var abs_adicionales = response.success.voto_abs_adicional;
				angular.forEach(abs_adicionales, function(abs_adicional, key){
					if(abs_adicional && abs_adicional.value != undefined && abs_adicional.value != ''){
						var opcion = -1;
						for(var i in abs_adicional.grupos_o_v.opciones_voto){
							if(abs_adicional.value == abs_adicional.grupos_o_v.opciones_voto[i].id){
								opcion = abs_adicional.grupos_o_v.opciones_voto[i].nombre;
							}
						}
						abs_adicional['option'] = opcion;
						$scope.confirmAbsAdicional[key].push(abs_adicional);
					}
				});

				$timeout(function(){
					$scope.$apply()
				});
			};

			this.configInstructions = function($scope){
				$scope.data.choiceInstructions   = -1;
				$scope.onChoiceInstructions = function(){
					switch($scope.data.choiceInstructions){
						case 'no':
							stepsManager.hideStepByPiece('vote');
							break;
						case 'yes':
						default:
							stepsManager.showStepByPiece('vote');
							break;
					}
				}
			};

			this.configTargetNext = function($scope, delegation, intention, vote){
				if(delegation === undefined) delegation = true;
				if(intention  === undefined) intention  = true;
				if(vote       === undefined) vote       = true;

				$scope.onStepChangingTargetNext = function(index, data){
					if(index == 'voteDelegation' && scope.firstLoadVoteDelegation){
						$scope.onChoiceVoteDelegation();
						scope.firstLoadVoteDelegation = false;
					}
					if(index == 'delegation' && scope.firstLoadDelegation){
						if($scope.data.conseller != undefined){
							if($scope.data.conseller['isDirector']){
								$scope.data.choiceDelegation = 'chairman';
							}
                                                        if($scope.data.conseller['isSecretary']){
								$scope.data.choiceDelegation = 'secretary';
							}
							else{
								/* Aunque los datos finales no cambien, esto es necesario para 
								 * que se vea bien en la vista de delegación
								 */
								
								for(var i in $scope.consellers){
									if($scope.data.conseller.id == $scope.consellers[i].id){
										$scope.data.conseller = $scope.consellers[i];
									}
								}
								$scope.$apply();
							}
						}
						scope.firstLoadDelegation = false;
					}
					if(index == "confirm"){
						scope.setConfirm($scope);
						$(".content.clearfix").scrollTop(0);
                                                if($scope.data.choiceVoteDelegation == 'av'){
                                                    $('.wb-next').text(localize.getLocalizedString("id00514_home:av:button:confirm"));
                                                } else {
                                                    $('.wb-next').text(localize.getLocalizedString("id00400_home:voto:button:confirm"));
                                                }
					}
					if(index == "end"){
						$scope.readyFinish = false;
                                                if($scope.data.choiceVoteDelegation == 'av'){
							scope.sendAv(data,$scope);
						}
						else if($scope.data.choiceVoteDelegation == 'vote' || (!delegation)){
							scope.sendVote(data, $scope);
						}
						else if($scope.data.choiceVoteDelegation == 'delegation' || (!vote)){
							scope.sendDelegation(data,$scope);
						}
                        
					}
					
					return true;
				}
			};

			this.configConditions = function($scope){
				$scope.showDirector = $localStorage['Config.delegation.options.presidente'] == 1;
				$scope.showList     = $localStorage['Config.delegation.options.listado']    == 1;
				$scope.showPersona  = $localStorage['Config.delegation.options.persona']    == 1;

				var name    = $localStorage['Config.delegation.require.nombre']  == 1;
				var tipodoc = $localStorage['Config.delegation.require.tipodoc'] == 1;
				var numdoc  = $localStorage['Config.delegation.require.numdoc']  == 1;
				var comments= $localStorage['Config.delegation.require.comments']== 1;

				//Si un field es obligatorio se mostrará por defecto
				$scope.showName    = name    || ($localStorage['Config.delegation.hide.nombre']  != 1);
				$scope.showTypeDoc = tipodoc || ($localStorage['Config.delegation.hide.tipodoc'] != 1);
				$scope.showNumDoc  = numdoc  || ($localStorage['Config.delegation.hide.numdoc']  != 1);
				$scope.showComments= comments|| ($localStorage['Config.delegation.hide.comments']!= 1);

				$scope.showChairman= ($localStorage['Config.delegation.hide.presidente']  != 1);
                                $scope.showSecretary= ($localStorage['Config.delegation.hide.secretary']  != 1);
				$scope.showListado = ($localStorage['Config.delegation.hide.listado']     != 1);
				$scope.showPersona = ($localStorage['Config.delegation.hide.persona']     != 1);

				$scope.data.conseller = -1;
				$http.get(ApiBase + '/delegados/consellers').success(function(consellers){
					$scope.consellers = consellers.delegados;

					/*if($scope.showChairman){
						var isThereDirector = false;
						for(var i in $scope.consellers){
							if($scope.consellers[i].is_director){
								isThereDirector = true;
								break;
							}
						}
						$scope.showChairman = $scope.showChairman && isThereDirector;
					}*/


				});

				$scope.onStepChangingNext = function(index, data){
					switch(index){
						case 'voteDelegation':
							if($scope.data.choiceVoteDelegation !== "vote" &&
							   $scope.data.choiceVoteDelegation !== "delegation"){
								logger.logUpper("id00243_app:vote_delegation:choice:error");
								return false;
							}
                                                        if($scope.data.choiceVoteDelegation == "vote" && $localStorage['Config.shares.minSharesBlock'] == 1){
                                                            var shares = new Number(jsUtils.cleanNumber(data.accionista.shares_num));
                                                            var min    = new Number(jsUtils.cleanNumber(data.accionista.shares_min));

                                                            if(shares < min){
                                                                    logger.logUpper("id00253_app:shares:sharesmin:error");
                                                                    return false;
                                                            }
                                                        }
							return true;
							break;
						case 'delegation':
							if($scope.data.choiceDelegation == "chairman")
								return true;
                                                        if($scope.data.choiceDelegation == "secretary")
								return true;
							if($scope.data.choiceDelegation == "list"){
								if($scope.data.conseller == -1){
									logger.logUpper("id00245_app:delegation:required:conseller");
								}
								return $scope.data.conseller != -1;
							}
							if($scope.data.choiceDelegation == "person"){
								var correct = true;
																
								if ($scope.data.person.documentNum != '' &&
									documentValidation.validate($scope.data.person.documentNum, $scope.data.person.documentType) === false)
								{
									logger.logUpper("id00402_app:delegation:validation:numdoc");
									correct = false;
								}
								if(name && ($scope.data.person.nombre == undefined || $scope.data.person.nombre == '')){
									logger.logUpper("id00240_app:delegation:required:name");
									correct = false;
								}
								if(tipodoc && ($scope.data.person.documentType == undefined || $scope.data.person.documentType == '')){
									logger.logUpper("id00241_app:delegation:required:tipodoc");
									correct = false;
								}
								if(numdoc && ($scope.data.person.documentNum == undefined || $scope.data.person.documentNum == '')){
									logger.logUpper("id00242_app:delegation:required:numdoc");
									correct = false;
								}
								if(comments && ($scope.data.observaciones == undefined || $scope.data.observaciones == '')){
									logger.logUpper("id00418_app:delegation:required:comments");
									correct = false;
								}
								return correct;
							}
							logger.logUpper("id00246_app:delegation:choice:error");
							return false;
							break;
						case 'instructions':
							if(!$scope.data.choiceInstructions || $scope.data.choiceInstructions === -1)
								logger.logUpper("id00244_app:delegation:vote:instructions:error");
							return $scope.data.choiceInstructions !== undefined &&
								$scope.data.choiceInstructions !== -1;
							break;
						case 'confirm':
							$('.wb-next').text(localize.getLocalizedString("id00340_app:voto:button:next"));
							break;
					}
					return true;
				};
			};

			this.configDelegation = function($scope){
				$scope.data.choiceDelegation     = $scope.data.choiceDelegation || -1;
				$scope.data.person               = $scope.data.person           || -1;
				($scope.onChoiceDelegation = function(){
					if($scope.data.choiceDelegation == 'chairman'){
						$scope.data.person = {nombre: localize.getLocalizedString("id00396_app:voto:delegation:chairman")};
					}
                                        if($scope.data.choiceDelegation == 'secretary'){
						$scope.data.person = {nombre: localize.getLocalizedString("id00422_app:voto:delegation:secretary")};
					}
					if($scope.data.choiceDelegation == 'list'){
						$scope.data.conseller = -1;
					}
					if($scope.data.choiceDelegation == 'person'){
						$scope.data.person = {
							nombre: '',
							documentNum:  '',
							documentType: 'nif'
						}
					}
				})();
			};

			this.configPrevious = function($scope){
				$scope.onStepChangingPrevious = function(index, data){
					if(index == 'end'){
						return false;
					}
					if(index == "confirm"){
						$('.wb-next').text(localize.getLocalizedString("id00340_app:voto:button:next"));
					}
					return true;
				}
				$scope.onStepChangingTargetPrevious = function(index,data){
					if(index == "confirm"){
						$(".content.clearfix").scrollTop(0);
                                                if($scope.data.choiceVoteDelegation == 'av'){
                                                    $('.wb-next').text(localize.getLocalizedString("id00514_home:av:button:confirm"));
                                                } else {
                                                    $('.wb-next').text(localize.getLocalizedString("id00400_home:voto:button:confirm"));
                                                }
						//$('.wb-next').text(localize.getLocalizedString("id00400_home:voto:button:confirm"));
					}
					return true;
				}
			};

			this.configFinish = function($scope){
				$scope.onFinished = function(data){
					scope.leave($scope);
				};
			};

			this.leave = function($scope){
				/*@TODO: solo voto o voto con más aplicaciones*/
                /*if ($scope != undefined && $scope.data.choiceVoteDelegation == 'av')
                {
                    AppService.doClose();
                }
				else*/ if(!$localStorage.foro && !$localStorage.derecho && !$localStorage.av){
					AppService.doLogout();
				} else {
					$location.path("/");
				}
				if($scope)
					$scope.$apply();
			};

			this.disableNextButton = function(){
				var link = $(".wb-next").parent();
				var button = link.parent();

				link.attr('href','#');
				button.addClass('disabled');
			};

			this.enableNextButton = function(){
				var link = $('.wb-next').parent();
				var button = link.parent();

				link.attr('href','#next');
				button.removeClass('disabled');
			};

			this.getPreviousAction = function(){
				var deferred = $q.defer();

				/*if(previousAction !== false){
					deferred.resolve(previousAction);
				}*/

				accionistasManager.getAccionista().then(function(accionista){
					accionista.getLastAccion().then(function(accion){
						previousAction = accion;
						deferred.resolve(previousAction);
					});
				});

				return deferred.promise;
			}
		}
	]).service('viewsCacheService',[
		"$http","$q",
		function($http,$q){
			this.cache = [];

			var $scope = this;
			this.get = function(view){
				var deferred = $q.defer();
				if($scope.cache[view] === undefined){
					$http.get(WebDefault+view).success(function(html){
						$scope.cache[view] = html;
						deferred.resolve($scope.cache[view]);
					}).catch(function(){
						console.log('Err. viewsCacheService');
					});
				} else {
					deferred.resolve($scope.cache[view]);
				}

				return deferred.promise;
			}
		}
	]).service('smallWizardPersistence', [
		'$http','$q',
		function($http,$q){
			var htmlCache  = [];
			this.getHtmlCache = function(id){
				var deferred = $q.defer();
				if(htmlCache[id] === undefined){
					$http.get(id).success(function(html){
						htmlCache[id] = html;
						deferred.resolve(htmlCache[id]);
					}).catch(function(error){
						deferred.reject(error);
					});
				} else {
					deferred.resolve(htmlCache[id]);
				}
				return deferred.promise;
			}
	}]).service('typeVotoPersistence', [
		'$http','$q',
		function($http,$q){
			var cache  = [];
			this.getPatternVoto = function(){
				var deferred = $q.defer();
                    if (cache.length === 0)
                    {
                        $http.get(WebDefault + '/' + NameController + '/' +'vototype')
                            .success(function(vototypeData){
                                if (vototypeData === "")
                                {
                                    deferred.reject();
                                }
                                else
                                {
                                    cache = vototypeData;
                                    deferred.resolve(vototypeData);
                                }
                            });
                    }
                    else
                    {
                        deferred.resolve(cache);
                    }
				
				return deferred.promise;
			}
	}]).service('typeAvVotoPersistence', [
		'$http','$q',
		function($http,$q){
			var cache  = [];
			this.getPatternVoto = function(){
				var deferred = $q.defer();
                    if (cache.length === 0)
                    {
                        $http.get(WebDefault + '/' + NameController + '/' +'avvototype')
                            .success(function(vototypeData){
                                if (vototypeData === "")
                                {
                                    deferred.reject();
                                }
                                else
                                {
                                    cache = vototypeData;
                                    deferred.resolve(vototypeData);
                                }
                            });
                    }
                    else
                    {
                        deferred.resolve(cache);
                    }
				
				return deferred.promise;
			}
	}]);
}).call(this);