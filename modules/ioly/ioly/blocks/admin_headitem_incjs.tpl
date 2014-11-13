[{$smarty.block.parent}]

[{if $oViewConf->getActiveClassName() eq "ioly_main"}]

    <script src="[{$iolyHelper->getIolyLibPath('angular.js', '1.3.0', 'angular.min.js')}]" type="text/javascript"></script>                    
    <script src="[{$iolyHelper->getIolyLibPath('bootstrap-gh-pages', '0.11.2', 'ui-bootstrap-tpls-0.11.2.min.js')}]" type="text/javascript"></script>                    
    <script src="[{$iolyHelper->getIolyLibPath('ng-table', '0.3.2', 'ng-table.js')}]" type="text/javascript"></script>                    
    <script src="[{$iolyHelper->getIolyLibPath('angular-loading-bar', '0.6.0', 'loading-bar.js')}]" type="text/javascript"></script>                    

    <script src="[{$iolyHelper->getIolyPath('out/admin/src/js/services.js')}]" type="text/javascript"></script>                    
    <script src="[{$iolyHelper->getIolyPath('out/admin/src/js/filters.js')}]" type="text/javascript"></script>                    
    <script src="[{$iolyHelper->getIolyPath('out/admin/src/js/app.js')}]" type="text/javascript"></script>                    
[{/if}]
