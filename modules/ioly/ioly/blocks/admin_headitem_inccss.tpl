[{$smarty.block.parent}]
[{if $oViewConf->getActiveClassName() eq "ioly_main"}]
    [{assign var="iolyHelper" value=$oView->getIolyHelper()}]
    <link rel="stylesheet" href="[{$iolyHelper->getIolyLibPath('ng-table', '0.3.2', 'examples/css/bootstrap.min.css')}]">
    <link rel="stylesheet" href="[{$iolyHelper->getIolyLibPath('ng-table', '0.3.2', 'examples/css/bootstrap-theme.min.css')}]">
    <link rel="stylesheet" href="[{$iolyHelper->getIolyLibPath('ng-table', '0.3.2', 'ng-table.css')}]">
    <link rel="stylesheet" href="[{$iolyHelper->getIolyLibPath('angular-loading-bar', '0.6.0', 'loading-bar.css')}]">
    <link rel="stylesheet" href="[{$oViewConf->getModuleUrl("ioly", 'out/admin/src/css/ioly.css')}]">
[{/if}]
