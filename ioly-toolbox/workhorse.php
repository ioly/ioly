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
/**
 * Class Workhorse, works for the AJAX script :)
 */
class Workhorse
{
    /**
     * Generate views
     * @param array $aShopIds
     */
    public function generateViews($aShopIds)
    {
        $msg = "";
        $oShop = oxNew('oxShop');
        $oShop->generateViews();
        foreach ($aShopIds as $sShopId) {
            $oShop->load($sShopId);
            $msg .= "Generating views for ShopID $sShopId ...<br/>";
            $oShop->generateViews();
        }
        $headerStatus = "HTTP/1.1 200 Ok";
        $res = array("status" => $msg . "<br/>Views generated!");
        $this->_sendJsonResponse($headerStatus, $res);
    }

    /**
     * Clear tmp dir
     */
    public function emptyTmp()
    {
        $msg = "";
        $tmpdir = oxRegistry::getConfig()->getConfigParam('sCompileDir');
        $d = opendir($tmpdir);
        while (($filename = readdir($d)) !== false) {
            $filepath = $tmpdir . $filename;
            if (is_file($filepath)) {
                $msg .= "Deleting $filepath ...<br>";
                unlink($filepath);
            }
        }
        $headerStatus = "HTTP/1.1 200 Ok";
        $res = array("status" => "Tmp clean!!<br/>" . $msg);
        $this->_sendJsonResponse($headerStatus, $res);
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
     * @param array  $aShopIds
     * @param bool   $deactivate
     */
    public function activateModule($moduleId, $aShopIds, $deactivate = false)
    {
        $msg = "";

        $oConfig = oxRegistry::getConfig();
        /**
         * @var oxmodulelist $oModuleList
         */
        $oModuleList = oxNew('oxModuleList');
        $sModulesDir = $oConfig->getModulesDir();
        $aModules = $oModuleList->getModulesFromDir($sModulesDir);
        if (!in_array($moduleId, array_keys($aModules))) {
            $msg .= "module not found: $moduleId!<br/>";
        } else {
            if ($deactivate) {
                $msg .= "De-";
            }
            $msg .= "Activating module $moduleId for shop ids: " . implode(", ", $aShopIds) . "<br/>";
            foreach ($aShopIds as $sShopId) {
                // set shopId
                $oConfig->setShopId($sShopId);

                foreach ($aModules as $sModuleId => $oModule) {
                    if ($moduleId != $sModuleId) {
                        continue;
                    }
                    /**
                     * @var oxmodule $oModule
                     */
                    if (!$deactivate) {
                        if (!$oModule->isActive()) {
                            $msg .= "shopId [$sShopId]: activating module: $sModuleId<br/>";
                            try {
                                if (class_exists('oxModuleInstaller')) {
                                    /** @var oxModuleCache $oModuleCache */
                                    $oModuleCache = oxNew('oxModuleCache', $oModule);
                                    /** @var oxModuleInstaller $oModuleInstaller */
                                    $oModuleInstaller = oxNew('oxModuleInstaller', $oModuleCache);

                                    if ($oModuleInstaller->activate($oModule)) {
                                        $msg .= "$sModuleId - activated<br/>";
                                    } else {
                                        $msg .= "$sModuleId - error activating<br/>";
                                    }
                                } else {
                                    if ($oModule->activate()) {
                                        $msg .= "$sModuleId - aktivated<br/>";
                                    } else {
                                        $msg .= "$sModuleId - error activating<br/>";
                                    }
                                }
                            } catch (oxException $oEx) {
                                $msg .= $oEx->getMessage();
                            }
                        } else {
                            $msg .= "shopId [$sShopId]: module already active: $sModuleId<br/>";
                        }
                    } else { // deactivate!
                        if ($oModule->isActive()) {
                            $msg .= "shopId [$sShopId]: deactivating module: $sModuleId<br/>";
                            try {
                                if (class_exists('oxModuleInstaller')) {
                                    /** @var oxModuleCache $oModuleCache */
                                    $oModuleCache = oxNew('oxModuleCache', $oModule);
                                    /** @var oxModuleInstaller $oModuleInstaller */
                                    $oModuleInstaller = oxNew('oxModuleInstaller', $oModuleCache);

                                    if ($oModuleInstaller->deactivate($oModule)) {
                                        $msg .= "$sModuleId - deactivated<br/>";
                                    } else {
                                        $msg .= "$sModuleId - error deactivating<br/>";
                                    }
                                } else {
                                    if ($oModule->deactivate()) {
                                        $msg .= "$sModuleId - deactivated<br/>";
                                    } else {
                                        $msg .= "$sModuleId - error deactivating<br/>";
                                    }
                                }
                            } catch (oxException $oEx) {
                                $msg .= $oEx->getMessage();
                            }
                        } else {
                            $msg .= "shopId [$sShopId]: module already inactive: $sModuleId<br/>";
                        }
                    }
                }
            }
        }
        $headerStatus = "HTTP/1.1 200 Ok";
        $res = array("status" => $msg);
        $this->_sendJsonResponse($headerStatus, $res);
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