<?php
/**
 * ioly
 *
 * PHP version 5.3
 *
 * @category ioly_modulmanager
 * @package  OXID Connector
 * @author   Dave Holloway <dh@gn2-netwerk.de>
 * @author   Tobias Merkl <merkl@proudsourcing.de>
 * @author   Stefan Moises <stefan@rent-a-hero.de>
 * @license  MIT License http://opensource.org/licenses/MIT
 * @link     http://getioly.com/
 * @version  1.8.0
 */
class ioly_helper extends oxSuperCfg
{
    /**
     * Return the path to the global lib dir
     * @param string $sModuleId
     * @param string $sVersion
     * @param string $sFileName
     * @return string
     */
    public function getIolyLibPath($sModuleId, $sVersion, $sFileName = '')
    {
        // fallback for old version
        $sFilePath = oxRegistry::getConfig()->getCurrentShopUrl(false).'ioly/libs/' .$sModuleId . '/' . $sVersion;
        if (!file_exists($sFilePath)) {
            $sFilePath = oxRegistry::getConfig()->getCurrentShopUrl(false).'modules/ioly/ioly/libs/' .$sModuleId . '/' . $sVersion;
        }
        if ($sFileName != '') {
            $sFilePath .= '/' . $sFileName;
        }
        return $sFilePath;
    }
    /**
     * Return the path to the ioly module
     * @param string $sFilePath
     * @return string
     */
    public function getIolyPath($sFilePath)
    {
        $sFilePath = oxRegistry::getConfig()->getCurrentShopUrl(false).'modules/ioly/ioly/' .$sFilePath;
        return $sFilePath;
    }

    /**
     * Get array of shop ids from a string
     * @param string $sShopIds
     *
     * @return array
     */
    public function getShopIdsFromString($sShopIds)
    {
        $aShopIds = array();
        if ($sShopIds == "all") {
            $aShopIds = oxRegistry::getConfig()->getShopIds();
        } elseif (strpos($sShopIds, ",") !== false) {
            $aShopIds = explode(",", $sShopIds);
        } else {
            // single shopid
            $aShopIds[] = $sShopIds;
        }
        return $aShopIds;
    }

    /**
     * Activate a module in one or more shops
     * @param string  $moduleId   The ID of the OXID module
     * @param string  $sShopIds
     * @param boolean $deactivate
     * @return array
     */
    public function activateModule($moduleId, $sShopIds, $deactivate = false)
    {
        $aShopIds = $this->getShopIdsFromString($sShopIds);

        $msg = "";

        $oConfig = oxRegistry::getConfig();
        /**
         * @var oxmodulelist $oModuleList
         */
        $oModuleList = oxNew('oxModuleList');
        $sModulesDir = $oConfig->getModulesDir();
        $aModules = $oModuleList->getModulesFromDir($sModulesDir);

        $headerStatus = "HTTP/1.1 200 Ok";

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
                                $headerStatus = "HTTP/1.1 500 Internal Server Error";
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
                                $headerStatus = "HTTP/1.1 500 Internal Server Error";
                                $msg .= $oEx->getMessage();
                            }
                        } else {
                            $msg .= "shopId [$sShopId]: module already inactive: $sModuleId<br/>";
                        }
                    }
                }
            }
        }
        return array("header" => $headerStatus, "message" => $msg);
    }
    /**
     * Generate views
     * @param array $aShopIds
     * @return array
     */
    public function generateViews($aShopIds)
    {
        if (!is_array($aShopIds)) {
            $aShopIds = $this->getShopIdsFromString($aShopIds);
        }
        $msg = "";
        $oShop = oxNew('oxShop');
        $oShop->generateViews();
        foreach ($aShopIds as $sShopId) {
            $oShop->load($sShopId);
            $msg .= "Generating views for ShopID $sShopId ...<br/>";
            $oShop->generateViews();
        }
        $msg .= "<br/>Views generated!";
        $headerStatus = "HTTP/1.1 200 Ok";
        return array("header" => $headerStatus, "message" => $msg);
    }

    /**
     * Clear tmp dir
     * @return array
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
        $msg .= "<br/>Tmp clean!!";
        return array("header" => $headerStatus, "message" => $msg);
    }
}
?>
