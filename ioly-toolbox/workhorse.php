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
 * @version  0.2.0
 */
require_once '../bootstrap.php';
require_once '../modules/ioly/ioly/core/ioly_helper.php';
/**
 * Class Workhorse, works for the AJAX script :)
 */
class Workhorse
{

    /**
     * Helper class
     * @var ioly_helper|null
     */
    protected $_iolyHelper = null;

    /**
     * Workhorse constructor.
     */
    public function __construct()
    {
        $this->_iolyHelper = oxRegistry::get('ioly_helper');
    }

    /**
     * Generate views
     * @param array $aShopIds
     */
    public function generateViews($aShopIds)
    {
        $aRet = $this->_iolyHelper->generateViews($aShopIds);
        $res = array("status" => $aRet['message']);
        $this->_sendJsonResponse($aRet['header'], $res);
    }

    /**
     * Clear tmp dir
     */
    public function emptyTmp()
    {
        $aRet = $this->_iolyHelper->emptyTmp();
        $res = array("status" => $aRet['message']);
        $this->_sendJsonResponse($aRet['header'], $res);
    }

    /**
     * Fix a disabled module
     * @param string $moduleId
     * @param array  $aShopIds
     * @param bool   $fixModule
     */
    public function clearModule($moduleId, $aShopIds, $fixModule = true)
    {
        $msg = "Checking module <b>$moduleId</b><br/>";
        $cfg = oxRegistry::getConfig();
        if (oxRegistry::getConfig()->getRequestParameter('oxmodule') != '') {
            $moduleId = oxRegistry::getConfig()->getRequestParameter('oxmodule');
        }
        foreach ($aShopIds as $sShopId) {
            // set shopId
            $cfg->setShopId($sShopId);
            $aModulePaths = $cfg->getConfigParam('aModules');
            $aDisabledModules = $cfg->getConfigParam('aDisabledModules');
            $iOldKey = array_search($moduleId, $aDisabledModules);
            if ($iOldKey !== false) {
                if ($fixModule) {
                    unset($aDisabledModules[$iOldKey]);
                    $cfg->saveShopConfVar('arr', 'aDisabledModules', $aDisabledModules);
                    $msg .= "Removed module '$moduleId' from aDisabledModules array with shop id: $sShopId<br/>";
                }
            }

            $aModulePaths = $cfg->getConfigParam('aModulePaths');
            if (array_key_exists($moduleId, $aModulePaths)) {
                if ($fixModule) {
                    unset($aModulePaths[$moduleId]);
                    $msg .= "<b>Removed module: $moduleId from 'aModulePaths' array with shop id: $sShopId!</b><br>";
                    $cfg->saveShopConfVar('arr', 'aModulePaths', $aModulePaths);
                }
            }
        }
        $headerStatus = "HTTP/1.1 200 Ok";
        $res = array("status" => $msg);
        $this->_sendJsonResponse($headerStatus, $res);
    }

    /**
     * Get a list of available modules
     */
    public function getModuleList()
    {
        $oConfig = oxRegistry::getConfig();
        /**
         * @var oxmodulelist $oModuleList
         */
        $oModuleList = oxNew('oxModuleList');
        $sModulesDir = $oConfig->getModulesDir();
        $aModules = $oModuleList->getModulesFromDir($sModulesDir);
        $aData = array();
        /**
         * @var int $id
         * @var oxModule $oModule
         */
        foreach ($aModules as $id => $oModule) {
            $aData[$id] = strip_tags($oModule->getTitle());
        }
        $headerStatus = "HTTP/1.1 200 Ok";
        $this->_sendJsonResponse($headerStatus, array("status" => "ok", "modules" => $aData));
    }

    /**
     * Activate a module in one or more shops
     * @param string $moduleId
     * @param string $sShopIds
     * @param bool   $deactivate
     */
    public function activateModule($moduleId, $sShopIds, $deactivate = false)
    {
        $aRet = $this->_iolyHelper->activateModule($moduleId, $sShopIds, $deactivate);
        $res = array("status" => $aRet['message']);
        $this->_sendJsonResponse($aRet['header'], $res);
    }

    /**
     * Send JSON response
     * @param string $headerStatus
     * @param string $res
     */
    protected function _sendJsonResponse($headerStatus, $res)
    {
        header("Content-Type: application/json");
        header($headerStatus);
        echo json_encode($res);
        die();

    }
}