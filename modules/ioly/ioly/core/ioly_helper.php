<?php

/**
 * @author smxsm
 * @package ioly-oxid-ce-487
 * 
 * Encoding: UTF-8
 * Date: 02.11.2014
 * 
 * Description of ioly_helper
 */
class ioly_helper  {

    /**
     * Return the path to the global lib dir
     * @param string $sModuleId
     * @param string $sVersion
     * @param string $sFileName
     * @return string
     */
    public function getIolyLibPath($sModuleId, $sVersion, $sFileName = '') {
        $sFilePath = oxRegistry::getConfig()->getCurrentShopUrl(false).'ioly/libs/' .$sModuleId . '/' . $sVersion;
        if($sFileName != '') {
            $sFilePath .= '/' . $sFileName;
        }
        return $sFilePath;
    }
    /**
     * Return the path to the ioly module
     * @param string $sFilePath
     * @return string
     */
    public function getIolyPath($sFilePath) {
        $sFilePath = oxRegistry::getConfig()->getCurrentShopUrl(false).'modules/ioly/ioly/' .$sFilePath;
        return $sFilePath;
    }
}
?>
