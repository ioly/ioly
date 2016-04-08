'use strict';

/* Services module */

angular.module('main.toolboxServices', [])

    /**
     * A simple angular value
     * @type string
     */
    .value('version', '0.0.1')

    /**
     * Service factory for calling our simple PHP interface
     * @param $http
     * @returns asynchronous "promise" functions
     */
    .factory('IolyToolboxService', ['$http', function($http) {
        function postFormData(fnc, data){
            return $http({
                method: 'POST',
                url: gOxidSelfLink,
                data: $.param({cl: 'ioly_main', fnc: fnc, data: angular.toJson(data)}),
                headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'}
            });
        }
        return {
            generateViews: function(shopIds){
                return $http.get(gOxidSelfLink, {params: {cmd:'generateViews', shopIds: shopIds}});
            },
            emptyTmp: function(){
                return $http.get(gOxidSelfLink, {params: {cmd:'emptyTmp'}});
            },
            clearModule: function(moduleId, shopIds, fixModule){
                return $http.get(gOxidSelfLink, {params: {cmd:'clearModule', moduleId: moduleId, shopIds: shopIds, fixModule: fixModule}});
            },
            activateModule: function(moduleId, shopIds, deactivate){
                return $http.get(gOxidSelfLink, {params: {cmd:'activateModule', moduleId: moduleId, shopIds: shopIds, deactivate: deactivate}});
            },
            getModuleList: function(){
                return $http.get(gOxidSelfLink, {params: {cmd:'getModuleList'}});
            }
        };
    }])

;
