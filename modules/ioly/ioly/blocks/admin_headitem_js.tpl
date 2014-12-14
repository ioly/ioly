[{$smarty.block.parent}]
[{if $oViewConf->getActiveClassName() eq "ioly_main"}]
<script>
    var gModuleBasePath = "[{$oViewConf->getModuleUrl('ioly', '')}]";
    var gOxidSelfLink = "[{$oViewConf->getSelfLink()}]";
    gOxidSelfLink = gOxidSelfLink.replace(/&amp;/g, '&');
</script>

[{/if}]
