(function() {
	'use strict';
	angular.module('workspaceDirectives',[
	]).directive('customNav', function() {
		return {
			restrict: "E",
			controller: ['$scope', '$element', '$location', '$compile', '$http', 
				function($scope, $element, $location, $compile, $http) {
					var getAddr, path, getNav;
					path = function() {
						return $location.path();
					};
					getAddr = function(path) {
						var addr;
						switch (path) {
							case String(path.match(/^\/app\/derecho\/[\w\/-]*/)):
								addr= 'views/nav-derecho.html';
								break;
							case String(path.match(/^\/app\/foro\/[\w\/-]*/)):
								addr= 'views/nav-foro.html';
								break;
                            case String(path.match(/^\/app\/av\/[\w\/-]*/)):
								addr= 'views/nav-av.html';
								break;
							default:
								addr= 'views/nav.html';
						}
						return addr;
						 
					};
					getNav = function(addr) {
						$http.get(addr).success(function(tplContent){
							$element.html($compile(tplContent)($scope));                
						  });
					};
					getNav(getAddr($location.path()));
					return $scope.$watch(path, function(newVal, oldVal) {
						if (newVal === oldVal) {
							return;
						}
						if (getAddr(newVal)===getAddr(oldVal)) {
							return;
						}
						return getNav(getAddr($location.path()));
						//return $element;
					});
				}
			]
		};
	}).directive('adhesionButton', [
		"$compile","localize", function($compile,localize){
			var text = {
				'-1': "id00119_app:foro:adhesion:loading",
				 '0': "id00118_app:foro:adhesion:adhere",
				 '1': "id00120_app:foro:adhesion:cancel",
				 '2': "id00120_app:foro:adhesion:cancel",
				 '3': "id00118_app:foro:adhesion:adhere",
				 '4': "id00121_app:foro:adhesion:rejected"
			};

			var btn = {
				'-1': "btn-dark",
				 '0': "btn-success",
				 '1': "btn-danger",
				 '2': "btn-danger",
				 '3': "btn-success",
				 '4': "btn-dark"
			};

			var icon = {
				'-1': "remove-sign",
				 '0': "plus-sign",
				 '1': "remove-sign",
				 '2': "remove-sign",
				 '3': "plus-sign",
				 '4': "remove-sign"
			}

			var disabled = {
				'-1': true,
				 '0': false,
				 '1': false,
				 '2': false,
				 '3': false,
				 '4': true
			};

			var action = {
				'-1': "",
				 '0': "adherir(item)",
				 '1': "cancelarAdhesion(item)",
				 '2': "cancelarAdhesion(item)",
				 '3': "adherir(item)",
				 '4': ""
			};

			var generate = function(element,scope,state,attrDisable){
				state = state.toString();
				element.html("");

				$compile('<button type="submit" class="btn '+ btn[state] +' btn-lg btn-w-lg" ng-disabled="'+(disabled[state] || attrDisable)+'" ng-click="'+action[state]+'"><span class="glyphicon glyphicon-'+icon[state]+'"></span> <span i18n="'+text[state]+'"></span></button>')(scope)
					.appendTo(element);
			};

			return {
				scope: {
					adhesionButton: '=',
					disable: '='
				},
				link: function(scope,element,attrs){
					var update = function(state){
						generate(element,scope.$parent,state,scope.disable || false);
					};
					update(scope.adhesionButton);

					scope.$watch('adhesionButton',function(state){update(state)});
					scope.$watch('disable'       ,function(){update(scope.adhesionButton)});
				}
			}
		}
	]).directive('itemButton',[
		"$compile", 'localize', function($compile, localize){
			var i18n = localize.getLocalizedString;

			var text = {
				'-1': "id00188_app:foro:item:button:loading",
				 '1': "id00189_app:foro:item:button:pending",
				 '2': "id00190_app:foro:item:button:public",
				 '3': "id00191_app:foro:item:button:retornate",
				 '4': "id00192_app:foro:item:button:rejected"
			};

			var btn = {
				'-1': "btn-dark",
				 '1': "btn-dark",
				 '2': "btn-info",
				 '3': "btn-success",
				 '4': "btn-dark"
			};

			var icon = {
				'-1': "remove-sign",
				 '1': "remove-sign",
				 '2': "eye-open",
				 '3': "plus-sign",
				 '4': "remove-sign"
			}

			var disabled = {
				'-1': true,
				 '1': true,
				 '2': false,
				 '3': false,
				 '4': true
			};

			var action = {
				'-1': "",
				 '1': "",
				 '2': "cancel(item)",
				 '3': "publish(item)",
				 '4': ""
			};

			var tooltip = {
				'-1': "",
				 '1': "",
				 '2': "id00234_app:tooltip:item-retornate",
				 '3': "id00206_app:tooltip:item-pending",
				 '4': ""
			};

			var generate = function(element,scope,state,attrDisable){
				state = state.toString();
				element.html("");

				var button = '<button type="submit" class="btn '+ btn[state] +' btn-lg btn-w-lg" ng-disabled="'+(disabled[state] || attrDisable)+'" ng-click="'+action[state]+'" tooltip-placement="bottom" tooltip="'+ i18n(tooltip[state]) +'"><span class="glyphicon glyphicon-'+icon[state]+'"></span> <span i18n="'+text[state]+'"></span></button>';

				if(state == '1'){
					button+= ' <button class="btn btn-w-lg btn-info btn-lg" ng-disabled="'+attrDisable+'" type="submit" ng-click="cancel(item)" tooltip-placement="" tooltip=""><span class="glyphicon glyphicon-eye-open"></span> <span i18n="id00235_app:foro:item:button:pendingb"></span></button>';
				}

				$compile(button)(scope).appendTo(element);
			};

			return {
				scope: {
					itemButton: '=',
					disable: '='
				},
				link: function(scope,element,attrs){
					var update = function(state){
						generate(element,scope.$parent,state,scope.disable || false);
					};
					update(scope.itemButton);

					scope.$watch('itemButton',function(state){update(state)});
					scope.$watch('disable'   ,function(){update(scope.itemButton)});
				}
			}
		}
	]).directive('totalForoCountBadge', [
        '$rootScope', 'intervalService',
        function ($rootScope, intervalService) {
            var ons;
            return{
                restrict: 'A',
                template: '{{totalforoPendientesCount}}',
                link: function ($scope, element, attrs, ctrl) {
                    var broadName = intervalService.subscribe('ActionsCount');
        
                    $scope.totalforoPendientesCount = 0;
                    ons = $rootScope.$on(broadName,function(event, args) { 
                          //$scope.totalforoPendientesCount = args;
                    });

                    element.on('$destroy', function() {
                        intervalService.unsubscribe('ActionsCount');
                        if(ons !== null) ons();
                        ons = null;
                    });
                }
            };
        }

    ]);
  
}).call(this);
