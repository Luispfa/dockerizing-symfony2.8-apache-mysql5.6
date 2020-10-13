(function() {
	'use strict';
	angular.module('adminWorkspaceControllers'
	).controller('propuestaAdminCtrl',[
		"$scope","$routeParams","itemsManager","logger","$route","stateService","$location","localize",
		function($scope,$routeParams,itemsManager,logger,$route,stateService,$location,localize){
			if($routeParams.id == undefined)
				$location.path("/404").replace();
			
			$scope.i18n = localize.getLocalizedString;
			$scope.stateTags = stateService.getIDTags();
			
			$scope.item = {};
			$scope.loading = false;
			$scope.state        = -1;
			$scope.initialState = -1;
			$scope.adhesions = [];

			itemsManager.getByTypeID("proposals",$routeParams.id).then(function(items){
				$scope.item = items["proposals"][0];
				$scope.initialState = $scope.item.state;
				$scope.sendMail = true;

				$scope.item.getAdhesions().then(function(adhesions){
					$scope.adhesions = adhesions;
					$scope.adhesions.unshift({accionista: $scope.item.autor, owner: true});
					$scope.total_shares = 0;

					adhesions.forEach(function(adh){if(adh.owner || adh.state ==2){ $scope.total_shares += adh.accionista.shares_num;}});
				});
			}).catch(function(){
				$location.path("/404").replace();
			});

			$scope.confirm = function(item){
				if($scope.item.state == $scope.initialState) return;

				$scope.loading = true;
				$scope.item.update().then(function(item) {
					$scope.loading = false;
					if (item.error !== undefined)
					{
						logger.logError(item.error);
					}
					else
					{
						logger.logSuccess("Propuesta modificada.");
					}
					$scope.item.reload();
					$scope.initialState = $scope.item.state;
					$scope.item.message = '';
				}).catch(function(){
					$scope.loading = false;
					logger.logError("No se ha podido realizar la acción.");
				});
			};

			$scope.sendComment = function(){
				$scope.item.sendComment($scope.item.message,$scope.sendMail)
					.then(function(){
						logger.logSuccess("Comentario enviado correctamente");
						$scope.item.reload();
						$scope.item.message = '';
					})
					.catch(function(){
						logger.logError("No se ha podido enviar el comentario");
					});
					
			};

			$scope.goToAdhesion = function(adhID){
				if(adhID)
					$location.path("/app/foro/adhesion/proposal").search("id",adhID);
			};

			$scope.clear = function(){
				$scope.state = $scope.initialState;
				$scope.item.message = '';
			};
		}
	]).controller('peticionAdminCtrl',[
		"$scope","$routeParams","itemsManager","logger","$route","stateService","$location","localize",
		function($scope,$routeParams,itemsManager,logger,$route,stateService,$locatio,localize){
			if($routeParams.id == undefined)
				$location.path("/404").replace();
			
			$scope.i18n = localize.getLocalizedString;
			$scope.stateTags = stateService.getIDTags();
			
			$scope.item = {};
			$scope.loading = false;
			$scope.state        = -1;
			$scope.initialState = -1;
			$scope.adhesions = [];

			itemsManager.getByTypeID("requests",$routeParams.id).then(function(items){
				$scope.item = items["requests"][0];
				$scope.initialState = $scope.item.state;
				$scope.sendMail = true;

				$scope.item.getAdhesions().then(function(adhesions){
					$scope.adhesions = adhesions;
					$scope.adhesions.unshift({accionista: $scope.item.autor, owner: true});
					$scope.total_shares = 0;

					adhesions.forEach(function(adh){if(adh.state ==2){ $scope.total_shares += adh.accionista.shares_num;}});
				});
			}).catch(function(){
				$location.path("/404").replace();
			});

			$scope.confirm = function(item){
				if($scope.item.state == $scope.initialState) return;

				$scope.loading = true;
				$scope.item.update().then(function(item) {
					$scope.loading = false;
					if (item.error !== undefined)
					{
						logger.logError(item.error);
					}
					else
					{
						logger.logSuccess("Petición modificada.");
					}
					$scope.item.reload();
					$scope.initialState = $scope.item.state;
					$scope.item.message = '';
				}).catch(function(){
					$scope.loading = false;
					logger.logError("No se ha podido realizar la acción.");
				});  
			};

			$scope.sendComment = function(){
				$scope.item.sendComment($scope.item.message,$scope.sendMail)
					.then(function(){
						logger.logSuccess("Comentario enviado correctamente");
						$scope.item.reload();
						$scope.item.message = '';
					})
					.catch(function(){
						logger.logError("No se ha podido enviar el comentario");
					});
			};

			$scope.goToAdhesion = function(adhID){
				if(adhID)
					$location.path("/app/foro/adhesion/request").search("id",adhID);
			};

			$scope.clear = function(){
				$scope.state = $scope.initialState;
				$scope.item.message = '';
			};
		}
	]).controller('iniciativaAdminCtrl',[
		"$scope","$routeParams","itemsManager","logger","$route","stateService","$location","localize",
		function($scope,$routeParams,itemsManager,logger,$route,stateService,$locatio,localize){
			if($routeParams.id == undefined)
				$location.path("/404").replace();
			
			$scope.i18n = localize.getLocalizedString;
			$scope.stateTags = stateService.getIDTags();
			
			$scope.item = {};
			$scope.loading = false;
			$scope.state        = -1;
			$scope.initialState = -1;
			$scope.adhesions = [];

			itemsManager.getByTypeID("initiatives",$routeParams.id).then(function(items){
				$scope.item = items["initiatives"][0];
				$scope.initialState = $scope.item.state;
				$scope.sendMail = true;

				$scope.item.getAdhesions().then(function(adhesions){
					$scope.adhesions = adhesions;
					$scope.adhesions.unshift({accionista: $scope.item.autor, owner: true});
					$scope.total_shares = 0;

					adhesions.forEach(function(adh){if(adh.owner || adh.state ==2){ $scope.total_shares += adh.accionista.shares_num;}});
				});
			}).catch(function(){
				$location.path("/404").replace();
			});

			$scope.confirm = function(item){
				if($scope.item.state == $scope.initialState) return;

				$scope.loading = true;
				$scope.item.update().then(function(item) {
					$scope.loading = false;
					if (item.error !== undefined)
					{
						logger.logError(item.error);
					}
					else
					{
						logger.logSuccess("Iniciativa modificada.");
					}
					$scope.item.reload();
					$scope.initialState = $scope.item.state;
					$scope.item.message = '';
				}).catch(function(){
					$scope.loading = false;
					logger.logError("No se ha podido realizar la acción.");
				});
			};

			$scope.sendComment = function(){
				$scope.item.sendComment($scope.item.message,$scope.sendMail)
					.then(function(){
						logger.logSuccess("Comentario enviado correctamente");
						$scope.item.reload();
						$scope.item.message = '';
					})
					.catch(function(){
						logger.logError("No se ha podido enviar el comentario");
					});
			};

			$scope.goToAdhesion = function(adhID){
				if(adhID)
					$location.path("/app/foro/adhesion/initiative").search("id",adhID);
			};

			$scope.clear = function(){
				$scope.state = $scope.initialState;
				$scope.item.message = '';
			};
		}
	]).controller('ofertaAdminCtrl',[
		"$scope","$routeParams","itemsManager","logger","$route","stateService","$location","localize",
		function($scope,$routeParams,itemsManager,logger,$route,stateService,$locatio,localize){
			if($routeParams.id == undefined)
				$location.path("/404").replace();
			
			$scope.i18n = localize.getLocalizedString;
			$scope.stateTags = stateService.getIDTags();
			
			$scope.item = {};
			$scope.loading = false;
			$scope.state        = -1;
			$scope.initialState = -1;
			$scope.adhesions = [];

			itemsManager.getByTypeID("offers",$routeParams.id).then(function(items){
				$scope.item = items["offers"][0];
				$scope.initialState = $scope.item.state;
				$scope.sendMail = true;

				$scope.item.getAdhesions().then(function(adhesions){
					$scope.adhesions = adhesions;
					$scope.adhesions.unshift({accionista: $scope.item.autor, owner: true});
					$scope.total_shares = 0;

					adhesions.forEach(function(adh){if(adh.owner || adh.state ==2){ $scope.total_shares += adh.accionista.shares_num;}});
				});
			}).catch(function(){
				$location.path("/404").replace();
			});

			$scope.confirm = function(item){
				if($scope.item.state == $scope.initialState) return;

				$scope.loading = true;
				$scope.item.update().then(function(item) {
					$scope.loading = false;
					if (item.error !== undefined)
					{
						logger.logError(item.error);
					}
					else
					{
						logger.logSuccess("Oferta modificada.");
					}
					$scope.item.reload();
					$scope.initialState = $scope.item.state;
					$scope.item.message = '';
				}).catch(function(){
					$scope.loading = false;
					logger.logError("No se ha podido realizar la acción.");
				});
			};

			$scope.sendComment = function(){
				$scope.item.sendComment($scope.item.message,$scope.sendMail)
					.then(function(){
						logger.logSuccess("Comentario enviado correctamente");
						$scope.item.reload();
						$scope.item.message = '';
					})
					.catch(function(){
						logger.logError("No se ha podido enviar el comentario");
					});
			};

			$scope.goToAdhesion = function(adhID){
				if(adhID)
					$location.path("/app/foro/adhesion/offer").search("id",adhID);
			};

			$scope.clear = function(){
				$scope.state = $scope.initialState;
				$scope.item.message = '';
			};
		}
	]);

}).call(this);