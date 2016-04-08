<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ioly Toolbox</title>
    <link rel="stylesheet" href="../modules/ioly/ioly/libs/ng-table/0.3.2/examples/css/bootstrap.min.css">
    <link rel="stylesheet" href="../modules/ioly/ioly/libs/ng-table/0.3.2/examples/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="../modules/ioly/ioly/libs/ng-table/0.3.2/ng-table.css">
    <link rel="stylesheet" href="../modules/ioly/ioly/libs/angular-loading-bar/0.6.0/loading-bar.css">
    <link rel="stylesheet" href="../modules/ioly/ioly/out/admin/src/css/ioly.css">
    <link rel="stylesheet" href="css/toolbox.css">

    <script>
        var gOxidSelfLink = location.href.replace(/index.php/g, '') + "ajax.php";
    </script>

</head>
<body>
<div id="toolboxarea" ng-app="main">
    <div ng-controller="ToolboxCtrl">

        <script type="text/ng-template" id="myModalContent.html">
            <div class="modal-header">
                <h3 class="modal-title">Info</h3>
            </div>
            <div class="modal-body" ng-bind-html="content">
                {{content}}
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" ng-click="cancel()">Cancel</button>
                <button class="btn btn-primary" ng-click="ok()">OK</button>
            </div>
        </script>

        <div id="toolboxHead">
            <h1>ioly Toolbox</h1>
        </div>
        <div id="iolylogo">
            <img src="../modules/ioly/ioly/ioly_logo.png" border="0" alt="" title="">
        </div>

        <div class="clear"></div>
        <br>
        <a name="iolyerrors" id="iolyerrors"></a>
        <div>
            <alert ng-repeat="alert in alerts" type="{{alert.type}}" close="closeAlert($index)"><span ng-bind-html="alert.trustedMsg"></span></alert>
        </div>

        <div class="">
            <button tooltip-placement="bottom" tooltip="Generate views for all or some subshops" class="btn btn-primary" ng-click="generateViews()">Generate Views ...</button>
            <button tooltip-placement="bottom" tooltip="Clear complete tmp dir for the shop" class="btn btn-primary" ng-click="emptyTmp()">Clear Temp</button>
            <button tooltip-placement="bottom" tooltip="(De-)Activate a specific module in any subshop" class="btn btn-primary" ng-click="activateModule()">(De-)Activate Module ...</button>
            <button tooltip-placement="bottom" tooltip="Remove module id from aDisabledModules and aModulePaths if something is broken in oxconfig table" class="btn btn-primary" ng-click="clearModule()">Clear Module ...</button>
        </div>
    </div>
</div>


<script src="../modules/ioly/ioly/libs/angular.js/1.3.0/angular.min.js" type="text/javascript"></script>
<script src="../modules/ioly/ioly/libs/bootstrap-gh-pages/0.11.2/ui-bootstrap-tpls-0.11.2.min.js" type="text/javascript"></script>
<script src="../modules/ioly/ioly/libs/ng-table/0.3.2/ng-table.js" type="text/javascript"></script>
<script src="../modules/ioly/ioly/libs/angular-loading-bar/0.6.0/loading-bar.js" type="text/javascript"></script>

<script src="js/service.js" type="text/javascript"></script>
<script src="../modules/ioly/ioly/out/admin/src/js/filters.js" type="text/javascript"></script>
<script src="js/toolbox.js" type="text/javascript"></script>

</body>
</html>