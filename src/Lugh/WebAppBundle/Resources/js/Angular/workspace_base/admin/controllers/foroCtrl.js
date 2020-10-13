(function() {
	'use strict';
	var types = {
		"proposals":  "propuesta",
		"requests":   "peticion",
		"initiatives":"iniciativa",
		"offers":     "oferta"
	};
	
	angular.module('adminWorkspaceControllers'
	).controller('foroAllCtrl',[
		'$scope','itemsManager','stateService','localize','$location','$filter','typeService',function($scope,itemsManager,stateService,localize,$location,$filter,typeService){
			var init;
			$scope.numPerPageOpt     = [3, 5, 10, 20, 100];
			$scope.numPerPage        = $scope.numPerPageOpt[2];
			$scope.stores            = [];
			$scope.currentPageStores = [];
			$scope.filteredStores    = [];
			$scope.searchKeywords    = '';
			$scope.row               = '';
			$scope.currentPage       = 1;

			$scope.states = stateService.getIDStates();
			$scope.states['0'] = 'todo';
			$scope.filterState = '0';

			$scope.typeTags = typeService.getShortTags(true);
			$scope.i18n     = localize.getLocalizedString;

			itemsManager.getAll().then(function(itemsList){
				$scope.stores = $scope.stores.concat(itemsList['initiatives']);
				$scope.stores = $scope.stores.concat(itemsList['offers']);
				$scope.stores = $scope.stores.concat(itemsList['proposals']);
				$scope.stores = $scope.stores.concat(itemsList['requests']);
				init();
			});

			$scope.loadItem = function(item){
				$location.path("/app/foro/"+types[item.type]).search("id",item.id);
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
	]).controller('foroAllAdhesionsCtrl',[
		'$scope','adhesionsManager','stateService','localize','$location','$filter','typeService',function($scope,adhesionsManager,stateService,localize,$location,$filter,typeService){
			var init;
			$scope.numPerPageOpt     = [3, 5, 10, 20, 100];
			$scope.numPerPage        = $scope.numPerPageOpt[2];
			$scope.stores            = [];
			$scope.currentPageStores = [];
			$scope.filteredStores    = [];
			$scope.searchKeywords    = '';
			$scope.row               = '';
			$scope.currentPage       = 1;

			$scope.states = stateService.getIDStates();
			$scope.states['0'] = 'todo';
			$scope.filterState = '0';

			$scope.getTitle = function(Adhesion){
				return Adhesion[Adhesion.type].title;
			}

			adhesionsManager.getAll().then(function(adhesions){
				adhesions['adhesionOffers']      = _.map(adhesions['adhesionOffers'],      function(obj){ obj.type = "offer";      return obj; });
				adhesions['adhesionInitiatives'] = _.map(adhesions['adhesionInitiatives'], function(obj){ obj.type = "initiative"; return obj; });
				adhesions['adhesionProposals']   = _.map(adhesions['adhesionProposals'],   function(obj){ obj.type = "proposal";   return obj; });
				adhesions['adhesionRequests']    = _.map(adhesions['adhesionRequests'],    function(obj){ obj.type = "request";    return obj; });

				$scope.stores = $scope.stores.concat(adhesions['adhesionInitiatives']);
				$scope.stores = $scope.stores.concat(adhesions['adhesionOffers']);
				$scope.stores = $scope.stores.concat(adhesions['adhesionProposals']);
				$scope.stores = $scope.stores.concat(adhesions['adhesionRequests']);
				init();
			});

			$scope.loadAdhesion = function(item){
				$location.path("/app/foro/adhesion/"+item.type).search("id",item.id);
			};
			$scope.loadItem = function(item){
				$location.path("/app/foro/"+types[item.type+"s"]).search("id",eval("item."+item.type+".id"));
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
	]).controller('foroAllProposalsCtrl',[
		'$scope','itemsManager','stateService','$filter','$location','$http',function($scope,itemsManager,stateService,$filter,$location,$http){
			var init;
			$scope.numPerPageOpt     = [3, 5, 10, 20, 100];
			$scope.numPerPage        = $scope.numPerPageOpt[2];
			$scope.stores            = [];
			$scope.currentPageStores = [];
			$scope.filteredStores    = [];
			$scope.searchKeywords    = '';
			$scope.row               = '';
			$scope.currentPage       = 1;

			$scope.states = stateService.getIDStates();
			$scope.states['0'] = 'todo';
			$scope.filterState     = '0';

			itemsManager.getByType("proposals").then(function(itemList){
				$scope.stores = itemList['proposals'];
				init();
			});

			$scope.loadItem = function(item){
				$location.path("/app/foro/"+types[item.type]).search("id",item.id);
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
	]).controller('foroAllInitiativesCtrl',[
		'$scope','itemsManager','stateService','$filter','$location','$http',function($scope,itemsManager,stateService,$filter,$location,$http){
			var init;
			$scope.numPerPageOpt     = [3, 5, 10, 20, 100];
			$scope.numPerPage        = $scope.numPerPageOpt[2];
			$scope.stores            = [];
			$scope.currentPageStores = [];
			$scope.filteredStores    = [];
			$scope.searchKeywords    = '';
			$scope.row               = '';
			$scope.currentPage       = 1;

			$scope.states = stateService.getIDStates();
			$scope.states['0'] = 'todo';
			$scope.filterState     = '0';

			itemsManager.getByType("initiatives").then(function(itemList){
				$scope.stores = itemList['initiatives'];
				init();
			});

			$scope.loadItem = function(item){
				$location.path("/app/foro/"+types[item.type]).search("id",item.id);
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
	]).controller('foroAllOffersCtrl',[
		'$scope','itemsManager','stateService','$filter','$location','$http',function($scope,itemsManager,stateService,$filter,$location,$http){
			var init;
			$scope.numPerPageOpt     = [3, 5, 10, 20, 100];
			$scope.numPerPage        = $scope.numPerPageOpt[2];
			$scope.stores            = [];
			$scope.currentPageStores = [];
			$scope.filteredStores    = [];
			$scope.searchKeywords    = '';
			$scope.row               = '';
			$scope.currentPage       = 1;

			$scope.states = stateService.getIDStates();
			$scope.states['0'] = 'todo';
			$scope.filterState     = '0';

			itemsManager.getByType("offers").then(function(itemList){
				$scope.stores = itemList['offers'];
				init();
			});

			$scope.loadItem = function(item){
				$location.path("/app/foro/"+types[item.type]).search("id",item.id);
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
	]).controller('foroAllRequestsCtrl',[
		'$scope','itemsManager','stateService','$filter','$location','$http',function($scope,itemsManager,stateService,$filter,$location,$http){
			var init;
			$scope.numPerPageOpt     = [3, 5, 10, 20, 100];
			$scope.numPerPage        = $scope.numPerPageOpt[2];
			$scope.stores            = [];
			$scope.currentPageStores = [];
			$scope.filteredStores    = [];
			$scope.searchKeywords    = '';
			$scope.row               = '';
			$scope.currentPage       = 1;

			$scope.states = stateService.getIDStates();
			$scope.states['0'] = 'todo';
			$scope.filterState     = '0';

			itemsManager.getByType("requests").then(function(itemList){
				$scope.stores = itemList['requests'];
				init();
			});

			$scope.loadItem = function(item){
				$location.path("/app/foro/"+types[item.type]).search("id",item.id);
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
	]).controller('foroPendingCtrl',[
		'$scope','itemsManager','stateService','$filter','$location','$http','adhesionsManager','$q',
		function($scope,itemsManager,stateService,$filter,$location,$http,adhesionsManager,$q){
			var init;
			var deferred1 = $q.defer();
			//var deferred2 = $q.defer();
			var all  = $q.all([deferred1.promise]);//, deferred2.promise]);
			$scope.numPerPageOpt     = [3, 5, 10, 20, 100];
			$scope.numPerPage        = $scope.numPerPageOpt[2];
			$scope.stores            = [];
			$scope.currentPageStores = [];
			$scope.filteredStores    = [];
			$scope.searchKeywords    = '';
			$scope.row               = '';
			$scope.currentPage       = 1;
			
			$scope.types = types;
			$scope.types['todo'] = 'todo';
			$scope.filterType    = 'todo';
			
			$scope.types.adhesion = "Adhesion";
			itemsManager.getByState("pending").then(function(itemList){
				$scope.stores = $scope.stores.concat(itemList['proposals']);
				$scope.stores = $scope.stores.concat(itemList['initiatives']);
				$scope.stores = $scope.stores.concat(itemList['offers']);
				$scope.stores = $scope.stores.concat(itemList['requests']);
				deferred1.resolve();
			});
			
			/*//Descomentar para incluir adhesiones//
			adhesionsManager.loadAllByState(1).then(function(adhesions){
				adhesions['adhesionOffers']      = _.map(adhesions['adhesionOffers'],      function(obj){ obj.type = "adhesion"; obj.subtype = "offer";      return obj;});
				adhesions['adhesionInitiatives'] = _.map(adhesions['adhesionInitiatives'], function(obj){ obj.type = "adhesion"; obj.subtype = "initiative"; return obj;});
				adhesions['adhesionProposals']   = _.map(adhesions['adhesionProposals'],   function(obj){ obj.type = "adhesion"; obj.subtype = "proposal";   return obj;});
				adhesions['adhesionRequests']    = _.map(adhesions['adhesionRequests'],    function(obj){ obj.type = "adhesion"; obj.subtype = "request";    return obj;});

				$scope.stores = $scope.stores.concat(adhesions['adhesionInitiatives']);
				$scope.stores = $scope.stores.concat(adhesions['adhesionOffers']);
				$scope.stores = $scope.stores.concat(adhesions['adhesionProposals']);
				$scope.stores = $scope.stores.concat(adhesions['adhesionRequests']);
				deferred2.resolve();
				
			});*/
			
			all.then(function(){
			   init();
			});

			$scope.loadItem = function(item){
				$location.path("/app/foro/"+types[item.type]).search("id",item.id);
			};
			
			$scope.loadAdhesion = function(item){
				$location.path("/app/foro/adhesion/"+item.subtype).search("id",item.id);
			};
			$scope.loadSubItem = function(item){
				$location.path("/app/foro/"+types[item.subtype+"s"]).search("id",eval("item."+item.subtype+".id"));
			};
			
			$scope.getTitle = function(Adhesion){
				return Adhesion[Adhesion.subtype].title;
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

			$scope.filterByType = function(){
				if($scope.filterType == "todo")
					$scope.filteredStores = $scope.stores;
				else
					$scope.filteredStores = $filter('filter')($scope.stores,{type: $scope.filterType});
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
	]).controller('foroProposalsByUser',[
		'$scope','$routeParams','itemsManager','$filter','stateService','$location',function($scope, $routeParams, itemsManager, $filter, stateService, $location){
			var init;
			$scope.numPerPageOpt     = [3, 5, 10, 20, 100];
			$scope.numPerPage        = $scope.numPerPageOpt[2];
			$scope.stores            = [];
			$scope.currentPageStores = [];
			$scope.filteredStores    = [];
			$scope.searchKeywords    = '';
			$scope.row               = '';
			$scope.currentPage       = 1;

			$scope.states = stateService.getIDStates();
			$scope.states['0'] = 'todo';
			$scope.filterState     = '0';

			itemsManager.getAllByAccionista($routeParams.id,'proposals')
				.then(function(itemList){
					$scope.stores = itemList['proposals'];
					init();
				});

			$scope.loadItem = function(item){
				$location.path("/app/foro/"+types[item.type]).search("id",item.id);
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
	]).controller('foroInitiativesByUser',[
		'$scope','$routeParams','itemsManager','$filter','stateService','$location',function($scope, $routeParams, itemsManager, $filter, stateService, $location){
			var init;
			$scope.numPerPageOpt     = [3, 5, 10, 20, 100];
			$scope.numPerPage        = $scope.numPerPageOpt[2];
			$scope.stores            = [];
			$scope.currentPageStores = [];
			$scope.filteredStores    = [];
			$scope.searchKeywords    = '';
			$scope.row               = '';
			$scope.currentPage       = 1;

			$scope.states = stateService.getIDStates();
			$scope.states['0'] = 'todo';
			$scope.filterState     = '0';

			itemsManager.getAllByAccionista($routeParams.id,'initiatives')
				.then(function(itemList){
					$scope.stores = itemList['initiatives'];
					init();
				});

			$scope.loadItem = function(item){
				$location.path("/app/foro/"+types[item.type]).search("id",item.id);
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
	]).controller('foroOffersByUser',[
		'$scope','$routeParams','itemsManager','$filter','stateService','$location',function($scope, $routeParams, itemsManager, $filter, stateService, $location){
			var init;
			$scope.numPerPageOpt     = [3, 5, 10, 20, 100];
			$scope.numPerPage        = $scope.numPerPageOpt[2];
			$scope.stores            = [];
			$scope.currentPageStores = [];
			$scope.filteredStores    = [];
			$scope.searchKeywords    = '';
			$scope.row               = '';
			$scope.currentPage       = 1;

			$scope.states = stateService.getIDStates();
			$scope.states['0'] = 'todo';
			$scope.filterState     = '0';

			itemsManager.getAllByAccionista($routeParams.id,'offers')
				.then(function(itemList){
					$scope.stores = itemList['offers'];
					init();
				});

			$scope.loadItem = function(item){
				$location.path("/app/foro/"+types[item.type]).search("id",item.id);
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
	]).controller('foroRequestsByUser',[
		'$scope','$routeParams','itemsManager','$filter','stateService','$location',function($scope, $routeParams, itemsManager, $filter, stateService, $location){
			var init;
			$scope.numPerPageOpt     = [3, 5, 10, 20, 100];
			$scope.numPerPage        = $scope.numPerPageOpt[2];
			$scope.stores            = [];
			$scope.currentPageStores = [];
			$scope.filteredStores    = [];
			$scope.searchKeywords    = '';
			$scope.row               = '';
			$scope.currentPage       = 1;

			$scope.states = stateService.getIDStates();
			$scope.states['0'] = 'todo';
			$scope.filterState     = '0';

			itemsManager.getAllByAccionista($routeParams.id,'requests')
				.then(function(itemList){
					$scope.stores = itemList['requests'];
					init();
				});

			$scope.loadItem = function(item){
				$location.path("/app/foro/"+types[item.type]).search("id",item.id);
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
	]);
}).call(this);