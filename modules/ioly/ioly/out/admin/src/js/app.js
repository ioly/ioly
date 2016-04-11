var app = angular.module('main', ['ngTable', 'main.services','main.filters','ui.bootstrap', 'angular-loading-bar']).
        controller('IolyCtrl', function ($scope, ngTableParams, IolyService, $modal, $document, $timeout, $location, $anchorScroll, $sce) {

            // Register a body reference to use later
            var bodyRef = angular.element( $document[0].body );
            $scope.numRecipes = 0;
            /**
             * Nice modal messsages
             */
            $scope.content = '';
            $scope.headline = '';
            $scope.showMsg = function (headline, content, callback) {
                // Add our overflow hidden class on opening
                // to prevent from scrollbar / page flickering
                bodyRef.addClass('ovh');
                $scope.headline = headline;
                $scope.content = content;
                var modalInstance = $modal.open({
                    templateUrl: 'myModalContent.html',
                    controller: 'ModalInstanceCtrl',
                    size: 'sm',
                    resolve: {
                        content: function () {
                            return $scope.content;
                        },
                        headline: function () {
                            return $scope.headline;
                        }
                    }
                });
                modalInstance.result.then(function () {
                    // Remove it on closing
                    //bodyRef.removeClass('ovh');
                    if(typeof callback !== "undefined") {
                        callback($scope.content);
                    }
                }, function () {
                    // Remove it on dismissal
                    //bodyRef.removeClass('ovh');
                });
            };
            /**
             * Alerts
             */
            $scope.alerts = [];
            $scope.addAlert = function (type, msg, timeout) {
                if(timeout === '' || typeof timeout === "undefined") {
                    timeout = 10000;
                }
                $scope.alerts.push({type: type, msg: msg, trustedMsg: $sce.trustAsHtml(msg)});
                $location.hash('iolyerrors');
                $anchorScroll();
                // set timeout to remove message auto-magically :)
                $timeout(function(){
                    $scope.closeAlert(0);
                }, timeout);
            };
            $scope.closeAlert = function (index) {
                $scope.alerts.splice(index, 1);
            };
            /**
             * 
             * Update the core ioly lib
             */
            $scope.updateIoly = function () {
                var responsePromise = IolyService.updateIoly();

                responsePromise.then(function (response) {
                    $scope.showMsg("Info", response.data.status);
                }, function (error) {
                    console.log(error);
                    $scope.addAlert('danger', error.data.message);
                });
            };
            
            /**
             * Read contributors list from Github
             */
            $scope.getContributors = function() {
                var responsePromise = IolyService.getContributors();

                responsePromise.then(function (response) {
                    $scope.contributors = response.data;
                }, function (error) {
                    console.log(error);
                    $scope.addAlert('danger', error.data.message);
                });
            };
            // call it right "on load"...
            $scope.getContributors();

            /**
             * 
             * Update the recipes db
             */
            $scope.updateRecipes = function () {
                var responsePromise = IolyService.updateRecipes();

                responsePromise.then(function (response) {
                    $scope.showMsg("Info", response.data.status);
                    // reload ng-table, too
                    $scope.refreshTable();
                }, function (error) {
                    console.log(error);
                    $scope.addAlert('danger', error.data.message);
                });
            };
            /**
             * 
             * Update the ioly oxid connector
             */
            $scope.updateConnector = function (successtext) {
                var responsePromise = IolyService.downloadModule("ioly/ioly-oxid-connector", "latest", '');

                responsePromise.then(function (response) {
                    $scope.showMsg("Info", successtext);
                    // reload ng-table, too
                    $scope.refreshTable();
                }, function (error) {
                    console.log(error);
                    $scope.addAlert('danger', error.data.message);
                });
            };
            /**
             * 
             * @param {type} page
             * @returns {undefined}
             */
            $scope.refreshTable = function () {
                var currPage = $scope.tableParams.$params.page;
                $scope.tableParams.reload();
                $scope.tableParams.$params.page = currPage;
            };

            /**
             * Uninstall a module
             * @param string packageString
             * @param string moduleversion
             * @param string successtext
             */
            $scope.removeModule = function (packageString, moduleversion, successtext) {
                console.log("removing module " + packageString + ", version:" + moduleversion);
                var responsePromise = IolyService.removeModule(packageString, moduleversion);

                responsePromise.then(function (response) {
                    $scope.showMsg("Info", successtext);
                    // reload ng-table, too
                    $scope.refreshTable();
                }, function (error) {
                    console.log(error);
                    $scope.addAlert('danger', error.data.message);
                });
            };

            /**
             * Download a module
             * @param string packageString
             * @param string moduleversion
             * @param string successtext
             */
            $scope.downloadModule = function (packageString, moduleversion, successtext) {
                console.log("loading module " + packageString + ", version:" + moduleversion);
                // first check for pre- and postinstall hooks
                var preInstall, postInstall, msg;
                var hooksPromise = IolyService.getModuleHooks(packageString, moduleversion);
                hooksPromise.then(function (response) {
                    // check for hook data
                    if(typeof response.data.status[0] !== "undefined") {
                        preInstall = response.data.status[0].preinstall;
                        postInstall = response.data.status[0].postinstall;
                        msg = '';
                        if(preInstall && typeof preInstall.message !== "undefined") {
                            msg = msg + preInstall.message;
                        }
                        // add link?
                        if(preInstall && typeof  preInstall.link !== "undefined") {
                            msg = msg + " <a href='" + preInstall.link + "' target='_blank'>"+preInstall.link+"</a>";
                        }
                        // custom hook message?
                        if(msg !== '') {
                            if(typeof  preInstall.type === "undefined" ||  preInstall.type === "alert") {
                                // add alert
                                $scope.addAlert('success', msg, 20000);
                            }
                            else {
                                // add overlay
                                $scope.showMsg("Info", msg);
                            }
                        }
                    }
                    // now start the download
                var responsePromise = IolyService.downloadModule(packageString, moduleversion);
                    // and wait for response...
                    responsePromise.then(function (response) {
                        // in case of success, check postinstall hook data...
                        msg = '';
                        if(postInstall && typeof postInstall.message !== "undefined") {
                            msg = msg + postInstall.message;
                        }
                        // add link?
                        if(postInstall && typeof  postInstall.link !== "undefined") {
                            msg = msg + " <a href='" + postInstall.link + "' target='_blank'>"+postInstall.link+"</a>";
                        }
                        // custom hook message?
                        if(msg !== '') {
                            if(typeof  postInstall.type === "undefined" ||  postInstall.type === "overlay") {
                                // default: add overlay
                                $scope.showMsg("Info", msg);
                            }
                            else {
                                // add alert
                                $scope.addAlert('success', msg, 20000);
                            }
                        }
                        else {
                            // show default text only
                            $scope.showMsg("Info", successtext);
                        }
                    // reload ng-table, too
                    $scope.refreshTable();
                }, function (error) {
                    console.log(error);
                    $scope.addAlert('danger', error.data.message);
                });
                }, function (error) {
                    console.log(error);
                    $scope.addAlert('danger', error.data.message);
                });
            };

        /**
         * Activate module
         */
        $scope.activateModule = function (activate, moduleid, moduleVersion) {
            var headline = "Activate module";
            var responsePromise = IolyService.getActiveModuleSubshops(moduleid, moduleVersion);
            responsePromise.then(function (response) {
                var minput = "<input type='hidden' name='moduleid' id='moduleid' value='"+moduleid+"'><input type='hidden' name='moduleversion' id='moduleversion' value='"+moduleVersion+"'>";
                var iDeact = 0;
                if(!activate) {
                    iDeact = 1;
                    headline = "Deactivate module";
                }
                minput += "<br/><input type='hidden' name='deactivate' id='deactivate' value='"+iDeact+"'></div>";
                minput += "<div id='shopsact'><h2>Active Shop-Ids</h2>"+response.data.subshops.toString()+"</div>";
                minput += "<div id='shops'><h2>Shop-Ids (comma-separated, e.g. '1,2,5' or 'all' for all shops)</h2><input type='text' name='shopids' id='shopids' value='all'></div>";
                $scope.showMsg(headline + " " + moduleid, minput, $scope.submitActivateModule);
            }, function (error) {
                console.log(error);
                $scope.addAlert('danger', error.data.status);
            });
        };
        $scope.submitActivateModule = function(content) {
            var moduleId = $document[0].querySelector('#moduleid').value;
            var moduleVersion = $document[0].querySelector('#moduleversion').value;
            var deactivate = $document[0].querySelector('#deactivate').value;
            var shopIds = $document[0].querySelector('#shopids').value;
            var responsePromise = IolyService.activateModule(moduleId, shopIds, deactivate, moduleVersion);

            responsePromise.then(function (response) {
                $scope.showMsg("Info", response.data.status);
                // reload ng-table, too
                $scope.refreshTable();
            }, function (error) {
                console.log(error);
                $scope.addAlert('danger', error.data.status);
            });
        };

            /**
             * ng-table settings
             * 
             */
            $scope.tableParams = new ngTableParams({
                page: 1,
                count: 10,
                sorting: {
                    name: 'asc'
                }
            }, {
                total: 0,
                getData: function ($defer, params) {
                    var sorting = params.sorting();
                    var filter = params.filter();
                    var sortString = '';
                    var sortDir = '';
                    var searchText = '';
                    for(var s in sorting) {
                        sortString = s;
                        sortDir = sorting[s];
                    }
                    for(var f in filter) {
                        searchText = filter[f];
                    }
                    if (typeof searchText === "undefined" || searchText === "undefined") {
                        searchText = '';
                    }
                    var responsePromise = IolyService.getAllModules(searchText, params.page() - 1, params.count(), sortString, sortDir);
                    responsePromise.then(function (response) {
                        params.total(response.data.numObjects);
                        var data = response.data.result;
                        $defer.resolve(data);
                        console.log("table data received: " + response.data.numObjects);
                        $scope.numRecipes = response.data.numObjects;
                    }, function (error) {
                        $scope.addAlert('error', error.data + " (Error " + error.status + ")");
                    });
                }
            });
        })
        /**
         * Modal controller
         */
        .controller('ModalInstanceCtrl', function ($scope, $modalInstance, content, headline, $sce) {
            $scope.content = $sce.trustAsHtml(content);
            $scope.headline = $sce.trustAsHtml(headline);
            $scope.ok = function () {
                $modalInstance.close();
            };
            $scope.cancel = function () {
                $modalInstance.dismiss('cancel');
            };
        })
;



