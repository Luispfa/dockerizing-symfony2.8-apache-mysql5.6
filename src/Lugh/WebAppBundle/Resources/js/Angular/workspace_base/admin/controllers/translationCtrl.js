(function() {
	'use strict';
    angular.module('adminWorkspaceControllers'
    ).controller('translationCtrl',[
        '$scope', '$filter','$location','$http', function($scope,$filter,$location,$http){
            var init;
            $scope.numPerPageOpt     = [3, 5, 10, 20, 100];
            $scope.numPerPage        = $scope.numPerPageOpt[2];
            $scope.stores            = [];
            $scope.currentPageStores = [];
            $scope.filteredStores    = [];
            $scope.searchKeywords    = '';
            $scope.row               = '';
            $scope.currentPage       = 1;
            $scope.editTag = [];
            $scope.languageFilter = 'es';

            $scope.languages = {'es': "Castellano",
            					'ca': "Català",
            					'en': "English"};

            var getStoresIndexByTag = function(tag) {
            	return $scope.stores.map(function(x) {return x.tag;}).indexOf(tag);
            }

            angular.forEach($scope.languages,function(language,lang){
	            $http({
		            method: "GET",
		            url: 'i18n/resources-locale_' + lang + '.js',
		            cache: false
		        }).success(function(langData){
		        	angular.forEach(langData, function(value,tag){

		        		var index = getStoresIndexByTag(tag);

		        		if(index === -1) {
		        			$scope.stores.push({"tag": tag,"es":"","ca":"","en":""});
		        			index = getStoresIndexByTag(tag);
		        		}

		        		$scope.stores[index][lang] = value;
		        	});
		        	init();
		        });
	    	});

	    	$scope.loadTagForEdition = function(data) {
	    		$scope.editTag = {
	    			tag: data['tag']
	    		};

	    		angular.forEach($scope.languages,function(language,lang){
	    			$scope.editTag[lang] = data[lang];
	    		});
	    	};

	    	$scope.submitTag = function(tag) {
	    		//@TODO -- guardar la nueva información, enviar las modificaciones al servidor

	    		$scope.editTag = [];
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
                $location.path("/app/profile").search("id",id);
            };

            return init();
        }

    ]);
})();