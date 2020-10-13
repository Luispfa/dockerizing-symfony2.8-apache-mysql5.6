(function() {
	'use strict';
	angular.module('homeControllers', ['vcRecaptcha', 'angularFileUpload'
	]).controller('MainEnabled', [
	'$scope','$localStorage', function($scope, $localStorage) {
		$scope.isCertEnabled = function() {
			return $localStorage['Config.certificate.enable'] === '1';
		};
		$scope.isuserpassEnabled = function() {
			return $localStorage['Config.userpass.enable'] === '1';
		};
		$scope.isAppEnabled = function() {
			return $localStorage['Platform.time.activate'];
		};
	}
  ]).controller('cookiesCtrl',[
	"$scope","$localStorage",
		function($scope,$localStorage){
			$scope.showCookiesMessage = function(){
				if($localStorage['cookies_message'] !== '1' && $localStorage['Config.cookies.message'] === '1'){
					$('body').addClass('cookies-view');
					return true;
				}
				return false;
			}

			if(!$scope.showCookiesMessage()){
				$('body').removeClass('cookies-view');
			}

			$scope.accept = function(){ 
				$localStorage['cookies_message'] = '1';
				$('body').removeClass('cookies-view');
			};
		}
  ]).controller('forgotCtrl', [
	'$scope', '$http', 'logger','$location', function($scope,$http,logger,$location){
		$scope.mail = '';
		$scope.submit = function() {
			//#TODO
			$http.post(ApiBase + '/publics/forgotmails', {'mail':$scope.mail}).success(function(response){
				if (response.success === 1 )
				{
					$location.path("/app/inicio")
					logger.logSuccess("id00210_app:logger:forgot-success");
				}
				else
				{
					$location.path("/app/inicio")
					logger.logSuccess("id00210_app:logger:forgot-success");
				}
			}).catch(function(){
				logger.logError("id00201_app:logger:server-error");
			});
		}
	}
  ]).controller('FormSubCtrl', [
	'$scope', '$http', 'logger', 'vcRecaptchaService', function($scope, $http, logger, vcRecaptchaService) {
            
                $scope.model = {
                                //key: '6LeaivUSAAAAACc-MpRhDk5UW07UT0hr8qXhvKuT' //v1
                                key: '6Ld6ckIUAAAAAO8X7Rq3yml9G9vbv0pvCbYbhma7' //invisible
                                //key: '6LepTEMUAAAAAK9HoJq48SCzyYOXKEVjjT1R8Vuz' //v2
			};
                $scope.reCaptcha = "";
            
		$scope.submit = function() {
     
                    if($scope.reCaptcha != null && $scope.reCaptcha != ''){

                        var dataObject = "webservice=1&_username=" + 
                                        encodeURIComponent(this.username) + 
                                        "&_password=" +
                                        encodeURIComponent(this.password);
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
                                        window.location.href = WebDefault + '/';
                                }
                                else
                                {
                                        return logger.logError("id00210_app:logger:wrong-login");
                                }
                          });
                  }
                  else{
                      return logger.logError("id00182_app:validation:recaptcha");
                  }
		};
	}
  ]).controller('CertificateLoginCtrl', [
	'$scope', '$http', 'logger','$modal','$location', '$localStorage', 'resetLocalStorage', 
	function($scope, $http, logger, $modal, $location, $localStorage, resetLocalStorage) {
		$scope.cerlogin = function() {
			var modalInstance;
			modalInstance = $modal.open({
				templateUrl: "myModalContent.html",
				backdrop: 'static'
			});
			var funcget = function(retry) {
				$http({
					url:       WebDefault + "/home/certificateLogin",
					method:    "GET",
					withCredentials: true,
					timeout: 45000
				}).success(function(response) {
					   if (response.success === true)
					   {
						   window.location.href = WebDefault + '/';
					   }
					   else if (response.status === 'VALID')
					   {
						   resetLocalStorage.reset(response, $localStorage);
						   $localStorage.$default(response);
						   $location.path("/app/registroCertificado")
						   modalInstance.dismiss('Valid Certificate')
					   }
					   else
					   {
						   modalInstance.dismiss('Invalid Certificate')
						   return logger.logError("id00211_app:logger:wrong-certificate");
					   }
				}).error(function(data, status, headers, config){
						if (retry<3)
						{
							retry++;
							funcget(retry);
							return;
						}
						modalInstance.dismiss('Error Certificate')
					   return logger.logError("id00212_app:logger:certificate-error");
				});
			}
			funcget(0);
			 
		};
	}
  ]).controller('registroCertificateFormCtrl',[
		'$scope','$http','anonymousToken','$location','logger','localize','documentValidation','$localStorage','jsUtils','validateAccionista','$timeout','$anchorScroll', 'resetLocalStorage','uploaderProvider','$modal',
		function($scope,$http,anonymousToken,$location,logger,localize,documentValidation, $localStorage, jsUtils, validateAccionista,$timeout,$anchorScroll, resetLocalStorage,uploaderProvider,$modal){
			var modalInstance;
			
			if ($localStorage.dni === undefined)
			{
				$location.path("/app/inicio");
			}

			var uploaderA = uploaderProvider.get("documentacionAUploader");
			var uploaderB = uploaderProvider.get("documentacionBUploader");

			$scope.showAlertsTop = $localStorage['Config.register.alertsTop'] == 1;
			$scope.showAlertsField=$localStorage['Config.register.alertsField'] == 1;
			$scope.accionesMin = $localStorage['Config.accionista.accionesMin'] === undefined ? 0 : $localStorage['Config.accionista.accionesMin'];
			$scope.i18n        = localize.getLocalizedString;
			$scope.token       = anonymousToken.getToken();
			$scope.emailc      = "";
			$scope.errors      = [];
			$scope.docTypes = {
				nif: "id00155_app:doctype:nif",
				cif: "id00156_app:doctype:cif",
				nie: "id00157_app:doctype:nie",
				otros: "id00158_app:doctype:other"
			};

			$scope.newAccionista = {
				accionista: {
					personaType:    documentValidation.typeDoc($localStorage.dni) == 'cif' ? 'pj' : 'pf',
					documentType:   documentValidation.typeDoc($localStorage.dni),
					documentNum:    $localStorage.dni,
					sharesNum:      0,
					representedBy:  "",
					name:           $localStorage.name,
					telephone:      '',
				},
				user: {
					usernmae:   '',
					email:      "",
					token:      $scope.token
				},
				LOPD: false
			};
			var data = {
				dni:        $localStorage.dni,
				name:       $localStorage.name,
				issuer:     $localStorage.issuer,
				clientCert: $localStorage.clientCert
			}
			resetLocalStorage.reset(data, $localStorage);

			var checkErrors = function(){
				$scope.errors = [];
				$scope.validation = validateAccionista.validate($scope.newAccionista,!$scope.form.$error.equal,uploaderA.queue.length,uploaderB.queue.length,true);

				var error = false;
				for(var index in $scope.validation){
					error = true;
					$scope.validation[index] = $scope.i18n($scope.validation[index]);
					$scope.errors.push({
						type: 'warning',
						msg:  $scope.validation[index]
					});
				}
                                if(!error){
                                    error = jsUtils.isValidMailDomain();
                                }

				if(error){
					$timeout(function(){
						$location.hash('topForm');
						$anchorScroll();
					});
				}



				return !error;
			};

			$scope.closeAlert = function(index){
				$scope.errors.splice(index,1);
			};

			$scope.submit = function(form){
				if(checkErrors()){
					$scope.newAccionista.accionista.sharesNum = jsUtils.cleanNumber($scope.newAccionista.accionista.sharesNum);
					modalInstance = $modal.open({
						templateUrl: "myModalContent.html",
						backdrop: 'static'
					});
					$http.post(ApiBase + '/publics/accionistacertificates', $scope.newAccionista)
					.success(function(data) {
						if(typeof data.success !== 'undefined'){
							window.location.href = WebDefault + '/';
							modalInstance.dismiss('Valid');
						}
						else if(data.error !== undefined)
						{
							modalInstance.dismiss('Error');
							logger.logError(data.error);
						}
					})
					.error(function() {
						modalInstance.dismiss('Error');
					});
				}
			}
	}
	 ]).controller('registroFormCtrl',[
		'$scope','vcRecaptchaService','$http','anonymousToken','$location','logger','localize','documentValidation','jsUtils','validateAccionista','$anchorScroll','$timeout','uploaderProvider','$localStorage','$modal',
		function($scope,vcRecaptchaService,$http,anonymousToken,$location,logger,localize,documentValidation,jsUtils,validateAccionista, $anchorScroll,$timeout,uploaderProvider,$localStorage, $modal){
			var modalInstance;
			$scope.model = {
				//key: '6LeaivUSAAAAACc-MpRhDk5UW07UT0hr8qXhvKuT' //v1
                                key: '6Ld6ckIUAAAAAO8X7Rq3yml9G9vbv0pvCbYbhma7' //invisible
                                //key: '6LepTEMUAAAAAK9HoJq48SCzyYOXKEVjjT1R8Vuz' //v2
			};

			var uploaderA = uploaderProvider.get("documentacionAUploader");
			var uploaderB = uploaderProvider.get("documentacionBUploader");

			$scope.showAlertsTop = $localStorage['Config.register.alertsTop'] == 1;
			$scope.showAlertsField=$localStorage['Config.register.alertsField'] == 1;
			$scope.accionesMin = $localStorage['Config.accionista.accionesMin'] === undefined ? 0 : $localStorage['Config.accionista.accionesMin'];
			$scope.i18n        = localize.getLocalizedString;
			$scope.token       = anonymousToken.getToken();
			$scope.emailc      = "";
			$scope.errors      = [];
                        $scope.reCaptcha   = "";
			
			/*$scope.updateLang = function(){
				return $scope.lang = localize.getLanguage();
			}*/

			$scope.docTypes = {
				nif: "id00155_app:doctype:nif",
				cif: "id00156_app:doctype:cif",
				nie: "id00157_app:doctype:nie",
				otros: "id00158_app:doctype:other"
			};

			$scope.newAccionista = {
				accionista: {
					name: '',
					personaType:    "pf",
					documentType:   "nif",
					documentNum:    "",
					sharesNum:      0,
					representedBy:  "",
					telephone:      "",
				},
				user: {
					username:   '',
					email:      "",
					token:      $scope.token
				},
				LOPD: false,
                                reCaptcha: ""
			};

			var checkErrors = function(){
				$scope.errors = [];
				$scope.validation = validateAccionista.validate($scope.newAccionista,!$scope.form.$error.equal,uploaderA.queue.length,uploaderB.queue.length);

				var error = false;
				for(var index in $scope.validation){
					error = true;
					$scope.validation[index] = $scope.i18n($scope.validation[index]);
					$scope.errors.push({
						type: 'warning',
						msg:  $scope.validation[index]
					});
				}
                                
                                if(!error){

                                    error = !jsUtils.isValidMailDomain($scope.newAccionista.user.email);
                                    $scope.errors.push({
						type: 'warning',
						msg:  $scope.i18n("id00180_app:validation:email")
					});
                                }

				if(error){
					$timeout(function(){
						$location.hash('topForm');
						$anchorScroll();
					});
				}

				return !error;
			};

			$scope.closeAlert = function(index){
				$scope.errors.splice(index,1);
			};

			$scope.submit = function(form){

                                //console.log($scope.newAccionista.reCaptcha);

				if(checkErrors()){
					$scope.newAccionista.accionista.sharesNum = jsUtils.cleanNumber($scope.newAccionista.accionista.sharesNum);
					modalInstance = $modal.open({
						templateUrl: "myModalContent.html",
						backdrop: 'static'
					});
					$http.post(ApiBase + '/publics/accionistas', $scope.newAccionista)
					.success(function(data) {
						if(typeof data.success !== 'undefined'){
							window.location.href = WebDefault + '/';
							modalInstance.dismiss('Valid');
						}
						else if(data.error === 'reCaptcha'){
							modalInstance.dismiss('Error');
							$scope.errors.push({
								type: 'warning',
								msg:  $scope.i18n("id00182_app:validation:recaptcha")
							});
							logger.logError(data.error);
							vcRecaptchaService.reload();
						}
						else if(data.error !== undefined)
						{
							modalInstance.dismiss('Error');
							logger.logError(data.error);
							vcRecaptchaService.reload();
						}
					})
					.error(function() {
						modalInstance.dismiss('Error');
						vcRecaptchaService.reload();
					});
				}
			}
		}
	]).controller('registroRaccFormCtrl',[
		'$scope','vcRecaptchaService','$http','anonymousToken','$location','logger','localize','documentValidation','jsUtils','validateAccionista','$anchorScroll','$timeout','uploaderProvider','$localStorage','$modal',
		function($scope,vcRecaptchaService,$http,anonymousToken,$location,logger,localize,documentValidation,jsUtils,validateAccionista, $anchorScroll,$timeout,uploaderProvider,$localStorage, $modal){
			var modalInstance;
			$scope.model = {
                                key: '6Ld6ckIUAAAAAO8X7Rq3yml9G9vbv0pvCbYbhma7' //invisible
			};

			
                        var uploaderA = uploaderProvider.get("documentacionAUploader");
			var uploaderB = uploaderProvider.get("documentacionBUploader");
                        

			$scope.showAlertsTop = $localStorage['Config.register.alertsTop'] == 1;
			$scope.showAlertsField=$localStorage['Config.register.alertsField'] == 1;
			$scope.accionesMin = $localStorage['Config.accionista.accionesMin'] === undefined ? 0 : $localStorage['Config.accionista.accionesMin'];
			$scope.i18n        = localize.getLocalizedString;
			$scope.token       = anonymousToken.getToken();
			$scope.emailc      = "";
			$scope.errors      = [];
                        $scope.reCaptcha   = "";
			
			
			$scope.docTypes = {
				nif: "id00155_app:doctype:nif",
				cif: "id00156_app:doctype:cif",
				nie: "id00157_app:doctype:nie",
				otros: "id00158_app:doctype:other"
			};
                        

			$scope.newAccionista = {
				accionista: {
					name: '',
					personaType:    "pf",
					documentType:   "nif",
					documentNum:    "",
					sharesNum:      0,
					representedBy:  "",
					telephone:      "",
				},
				user: {
					username:   '',
					email:      "",
					token:      $scope.token
				},
				LOPD: false,
                                reCaptcha: ""
			};

			var checkErrors = function(){
				$scope.errors = [];
				$scope.validation = validateAccionista.validateRacc($scope.newAccionista,!$scope.form.$error.equal,uploaderA.queue.length,uploaderB.queue.length);

				var error = false;
				for(var index in $scope.validation){
					error = true;
					$scope.validation[index] = $scope.i18n($scope.validation[index]);
					$scope.errors.push({
						type: 'warning',
						msg:  $scope.validation[index]
					});
				}
                                
                                if(!error){

                                    error = !jsUtils.isValidMailDomain($scope.newAccionista.user.email);
                                    $scope.errors.push({
						type: 'warning',
						msg:  $scope.i18n("id00180_app:validation:email")
					});
                                }

				if(error){
					$timeout(function(){
						$location.hash('topForm');
						$anchorScroll();
					});
				}

				return !error;
			};

			$scope.closeAlert = function(index){
				$scope.errors.splice(index,1);
			};

			$scope.submit = function(form){

                                //console.log($scope.newAccionista.reCaptcha);

				if(checkErrors()){
					$scope.newAccionista.accionista.sharesNum = jsUtils.cleanNumber($scope.newAccionista.accionista.sharesNum);
					modalInstance = $modal.open({
						templateUrl: "myModalContent.html",
						backdrop: 'static'
					});
					$http.post(ApiBase + '/publics/accionistas', $scope.newAccionista)
					.success(function(data) {
						if(typeof data.success !== 'undefined'){
							window.location.href = WebDefault + '/';
							modalInstance.dismiss('Valid');
						}
						else if(data.error === 'reCaptcha'){
							modalInstance.dismiss('Error');
							$scope.errors.push({
								type: 'warning',
								msg:  $scope.i18n("id00182_app:validation:recaptcha")
							});
							logger.logError(data.error);
							vcRecaptchaService.reload();
						}
						else if(data.error !== undefined)
						{
							modalInstance.dismiss('Error');
							logger.logError(data.error);
							vcRecaptchaService.reload();
						}
					})
					.error(function() {
						modalInstance.dismiss('Error');
						vcRecaptchaService.reload();
					});
				}
			}
		}
	]).controller("contactCtrl",[
		"$scope","$http","logger","$location",'vcRecaptchaService','localize', '$localStorage' ,
				function($scope,$http,logger,$location, vcRecaptchaService, localize, $localStorage){
                           
                                    
                        $scope.i18n        = localize.getLocalizedString;            
                                    
                        $scope.isAppEnabled = function() {
                            return $localStorage['Platform.time.activate'];
                        };
                                    
			$scope.mail = {
				name: "",
				from: "",
				body: "",
				subject: "",
                                recaptcha: ""
			};
                        		
			$scope.model = {
				//key: '6LeaivUSAAAAACc-MpRhDk5UW07UT0hr8qXhvKuT' //v1
                                key: '6Ld6ckIUAAAAAO8X7Rq3yml9G9vbv0pvCbYbhma7' //invisible
                                //key: '6LepTEMUAAAAAK9HoJq48SCzyYOXKEVjjT1R8Vuz' //v2
			};

			$scope.loading = false;
						
			/*$scope.updateLang = function(){
				return $scope.lang = localize.getLanguage();
			}*/
                                    
                        /*var acuseDeRecibo = function(acuse){

                            $http.post(ApiBase + '/mails/toemailusers',acuse)
                            
                                    .success(function(data){
                                        
                                        if(typeof data.success !== 'undefined'){
                                            //logger.logSuccess("id00062_app:logger:success-email");
                                            }
                                            else if(data.error !== undefined)
                                            {
                                                    $scope.loading = false;
                                                    //logger.logError(data.error);
                                                    vcRecaptchaService.reload();
                                            }
                                    })
                                    .error(function(){
                                            //logger.logError("id00061_app:logger:error-retry");
                                    });
                            
                        }*/

			$scope.submit = function(mail){
                            
				$scope.loading = true;
                                $scope.mail.reCaptcha = mail.recaptcha;
                                mail.body = "From: "+ mail.from + ".   " + mail.body;
                                

                                //correo a los administradores
				$http.post(ApiBase + '/publics/toadmins',mail)
					.success(function(data){
                                            if(typeof data.success !== 'undefined'){
                                                logger.logSuccess("id00062_app:logger:success-email");
						$location.path("");
                                                /*var acuse = {
                                                        to: [mail.from],
                                                        body: $scope.i18n("id00489_app:contact:acuse-body"),
                                                        subject: $scope.i18n("id00488_app:contact:acuse-subject")
                                                    };
                                                
                                                acuseDeRecibo(acuse);*/
                                                
                                                }
                                                else if(data.error === 'reCaptcha'){
                                                        $scope.loading = false;
                                                        logger.logError(data.error);
                                                        vcRecaptchaService.reload();
                                                }
                                                else if(data.error !== undefined)
                                                {
                                                        $scope.loading = false;
                                                        logger.logError(data.error);
                                                        vcRecaptchaService.reload();
                                                }
					})
					.error(function(){
						$scope.loading = false;
						logger.logError("id00061_app:logger:error-retry");
					});
                                        
			};
                        
		}
	]);

}).call(this);
