var app = angular.module('main', ['ngTable', 'main.toolboxServices','main.filters','ui.bootstrap', 'angular-loading-bar']).
controller('ToolboxCtrl', function ($scope, ngTableParams, IolyToolboxService, $modal, $document, $timeout, $location, $anchorScroll, $sce, $document) {

    // Register a body reference to use later
    var bodyRef = angular.element( $document[0].body );
    $scope.numRecipes = 0;
    /**
     * Nice modal messsages
     */
    $scope.content = '';
    $scope.showMsg = function (content, callback) {
        // Add our overflow hidden class on opening
        // to prevent from scrollbar / page flickering
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
            if(typeof callback !== "undefined") {
                callback($scope.content);
            }
        }, function () {
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
        // set timeout to remove message auto-magically :)
        $timeout(function(){
            $scope.closeAlert(0);
        }, timeout);
    };
    $scope.closeAlert = function (index) {
        $scope.alerts.splice(index, 1);
    };

    /**
     * Generate views
     */
    $scope.generateViews = function () {
        var minput = "<div id='shops'><h2>Shop-Ids (comma-separated or 'all' for all shops)</h2><input type='text' name='shopids' id='shopids' value='all'></div>";
        $scope.showMsg(minput, $scope.submitGenerateViews);
    };
    $scope.submitGenerateViews = function(content) {
        var shopIds = $document[0].querySelector('#shopids').value;
        console.log("Generating views for shopIds: " + shopIds);
        var responsePromise = IolyToolboxService.generateViews(shopIds);

        responsePromise.then(function (response) {
            $scope.showMsg(response.data.status);
        }, function (error) {
            console.log(error);
            $scope.addAlert('danger', error.data.message);
        });
    };
    /**
     * Clear tmp
     */
    $scope.emptyTmp = function () {
        console.log("Clearing tmp folder ...");
        var responsePromise = IolyToolboxService.emptyTmp();

        responsePromise.then(function (response) {
            $scope.showMsg(response.data.status);
        }, function (error) {
            console.log(error);
            $scope.addAlert('danger', error.data.message);
        });
    };
    /**
     * Activate module
     */
    $scope.activateModule = function () {
        console.log("Activate module ...");
        var responsePromise = IolyToolboxService.getModuleList();
        responsePromise.then(function (response) {
            var minput = "<label for='moduleid'>Module-Id:</label> <input type='text' name='moduleid' id='moduleid'>";
            var select = "<br/><label for='moduleid2'>Or select:</label><select class='form-control' id='moduleid2' name='moduleid2'><option value=''>Please select</option>";
            if(typeof response.data.modules !== "undefined") {
                for (var f in response.data.modules) {
                    select += "<option value='" + f + "'>" + response.data.modules[f] + " [id: " + f + "]" + "</option>";
                }
            }
            select += "</select>";
            minput += select;
            minput += "<br/><div id='cbs'><label for='deactivate'><strong>Deactivate</strong> module?</label><input class='form-control' type='checkbox' name='deactivate' id='deactivate' value='1'></div>";
            minput += "<div id='shops'><h2>Shop-Ids (comma-separated or 'all' for all shops)</h2><input type='text' name='shopids' id='shopids' value='all'></div>";
            $scope.showMsg(minput, $scope.submitActivateModule);

        }, function (error) {
            console.log(error);
            $scope.addAlert('danger', error.data.message);
        });
    };
    $scope.submitActivateModule = function(content) {
        var moduleId = $document[0].querySelector('#moduleid').value;
        var moduleIdSel = $document[0].querySelector('#moduleid2');
        var deactivate = $document[0].querySelector('#deactivate').checked;
        if(typeof moduleIdSel !== "undefined") {
            var selVal = moduleIdSel.options[moduleIdSel.options.selectedIndex].value;
            if(selVal !== '') {
                moduleId = selVal;
            }
        }
        var shopIds = $document[0].querySelector('#shopids').value;
        var responsePromise = IolyToolboxService.activateModule(moduleId, shopIds, deactivate);

        responsePromise.then(function (response) {
            $scope.showMsg(response.data.status);
        }, function (error) {
            console.log(error);
            $scope.addAlert('danger', error.data.message);
        });
    };


    /**
     * Clear module
     */
    $scope.clearModule = function () {
        console.log("Clearing module ...");
        var responsePromise = IolyToolboxService.getModuleList();
        responsePromise.then(function (response) {
            var minput = "<label for='moduleid'>Module-Id:</label> <input type='text' name='moduleid' id='moduleid'>";
            var select = "<br/><label for='moduleid2'>Or select:</label><select class='form-control' id='moduleid2' name='moduleid2'><option value=''>Please select</option>";
            if(typeof response.data.modules !== "undefined") {
                for (var f in response.data.modules) {
                    select += "<option value='" + f + "'>" + response.data.modules[f] + " [id: " + f + "]" + "</option>";
                }
            }
            select += "</select>";
            minput += select;
            minput += "<div id='shops'><h2>Shop-Ids (comma-separated or 'all' for all shops)</h2><input type='text' name='shopids' id='shopids' value='all'></div>";
            $scope.showMsg(minput, $scope.submitClearModule);

        }, function (error) {
            console.log(error);
            $scope.addAlert('danger', error.data.message);
        });
    };
    $scope.submitClearModule = function(content) {
        var moduleId = $document[0].querySelector('#moduleid').value;
        var moduleIdSel = $document[0].querySelector('#moduleid2');
        if(typeof moduleIdSel !== "undefined") {
            var selVal = moduleIdSel.options[moduleIdSel.options.selectedIndex].value;
            if(selVal !== '') {
                moduleId = selVal;
            }
        }
        var shopIds = $document[0].querySelector('#shopids').value;
        var responsePromise = IolyToolboxService.clearModule(moduleId, shopIds, true);

        responsePromise.then(function (response) {
            $scope.showMsg(response.data.status);
        }, function (error) {
            console.log(error);
            $scope.addAlert('danger', error.data.message);
        });
    };
    
    })
    /**
     * Modal controller
     */
    .controller('ModalInstanceCtrl', function ($scope, $modalInstance, content, $sce) {
        $scope.content = $sce.trustAsHtml(content);
        $scope.ok = function () {
            $modalInstance.close();
        };
        $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
        };
    })
    ;

