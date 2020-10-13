(function() {
'use strict';
var extend = function(dest,origin){
	 for(var i in origin){
		dest[i] = origin[i];
	 }
};
  
angular.module('app.services.steps', []).factory('stepsManager', [
	'$q','$rootScope','localize','$compile', function($q, $rootScope,localize,$compile) {
	var stepsManager = {
		_pool:              {},
		_wizardSteps:       undefined,
		_loadingSteps:      [],
		_mutexDirectives:   0,
		_element:           undefined,
		_settings: {
			onStepChanging: function(event,currentIndex,newIndex){
				return stepsManager.onStepChanging(event,currentIndex,newIndex);
			},
			onFinished: function(event,currentIndex){
				return stepsManager.onFinished(event,currentIndex);
			},
			labels: {
				cancel:     "<span class='wizardButton wb-cancel  '><span i18n='id00338_app:voto:button:cancel'></span></span>",
				finish:     "<span class='wizardButton wb-finish  '><span i18n='id00339_app:voto:button:finish'></span></span>",
				next:       "<span class='wizardButton wb-next    '><span i18n='id00340_app:voto:button:next'></span></span>",
				previous:   "<span class='wizardButton wb-previous'><span i18n='id00350_app:voto:button:previous'></span></span>"
			}
		},
		_retrieveInstance: function(stepId, stepData) {
			var instance = this._pool[stepId];

			if (instance) {
				instance.setData(stepData);
			} else {
				//instance = new Step(stepData);
				this._pool[stepId] = stepData;
			}

			return instance;
		},
		_search: function(stepId) {
			return this._pool[stepId];
		},
		_getOrderByIndex: function() {
			return _.indexBy(this._pool, 'index');
		},
		getData: function() {
			var data = {};
			for(var key in this._pool){
				if (this._pool[key].data !== undefined && _.keys(this._pool[key].data).length != 0) {
					var d = {};
					var keys = _.keys(this._pool[key].data);
					for (var k in keys)
					{
						d[keys[k]] =  this._pool[key].scope[keys[k]];

					}
					extend(data, d);
				}
				if (this._pool[key].scope.data !== undefined && _.keys(this._pool[key].scope.data).length != 0) {
					var d = {};
					var keys = _.keys(this._pool[key].scope.data);
					for (var k in keys)
					{
						d[keys[k]] =  this._pool[key].scope.data[keys[k]];

					}
					extend(data, d);
				}
			} 
			return data;
		},
		/* Public Methods */
		/* Use this function in order to get a model instance by it's id */
		init: function(ele){
			this._loadingSteps = [];
			//this._loadingSteps.push("uiWizardForm");
			this._element = ele;
			this._mutexDirectives = 0;
                        this._pool = {};
		},
		wait: function() {
			this._mutexDirectives--; 
		},
		signal: function() {
			this._mutexDirectives++; 
		},
		resetMutex: function() {
			this._mutexDirectives = 0;
		},
		getStep: function(stepId) {
			var deferred = $q.defer();
			var model = this._search(stepId);
			if (model) {
				deferred.resolve(model);
			} 
			else{
				deferred.reject();
			}
			return deferred.promise;
		},
		/*  This function is useful when we got somehow the model data and we wish to store it or update the pool and get a model instance in return */
		setStep: function(stepData) {
			var scope = this;
			var step = scope._search(stepData.id);

			if(step){
				step.setData(stepData, stepData.scope);
			} else {
				step = scope._retrieveInstance(stepData.id,stepData);
			}

			this._loadingSteps.push(stepData.id);
			return step;
		},
		stepReady: function(stepId){
			var i = this._loadingSteps.indexOf(stepId);
			$rootScope.$broadcast('prgBarVoto');

			if(i > -1)
				this._loadingSteps.splice(i,1);

			if(this._loadingSteps.length === 0){
				this.stepsFinished();
			}
		},
		directiveFinish: function() {
			if (this._mutexDirectives >= 0) {
				this.putViews();
			}
		},
		stepsFinished: function(){
			stepsManager.applyWizardSteps(this._element);
		},
		setWizardSteps: function(wizardSteps) {
			if (wizardSteps) {
				this._wizardSteps = wizardSteps;
			}
		},
		getWizardSteps: function(wizardSteps) {
			return this._wizardSteps;
		},
		getStepsCount: function() {
			return _.keys(this._pool).length;
		},
		getStepsByPiece: function(piece) {
			var steps = [];
			for(var key in this._pool){
				if(this._pool[key].piece === piece){
					steps.push(this._pool[key]);
				}
			}
			return steps;
		},
		hideStepByPiece: function(piece) {
			var index = 0;
			var current = -1;
			var pool = this._getOrderByIndex();
			for (var key in pool) {
				if (pool[key].piece === piece) {
					pool[key].hideStep(this._wizardSteps, index);
					current = index;
				}
				else if(pool[key].hidden === false) {
					pool[key].currentIndex = index;
					if(current !== -1 && index >= current) {
						pool[key].disableStep(this._wizardSteps);
					}
					index++;
				}
			}
		},
		showStepByPiece: function(piece) {
			var index = 0;
			var current = -1;
			var pool = this._getOrderByIndex();
			for (var key in pool) {
				if (pool[key].piece === piece) {
					pool[key].showStep(this._wizardSteps, index);
					current = index;
					index++;
				}
				else if(pool[key].hidden === false) {
					pool[key].currentIndex = index;
					if(current !== -1 && index >= current) {
						pool[key].disableStep(this._wizardSteps);
					}
					index++;
				}
				
			}
		},
		putViews: function() {
			$rootScope.$broadcast('prgBarVoto');
			for (var key in this._pool) {
				this._search(key).putView()
			}
		},
		applyWizardSteps: function(ele) {
			var th = this;
			this.setWizardSteps(ele.steps(this._settings));

			$(ele).find('.steps').attr('id', 'nav-container');

			for (var key in this._pool) {
				this._search(key).compileWithContent(ele);
			}

			$('.wizardButton').each(function(){
				$(this).html($compile($(this).contents())($rootScope));
			});

			this.compileTitle();
			$rootScope.$broadcast('prgBarVotoFinish');
		},
		compileTitle: function() {
			for(var key in this._pool) {
				this._pool[key].compileTitle();
			}
		},
		onStepChanging: function(event,currentIndex,newIndex){
			var data = this.getData();
			var permissionPre = true, permissionPost = true;
			var keys = _.keys(this._pool);

			if(currentIndex > newIndex){
				keys.reverse();
			}

			for(var key in keys){
				if(!permissionPost || !permissionPre)
					break;
				
				if(this._pool[keys[key]].currentIndex == currentIndex){
					permissionPost = this._pool[keys[key]].onStepChanging(event,currentIndex,newIndex, data);
				}
				if(this._pool[keys[key]].currentIndex == newIndex){
					permissionPre = this._pool[keys[key]].onStepChangingTarget(event,currentIndex,newIndex, data);
				}
			}

			return permissionPost && permissionPre;
		},
		onFinished: function(event,currentIndex){
			var data = this.getData();
			for(var key in this._pool){
				if(this._pool[key].currentIndex == currentIndex){
					this._pool[key].onFinished(event,currentIndex, data);
				}
			}
		},
		refreshPuntos: function() {
			for(var key in this._pool){
				this._pool[key].refreshPuntos();
			}
		}
	};
	return stepsManager;
	
}]).factory('Step', ['stepsManager','viewsCacheService', "$compile","localize",
function(stepsManager,viewsCacheService, $compile,localize) {
	function Step(stepData,$scope){
		if (stepData) {
			if (stepData.id === undefined)
			{
				//throw
			}
			this.setData(stepData,$scope);
		}
		// Some other initializations
	}
	Step.prototype = {
		id:           undefined,
		index:        undefined,
		currentIndex: undefined,
		hidden:       undefined,
		piece:        undefined,
		path:         undefined,
		scope:        undefined,
		view:         undefined,
		content:      undefined,
		tab:          undefined,
		type:         undefined,
		title:        undefined,
		title_body:   undefined,
		subtitle_body:undefined,
		body:         undefined,
		data:         undefined,
		step:         undefined,

		setData: function(stepData,$scope) {
			extend(this, stepData);
			this._setScope($scope);
			this.hidden = false;
		},
		delete: function() {
			
		},
		update: function() {
			
		},
		_prepare: function(ele,title,stepId,piece,html){
			var h = $($(html).html());
						
			//h.find('.' + piece + 'Title').text(localize.getLocalizedString(title));
			//h.find('.' + piece + 'Title').html($compile('<span i18n="'+title+'"></span>')(this.scope));
			//if (title !== undefined && title !== '') h.find('.' + piece + 'Title').attr('i18n', title);
			this._setTitle(h, piece, title);
			h.find('.' + piece + 'Content').attr('id',piece + 'Content-'+stepId);
			h.appendTo(ele);
			ele.contents().hide();
		},
		_setTitle: function(h, piece, title) {
				if (title !== undefined && title !== '') h.find('.' + piece + 'Title').attr('i18n', title);
		},
		_setScope: function(scope) {
			this.scope = scope;

			this.scope.title_body = this.title_body;
						this.scope.subtitle_body = this.subtitle_body;
			this.scope.body  = this.body;

			this.data = this.data || {};
			for(var key in this.data){
				this.scope[key] = this.data[key];
			}
		},
		_setIndex: function() {
			var id = this.content.parent().attr("id");
			this.index = id.substr(id.lastIndexOf("-")+1);
			this.currentIndex = this.index;
		},
		_setStep: function(ele) {
			this.step = ele.steps('getStep',this.index);
		},
		_compile: function() {
			$compile(this.content)(this.scope);
		},
		_setContent: function(ele) {
			this.content = ele.find('#' + this.piece + 'Content-'+this.id);  
		},
		_setTab: function(ele) {
			var indx = this.currentIndex === undefined ? this.index : this.currentIndex;
			this.tab = $(ele.find("li[role='tab']")[indx]);  
		},
		compileTitle: function() {
			var menu_title = $('ul[role="tablist"]').find('.' + this.piece + 'Title');
			menu_title.html($compile(menu_title)(this.scope));
		},
		putView: function() {  
			var scope = this;
			viewsCacheService.get(this.path).then(function(html){
				scope._prepare(scope.view,scope.title,scope.id,scope.piece,html);
				scope.view.replaceWith(scope.view.contents());
				stepsManager.stepReady(scope.id);
			}).catch(function(error){
				
			});
		},
		compileWithContent: function(ele) {
		  this._setContent(ele);
		  this._compile();
		  this._setIndex();
		  this._setStep(ele);
		  this._setTab(ele);
		},
		setTab: function(ele) {
			this._setTab(ele);  
		},
		showStep: function(wizardSteps, index) {
			var indx = parseInt(this.index) > index ? index : parseInt(this.index);
			if (this.hidden === true)
			{
				this.hidden = false;
				wizardSteps.steps('insert',indx, this.step);
				this._setContent(wizardSteps);
				this._setTitle(wizardSteps, this.piece, this.title);
				this.compileTitle();
				this._compile();
			}
			this.currentIndex = indx;
		},
		hideStep: function(wizardSteps, index) {
			if (this.hidden === false)
			{
				this.hidden = true;
				wizardSteps.steps('remove',parseInt(index));
				this.currentIndex = -1;
			}
		},
		disableStep: function(ele) {
			this._setTab(ele);
			this.tab.removeClass('done').addClass('disabled');
			this.tab.removeAttr('aria-selected');
			this.tab.attr('aria-disabled','true');
			this._setTitle(ele, this.piece, this.title);
			this.compileTitle();
		},
		enableStep: function(ele) {
			this._setTab(ele);
			this.tab.removeClass('disabled').addClass('done');
			this.tab.attr('aria-selected', "false");
			this.tab.attr('aria-disabled','false');
		},
		onStepChangingTarget: function(event,currentIndex,nextIndex, data){
			if(currentIndex < nextIndex){
				if(this.scope.onStepChangingTargetNext === undefined){
					return true;
				}
				return this.scope.onStepChangingTargetNext(this.piece, data);
			}
			if(currentIndex > nextIndex){
				if(this.scope.onStepChangingTargetPrevious === undefined){
					return true;
				}
				return this.scope.onStepChangingTargetPrevious(this.piece, data);
			}
		},
		onStepChanging: function(event,currentIndex,nextIndex, data){
			if(currentIndex < nextIndex){
				if(this.scope.onStepChangingNext === undefined){
					return true;
				}
				return this.scope.onStepChangingNext(this.piece, data);
			}
			if(currentIndex > nextIndex){
				if(this.scope.onStepChangingPrevious === undefined){
					return true;
				}
				return this.scope.onStepChangingPrevious(this.piece, data);
			}
		},
		onFinished: function(event,currentIndex, data){
			if(this.scope.onFinished === undefined)
				return true;
			
			this.scope.onFinished(data);
		},
		refreshPuntos: function() {
			
		}
	};
	return Step;
}]).factory('ArbitraryStep', ['Step',  
function(Step) {
	function ArbitraryStep(stepData,$scope){
		extend(this, Step.prototype);
		extend(this, this.__proto__);
		if (stepData) {
			this.setData(stepData,$scope);
		}
	}
	ArbitraryStep.prototype = {
	};
	return ArbitraryStep;
	
}]).factory('SharesStep', ['Step', 
function(Step) {
	function SharesStep(stepData,$scope){
		extend(this, Step.prototype);
		extend(this, this.__proto__);
		if (stepData) {
			this.setData(stepData,$scope);
		}
		// Some other initializations
	}
	SharesStep.prototype = {
	};
	return SharesStep;
	
}]).factory('VoteDelegateStep', ['Step', 
function(Step) {
	function VoteDelegateStep(stepData,$scope){
		extend(this, Step.prototype);
		extend(this, this.__proto__);
		if (stepData) {
			this.setData(stepData,$scope);
		}
		// Some other initializations
	}
	VoteDelegateStep.prototype = {
	};
	return VoteDelegateStep;
	
}]).factory('IntentionDelegationVoteStep', ['Step', 
function(Step) {
	function IntentionDelegationVoteStep(stepData,$scope){
		extend(this, Step.prototype);
		extend(this, this.__proto__);
		if (stepData) {
			this.setData(stepData,$scope);
		}
		// Some other initializations
	}
	IntentionDelegationVoteStep.prototype = {
	};
	return IntentionDelegationVoteStep;
	
}]).factory('VoteStep', ['Step', '$compile',"smallWizardPersistence","$http","stepsManager","$timeout",
function(Step, $compile,smallWizardPersistence,$http,stepsManager,$timeout) {
	function VoteStep(stepData,$scope){
		extend(this, Step.prototype);
		extend(this, this.__proto__);
		if (stepData) {
			this.setData(stepData,$scope);
		}
		// Some other initializations
	}
	VoteStep.prototype = {
		voteType:   undefined,
		smContent:  undefined,

		_getPuntos: function(){
			if(this.data.puntos === undefined){
				this._postPuntos();
			}
		},
		_postPuntos: function() {
			var th = this;

			var sortByOrder = function(stepsArray){
				stepsArray.sort(function(a,b){
					return a['orden'] - b['orden'];
				});
			};

			var replaceText = function(puntos,replacement){
				sortByOrder(replacement);
				sortByOrder(puntos);

				var replaceText = function(punto,replacement){
					punto.text = replacement.text;

					for(var i in replacement.grupos_o_v.opciones_voto){
						punto.grupos_o_v.opciones_voto[i].nombre = replacement.grupos_o_v.opciones_voto[i].nombre;
					}

					if(punto.subpuntos.length > 0){
						sortByOrder(punto.subpuntos);
						sortByOrder(replacement.subpuntos);
						
						for(var i in punto.subpuntos){
							replaceText(punto.subpuntos[i],replacement.subpuntos[i])
						}
					}
				}

				for(var i in puntos){
					replaceText(puntos[i],replacement[i]);
				}
			}

			$http.get(ApiBase+'/puntos/'+this.data.type.id+'/tipo').success(function(response){
				if(th.data.puntos != undefined){
					replaceText(th.data.puntos,response.puntos);
				} else {
					th.data.puntos = response.puntos;
				}
				if(th.scope.puntos != undefined){
					replaceText(th.scope.puntos,response.puntos);
				} else {
					th.scope.puntos = response.puntos;
				}
			}).catch(function(){
				console.error('E. puntos');
			});

			$http.get(ApiBase + '/absadicionals/' + this.data.type.id + '/tipo').success(function(response){
				if(response.error === undefined){
					if(th.scope.abs_adicionals != undefined){
                                                for(var j in th.scope.abs_adicionals){
                                                    th.scope.abs_adicionals[j].text = response[j].text;
                                                    for(var i in th.scope.abs_adicionals[j].grupos_o_v.opciones_voto){
                                                            th.scope.abs_adicionals[j].grupos_o_v.opciones_voto[i].nombre = response[j].grupos_o_v.opciones_voto[i].nombre;
                                                    }
                                                }
					}
					else{
						th.scope.abs_adicionals = response;
					}
					if(th.data.abs_adicionals != undefined){
                                                for(var j in th.scope.abs_adicionals){
                                                    th.data.abs_adicionals[j].text = response[j].text;
                                                    for(var i in th.scope.abs_adicionals[j].grupos_o_v.opciones_voto){
                                                            th.data.abs_adicionals[j].grupos_o_v.opciones_voto[i].nombre = response[j].grupos_o_v.opciones_voto[i].nombre;
                                                    }
                                                }
					}
					else{
						th.data.abs_adicionals  = response;	
					}
				} else if(response.error !== 'No Item'){
					console.error('E. abs_ad');
				}
			}).catch(function(){
				console.error('E. abs_ad');
			});
		},
		_prepare: function(ele,title,stepId,piece,html){
			var h = $($(html).html());
					   
			//h.find("#wizard-title").text(title);
			//var title_element = h.find("#wizard-title");
			//title_element.attr('id', 'wizard-title-'+this.voteType.tipo);
			h.find("#wizard-container").attr("id","sm-"+this.voteType.tipo);
			h.find("#sm-wizard-container").attr("id","sm-wizard-"+this.voteType.tipo);
			this._setTitle(h, piece, title);
			//if (title !== undefined && title !== '') title_element.attr('i18n', title);
						
			h.appendTo(ele);
			ele.contents().hide();
		},
		_compile: function() {
			var th = this;
			var wizard;
			var sw = this.content.find("#sm-wizard-"+this.voteType.tipo);

			var getWizardAndInit;
			(getWizardAndInit = function(){
				smallWizardPersistence.getHtmlCache('views/app/voto/juntas/VotoContent.html').then(function(htmlCache){
					initWizard(htmlCache);
				});
			})();

			var initWizard = function(htmlCache){
				wizard = $(htmlCache);
				
				th.scope.$on('ngRepeatFinished[stepsReady-'+ th.voteType['tipo'] +']',function(){
					wizard = wizard.smartWizard(
					{
						transitionEffect: 'fade', 
						enableFinishButton: true,
						labelNext:     '<span class="smartWizardButton"><span i18n="id00340_app:voto:button:next"></span></span>',
						labelPrevious: '<span class="smartWizardButton"><span i18n="id00350_app:voto:button:previous"></span></span>',
						/*labelFinish:   '<span class="smartWizardButton"><span i18n="id00400_home:voto:button:confirm"></span></span>',*/
                                                labelFinish:   '<span class="smartWizardButton"><span i18n="id00514_home:av:button:confirm"></span></span>',
						onShowStep: function(){
							$(".content.clearfix").scrollTop($("#votoPanel").offset().top + $(".content.clearfix").scrollTop() - $('header').height() - 10);
							return true;
						},
						onFinish: function(){
							stepsManager.getWizardSteps().steps("next");
						}
					});

					$('.smartWizardButton').each(function(){
						$(this).html($compile($(this).contents())(th.scope));
					});

					/*$(".smartWizardButton").parent().click(function(){
						$(".content.clearfix").scrollTop($("#votoPanel").offset().top);

					});*/
				});

				sw.html(wizard);
				th._getPuntos();
				$compile(th.content)(th.scope);

			};
		},
		_setContent: function(ele) {
			this.content = ele.find("#sm-"+this.voteType.tipo);
		},
		_setTitle: function(h, piece, title) {
			var title_element = h.find("#wizard-title");
			title_element.attr('id', 'wizard-title-'+this.voteType.tipo);
			if (title !== undefined && title !== '') title_element.attr('i18n', title);
		},
		_updateType: function(){
			var th = this;
			$http.get(ApiBase+'/tipovotos/'+th.voteType.id).success(function(response){
				th.voteType = response.tipoVotos;
				th.scope.type= response.tipoVotos;
			});
		},
		compileTitle: function() {
			var menu_title = $('ul[role="tablist"]').find("#wizard-title-"+this.voteType.tipo);
			menu_title.html($compile(menu_title)(this.scope));
		},
		getPuntos: function(){
			return this.scope.puntos;
		},
		getAbsAdicional: function(){
			return this.scope.abs_adicionals;
		},
		refreshPuntos: function() {
			this._postPuntos();
			this._updateType();
		}
	};
	return VoteStep;
	
}]).factory('VoteDelegationStep',['Step',
function(Step) {
	function VoteDelegationStep(stepData,$scope){
		extend(this, Step.prototype);
		extend(this, this.__proto__);
		if (stepData) {
			this.setData(stepData,$scope);
		}
		// Some other initializations
	}
	VoteDelegationStep.prototype = {
	};
	return VoteDelegationStep;
	
}]).factory('DelegationStep',['Step', 
function(Step) {
	function DelegationStep(stepData,$scope){
		extend(this, Step.prototype);
		extend(this, this.__proto__);
		if (stepData) {
			this.setData(stepData,$scope);
		}
		// Some other initializations
	}
	DelegationStep.prototype = {
	};
	return DelegationStep;
	
}]).factory('InstructionsStep',['Step',
function(Step) {
	function InstructionsStep(stepData,$scope){
		extend(this, Step.prototype);
		extend(this, this.__proto__);
		if (stepData) {
			this.setData(stepData,$scope);
		}
		// Some other initializations
	}
	InstructionsStep.prototype = {
	};
	return InstructionsStep;
	
}]).factory('ConfirmStep', ['Step',  
function(Step) {
	function ConfirmStep(stepData,$scope){
		extend(this, Step.prototype);
		extend(this, this.__proto__);
		if (stepData) {
			this.setData(stepData,$scope);
		}
	}
	ConfirmStep.prototype = {
	};
	return ConfirmStep;
	
}]).factory('EndStep', ['Step',  
function(Step) {
	function EndStep(stepData,$scope){
		extend(this, Step.prototype);
		extend(this, this.__proto__);
		if (stepData) {
			this.setData(stepData,$scope);
		}
	}
	EndStep.prototype = {
	};
	return EndStep;
}]);

}).call(this);