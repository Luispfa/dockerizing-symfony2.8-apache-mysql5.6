(function() {
	'use strict';
	angular.module('workspaceControllers'
	).directive('uiWizardForm',[
		"$compile","$timeout","$rootScope","assocArray","stepsManager","votoUtils","typeVotoPersistence","logger",
		function($compile, $timeout,$rootScope,assocArray,stepsManager,votoUtils,  typeVotoPersistence, logger){
			var directives = {
				"arbitrary-view": "arbitrary-step",
				"shares":         "shares-step",
				"vote-delegate":  "vote-delegate-step",
				"DCICV":          "intention-delegation-vote-step",
				"DSISV":          "non-intention-delegation-non-vote-step",
				"DSICV":          "non-intention-delegation-vote-step",
				"DCISV":          "intention-delegation-non-vote-step",
				"vote":           "vote-step",
				"confirm":        "confirm-step",
				"end":            "end-step"
			};

			var loadConfig = function(config){
				var code = '<div>';
				var directive,title,data,id;
				for(var i in config){
					directive = directives[config[i].type];
					title     = config[i].title;
					data      = config[i].data;
					id        = config[i].id;

					code += '<' + directive + ' ';

					for(var j in data){
						code += j + '="' + data[j] + '" ';
					}

					code += 'title="'+ title +'" step-id="' + id + '"></' + directive + '>\n';
				}
				code += "</div>";
				return code;
			};

			return {
				//controller: "wizardVotoCtrl",
				link: function($scope,ele,attrs,controller){
					votoUtils.init();
					var wizard = "";
					typeVotoPersistence.getPatternVoto().then(function(vototypeData){
						$scope.config = vototypeData;
						stepsManager.init(ele);

						wizard = loadConfig($scope.config);
						ele.html($(wizard).contents());
						$compile(ele.contents())($scope);

						$timeout(
							function() {
								stepsManager.directiveFinish();
							}
						);
					}).catch(function(){
						logger.logError("id00201_app:logger:server-error");
					});
				}
			}
		}
	]).directive('recursiveWizardConstruct',[
		"$compile","stepsManager","$rootScope",
		function($compile,stepsManager, $rootScope){
			var sortByOrder = function(stepsArray){
				stepsArray.sort(function(a,b){
					return a['orden'] - b['orden'];
				});
			};

			var getModel = function(depth){
				var model = 'step';
				for(var i = 0; i < depth.length; ++i){
					model += ".subpuntos["+ depth[i] +"]";
				}
				return model;
			};

			var getOption = function(step,option,depth,index,disabled,option_index){
				var code = '<div class="col-sm-2"><label class="ui-radio"><input type="radio" ';

				if(!disabled){
					code += 'name="step-'+ step.id + '" ng-value="\''+ option.id + '\'"';
					code += ' ng-model="';

					var model = 'step';
					var reference = index;
					for(var i = 0; i < depth.length; ++i){
						model += ".subpuntos["+ depth[i] +"]";
						reference += '-' + depth[i];
					}

					code += model + '.value" ng-change="onVote(\'' + reference + '\')" ';
				}

				if(disabled){
					code += 'value="'+ option.id + '"';
					if(step.value == option.id){
						code += ' checked';
					} else {
						code += ' disabled';
					}
				}

				var model = getModel(depth);
				model += '.grupos_o_v.opciones_voto['+option_index+'].nombre';

				code += '><span>{{' + model + '}}</span></label></div>';
				return code;
			};

			var getOptionCheckbox = function(step,option,depth,index,disabled,option_index){
				var code = '<label class="ui-checkbox"><input type="checkbox" ';

				if(!disabled){
					code += 'name="step-'+ step.id + '" ng-true-value="' + option.id + '"';
					code += ' ng-model="';

					var model = 'step';
					var reference = index;
					for(var i = 0; i < depth.length; ++i){
						model += ".subpuntos["+ depth[i] +"]";
						reference += '-' + depth[i];
					}

					code += model + '.value" ng-change="onVote(\'' + reference + '\')"';
				}

				if(disabled){
					if(step.value == option.id){
						code += ' checked disabled';
					} else {
						code += ' disabled';
					}
				}

				var model = getModel(depth);
				model += '.grupos_o_v.opciones_voto['+option_index+'].nombre';

				code += ' /><span>{{' + model + '}}</span></label>';
				return code;
			};

			var getOptions = function(step,depth,index,disabled,showResult){
				if(step['informativo'])
					return '';

				var code ='<div class="form-group"> <div class="col-sm-12">';

				if(!showResult){
					if(step['grupos_o_v']['opciones_voto'].length == 1){
						code += getOptionCheckbox(step,step['grupos_o_v']['opciones_voto'][0],depth,index,disabled,0);
					}
					else{
						for(var i = 0; i < step['grupos_o_v']['opciones_voto'].length; ++i){
							code += getOption(step,step['grupos_o_v']['opciones_voto'][i],depth,index,disabled,i);
						}
					}
				}
				else {
					for(var i = 0; i < step['grupos_o_v']['opciones_voto'].length; ++i){
						if(step['grupos_o_v']['opciones_voto'][i].id == step.value){
							var model = getModel(depth);
							model += '.grupos_o_v.opciones_voto['+i+'].nombre';
							code += '<label><span>{{' + model + '}}</span></label>';
						}
					}
				}

				code += '</div> </div>';
				return code;
			};

			var getBody = function(step,depth,index,disabled,panelDefault,showResult,voteDelegate){
				if(disabled && (step.value === undefined || step.value === false) && step['subpuntos'].length == 0) return '';
				
				var code = '<p>{{' + getModel(depth) + '.text}}</p><hr/>';
				if(step['subpuntos'].length === 0){
					code += getOptions(step,depth,index,disabled,showResult);
				}
				else{
					sortByOrder(step['subpuntos']);
					var panels = '';
					for(var i = 0; i < step['subpuntos'].length; ++i){
						panels += getSubPanel(step['subpuntos'][i],depth.concat(i),index,disabled,panelDefault,showResult,voteDelegate);
					}
					if(panels == '') return '';
					code+=panels;
				}
				return code;
			};

			var getTypePanel = function(step,panelDefault){
				var panelType = 'panel-primary';
				if(panelDefault){
					panelType = 'panel-default';
				} else if(step.extra != 0){
					switch(step.extra){
						case 1:
							panelType = 'panel-extra-a';
							break;
						case 2:
							panelType = 'panel-extra-b';
							break;
						case 3:
							panelType = 'panel-extra-c';
							break;
						case 4:
							panelType = 'panel-extra-d';
							break;
                                                case 5:
							panelType = 'panel-extra-e';
							break;
						case 6:
							panelType = 'panel-extra-f';
							break;
						case 7:
							panelType = 'panel-extra-g';
							break;
						case 8:
							panelType = 'panel-extra-h';
							break;
                                                case 9:
							panelType = 'panel-extra-i';
							break;
					}
				}
				return panelType;
			}

			var getSubPanel = function(step,depth,index,disabled,panelDefault,showResult,voteDelegate){
				if(voteDelegate != undefined) {
					if(voteDelegate == 'vote' && step['vote_proxy'] == 2){
						return '<div></div>';
					}
					if(voteDelegate == 'delegation' && step['vote_proxy'] == 1){
						return '<div></div>';
					}
				}

				if(disabled && (step.value === undefined || step.value === false) && step['subpuntos'].length == 0) return '';
				var code = '<div class="panel ' + getTypePanel(step,panelDefault) + ' row" style="margin-left:10px;margin-right:10px;">';

				var informativo = step['informativo'] ? ' <span i18n="id00415_app:voto:informative"></span>' : '';
				code += '<div class="panel-heading"><h3 class="panel-title">' + step['num_punto'] + informativo + '</h3></div>';
				code += '<div class="panel-body">';


				code += getBody(step,depth,index,disabled,panelDefault,showResult,voteDelegate);

				code += '</div>';
				code += '</div>';
				return code;
			};

			var getPanel = function(step,depth,index,disabled,panelDefault,showResult,voteDelegate){
				if(voteDelegate != undefined) {
					if(voteDelegate == 'vote' && step['vote_proxy'] == 2){
						return '<div></div>';
					}
					if(voteDelegate == 'delegation' && step['vote_proxy'] == 1){
						return '<div></div>';
					}
				}

				if(disabled && (step.value === undefined || step.value === false) && step['subpuntos'].length == 0) return '';
				var code = '<div class="panel ' + getTypePanel(step,panelDefault) + '">';

				var informativo = step['informativo'] ? ' <span i18n="id00415_app:voto:informative"></span>' : '';
				code += '<div class="panel-heading"><h3 class="panel-title">' + step['num_punto'] + informativo + '</h3></div>';
				code += '<div class="panel-body">';

				var body = getBody(step,depth,index,disabled,panelDefault,showResult,voteDelegate);

				if(body == '')
					return '';
				else
					code += body;

				code += '</div>';
				code += '</div>';
				return code;
			};

			var code = [];
			return {
				scope: false,
				link: function($scope,ele,attrs){
					var load;
					(load = function(){
						var puntos = {};
						if(attrs['confirm'] === undefined){
							puntos = $scope.puntos[attrs['recursiveWizardConstruct']];
						} else {
							puntos = $scope.confirmPuntos[attrs['tipo']][attrs['recursiveWizardConstruct']];
						}

						if(attrs['panelDefault'] === undefined){
							code[attrs['tipo']] = code[attrs['tipo']] || [];

							//code[attrs['tipo']][attrs['index']] = code[attrs['tipo']][attrs['index']] || getPanel(puntos,[],attrs['index'],false,false,false,attrs['voteDelegate']);
							//code[attrs['tipo']][attrs['index']] =  getPanel(puntos,[],attrs['index'],false,false,false,attrs['voteDelegate']);
							var panel = getPanel(puntos,[],attrs['index'],false,false,false,attrs['voteDelegate']);

							ele.html($compile(panel)($scope));
						}
						else{
							var confirm = getPanel(puntos,[],attrs['index'],attrs['confirm'] !== undefined,true,true,attrs['voteDelegate']);
							ele.html($compile(confirm)($scope));
						}
					})();

					//$scope.$watch('puntos',load);
					$rootScope.$on('localizeResourcesUpdated', function(event) {
						if (!event.defaultPrevented) {
							stepsManager.refreshPuntos();
							//load();
							event.preventDefault();
						}
					});

					/*attrs.$observe('voteDelegate', function(){
						load();
					});*/
				}
			}
		}
	]).directive('prgVoto',['stepsManager',
		function(stepsManager) {
			return {
				scope: true,
				link: function($scope,ele) {
					var pgrVal = 0;
					
					$scope.$on('prgBarVoto',function(){
						pgrVal += 100/stepsManager.getStepsCount();
					});
					$scope.pg = function(){
						return pgrVal;
					};
					$scope.$on('prgBarVotoFinish', function() {
						ele.hide();
					});
				}
			}
		   
		}
	]);
}).call(this);