(function() {
	'use strict';

	angular.module('adminWorkspaceControllers'
	).controller('votosCtrl',[
		"$scope","actionsManager","$filter","$location",
		function($scope,actionsManager,$filter,$location){
			var init;
			$scope.numPerPageOpt     = [3, 5, 10, 20, 100];
			$scope.numPerPage        = $scope.numPerPageOpt[2];
			$scope.stores            = [];
			$scope.currentPageStores = [];
			$scope.filteredStores    = [];
			$scope.searchKeywords    = '';
			$scope.row               = '';
			$scope.currentPage       = 1;

			actionsManager.getVotos().then(function(votos){
				$scope.stores = votos;
				init();
			});

			$scope.loadAccion = function(id){
				$location.path("app/voto/accion").search("id",id);
			};

			$scope.select = function(page) {
				var end, start;
				start = (page - 1) * $scope.numPerPage;
				end = start + $scope.numPerPage;
				return $scope.currentPageStores = $scope.filteredStores.slice(start, end);
			};

			$scope.onFilterChange = function() {
				$scope.select(1);
				$scope.currentPage = 1;
				return $scope.row = '';
			};

			$scope.onNumPerPageChange = function() {
				$scope.select(1);
				return $scope.currentPage = 1;
			};

			$scope.onOrderChange = function() {
				$scope.select(1);
				return $scope.currentPage = 1;
			};

			$scope.search = function() {
				$scope.filteredStores = $filter('filter')($scope.stores, $scope.searchKeywords);
				return $scope.onFilterChange();
			};

			$scope.filterByState = function(){
				if($scope.filterState == 0)
					$scope.filteredStores = $scope.stores;
				else
					$scope.filteredStores = $filter('filter')($scope.stores,{state: $scope.filterState});
				return $scope.onFilterChange();
			};

			$scope.order = function(rowName) {
				if ($scope.row === rowName) {
					return;
				}
				$scope.row = rowName;
				$scope.filteredStores = $filter('orderBy')($scope.stores, rowName);
				return $scope.onOrderChange();
			};

			init = function() {
				$scope.search();
				return $scope.select($scope.currentPage);
			};

			return init();
		}
	]).controller('delegacionesCtrl',[
		"$scope","actionsManager","$filter","$location",
		function($scope,actionsManager,$filter,$location){
			var init;
			$scope.numPerPageOpt     = [3, 5, 10, 20, 100];
			$scope.numPerPage        = $scope.numPerPageOpt[2];
			$scope.stores            = [];
			$scope.currentPageStores = [];
			$scope.filteredStores    = [];
			$scope.searchKeywords    = '';
			$scope.row               = '';
			$scope.currentPage       = 1;

			actionsManager.getDelegaciones().then(function(delegaciones){
				$scope.stores = delegaciones;
				init();
			});

			$scope.loadAccion = function(id){
				$location.path("app/voto/accion").search("id",id);
			};

			$scope.select = function(page) {
				var end, start;
				start = (page - 1) * $scope.numPerPage;
				end = start + $scope.numPerPage;
				return $scope.currentPageStores = $scope.filteredStores.slice(start, end);
			};

			$scope.onFilterChange = function() {
				$scope.select(1);
				$scope.currentPage = 1;
				return $scope.row = '';
			};

			$scope.onNumPerPageChange = function() {
				$scope.select(1);
				return $scope.currentPage = 1;
			};

			$scope.onOrderChange = function() {
				$scope.select(1);
				return $scope.currentPage = 1;
			};

			$scope.search = function() {
				$scope.filteredStores = $filter('filter')($scope.stores, $scope.searchKeywords);
				return $scope.onFilterChange();
			};

			$scope.filterByState = function(){
				if($scope.filterState == 0)
					$scope.filteredStores = $scope.stores;
				else
					$scope.filteredStores = $filter('filter')($scope.stores,{state: $scope.filterState});
				return $scope.onFilterChange();
			};

			$scope.order = function(rowName) {
				if ($scope.row === rowName) {
					return;
				}
				$scope.row = rowName;
				$scope.filteredStores = $filter('orderBy')($scope.stores, rowName);
				return $scope.onOrderChange();
			};

			init = function() {
				$scope.search();
				return $scope.select($scope.currentPage);
			};

			return init();
		}
	]).controller('anulacionesCtrl',[
		"$scope","actionsManager","$filter","$location",
		function($scope,actionsManager,$filter,$location){
			var init;
			$scope.numPerPageOpt     = [3, 5, 10, 20, 100];
			$scope.numPerPage        = $scope.numPerPageOpt[2];
			$scope.stores            = [];
			$scope.currentPageStores = [];
			$scope.filteredStores    = [];
			$scope.searchKeywords    = '';
			$scope.row               = '';
			$scope.currentPage       = 1;

			actionsManager.getAnulaciones().then(function(anulaciones){
				$scope.stores = anulaciones;
				init();
			});

			$scope.loadAccion = function(id){
				$location.path("app/voto/accion").search("id",id);
			};

			$scope.select = function(page) {
				var end, start;
				start = (page - 1) * $scope.numPerPage;
				end = start + $scope.numPerPage;
				return $scope.currentPageStores = $scope.filteredStores.slice(start, end);
			};

			$scope.onFilterChange = function() {
				$scope.select(1);
				$scope.currentPage = 1;
				return $scope.row = '';
			};

			$scope.onNumPerPageChange = function() {
				$scope.select(1);
				return $scope.currentPage = 1;
			};

			$scope.onOrderChange = function() {
				$scope.select(1);
				return $scope.currentPage = 1;
			};

			$scope.search = function() {
				$scope.filteredStores = $filter('filter')($scope.stores, $scope.searchKeywords);
				return $scope.onFilterChange();
			};

			$scope.filterByState = function(){
				if($scope.filterState == 0)
					$scope.filteredStores = $scope.stores;
				else
					$scope.filteredStores = $filter('filter')($scope.stores,{state: $scope.filterState});
				return $scope.onFilterChange();
			};

			$scope.order = function(rowName) {
				if ($scope.row === rowName) {
					return;
				}
				$scope.row = rowName;
				$scope.filteredStores = $filter('orderBy')($scope.stores, rowName);
				return $scope.onOrderChange();
			};

			init = function() {
				$scope.search();
				return $scope.select($scope.currentPage);
			};

			return init();
		}
	]).controller('accionesCtrl',[
		"$scope","actionsManager","$filter","$location",
		function($scope,actionsManager,$filter,$location){
			var init;
			$scope.numPerPageOpt     = [3, 5, 10, 20, 100];
			$scope.numPerPage        = $scope.numPerPageOpt[2];
			$scope.stores            = [];
			$scope.currentPageStores = [];
			$scope.filteredStores    = [];
			$scope.searchKeywords    = '';
			$scope.row               = '';
			$scope.currentPage       = 1;

			$scope.type = {
			 'def': 'Acción',
				 0: 'Voto',
				 1: 'Delegación',
				99: 'Anulación'
			};

			actionsManager.getAcciones().then(function(acciones){
				$scope.stores = acciones;
				init();
			});

			$scope.compareDatesByDay = function(date1, date2){
				return (date1.getFullYear() == date2.getFullYear()) &&
				       (date1.getMonth()    == date2.getMonth())    &&
				       (date1.getDate()     == date2.getDate());
			}

			$scope.compareDatesByMonth = function(date1, date2){
				return (date1.getFullYear() == date2.getFullYear()) &&
				       (date1.getMonth()    == date2.getMonth());
			}

			$scope.compareDatesByYear = function(date1, date2){
				return (date1.getFullYear() == date2.getFullYear());
			}

			$scope.loadAccion = function(id){
				$location.path("app/voto/accion").search("id",id);
			};
			
			$scope.excel = function(){
				window.location.href = ApiBase + "/accions/excel";
			}

			$scope.select = function(page) {
				var end, start;
				start = (page - 1) * $scope.numPerPage;
				end = start + $scope.numPerPage;
				return $scope.currentPageStores = $scope.filteredStores.slice(start, end);
			};

			$scope.onFilterChange = function() {
				$scope.select(1);
				$scope.currentPage = 1;
				return $scope.row = '';
			};

			$scope.onNumPerPageChange = function() {
				$scope.select(1);
				return $scope.currentPage = 1;
			};

			$scope.onOrderChange = function() {
				$scope.select(1);
				return $scope.currentPage = 1;
			};

			$scope.search = function() {
				$scope.filteredStores = $filter('filter')($scope.stores, $scope.searchKeywords);
				return $scope.onFilterChange();
			};

			$scope.filterByState = function(){
				if($scope.filterState == 0)
					$scope.filteredStores = $scope.stores;
				else
					$scope.filteredStores = $filter('filter')($scope.stores,{state: $scope.filterState});
				return $scope.onFilterChange();
			};

			$scope.order = function(rowName) {
				if ($scope.row === rowName) {
					return;
				}
				$scope.row = rowName;
				$scope.filteredStores = $filter('orderBy')($scope.stores, rowName);
				return $scope.onOrderChange();
			};

			init = function() {
				$scope.search();

				var datesArray = [];
				for(var i in $scope.stores){
					datesArray.push(new Date($scope.stores[i].date_time));
				}

				$scope.disabled = function(date, mode){
					for(var i in datesArray){
						if(mode == 'day' && $scope.compareDatesByDay(datesArray[i],date)){
							return false;
						}
						if(mode == 'month' && $scope.compareDatesByMonth(datesArray[i],date)){
							return false;
						}
						if(mode == 'year' && $scope.compareDatesByYear(datesArray[i],date)){
							return false;
						}
					}
					return true;
				}
				$scope.today();
				$scope.clear();

				return $scope.select($scope.currentPage);
			};

			/**
			 * Date picker
			 */
			$scope.filterDate = function() {
				if($scope.dt != null){
					$scope.filteredStores = $filter('filter')($scope.stores,function(value,index){
						var date = new Date(value.date_time);
						return $scope.compareDatesByDay($scope.dt, date);
					});
				}
				else {
					$scope.filteredStores = $scope.stores;
				}

				return $scope.onFilterChange();
			}

			$scope.today = function() {
				return $scope.dt = new Date();
			};
			$scope.clear = function() {
				return $scope.dt = null;
			};
			$scope.open = function($event) {
				$event.preventDefault();
				$event.stopPropagation();
				return $scope.opened = true;
			};
			$scope.dateOptions = {
				'date-format': "'dd/MM/yy'",
				'starting-day': 1,
				'show-weeks': false
			};

			$scope.formats = ['dd/MM/yy'];// ['dd-MMMM-yyyy', 'yyyy/MM/dd', 'shortDate'];
			$scope.format = $scope.formats[0];
			//return init();
		}
	]).controller('historialCtrl',[
		'$scope', '$routeParams', '$location', 'accionistasAdminManager',
		function($scope,$routeParams,$location,accionistasAdminManager){
			if($routeParams.id == undefined)
				$location.path("/404").replace();

			$scope.type = {
			 'def': 'Acción',
				 0: 'Voto',
				 1: 'Delegación',
				99: 'Anulación'
			};

			accionistasAdminManager.getAccionista($routeParams.id).then(function(accionista){
				$scope.accionista = accionista;
				accionista.getAccions().then(function(actions){
					$scope.actions = actions.accion;
				});
			});

			$scope.loadAction = function(id){
				$location.path("app/voto/accion").search("id",id);
			}
		}
	]).controller('accionCtrl',[
		'$scope', '$routeParams', '$location', 'actionsManager',
		function($scope,$routeParams,$location,actionsManager){
			if($routeParams.id == undefined)
				$location.path("/404").replace();

			$scope.type = {
			 'def': 'Acción',
				 0: 'Voto',
				 1: 'Delegación',
				99: 'Anulación'
			};

			$scope.action = {discr: 'def'};
			actionsManager.getAccion($routeParams.id).then(function(action){
				$scope.action = action;
			});

			$scope.loadAccionista = function(id){
                            //$location.path("/app/profile-admin").search("id",id);
                            $location.path("/app/users/profile").search("id",id);
			}

			$scope.loadHistorial = function(id){
				$location.path("/app/voto/historial").search("id",id);
			}
		}
	]).controller('movimientosCtrl',[
		'$scope',
		function($scope){
			$scope.getCOBSA = function(){
				window.location.href = ApiBase + "/accions/movimientosfile";
			}

			$scope.getTotal = function(){
				window.location.href = ApiBase + "/accions/movimientosfiletotal";
			}

			$scope.today = function() {
				return $scope.dt = new Date();
			};
			$scope.clear = function() {
				return $scope.dt = null;
			};
			$scope.open = function($event) {
				$event.preventDefault();
				$event.stopPropagation();
				return $scope.opened = true;
			};
			$scope.dateOptions = {
				'date-format': "'dd/MM/yy'",
				'starting-day': 1,
				'show-weeks': false
			};

			$scope.formats = ['dd/MM/yy'];// ['dd-MMMM-yyyy', 'yyyy/MM/dd', 'shortDate'];
			$scope.format = $scope.formats[0];

			$scope.getByDate = function(){
				window.location.href = ApiBase + "/accions/movimientosfiledate?day="+$scope.dt.getDate()+"&month="+($scope.dt.getMonth()+1)+"&year="+$scope.dt.getFullYear();
			}
		}
	]);
}).call(this);