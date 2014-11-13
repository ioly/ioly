var app = angular.module('main', ['ngTable', 'main.services','main.filters','ui.bootstrap', 'angular-loading-bar']).
        controller('IolyCtrl', function ($scope, ngTableParams, IolyService, $modal, $filter, $document, $timeout, $location, $anchorScroll, $sce) {

            // Register a body reference to use later
            var bodyRef = angular.element( $document[0].body );
            // loading flag
            $scope.loading = false;
            $scope.downloading = false;
            $scope.dynamic = 0;
            $scope.timerPromise = null;
            
            
            /**
             * Nice modal messsages
             */
            $scope.content = '';
            $scope.showMsg = function (content) {
                // Add our overflow hidden class on opening
                bodyRef.addClass('ovh');
                $scope.content = content;
                var modalInstance = $modal.open({
                    templateUrl: 'myModalContent.html',
                    controller: 'ModalInstanceCtrl',
                    size: 'sm',
                    resolve: {
                        content: function () {
                            return $scope.content;
                        }
                    }
                });
                modalInstance.result.then(function () {
                    // Remove it on closing
                    //bodyRef.removeClass('ovh');
                }, function () {
                    // Remove it on dismissal
                    //bodyRef.removeClass('ovh');
                });
            };
            /**
             * Alerts
             */
            $scope.alerts = [];
            $scope.addAlert = function (type, msg) {
                $scope.alerts.push({type: type, msg: msg, trustedMsg: $sce.trustAsHtml(msg)});
                $location.hash('iolyerrors');
                $anchorScroll();
            };
            $scope.closeAlert = function (index) {
                $scope.alerts.splice(index, 1);
            };

            /**
             * should the info divs be collapsed on load?
             */
            $scope.isCollapsed = true;
            /**
             * 
             * Update the core ioly lib
             */
            $scope.updateIoly = function () {
                $scope.loading = true;
                var responsePromise = IolyService.updateIoly();

                responsePromise.then(function (response) {
                    $scope.showMsg(response.data.status);
                    $scope.loading = false;
                }, function (error) {
                    $scope.loading = false;
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
                $scope.loading = true;
                var responsePromise = IolyService.updateRecipes();

                responsePromise.then(function (response) {
                    $scope.loading = false;
                    $scope.showMsg(response.data.status);
                    // reload ng-table, too
                    $scope.refreshTable();
                }, function (error) {
                    $scope.loading = false;
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
                console.log("refreshing table, page: " + currPage);
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
                    $scope.showMsg(successtext);
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
                //$scope.downloading = true;
                $scope.dynamic = 1;
                $scope.updateCurlStatus();
                var responsePromise = IolyService.downloadModule(packageString, moduleversion);

                responsePromise.then(function (response) {
                    $scope.showMsg(successtext);
                    $scope.downloading = false;
                    $timeout.cancel($scope.timerPromise);
                    // reload ng-table, too
                    $scope.refreshTable();
                }, function (error) {
                    $timeout.cancel($scope.timerPromise);
                    $scope.downloading = false;
                    $scope.dynamic = 0;
                    console.log(error);
                    $scope.addAlert('danger', error.data.message);
                });
            };
            /**
             * Update CURL progress bar
             */
            $scope.updateCurlStatus = function() {
                var responsePromise = IolyService.getCurlStatusAjax();

                responsePromise.then(function (response) {
                    console.log(response.data);
                    if(typeof response.data.status !== "undefined") {
                        var percent = response.data.status.progress;
                        $scope.dynamic = percent;
                        console.log("percent: " + percent);
                        if(percent < 100) {
                            $scope.timerPromise = $timeout(function(){
                                $scope.updateCurlStatus();
                            }, 50);
                        }
                        else {
                            $timeout.cancel($scope.timerPromise);
                        }
                    }

                }, function (error) {
                    $timeout.cancel($scope.timerPromise);
                    console.log(error);
                    $scope.addAlert('danger', error.data.message);
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
                    }, function (error) {
                        $scope.addAlert('error', error.data + " (Error " + error.status + ")");
                    });
                }
            });
        })
        /**
         * Modal controller
         */
        .controller('ModalInstanceCtrl', function ($scope, $modalInstance, content) {
            $scope.content = content;
            $scope.ok = function () {
                $modalInstance.close();
            };
            $scope.cancel = function () {
                $modalInstance.dismiss('cancel');
            };
})
;



