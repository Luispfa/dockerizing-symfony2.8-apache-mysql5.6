(function () {
    'use strict';
    angular.module('sharedServices', [
    ]).config(["$provide", function ($provide) {
            $provide.decorator('logger', function ($delegate, EventLogger) {
                return EventLogger($delegate);
            });
        }]).factory('EventLogger', ['localize', function (localize) {
            return function ($delegate) {
                return {
                    log: function (tag) {
                        tag = tag.toLowerCase();
                        $delegate.log(localize.getLocalizedString(tag));
                    },
                    logWarning: function (tag, noTranslate) {
                        if (noTranslate) {
                            $delegate.logWarning(tag);
                            return;
                        }
                        tag = tag.toLowerCase();
                        $delegate.logWarning(localize.getLocalizedString(tag));
                    },
                    logSuccess: function (tag, noTranslate) {
                        if (noTranslate) {
                            $delegate.logSuccess(tag);
                            return;
                        }
                        tag = tag.toLowerCase();
                        $delegate.logSuccess(localize.getLocalizedString(tag));
                    },
                    logError: function (tag, noTranslate) {
                        if (noTranslate) {
                            $delegate.logError(tag);
                            return;
                        }
                        tag = tag.toLowerCase();
                        $delegate.logError(localize.getLocalizedString(tag));
                    },
                    logUpper: function (tag, noTranslate) {
                        if (noTranslate) {
                            $delegate.logUpper(tag);
                            return;
                        }
                        tag = tag.toLowerCase();
                        $delegate.logUpper(localize.getLocalizedString(tag));
                    },
                    logUpperSuccess: function (tag, noTranslate) {
                        if (noTranslate) {
                            $delegate.logUpperSuccess(tag);
                            return;
                        }
                        tag = tag.toLowerCase();
                        $delegate.logUpperSuccess(localize.getLocalizedString(tag));
                    }
                }
            }
        }]).service('paramRequest', [
        '$http', '$q', '$localStorage', function ($http, $q, $localStorage) {
            var t = this;
            function reset(apprequest) {
                angular.forEach(apprequest, function (value, key) {
                    delete $localStorage[key];
                });
            }
            ;

            this.load = function (force) {
                var deferred = $q.defer();

                if (force ||
                        $localStorage['Config.certificate.enable'] === undefined &&
                        $localStorage['Config.userpass.enable'] === undefined)
                {
                    $http.get(WebDefault + '/home/paramrequest')
                            .success(function (paramrequest) {
                                var params = {};
                                angular.forEach(paramrequest, function (value, key) {
                                    params[value['key_param']] = value['value_param'];
                                });
                                reset(params);
                                $localStorage.$default(params);
                                deferred.resolve(params);
                            }
                            ).error(function (error) {
                        deferred.reject();
                    });
                } else {
                    deferred.resolve($localStorage);
                }

                return deferred.promise;
            };

            this.get = function (param) {
                var deferred = $q.defer();
                t.load().then(function () {
                    deferred.resolve($localStorage[param]);
                });
                return deferred.promise;
            };
        }
    ]).factory('assocArray', [
        function () {
            var assocArray = function () {
                this.keys = [];
            };

            assocArray.prototype.push = function (key, value) {
                key = key || "";
                value = value || "";

                if (typeof key == "object") {
                    for (var k in key) {
                        this[k] = key[k];
                        this.keys.push(k);
                    }
                    return this;
                }

                this[key] = value;
                this.keys.push(key);
                return this;
            };

            assocArray.prototype.pull = function (key) {
                key = key || "";

                var ret = this[key];
                delete this[key];

                var i = this.keys.indexOf(key);
                if (i > -1)
                    this.keys.splice(i, 1);

                return ret;
            };

            /* Si array[key] es undefined, get lo convierte en un array. */
            assocArray.prototype.get = function (key) {
                key = key || "";

                if (this[key] == undefined) {
                    this[key] = [];
                    this.keys.push(key);
                }

                return this[key];
            };

            assocArray.prototype.set = function (key, value) {
                key = key || "";
                value = value || "";

                this[key] = value;
                return this[key];
            }

            assocArray.prototype.clone = function () {
                var cl = new assocArray();

                for (var i in this.keys) {
                    cl.push(this.keys[i], this[this.keys[i]]);
                }

                return cl;
            };

            assocArray.prototype.forEach = function (callback) {
                for (var i in this.keys) {
                    callback.call(this, this[this.keys[i]], this.keys[i]);
                }
            };

            assocArray.prototype.slice = function (key) {
                var cl = this.clone();

                cl.pull(key);
                return cl;
            };

            //Devuelve un objeto JS limpio, sin elementos propios del array asociativo
            //También elimina la referencia, pero los objetos que hay dentro aún podrían estar
            //referenciados, puede valer la pena mirar fullObjectize
            assocArray.prototype.objectize = function () {
                var res = {};

                for (var i in this.keys) {
                    res[this.keys[i]] = this[this.keys[i]];
                }

                return res;
            }

            //Devuelve un objeto JS limpio, y si algun elemento es assocArray, lo limpia también, recursivamente
            assocArray.prototype.fullObjectize = function () {
                var res = {};

                for (var i in this.keys) {
                    res[this.keys[i]] = this[this.keys[i]];

                    if (res[this.keys[i]] instanceof assocArray) {
                        res[this.keys[i]] = res[this.keys[i]].fullObjectize();
                    }
                }

                return res;
            }

            return assocArray;
        }
    ]).service('jsUtils', [
        function () {
            this.swapKeyValue = function (obj) {
                var new_obj = {};

                for (var prop in obj) {
                    if (obj.hasOwnProperty(prop)) {
                        new_obj[obj[prop]] = prop;
                    }
                }
                return new_obj;
            }

            //Elimina caracteres indeseados de un número (sólo enteros)
            this.cleanNumber = function (number) {
                if (typeof number === "number") {
                    return new String(number.toString());
                }

                var n = new String(number);
                if (typeof number === "string") {
                    number = number.replace(/\D/g, '');

                    if (number != 0)
                        number = number.replace(/^[0]+/g, '');
                    else
                        number = "0"; //Evitamos que haya varios 0's

                    return number;
                }
            }

            //Comprueba que el dominio del email no esté en la blacklist
            this.isValidMailDomain = function (email) {
                if (email != null && email != '') {
                    var domain_list = ["0-mail.com", "0815.ru", "0clickemail.com", "0wnd.net", "0wnd.org", "10minutemail.com", "20minutemail.com",
                        "2prong.com", "30minutemail.com", "3d-painting.com", "4warding.com", "4warding.net", "4warding.org", "60minutemail.com",
                        "675hosting.com", "675hosting.net", "675hosting.org", "6url.com", "75hosting.com", "75hosting.net", "75hosting.org", "7tags.com",
                        "9ox.net", "a-bc.net", "afrobacon.com", "ajaxapp.net", "amilegit.com", "amiri.net", "amiriindustries.com", "anonbox.net",
                        "anonymbox.com", "antichef.com", "antichef.net", "antispam.de", "baxomale.ht.cx", "beefmilk.com", "binkmail.com",
                        "bio-muesli.net", "bobmail.info", "bodhi.lawlita.com", "bofthew.com", "brefmail.com", "broadbandninja.com", "bsnow.net",
                        "bugmenot.com", "bumpymail.com", "casualdx.com", "centermail.com", "centermail.net", "chogmail.com", "choicemail1.com",
                        "cool.fr.nf", "correo.blogos.net", "cosmorph.com", "courriel.fr.nf", "courrieltemporaire.com", "cubiclink.com", "curryworld.de",
                        "cust.in", "dacoolest.com", "dandikmail.com", "dayrep.com", "deadaddress.com", "deadspam.com", "despam.it", "despammed.com",
                        "devnullmail.com", "dfgh.net", "digitalsanctuary.com", "discardmail.com", "discardmail.de", "Disposableemailaddresses:emailmiser.com",
                        "disposableaddress.com", "disposeamail.com", "disposemail.com", "dispostable.com", "dm.w3internet.co.ukexample.com", "dodgeit.com",
                        "dodgit.com", "dodgit.org", "donemail.ru", "dontreg.com", "dontsendmespam.de", "dump-email.info", "dumpandjunk.com", "dumpmail.de",
                        "dumpyemail.com", "e4ward.com", "email60.com", "emaildienst.de", "emailias.com", "emailigo.de", "emailinfive.com", "emailmiser.com",
                        "emailsensei.com", "emailtemporario.com.br", "emailto.de", "emailwarden.com", "emailx.at.hm", "emailxfer.com", "emz.net", "enterto.com",
                        "ephemail.net", "etranquil.com", "etranquil.net", "etranquil.org", "explodemail.com", "fakeinbox.com", "fakeinformation.com",
                        "fastacura.com", "fastchevy.com", "fastchrysler.com", "fastkawasaki.com", "fastmazda.com", "fastmitsubishi.com", "fastnissan.com",
                        "fastsubaru.com", "fastsuzuki.com", "fasttoyota.com", "fastyamaha.com", "filzmail.com", "fizmail.com", "fr33mail.info", "frapmail.com",
                        "front14.org", "fux0ringduh.com", "garliclife.com", "get1mail.com", "get2mail.fr", "getonemail.com", "getonemail.net", "ghosttexter.de",
                        "girlsundertheinfluence.com", "gishpuppy.com", "gowikibooks.com", "gowikicampus.com", "gowikicars.com", "gowikifilms.com", "gowikigames.com",
                        "gowikimusic.com", "gowikinetwork.com", "gowikitravel.com", "gowikitv.com", "great-host.in", "greensloth.com", "gsrv.co.uk", "guerillamail.biz",
                        "guerillamail.com", "guerillamail.net", "guerillamail.org", "guerrillamail.biz", "guerrillamail.com", "guerrillamail.de", "guerrillamail.net",
                        "guerrillamail.org", "guerrillamailblock.com", "h.mintemail.com", "h8s.org", "haltospam.com", "hatespam.org", "hidemail.de", "hochsitze.com",
                        "hotpop.com", "hulapla.de", "ieatspam.eu", "ieatspam.info", "ihateyoualot.info", "iheartspam.org", "imails.info", "inboxclean.com", "inboxclean.org",
                        "incognitomail.com", "incognitomail.net", "incognitomail.org", "insorg-mail.info", "ipoo.org", "irish2me.com", "iwi.net", "jetable.com", "jetable.fr.nf",
                        "jetable.net", "jetable.org", "jnxjn.com", "junk1e.com", "kasmail.com", "kaspop.com", "keepmymail.com", "killmail.com", "killmail.net", "kir.ch.tc",
                        "klassmaster.com", "klassmaster.net", "klzlk.com", "kulturbetrieb.info", "kurzepost.de", "letthemeatspam.com", "lhsdv.com", "lifebyfood.com",
                        "link2mail.net", "litedrop.com", "lol.ovpn.to", "lookugly.com", "lopl.co.cc", "lortemail.dk", "lr78.com", "m4ilweb.info", "maboard.com",
                        "mail-temporaire.fr", "mail.by", "mail.mezimages.net", "mail2rss.org", "mail333.com", "mail4trash.com", "mailbidon.com", "mailblocks.com",
                        "mailcatch.com", "maileater.com", "mailexpire.com", "mailfreeonline.com", "mailin8r.com", "mailinater.com", "mailinator.com", "mailinator.net",
                        "mailinator2.com", "mailincubator.com", "mailme.ir", "mailme.lv", "mailmetrash.com", "mailmoat.com", "mailnator.com", "mailnesia.com", "mailnull.com",
                        "mailshell.com", "mailsiphon.com", "mailslite.com", "mailzilla.com", "mailzilla.org", "mbx.cc", "mega.zik.dj", "meinspamschutz.de", "meltmail.com",
                        "messagebeamer.de", "mierdamail.com", "mintemail.com", "moburl.com", "moncourrier.fr.nf", "monemail.fr.nf", "monmail.fr.nf", "msa.minsmail.com",
                        "mt2009.com", "mx0.wwwnew.eu", "mycleaninbox.net", "mypartyclip.de", "myphantomemail.com", "myspaceinc.com", "myspaceinc.net", "myspaceinc.org",
                        "myspacepimpedup.com", "myspamless.com", "mytrashmail.com", "neomailbox.com", "nepwk.com", "nervmich.net", "nervtmich.net", "netmails.com",
                        "netmails.net", "netzidiot.de", "neverbox.com", "no-spam.ws", "nobulk.com", "noclickemail.com", "nogmailspam.info", "nomail.xl.cx", "nomail2me.com",
                        "nomorespamemails.com", "nospam.ze.tc", "nospam4.us", "nospamfor.us", "nospamthanks.info", "notmailinator.com", "nowmymail.com", "nurfuerspam.de",
                        "nus.edu.sg", "nwldx.com", "objectmail.com", "obobbo.com", "oneoffemail.com", "onewaymail.com", "online.ms", "oopi.org", "ordinaryamerican.net",
                        "otherinbox.com", "ourklips.com", "outlawspam.com", "ovpn.to", "owlpic.com", "pancakemail.com", "pimpedupmyspace.com", "pjjkp.com",
                        "politikerclub.de", "poofy.org", "pookmail.com", "privacy.net", "proxymail.eu", "prtnx.com", "punkass.com", "PutThisInYourSpamDatabase.com", "qq.com",
                        "quickinbox.com", "rcpt.at", "recode.me", "recursor.net", "regbypass.com", "regbypass.comsafe-mail.net", "rejectmail.com", "rklips.com", "rmqkr.net",
                        "rppkn.com", "rtrtr.com", "s0ny.net", "safe-mail.net", "safersignup.de", "safetymail.info", "safetypost.de", "sandelf.de", "saynotospams.com",
                        "selfdestructingmail.com", "SendSpamHere.com", "sharklasers.com", "shiftmail.com", "shitmail.me", "shortmail.net", "sibmail.com", "skeefmail.com",
                        "slaskpost.se", "slopsbox.com", "smellfear.com", "snakemail.com", "sneakemail.com", "sofimail.com", "sofort-mail.de", "sogetthis.com",
                        "soodonims.com", "spam.la", "spam.su", "spamavert.com", "spambob.com", "spambob.net", "spambob.org", "spambog.com", "spambog.de", "spambog.ru",
                        "spambox.info", "spambox.irishspringrealty.com", "spambox.us", "spamcannon.com", "spamcannon.net", "spamcero.com", "spamcon.org",
                        "spamcorptastic.com", "spamcowboy.com", "spamcowboy.net", "spamcowboy.org", "spamday.com", "spamex.com", "spamfree24.com", "spamfree24.de",
                        "spamfree24.eu", "spamfree24.info", "spamfree24.net", "spamfree24.org", "spamgourmet.com", "spamgourmet.net", "spamgourmet.org", "SpamHereLots.com",
                        "SpamHerePlease.com", "spamhole.com", "spamify.com", "spaminator.de", "spamkill.info", "spaml.com", "spaml.de", "spammotel.com", "spamobox.com",
                        "spamoff.de", "spamslicer.com", "spamspot.com", "spamthis.co.uk", "spamthisplease.com", "spamtrail.com", "speed.1s.fr", "supergreatmail.com",
                        "supermailer.jp", "suremail.info", "teewars.org", "teleworm.com", "tempalias.com", "tempe-mail.com", "tempemail.biz", "tempemail.com", "TempEMail.net",
                        "tempinbox.co.uk", "tempinbox.com", "tempmail.it", "tempmail2.com", "tempomail.fr", "temporarily.de", "temporarioemail.com.br", "temporaryemail.net",
                        "temporaryforwarding.com", "temporaryinbox.com", "thanksnospam.info", "thankyou2010.com", "thisisnotmyrealemail.com", "throwawayemailaddress.com",
                        "tilien.com", "tmailinator.com", "tradermail.info", "trash-amil.com", "trash-mail.at", "trash-mail.com", "trash-mail.de", "trash2009.com",
                        "trashemail.de", "trashmail.at", "trashmail.com", "trashmail.de", "trashmail.me", "trashmail.net", "trashmail.org", "trashmail.ws", "trashmailer.com",
                        "trashymail.com", "trashymail.net", "trillianpro.com", "turual.com", "twinmail.de", "tyldd.com", "uggsrock.com", "upliftnow.com", "uplipht.com",
                        "venompen.com", "veryrealemail.com", "viditag.com", "viewcastmedia.com", "viewcastmedia.net", "viewcastmedia.org", "webm4il.info",
                        "wegwerfadresse.de", "wegwerfemail.de", "wegwerfmail.de", "wegwerfmail.net", "wegwerfmail.org", "wetrainbayarea.com", "wetrainbayarea.org",
                        "wh4f.org", "whyspam.me", "willselfdestruct.com", "winemaven.info", "wronghead.com", "wuzup.net", "wuzupmail.net", "www.e4ward.com", "www.gishpuppy.com",
                        "www.mailinator.com", "wwwnew.eu", "xagloo.com", "xemaps.com", "xents.com", "xmaily.com", "xoxy.net", "yep.it", "yogamaven.com", "yopmail.com", "yopmail.fr",
                        "yopmail.net", "ypmail.webarnak.fr.eu.org", "yuurok.com", "zehnminutenmail.de", "zippymail.info", "zoaxe.com", "zoemail.org"];

                    var domain = email.split("@").pop();

                    return domain_list.indexOf(domain) == -1;
                } else {

                    return false;
                }

            }

            //Inserta puntos para los miles (sólo enteros)
            this.formatNumber = function (number) {
                if (number == undefined || number == '')
                    return '';

                //Eliminar puntos
                var input = this.cleanNumber(number);
                var transformedInput = "";
                var count = input.length;

                //Insertar puntos
                for (var index in input) {
                    if (isNaN(index))
                        continue;

                    if (count % 3 === 0 && index != 0) {
                        transformedInput += ".";
                    }
                    if (!isNaN(input[index])) {
                        transformedInput += input[index];
                    }
                    count--;
                }

                return transformedInput;
            }

            //Inserta stuff en la posición index (modifica el array por referencia)
            this.insertOnArray = function (array, index, stuff) {
                array.splice(index, 0, stuff);
            }
        }
    ]).service('resetLocalStorage', [
        function () {
            this.reset = function (values, $localStorage) {
                angular.forEach(values, function (value, key) {
                    delete $localStorage[key];
                });
            };
        }
    ]).service('AppService', ['logger',
        function (logger) {
            this.doLogout = function () {
                window.location.href = Routing.generate('fos_user_security_logout', true);
            };
            this.doClose = function () {
                window.close();
            };
            this.UnAuthorize = function (status) {
                if (status === 401)
                {
                    this.doLogout();
                }
            };
        }
    ]).service('anonymousToken', [
        function () {
            var token;
            this.getToken = function () {
                if (token === undefined) {
                    token = this.newToken();
                }
                return token;
            };
            this.newToken = function () {
                return Math.floor(Math.random() * 10000000000).toString();
            };
            this.reset = function () {
                token = this.newToken();
            };
        }
    ]).service('uploaderProvider', [
        'FileUploader', 'anonymousToken', '$http', 'logger', function ($fileUploader, anonymousToken, $http, logger) {
            /* Multiton pattern
             * Provee el objeto uploader
             *
             * Véase la directiva sharedDirectives -> uploadDocument para instrucciones
             */

            this.uploaders = {};
            this.get = function (ID, $scope, token) {
                if (this.uploaders[ID] === undefined) {
                    this.uploaders[ID] = newUploader($scope, token);
                }
                return this.uploaders[ID];
            };

            //Nota: no sirve con crear un new FileUploader: las referencias no se actualizarían
            this.renew = function (ID, token) {
                this.uploaders[ID].clearQueue();
                this.uploaders[ID].formData[0].token = token;
                this.uploaders[ID].setAdditional([]);
            };

            var newUploader = function ($scope, token) {
                if (token === undefined)
                {
                    token = anonymousToken.getToken();
                }
                return new $fileUploader({
                    scope: $scope, // to automatically update the html. Default: $rootScope
                    url: ApiBase + '/publics/documents',
                    formData: [
                        {token: token}
                    ],
                    autoUpload: true,
                    onSuccessItem: function (item, response, status, headers) {
                        if (response.success) {
                            item.id = response.success.id;
                            item.nombre_externo = response.success.nombre_externo;
                        }
                    },
                    onErrorItem(item, response, status, headers) {
                        if (typeof response.error.code === 'undefined') {
                            angular.forEach(response.error, function (value, key) {
                                logger.logError(value);
                            });
                        }
                        this.removeFile(item);
                    },
                    additionalDocuments: [],
                    removeFile: function (item, isAdditional) {
                        var uploader = this;
                        $http.put(ApiBase + '/publics/' + item.id + '/removedocument')
                                .success(function () {
                                    if (isAdditional !== undefined) {
                                        uploader.additionalDocuments.splice(isAdditional, 1);
                                    } else {
                                        item.remove();
                                    }
                                });
                    },
                    downloadFile: function (item, isPublic) {
                        if (!isPublic) {
                            window.location.href = ApiBase + "/documents/" + item.id;
                        }
                    },
                    setAdditional: function (additionalDocuments) {
                        this.additionalDocuments = additionalDocuments;
                    },
                    getToken: function () {
                        return this.formData[0].token;
                    }
                });
            }
        }
    ]).service('stateService', [
        'jsUtils', function (jsUtils) {
            var IDStates = {
                1: "Pendiente",
                2: "Público",
                3: "Retornado",
                4: "Rechazado"
            };

            var IDAlias = {
                1: "pending",
                2: "public",
                3: "retornate",
                4: "reject"
            };

            var IDTags = {
                1: "id00095_app:states:name:pending",
                2: "id00096_app:states:name:public",
                3: "id00097_app:states:name:retornate",
                4: "id00098_app:states:name:reject"
            };

            var ThreadIDTags = {
                1: "id00164_app:states:name:response-pending",
                2: "id00096_app:states:name:public",
                3: "id00165_app:states:name:resolved",
                4: "id00098_app:states:name:reject",
                locked: "id00133_app:states:name:locked"
            };

            var QuestionIDTags = {
                1: "id00164_app:states:name:response-pending",
                2: "id00096_app:states:name:public",
                3: "id00165_app:states:name:resolved",
                4: "id00098_app:states:name:reject"
            };

            var IdApps = {
                0: "voto",
                1: "foro",
                2: "derecho",
                3: "av"
            };

            var StatesID = jsUtils.swapKeyValue(IDStates);
            var AliasID = jsUtils.swapKeyValue(IDAlias);
            var AppsId = jsUtils.swapKeyValue(IdApps);

            this.getIDStates = function () {
                return IDStates;
            };
            this.getIDAlias = function () {
                return IDAlias;
            };
            this.getIDTags = function () {
                return IDTags;
            };
            this.getThreadIDTags = function () {
                return ThreadIDTags;
            };
            this.getQuestionIDTags = function () {
                return QuestionIDTags;
            };
            this.getIdApps = function () {
                return IdApps;
            };

            this.getNameState = function (idState) {
                return IDStates[idState];
            };

            this.getAliasState = function (idState) {
                return IDAlias[idState];
            };

            this.getIDByName = function (NameState) {
                return StatesID[NameState];
            };

            this.getIDByAlias = function (alias) {
                return AliasID[alias];
            };

            this.getIDByAppName = function (nameApp) {
                return AppsId[nameApp];
            };

            this.getTag = function (idState) {
                return IDTags[idState];
            }
        }
    ]).service('typeService', [
        function () {
            var types = ["proposals", "initiatives", "offers", "requests", "threads", "questions"];

            var singularTypes = {
                "proposals": "proposal",
                "requests": "request",
                "initiatives": "initiative",
                "offers": "offer"
            };

            var capitalizedSingularTypes = {
                "proposals": "Proposal",
                "requests": "Request",
                "initiatives": "Initiative",
                "offers": "Offer"
            };

            var capitalizedPluralTypes = {
                "proposals": "Proposals",
                "requests": "Requests",
                "initiatives": "Initiatives",
                "offers": "Offers"
            };

            var typesURL = {
                "proposals": "propuesta",
                "requests": "peticion",
                "initiatives": "iniciativa",
                "offers": "oferta"
            };

            var shortTags = {
                "proposals": "id00044_app:foro:proposals",
                "requests": "id00047_app:foro:requests",
                "initiatives": "id00045_app:foro:initiatives",
                "offers": "id00046_app:foro:offers"
            };

            var longTags = {
                "proposals": "id00075_app:foro:long:proposals",
                "requests": "id00079_app:foro:long:requests",
                "initiatives": "id00076_app:foro:long:initiatives",
                "offers": "id00078_app:foro:long:offers"
            };

            var singularShortTags = {
                "proposals": "id00099_app:foro:singular:short:proposal",
                "requests": "id00102_app:foro:singular:short:request",
                "initiatives": "id00100_app:foro:singular:short:initiative",
                "offers": "id00101_app:foro:singular:short:offer"
            };

            var singularLongTags = {
                "proposals": "id00081_app:foro:singular:long:proposal",
                "requests": "id00084_app:foro:singular:long:request",
                "initiatives": "id00082_app:foro:singular:long:initiative",
                "offers": "id00083_app:foro:singular:long:offer"
            };

            this.getTypes = function () {
                return types;
            };

            this.getSingularTypes = function () {
                return singularTypes;
            };

            this.getCapSingularTypes = function () {
                return capitalizedSingularTypes;
            };

            this.getCapPluralTypes = function () {
                return capitalizedPluralTypes;
            }

            this.getTypesToURL = function () {
                return typesURL;
            };

            this.getShortTags = function (singular) {
                return singular ? singularShortTags : shortTags;
            };

            this.getLongTags = function (singular) {
                return singular ? singularLongTags : longTags;
            };
        }
    ]).service('mailEvent', [
        '$rootScope', 'mailsManager', '$interval', function ($rootScope, mailsManager, $interval) {
            var mailsIn;
            var update = function () {
                mailsManager.loadAllMails().then(function (mails_in) {
                    if (JSON.stringify(mails_in) !== JSON.stringify(mailsIn))
                    {
                        $rootScope.$broadcast('mails_in', mails_in);
                        mailsIn = mails_in;
                    }
                });
            }
            update();
            //$rootScope.mails_interval = $interval(update, 60000);

            this.getMailsIn = function () {
                return mailsIn;
            }
        }
    ]).service('mailEventOut', [
        '$rootScope', 'mailsManager', function ($rootScope, mailsManager) {
            var mailsOut;
            var update = function () {
                mailsManager.loadAllOutMails().then(function (mails_out) {
                    $rootScope.$broadcast('mails_out', mails_out);
                    mailsOut = mails_out;
                });
            }

            update();
            this.getMailsOut = function () {
                return mailsOut;
            }
            this.update = function () {
                return update();
            }
        }
    ]).service('documentValidation', [
        function () {
            this.validate = function (value, tipoDoc) {
                return (this.typeDoc(value) === tipoDoc || tipoDoc === "otros");
            }

            this.typeDoc = function (value) {
                if (value === undefined) {
                    return false;
                }

                value = value.toUpperCase();

                if (value.match(/^[0-9]{8}[A-Z]{1}$/)) {
                    if (value.slice(-1).match(/(T|R|W|A|G|M|Y|F|P|D|X|B|N|J|Z|S|Q|V|H|L|C|K|E)/)) {
                        if (value[8] === "TRWAGMYFPDXBNJZSQVHLCKE".charAt(value.substring(0, 8) % 23)) {
                            return 'nif';
                        }
                    }
                }

                var par = 0;
                var non = 0;
                var letras = "ABCDEFGHKLMNPQS";
                var letr = value.charAt(0);

                if (value.length == 9) {
                    //alert('El Cif debe tener 9 dÃ­gitos');
                    if (letras.indexOf(letr.toUpperCase()) != -1) {
                        //alert("El comienzo del Cif no es vÃ¡lido");
                        var zz, nn;
                        for (zz = 2; zz < 8; zz += 2) {
                            par = par + parseInt(value.charAt(zz));
                        }

                        for (zz = 1; zz < 9; zz += 2) {
                            nn = 2 * parseInt(value.charAt(zz));
                            if (nn > 9)
                                nn = 1 + (nn - 10);
                            non = non + nn;
                        }

                        var parcial = par + non;
                        var control = (10 - (parcial % 10));
                        if (control == 10)
                            control = 0;

                        if (control == value.charAt(8)) {
                            //alert("El Cif no es vÃ¡lido");
                            return "cif";
                        }
                    }
                }

                // Basic format test
                if (value.match('((^[A-Z]{1}[0-9]{7}[A-Z0-9]{1}$|^[T]{1}[A-Z0-9]{8}$)|^[0-9]{8}[A-Z]{1}$)')) {
                    if (/^[T]{1}/.test(value)) {
                        if (value[8] === /^[T]{1}[A-Z0-9]{8}$/.test(value))
                        {
                            return "nie";
                        }
                    }
                    if (/^[XYZ]{1}/.test(value)) {
                        if (
                                value[8] === "TRWAGMYFPDXBNJZSQVHLCKE".charAt(
                                value.replace('X', '0')
                                .replace('Y', '1')
                                .replace('Z', '2')
                                .substring(0, 8) % 23
                                )
                                )
                        {
                            return "nie";
                        }
                    }
                }
                return "otros";

            };

        }
    ]).factory('transformRequestAsFormPost', [
        function () {
            function transformRequest(data, getHeaders) {
                var headers = getHeaders();
                headers[ "Content-Type" ] = "application/x-www-form-urlencoded; charset=utf-8";
                /*headers[ "Access-Control-Allow-Headers" ]   = "Content-Type";
                 headers[ "Access-Control-Allow-Methods" ]   = "GET, POST, OPTIONS";
                 headers[ "Access-Control-Allow-Origin" ]    = "*";*/
                return(serializeData(data));
            }
            // Return the factory value.
            return(transformRequest);
            // ---
            // PRVIATE METHODS.
            // ---
            // I serialize the given Object into a key-value pair string. This
            // method expects an object and will default to the toString() method.
            // --
            // NOTE: This is an atered version of the jQuery.param() method which
            // will serialize a data collection for Form posting.
            // --
            // https://github.com/jquery/jquery/blob/master/src/serialize.js#L45
            function serializeData(data) {
                // If this is not an object, defer to native stringification.
                if (!angular.isObject(data)) {
                    return((data == null) ? "" : data.toString());
                }
                var buffer = [];
                // Serialize each key in the object.
                for (var name in data) {
                    if (!data.hasOwnProperty(name)) {
                        continue;
                    }
                    var value = data[ name ];
                    buffer.push(
                            encodeURIComponent(name) +
                            "=" +
                            encodeURIComponent((value == null) ? "" : value)
                            );
                }
                // Serialize the buffer and clean it up for transportation.
                var source = buffer
                        .join("&")
                        .replace(/%20/g, "+")
                        ;
                return(source);
            }
        }
    ]);
}).call(this);