<?php
/**
 * ioly Toolbox
 *
 * PHP version 5.3
 *
 * @category ioly_toolbox
 * @package  OXID Toolbpx
 * @author   Stefan Moises <stefan@rent-a-hero.de>
 * @license  MIT License http://opensource.org/licenses/MIT
 * @link     http://getioly.com/
 * @version  0.1.0
 */

require_once '../bootstrap.php';
require_once 'workhorse.php';

// default commands
$cmd = "generateViews";
$shopId = "all";

$oShop = oxNew('oxShop');
if (($sCmd = oxRegistry::getConfig()->getRequestParameter("cmd")) != '') {
    $cmd = $sCmd;
}
if (($iShopId = oxRegistry::getConfig()->getRequestParameter("shopId")) != '') {
    $shopId = $iShopId;
}
$oWorkHorse = new Workhorse();

$msg = "";
switch ($cmd) {
    case "generateViews":
        $sShopIds = oxRegistry::getConfig()->getRequestParameter("shopIds");
        $aShopIds = array();
        if ($sShopIds == "all") {
            $aShopIds = oxRegistry::getConfig()->getShopIds();
        } elseif (strpos($sShopIds, ",") !== false) {
            $aShopIds = explode(",", $sShopIds);
        } else {
            // single shopid
            $aShopIds[] = $sShopIds;
        }
        $oWorkHorse->generateViews($aShopIds);
        break;
    case "emptyTmp":
        $oWorkHorse->emptyTmp();
        break;
    case "clearModule":
        $sShopIds = oxRegistry::getConfig()->getRequestParameter("shopIds");
        $moduleId = oxRegistry::getConfig()->getRequestParameter("moduleId");
        $fixModule = oxRegistry::getConfig()->getRequestParameter("fixModule");
        $aShopIds = array();
        if ($sShopIds == "all") {
            $aShopIds = oxRegistry::getConfig()->getShopIds();
        } elseif (strpos($sShopIds, ",") !== false) {
            $aShopIds = explode(",", $sShopIds);
        } else {
            // single shopid
            $aShopIds[] = $sShopIds;
        }
        $oWorkHorse->clearModule($moduleId, $aShopIds, $fixModule);
        break;
    case "activateModule":
        $sShopIds = oxRegistry::getConfig()->getRequestParameter("shopIds");
        $moduleId = oxRegistry::getConfig()->getRequestParameter("moduleId");
        $deactivate = oxRegistry::getConfig()->getRequestParameter("deactivate") == "true" ? 1 : 0;
        $aShopIds = array();
        if ($sShopIds == "all") {
            $aShopIds = oxRegistry::getConfig()->getShopIds();
        } elseif (strpos($sShopIds, ",") !== false) {
            $aShopIds = explode(",", $sShopIds);
        } else {
            // single shopid
            $aShopIds[] = $sShopIds;
        }
        $oWorkHorse->activateModule($moduleId, $aShopIds, $deactivate);
        break;
    case "getModuleList":
        $oWorkHorse->getModuleList();
        break;
    default:
}
