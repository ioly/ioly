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
 * @version	 1.5.1
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
