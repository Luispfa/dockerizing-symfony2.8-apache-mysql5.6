(function() {
    'use strict';
    angular.module('adminWorkspaceControllers'
    ).controller('usersAllCtrl',[
        '$scope', '$filter','$location','accionistasAdminManager','stateService', function($scope,$filter,$location,AccionistasManager,stateService){
            var init;
            $scope.numPerPageOpt     = [3, 5, 10, 20];
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
            
            $scope.getNameState = stateService.getNameState;

            //Cargar lista de accionistas
            AccionistasManager.loadAllAccionistas().then(function(accionistas){
                $scope.stores = accionistas;
                init();
            });
            
            $scope.excel = function(){
                window.location.href = ApiBase + "/accionistas/excel";
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
                    $scope.filteredStores = $filter('filter')($scope.stores,{item_accionista:{state: $scope.filterState}});
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

            $scope.loadUserProfile = function(id) {
                //$location.path("/app/profile-admin").search("id",id);
                $location.path("/app/users/profile").search("id",id);
            };

            return init();
        }

    ])
    .controller('usersCertificateCtrl',[
        '$scope', '$filter','$location','accionistasAdminManager','stateService', function($scope,$filter,$location,AccionistasManager,stateService){
            var init;
            $scope.numPerPageOpt     = [3, 5, 10, 20];
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
            
            $scope.getNameState = stateService.getNameState;

            //Cargar lista de accionistas
            AccionistasManager.loadAllByCertificate(true).then(function(accionistas){
                $scope.stores = accionistas;
                init();
            });
            
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
                    $scope.filteredStores = $filter('filter')($scope.stores,{item_accionista:{state: $scope.filterState}});
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

            $scope.loadUserProfile = function(id) {
                //$location.path("/app/profile-admin").search("id",id);
                $location.path("/app/users/profile").search("id",id);
            };

            return init();
        }

    ])
    .controller('usersNoCertificateCtrl',[
        '$scope', '$filter','$location','accionistasAdminManager','stateService', function($scope,$filter,$location,AccionistasManager,stateService){
            var init;
            $scope.numPerPageOpt     = [3, 5, 10, 20];
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
            
            $scope.getNameState = stateService.getNameState;

            //Cargar lista de accionistas
            AccionistasManager.loadAllByCertificate(false).then(function(accionistas){
                $scope.stores = accionistas;
                init();
            });
            
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
                    $scope.filteredStores = $filter('filter')($scope.stores,{item_accionista:{state: $scope.filterState}});
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

            $scope.loadUserProfile = function(id) {
                //$location.path("/app/profile-admin").search("id",id);
                $location.path("/app/users/profile").search("id",id);
            };

            return init();
        }

    ]);
}).call(this);