(function() {
	'use strict';
	angular.module('homeControllers'
	).controller('profileCtrl',[
		'$scope','accionistasHomeManager',"$location","$http","localize",'anonymousToken','logger','documentValidation','uploaderProvider','jsUtils', '$modal',
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
				if (accionista.item_accionista.state !== 3)
				{
					$location.path("/404").replace();
				}
				accionista.token = uploader.getToken();
				accionista.tipoPersona = (accionista.represented_by != '') ? 'pj' : 'pf';
				initialData = accionista;
				
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
				$http.post(ApiBase + '/publics/regrants', $scope.accionista)
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