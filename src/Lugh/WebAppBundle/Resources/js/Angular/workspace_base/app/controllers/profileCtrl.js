(function() {
    'use strict';
    angular.module('workspaceControllers'
    ).controller('resetPasswordModal',[
        '$scope','$modalInstance',
        function($scope,$modalInstance){
            $scope.ok = function(){
                $modalInstance.close(true);
            };
            $scope.cancel = function(){
                $modalInstance.close(false);
            };
        }
    ]).controller('restorePassword',[
        '$scope','$http','logger','$modal','$location',
        function($scope,$http,logger,$modal, $location){
            $scope.username = '';
            $scope.currentPassword = '';
            $scope.newPassword = '';
            $scope.repeat = '';

            $scope.resetPassword = function(){
                
                if($scope.currentPassword == ''){
                    logger.logError("id00406_app:reset:password:novalid");
                    return;
                }

                if($scope.newPassword == ''){
                    logger.logError("id00406_app:reset:password:novalid");
                    return;
                }
                
                if($scope.currentPassword === $scope.newPassword){
                    logger.logError("id00487_app:reset:password:same");
                    return;
                }

                if($scope.newPassword == ''){
                    logger.logError("id00406_app:reset:password:novalid");
                    return;
                }

                if($scope.newPassword != $scope.repeat){
                    logger.logError("id00405_app:reset:password:noequals");
                    return;
                }

                var modalInstance = $modal.open({
                    templateUrl:WebDefault + "/workspace/views/app/resetPasswordModal.html",
                    controller: 'resetPasswordModal',
                    backdrop: 'static'
                });

                modalInstance.result.then(function(ok){
                    if(!ok){
                        logger.logWarning('id00407_app:reset:password:usercancel');
                    } else {
                        
                        var dataObject = "webservice=1&_username=" + 
				encodeURIComponent(String($scope.username)) + 
				"&_password=" +
				encodeURIComponent($scope.currentPassword);
                        $http({
                                 url:       WebDefault + "/login_check",
                                 method:    "POST",
                                 headers:   {'Content-Type': 'application/x-www-form-urlencoded'},
                                 data:      dataObject,
                                 withCredentials: true,
                                 timeout: 45000
                         }).then(function(response) {
                                if (response.data.success === 1)
                                {
                                        //window.location.href = WebDefault + '/';
                                        $http.post(ApiBase + '/accionistas/changepasswords',{ password: $scope.newPassword, current_password: $scope.currentPassword}).success(function(response){
                                        if(response.error === undefined){
                                            logger.logSuccess('Contraseña cambiada con éxito.');
                                            $location.path("/");
                                        }
                                        else{
                                            logger.logError('id00408_app:reset:password:error');
                                        }
                                    }).catch(function(response){
                                        logger.logError('id00408_app:reset:password:error');
                                    });
                                }
                                else
                                {
                                        return logger.logError("id00210_app:logger:wrong-login");
                                }
                          });
                        };
                    
                });
            }
        }
    ]).controller('profileCtrl',[
        '$scope','accionistasManager','$localStorage', '$routeParams', "$location","$http", function($scope,accionistasManager,$localStorage,$routeParams,$location,$http) {
            $scope.foro    = {
                "propuestas" : 0,
                "iniciativas": 0,
                "ofertas"    : 0,
                "peticiones" : 0
            };
            $scope.voto    = "Sin acción";
            $scope.derecho = 0;

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
                    });
                }
                
                if($localStorage.derecho == 1){
                    accionista.getThreads().then(function(accionista){
                        $scope.derecho = accionista.threads.length;
                    });
                }

                $scope.goToItemList = function(type){
                    $location.path('/app/foro/actividad-personal/'+type).search('id',accionista.id);
                };

                $scope.goToThreads = function(){
                    $location.path('app/derecho/solicitudes-propias').search('id',accionista.id);
                };
                
            };

            accionistasManager.getAccionista($routeParams.id).then(function(accionista){
                updateView(accionista);
            }).catch(function(){
                $location.path("/404").replace();
            });

            $scope.downloadDocument = function(id){
                window.location.href = ApiBase + "/documents/" + id;
            };
        }

    ]).controller('profileForoCtrl',[
		'$scope','accionistasManager',"$location","$http","localize",'anonymousToken','logger','documentValidation','uploaderProvider','jsUtils', '$modal',
		function($scope,accionistasManager,$location,$http,localize,anonymousToken,logger,documentValidation,uploaderProvider,jsUtils,$modal) {
			var modalInstance;
			($scope.clear = function(){
				$scope.accionista = {
					documentType: 'nif',
					documentNum: ''
				};
			})();
			var i18n = localize.getLocalizedString;
			var initialData = {};

			var uploader = $scope.uploader = uploaderProvider.get($scope.uploaderID,$scope,anonymousToken.newToken());

			accionistasManager.getAccionista().then(function(accionista){
                
				if (accionista.getApp("foro").state !== 3)
				{
					$location.path("/404").replace();
				}
                
				accionista.token = uploader.getToken();
				accionista.tipoPersona = (accionista.represented_by != '') ? 'pj' : 'pf';
				initialData = accionista;
				accionista.messages = accionista.getApp("foro").messages;
                
				($scope.clear = function(){
					$scope.accionista = JSON.parse(JSON.stringify(initialData));
					$scope.accionista.message = "";
					$scope.accionista.shares_num = jsUtils.formatNumber($scope.accionista.shares_num);
				})();

				$scope.uploader.setAdditional($scope.accionista.documents);
			}).catch(function(){
				$location.path("/404").replace();
			});

			$scope.docTypes = {
				nif: "id00155_app:doctype:nif",
				cif: "id00156_app:doctype:cif",
				nie: "id00157_app:doctype:nie",
				otros: "id00158_app:doctype:other"
			};
			$scope.i18n = localize.getLocalizedString;

			$scope.downloadDocument = function(id){
				window.location.href = ApiBase + "/documents/" + id;
			};

			$scope.isValid = function(){
				return  $scope.accionista_form.$valid &&
						documentValidation.validate($scope.accionista.document_num, $scope.accionista.document_type);
			}

			$scope.submit = function(){
				if($scope.accionista.tipoPersona == 'pf'){
					$scope.accionista.represented_by = '';
				}
				$scope.accionista.shares_num = jsUtils.cleanNumber($scope.accionista.shares_num);
				modalInstance = $modal.open({
					templateUrl: "myModalContent.html",
					backdrop: 'static'
				});
				$http.post(ApiBase + '/accionistas/regrants/foros', $scope.accionista)
					.success(function(data){
						if(data.error === undefined) {
							modalInstance.dismiss('Valid');
							logger.logSuccess("id00159_app:logger:success-sent-info");
						}
						else {
							modalInstance.dismiss('Error');
							logger.logError("id00061_app:logger:error-retry");
						}
						$location.path('/');
					}).error(function(){
						modalInstance.dismiss('Error');
						logger.logError("id00061_app:logger:error-retry");
						$location.path('/');
					});
			}
		}

	]).controller('profileVotoCtrl',[
		'$scope','accionistasManager',"$location","$http","localize",'anonymousToken','logger','documentValidation','uploaderProvider','jsUtils', '$modal',
		function($scope,accionistasManager,$location,$http,localize,anonymousToken,logger,documentValidation,uploaderProvider,jsUtils,$modal) {
			var modalInstance;
			($scope.clear = function(){
				$scope.accionista = {
					documentType: 'nif',
					documentNum: ''
				};
			})();
			var i18n = localize.getLocalizedString;
			var initialData = {};

			var uploader = $scope.uploader = uploaderProvider.get($scope.uploaderID,$scope,anonymousToken.newToken());

			accionistasManager.getAccionista().then(function(accionista){
                
				if (accionista.getApp("voto").state !== 3)
				{
					$location.path("/404").replace();
				}
                
				accionista.token = uploader.getToken();
				accionista.tipoPersona = (accionista.represented_by != '') ? 'pj' : 'pf';
				initialData = accionista;
				accionista.messages = accionista.getApp("voto").messages;
                
				($scope.clear = function(){
					$scope.accionista = JSON.parse(JSON.stringify(initialData));
					$scope.accionista.message = "";
					$scope.accionista.shares_num = jsUtils.formatNumber($scope.accionista.shares_num);
				})();

				$scope.uploader.setAdditional($scope.accionista.documents);
			}).catch(function(){
				$location.path("/404").replace();
			});

			$scope.docTypes = {
				nif: "id00155_app:doctype:nif",
				cif: "id00156_app:doctype:cif",
				nie: "id00157_app:doctype:nie",
				otros: "id00158_app:doctype:other"
			};
			$scope.i18n = localize.getLocalizedString;

			$scope.downloadDocument = function(id){
				window.location.href = ApiBase + "/documents/" + id;
			};

			$scope.isValid = function(){
				return  $scope.accionista_form.$valid &&
						documentValidation.validate($scope.accionista.document_num, $scope.accionista.document_type);
			}

			$scope.submit = function(){
				if($scope.accionista.tipoPersona == 'pf'){
					$scope.accionista.represented_by = '';
				}
				$scope.accionista.shares_num = jsUtils.cleanNumber($scope.accionista.shares_num);
				modalInstance = $modal.open({
					templateUrl: "myModalContent.html",
					backdrop: 'static'
				});
				$http.post(ApiBase + '/accionistas/regrants/votos', $scope.accionista)
					.success(function(data){
						if(data.error === undefined) {
							modalInstance.dismiss('Valid');
							logger.logSuccess("id00159_app:logger:success-sent-info");
						}
						else {
							modalInstance.dismiss('Error');
							logger.logError("id00061_app:logger:error-retry");
						}
						$location.path('/');
					}).error(function(){
						modalInstance.dismiss('Error');
						logger.logError("id00061_app:logger:error-retry");
						$location.path('/');
					});
			}
		}

	]).controller('profileDerechoCtrl',[
		'$scope','accionistasManager',"$location","$http","localize",'anonymousToken','logger','documentValidation','uploaderProvider','jsUtils', '$modal',
		function($scope,accionistasManager,$location,$http,localize,anonymousToken,logger,documentValidation,uploaderProvider,jsUtils,$modal) {
			var modalInstance;
			($scope.clear = function(){
				$scope.accionista = {
					documentType: 'nif',
					documentNum: ''
				};
			})();
			var i18n = localize.getLocalizedString;
			var initialData = {};

			var uploader = $scope.uploader = uploaderProvider.get($scope.uploaderID,$scope,anonymousToken.newToken());

			accionistasManager.getAccionista().then(function(accionista){
                
				if (accionista.getApp("derecho").state !== 3)
				{
					$location.path("/404").replace();
				}
                
				accionista.token = uploader.getToken();
				accionista.tipoPersona = (accionista.represented_by != '') ? 'pj' : 'pf';
				initialData = accionista;
				accionista.messages = accionista.getApp("derecho").messages;
                
				($scope.clear = function(){
					$scope.accionista = JSON.parse(JSON.stringify(initialData));
					$scope.accionista.message = "";
					$scope.accionista.shares_num = jsUtils.formatNumber($scope.accionista.shares_num);
				})();

				$scope.uploader.setAdditional($scope.accionista.documents);
			}).catch(function(){
				$location.path("/404").replace();
			});

			$scope.docTypes = {
				nif: "id00155_app:doctype:nif",
				cif: "id00156_app:doctype:cif",
				nie: "id00157_app:doctype:nie",
				otros: "id00158_app:doctype:other"
			};
			$scope.i18n = localize.getLocalizedString;

			$scope.downloadDocument = function(id){
				window.location.href = ApiBase + "/documents/" + id;
			};

			$scope.isValid = function(){
				return  $scope.accionista_form.$valid &&
						documentValidation.validate($scope.accionista.document_num, $scope.accionista.document_type);
			}

			$scope.submit = function(){
				if($scope.accionista.tipoPersona == 'pf'){
					$scope.accionista.represented_by = '';
				}
				$scope.accionista.shares_num = jsUtils.cleanNumber($scope.accionista.shares_num);
				modalInstance = $modal.open({
					templateUrl: "myModalContent.html",
					backdrop: 'static'
				});
				$http.post(ApiBase + '/accionistas/regrants/derechos', $scope.accionista)
					.success(function(data){
						if(data.error === undefined) {
							modalInstance.dismiss('Valid');
							logger.logSuccess("id00159_app:logger:success-sent-info");
						}
						else {
							modalInstance.dismiss('Error');
							logger.logError("id00061_app:logger:error-retry");
						}
						$location.path('/');
					}).error(function(){
						modalInstance.dismiss('Error');
						logger.logError("id00061_app:logger:error-retry");
						$location.path('/');
					});
			}
		}

	]).controller('profileAVCtrl',[
		'$scope','accionistasManager',"$location","$http","localize",'anonymousToken','logger','documentValidation','uploaderProvider','jsUtils', '$modal',
		function($scope,accionistasManager,$location,$http,localize,anonymousToken,logger,documentValidation,uploaderProvider,jsUtils,$modal) {
			var modalInstance;
			($scope.clear = function(){
				$scope.accionista = {
					documentType: 'nif',
					documentNum: ''
				};
			})();
			var i18n = localize.getLocalizedString;
			var initialData = {};

			var uploader = $scope.uploader = uploaderProvider.get($scope.uploaderID,$scope,anonymousToken.newToken());

			accionistasManager.getAccionista().then(function(accionista){
                
				if (accionista.getApp("av").state !== 3)
				{
					//$location.path("/404").replace();
				}
                
				accionista.token = uploader.getToken();
				accionista.tipoPersona = (accionista.represented_by != '') ? 'pj' : 'pf';
				initialData = accionista;
				accionista.messages = accionista.getApp("av").messages;
                
				($scope.clear = function(){
					$scope.accionista = JSON.parse(JSON.stringify(initialData));
					$scope.accionista.message = "";
					$scope.accionista.shares_num = jsUtils.formatNumber($scope.accionista.shares_num);
				})();

				$scope.uploader.setAdditional($scope.accionista.documents);
			}).catch(function(){
				$location.path("/404").replace();
			});

			$scope.docTypes = {
				nif: "id00155_app:doctype:nif",
				cif: "id00156_app:doctype:cif",
				nie: "id00157_app:doctype:nie",
				otros: "id00158_app:doctype:other"
			};
			$scope.i18n = localize.getLocalizedString;

			$scope.downloadDocument = function(id){
				window.location.href = ApiBase + "/documents/" + id;
			};

			$scope.isValid = function(){
				return  $scope.accionista_form.$valid &&
						documentValidation.validate($scope.accionista.document_num, $scope.accionista.document_type);
			}

			$scope.submit = function(){
				if($scope.accionista.tipoPersona == 'pf'){
					$scope.accionista.represented_by = '';
				}
				$scope.accionista.shares_num = jsUtils.cleanNumber($scope.accionista.shares_num);
				modalInstance = $modal.open({
					templateUrl: "myModalContent.html",
					backdrop: 'static'
				});
				$http.post(ApiBase + '/accionistas/regrants/avs', $scope.accionista)
					.success(function(data){
						if(data.error === undefined) {
							modalInstance.dismiss('Valid');
							logger.logSuccess("id00159_app:logger:success-sent-info");
						}
						else {
							modalInstance.dismiss('Error');
							logger.logError("id00061_app:logger:error-retry");
						}
						$location.path('/');
					}).error(function(){
						modalInstance.dismiss('Error');
						logger.logError("id00061_app:logger:error-retry");
						$location.path('/');
					});
			}
		}

	]);
}).call(this);