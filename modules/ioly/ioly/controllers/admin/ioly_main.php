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
 * @version     1.9.1
 */
class ioly_main extends oxAdminView
{
    /**
     * Current class template name.
     * @var string
     */
    protected $_sThisTemplate = 'ioly_main.tpl';
    /**
     * URL to ioly core (ioly.php)
     * @var string
     */
    protected $_iolyCoreUrl = "https://raw.githubusercontent.com/ioly/ioly/core/ioly.php";

    protected $_iolyCore = null;
    protected $_authFile = null;
    /**
     * The ioly core
     * @var ioly\ioly
     */
    protected $_ioly = null;
    protected $_iolyHelper = null;
    protected $_allModules = null;
    protected $_currSortKey = '';
    /**
     * Required JS libs and versions
     * @var array
     */
    protected $_requiredJsLibs = array(
        "ioly/oxid-connector-js-libs" => "1.0.0"
    );
    /**
     * Filter ioly modules for OXID
     * @var array
     */
    protected $_iolyFilter = array('type' => 'oxid');

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_iolyCore = getShopBasePath() . '/modules/ioly/ioly/ioly.php';
        $this->_authFile = getShopBasePath() . '/modules/ioly/ioly/.auth';
        if ($this->_initIoly()) {
            $this->_checkForJsLibs();
        }
    }

    /**
     * Check if AngularJS, JQuery etc. are avaliable
     * If not, download them via ioly core :)
     */
    protected function _checkForJsLibs()
    {
        foreach ($this->_requiredJsLibs as $jsLib => $jsVersion) {
            if (!$this->_ioly->isInstalled($jsLib)) {
                try {
                    $this->_ioly->install($jsLib, $jsVersion);
                } catch (Exception $ex) {
                    $this->addTplParam("iolyerrorfatal", $ex->getMessage());
                }
            }
        }
    }

    /**
     * Gets ioly core if it doesn't exist.
     * @return boolean
     */
    protected function _initIoly()
    {
        if (!file_exists($this->_iolyCore)) {
            if (!$this->updateIoly()) {
                $this->addTplParam("iolyerrorfatal", oxRegistry::getLang()->translateString('IOLY_EXCEPTION_CORE_NOT_LOADED'));
            }
        }
        if (file_exists($this->_iolyCore)) {
            include_once $this->_iolyCore;
            $this->_ioly = new ioly\ioly();
            $this->_ioly->setSystemBasePath(oxRegistry::getConfig()->getConfigParam('sShopDir'));
            $this->_ioly->setSystemVersion($this->getShopMainVersion());
            return true;
        }
        return false;
    }

    /**
     * Set multiple cookbooks as defined in module settings.
     * @return null
     */
    protected function _setCookbooks()
    {
        if (($aCookbookUrls = oxRegistry::getConfig()->getConfigParam('iolycookbookurl')) && is_array($aCookbookUrls)) {
            // remove local zip files
            $this->_ioly->clearCookbooks();
            // and download new ones....
            $this->_ioly->setCookbooks($aCookbookUrls);
            return true;
        }
        return false;
    }

    /**
     * Allow module activation via ioly?
     * @return bool
     */
    public function allowActivation()
    {
        return oxRegistry::getConfig()->getShopConfVar('iolyenableinst');
    }

    /**
     * Get modules
     */
    public function getAllModules()
    {
        if ($this->_allModules === null) {
            $searchString = oxRegistry::getConfig()->getRequestParameter('searchstring');
            if ($searchString != '') {
                $allModules = $this->_ioly->search($searchString, $this->_iolyFilter);
                $this->addTplParam('searchstring', $searchString);
            } else {
                $allModules = $this->_ioly->listAll($this->_iolyFilter);
            }
            $this->_allModules = $allModules;
        }
    }

    /**
     * Returns all modules as JSON
     */
    public function getAllModulesAjax()
    {
        $page = oxRegistry::getConfig()->getRequestParameter('page');
        if (!$page && $page !== '0') {
            $page = 0;
        } else {
            $page = (int)$page;
        }
        $pageSize = (int)oxRegistry::getConfig()->getRequestParameter('pageSize');
        if (!$pageSize) {
            $pageSize = 20;
        } else {
            $pageSize = (int)$pageSize;
        }
        $offset = $page * $pageSize;
        $this->_currSortKey = oxRegistry::getConfig()->getRequestParameter('orderBy');
        $orderDir = oxRegistry::getConfig()->getRequestParameter('orderDir');
        // fill internal variable
        $this->getAllModules();
        $numItems = 0;
        $data = array();
        if ($this->_allModules && is_array($this->_allModules)) {

            // filter module list
            $onlyInstalled = (oxRegistry::getConfig()->getRequestParameter('onlyInstalled') == "true" || oxRegistry::getConfig()->getRequestParameter('onlyInstalled') == "1" ) ? true : false;
            $onlyActive = (oxRegistry::getConfig()->getRequestParameter('onlyActive') == "true" || oxRegistry::getConfig()->getRequestParameter('onlyActive') == "1") ? true : false;
            $this->_filterModules($onlyInstalled, $onlyActive);

            $numItems = count($this->_allModules);
            // sort by requested field
            if ($this->_currSortKey != '') {
                uasort($this->_allModules, array($this, 'cmpModules'));
            }
            // reverse order?
            if ($orderDir == "desc") {
                $this->_allModules = array_reverse($this->_allModules);
            }
            $data = array_slice($this->_allModules, $offset, $pageSize, false);
            // check if module is active?
            if (oxRegistry::getConfig()->getShopConfVar('iolycheckactive')) {
                foreach ($data as $idx => $aPackage) {
                    $aVersions = $aPackage['versions'];
                    $packageId = $aPackage['packageString'];
                    foreach ($aVersions as $packageVersion => $versionInfo) {
                        // if not even installed yet, continue!
                        if (!$this->_ioly->isInstalledInVersion($packageId, $packageVersion)) {
                            continue;
                        }
                        if ($this->isModuleActive($packageId, $packageVersion)) {
                            $aPackage['active'] = true;
                        }
                    }
                    $data[$idx] = $aPackage;
                }
            }
            $headerStatus = "HTTP/1.1 200 Ok";
        }
        $res = array(
            'numObjects' => $numItems,
            'result' => $data,
        );
        $this->_returnJsonResponse($headerStatus, $res);
    }

    /**
     * Filter modules
     * @param boolean $onlyInstalled Only show installed modules
     * @param boolean $onlyActive    Only show installed and active modules
     */
    protected function _filterModules($onlyInstalled = false, $onlyActive = true)
    {
        if ($onlyActive) {
            $onlyInstalled = true;
        }
        foreach ($this->_allModules as $idx => $aPackage) {
            $aVersions = $aPackage['versions'];
            $packageId = $aPackage['packageString'];
            $addModule = false;
            foreach ($aVersions as $packageVersion => $versionInfo) {
                if ($this->_ioly->isInstalledInVersion($packageId, $packageVersion)) {
                    $addModule = true;
                    if ($onlyActive) {
                        if ($this->isModuleActive($packageId, $packageVersion)) {
                            $addModule = true;
                            $aPackage['active'] = true;
                        } else {
                            $addModule = false;
                        }
                    }
                } else {
                    if (!$onlyInstalled) {
                        $addModule = true;
                    }
                }
            }
            if ($addModule) {
                $this->_allModules[$idx] = $aPackage;
            } else {
                unset($this->_allModules[$idx]);
            }
        }
    }

    /**
     * Comparator function to sort modules by name
     * @param string $a
     * @param string $b
     * @return int
     */
    public function cmpModules($a, $b)
    {
        if (strtolower($a[$this->_currSortKey]) == strtolower($b[$this->_currSortKey])) {
            return 0;
        }
        return (strtolower($a[$this->_currSortKey]) < strtolower($b[$this->_currSortKey])) ? -1 : 1;
    }

    /**
     * Update ioly via AJAX
     */
    public function updateIolyAjax()
    {
        if (!$this->updateIoly()) {
            $msg = oxRegistry::getLang()->translateString('IOLY_IOLY_UPDATE_ERROR');
            $headerStatus = "HTTP/1.1 500 Internal Server Error";
            $res = array("status" => 500, "message" => $msg);
        } else {
            $msg = oxRegistry::getLang()->translateString('IOLY_IOLY_UPDATE_SUCCESS');
            $headerStatus = "HTTP/1.1 200 Ok";
            $res = array("status" => $msg);
        }
        $this->_returnJsonResponse($headerStatus, $res);
    }

    /**
     * Update ioly via AJAX
     */
    public function updateRecipesAjax()
    {
        try {
            $this->_setCookbooks();
            $msg = oxRegistry::getLang()->translateString('IOLY_RECIPE_UPDATE_SUCCESS');
            $headerStatus = "HTTP/1.1 200 Ok";
            $res = array("status" => $msg);
        } catch (Exception $ex) {
            $msg = oxRegistry::getLang()->translateString('IOLY_RECIPE_UPDATE_ERROR') . $ex->getMessage();
            $headerStatus = "HTTP/1.1 500 Internal Server Error";
            $res = array("status" => 500, "message" => $msg);
        }
        $this->_returnJsonResponse($headerStatus, $res);
    }

    /**
     *
     * @param type $download_size
     * @param type $downloaded_size
     * @param type $upload_size
     * @param type $uploaded_size
     */
    public static function getCurlStatus($download_size, $downloaded_size, $upload_size, $uploaded_size)
    {
        static $previousProgress = 0;
        if ($download_size == 0) {
            $progress = 0;
        } else {
            $progress = round($downloaded_size * 100 / $download_size);
        }
        if ($progress > $previousProgress) {
            $aStatus = array("progress" => $progress, "download_size" => $download_size, "downloaded_size" => $downloaded_size);
            oxRegistry::getSession()->setVariable('iolyDownloadStatus', $aStatus);
        }
    }

    /**
     * Read CURL download status from session via AJAX
     */
    public function getCurlStatusAjax()
    {
        $aStatus = oxRegistry::getSession()->getVariable('iolyDownloadStatus');
        if ($aStatus && $aStatus != '') {
            $headerStatus = "HTTP/1.1 200 Ok";
            $res = array("status" => $aStatus);
            $this->_returnJsonResponse($headerStatus, $res);
        }
    }

    /**
     * Read "hooks" data from module package
     * to get preinstall and postinstall hooks
     */
    public function getModuleHooksAjax()
    {
        $moduleId = strtolower(urldecode(oxRegistry::getConfig()->getRequestParameter('moduleid')));
        $moduleVersion = oxRegistry::getConfig()->getRequestParameter('moduleversion');
        try {
            $value = $this->_ioly->getJsonValueFromPackage($moduleId, $moduleVersion, "hooks");
            $headerStatus = "HTTP/1.1 200 Ok";
            $res = array("status" => $value);
        } catch (Exception $ex) {
            $headerStatus = "HTTP/1.1 500 Internal Server Error";
            $res = array("status" => 500, "message" => $this->_getIolyErrorMsg($ex));
        }
        $this->_returnJsonResponse($headerStatus, $res);
    }

    /**
     * Download module via AJAX
     */
    public function downloadModuleAjax()
    {
        // reset status
        $moduleId = strtolower(urldecode(oxRegistry::getConfig()->getRequestParameter('moduleid')));
        $moduleVersion = oxRegistry::getConfig()->getRequestParameter('moduleversion');
        try {
            $this->_ioly->setCurlCallback(array($this, "getCurlStatus"));
            oxRegistry::getSession()->deleteVariable('iolyDownloadStatus');
            $success = $this->_ioly->install($moduleId, $moduleVersion);
            $headerStatus = "HTTP/1.1 200 Ok";
            $res = array("status" => $success);
        } catch (Exception $ex) {
            $headerStatus = "HTTP/1.1 500 Internal Server Error";
            $res = array("status" => 500, "message" => $this->_getIolyErrorMsg($ex));
        }
        $this->_returnJsonResponse($headerStatus, $res);
    }

    /**
     *
     * @param Exception $ex
     * @return string
     */
    protected function _getIolyErrorMsg($ex)
    {
        if (get_class($ex) == "ioly\Exception") {
            $sLangCode = "IOLY_EXCEPTION_MESSAGE_CODE_" . $ex->getCode();
            if (($sLang = oxRegistry::getLang()->translateString($sLangCode)) != $sLangCode) {
                $sMsg = $sLang;
            } else {
                $sMsg = $ex->getMessage();
            }
            $aData = $ex->getExtraData();
            if (count($aData)) {
                $sMsg .= " " . implode("\n", $aData);
            }
            return $sMsg;
        }
        return $ex->getMessage();
    }

    /**
     * Get subshop IDs where the module is active
     */
    public function getActiveModuleSubshopsAjax()
    {
        $moduleId = oxRegistry::getConfig()->getRequestParameter("moduleid");
        $moduleVersion = oxRegistry::getConfig()->getRequestParameter("moduleversion");
        if (strpos($moduleId, "/") !== false) {
            $moduleId = $this->getModuleOxid($moduleId, $moduleVersion);
        }
        $aSubshopsActive = $this->getActiveModuleSubshops($moduleId);
        $headerStatus = "HTTP/1.1 200 Ok";
        $res = array("status" => "ok", "subshops" => $aSubshopsActive);
        $this->_returnJsonResponse($headerStatus, $res);
    }

    /**
     * Get subshop IDs where the module is active
     * @param string $moduleOxid The module OXID
     *
     * @return array
     */
    public function getActiveModuleSubshops($moduleOxid)
    {
        $aSubshopsActive = array();
        $oConfig = oxRegistry::getConfig();
        $aShopIds = oxRegistry::getConfig()->getShopIds();
        /**
         * @var oxmodulelist $oModuleList
         */
        $oModuleList = oxNew('oxModuleList');
        $sModulesDir = $oConfig->getModulesDir();
        $aModules = $oModuleList->getModulesFromDir($sModulesDir);
        if (in_array($moduleOxid, array_keys($aModules))) {
            foreach ($aShopIds as $sShopId) {
                // set shopId
                $oConfig->setShopId($sShopId);
                /**
                 * @var oxmodule $oModule
                 */
                $oModule = oxNew('oxModule');
                if ($oModule->load($moduleOxid) && $oModule->isActive()) {
                    $aSubshopsActive[] = $sShopId;
                }
            }
        }
        return $aSubshopsActive;
    }

    /**
     * Activate a module in one or more shops
     */
    public function activateModuleAjax()
    {
        $helper = $this->getIolyHelper();
        $sShopIds = oxRegistry::getConfig()->getRequestParameter("shopids");
        $moduleId = oxRegistry::getConfig()->getRequestParameter("moduleid");
        $moduleVersion = oxRegistry::getConfig()->getRequestParameter("moduleversion");
        $deactivate = oxRegistry::getConfig()->getRequestParameter("deactivate");
        if (strpos($moduleId, "/") !== false) {
            $moduleId = $this->getModuleOxid($moduleId, $moduleVersion);
        }
        $aRet = $helper->activateModule($moduleId, $sShopIds, $deactivate);
        $res = array("status" => $aRet['message']);
        $this->_returnJsonResponse($aRet['header'], $res);
    }

    /**
     * Generate views
     */
    public function generateViewsAjax()
    {
        $sShopIds = oxRegistry::getConfig()->getRequestParameter("shopIds");
        $helper = $this->getIolyHelper();
        $aRet = $helper->generateViews($sShopIds);
        $res = array("status" => $aRet['message']);
        $this->_returnJsonResponse($aRet['header'], $res);
    }

    /**
     * Clear tmp dir
     */
    public function emptyTmpAjax()
    {
        $helper = $this->getIolyHelper();
        $aRet = $helper->emptyTmp();
        $res = array("status" => $aRet['message']);
        $this->_returnJsonResponse($aRet['header'], $res);
    }

    /**
     * Uninstall module via AJAX
     */
    public function removeModuleAjax()
    {
        $moduleId = strtolower(urldecode(oxRegistry::getConfig()->getRequestParameter('moduleid')));
        $moduleVersion = oxRegistry::getConfig()->getRequestParameter('moduleversion');
        try {
            // check if the module is still activated!
            $isStillActive = $this->isModuleActive($moduleId, $moduleVersion);
            if (!$isStillActive) {
                $success = $this->_ioly->uninstall($moduleId, $moduleVersion);
                $headerStatus = "HTTP/1.1 200 Ok";
                $res = array("status" => $success);
            } else {
                $headerStatus = "HTTP/1.1 412 Precondition Failed";
                $res = array("status" => 412, "message" => oxRegistry::getLang()->translateString('IOLY_EXCEPTION_MESSAGE_MODULE_ACTIVE'));
            }
        } catch (Exception $ex) {
            $headerStatus = "HTTP/1.1 500 Internal Server Error";
            $res = array("status" => 500, "message" => $this->_getIolyErrorMsg($ex));
        }
        $this->_returnJsonResponse($headerStatus, $res);
    }

    /**
     * Check if an installed module is still active in the shop
     * and should not be removed!
     * @param string $moduleId
     * @param string $moduleVersion
     * @return boolean
     */
    public function isModuleActive($moduleId, $moduleVersion)
    {
        $moduleOxid = $this->getModuleOxid($moduleId, $moduleVersion);
        return (count($this->getActiveModuleSubshops($moduleOxid)) > 0) ? true : false;
    }

    /**
     * Check if an installed module is still active in the shop
     * and should not be removed!
     */
    public function isModuleActiveAjax()
    {
        $moduleId = oxRegistry::getConfig()->getRequestParameter("moduleid");
        $moduleVersion = oxRegistry::getConfig()->getRequestParameter("moduleversion");
        $headerStatus = "HTTP/1.1 200 Ok";
        $active = $this->isModuleActive($moduleId, $moduleVersion);
        $res = array("status" => 1, "active" => $active);
        $this->_returnJsonResponse($headerStatus, $res);
    }

    /**
     * Get OXID ID of an installed module
     * Reads metadata.php since we don't have that info in the JSON files.
     * @param string $moduleId
     * @param string $moduleVersion
     * @return string
     */
    public function getModuleOxid($moduleId, $moduleVersion)
    {
        $aFiles = $this->_ioly->getFileList($moduleId, $moduleVersion);
        if ($aFiles && is_array($aFiles)) {
            foreach (array_keys($aFiles) as $filePath) {
                if (strpos($filePath, "metadata.php") !== false && strpos($filePath, "metadata.php.") === false) {
                    $metaPath = oxRegistry::getConfig()->getConfigParam('sShopDir') . $filePath;
                    if (file_exists($metaPath)) {
                        include $metaPath;
                        return $aModule['id'];
                    }
                }
            }
        }
        return null;
    }

    /**
     * Return JSON and exit
     * @param string $headerStatus
     * @param array  $res
     */
    protected function _returnJsonResponse($headerStatus, $res)
    {
        header("Content-Type: application/json");
        header($headerStatus);
        echo json_encode($res);
        die();
    }

    /**
     * Return our helper class for the view
     * @return ioly_helper
     */
    public function getIolyHelper()
    {
        if ($this->_iolyHelper === null) {
            $this->_iolyHelper = oxRegistry::get('ioly_helper');
        }
        return $this->_iolyHelper;
    }

    /**
     * Get session id
     * @return string
     */
    public function getSessionId()
    {
        if (($oSession = oxRegistry::getSession()) != null) {
            return $oSession->getId();
        }
        return "";
    }

    /**
     * Get session token
     * @return string
     */
    public function getSessionChallengeToken()
    {
        return oxRegistry::getSession()->getSessionChallengeToken();
    }

    /**
     * Return the shop version string
     * @return string
     */
    public function getShopMainVersion()
    {
        return substr(oxRegistry::getConfig()->getVersion(), 0, 3);
    }

    /**
     * Update ioly lib
     * @return boolean
     */
    public function updateIoly()
    {
        $core = $this->_iolyCoreUrl;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $core);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $data = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $response = array($data, intVal($responseCode));
        if ($data && @file_put_contents($this->_iolyCore, $data)) {
            return $response;
        }
        return false;
    }

    /**
     * Gets ioly OXID connector version
     *
     * @return string
     */
    public function getModuleVersion()
    {
        $sMetaDataPath = oxRegistry::getConfig()->getConfigParam("sShopDir") . "modules/ioly/ioly/metadata.php";
        include $sMetaDataPath;
        return $aModule["version"];
    }

    /**
     * Gets ioly core version
     *
     * @return string
     */
    public function getIolyCoreVersion()
    {
        return $this->_ioly->getCoreVersion();
    }

    /**
     * Gets ioly cookbooks version
     *
     * @return string
     */
    public function getIolyCookbookVersion()
    {
        return $this->_ioly->getCookbookVersion();
    }

    /**
     * Auto-uodate everything on load?
     */
    public function iolyAutoUpdate()
    {
        $message_success = $message_error = '';
        if (oxRegistry::getConfig()->getConfigParam('iolyautoupdate') == true) {
            if ($this->updateIoly()) {
                $message_success = oxRegistry::getLang()->translateString('IOLY_IOLY_UPDATE_SUCCESS') . '<br>';
            } else {
                $message_error = oxRegistry::getLang()->translateString('IOLY_EXCEPTION_CORE_NOT_LOADED') . '<br>';
            }

            if ($this->_setCookbooks()) {
                $message_success .= oxRegistry::getLang()->translateString('IOLY_RECIPE_UPDATE_SUCCESS') . '<br>';
            } else {
                $message_error .= oxRegistry::getLang()->translateString('IOLY_EXCEPTION_CORE_NOT_LOADED') . '<br>';
            }

            try {
                $this->_ioly->setCurlCallback(array($this, "getCurlStatus"));
                oxRegistry::getSession()->deleteVariable('iolyDownloadStatus');
                $success = $this->_ioly->install("ioly/ioly-oxid-connector", "latest");
                $message_success .= oxRegistry::getLang()->translateString('IOLY_CONNECTOR_UPDATE_SUCCESS') . '<br>';
                $res = array("status" => $success);
            } catch (Exception $ex) {
                $message_error .= oxRegistry::getLang()->translateString('IOLY_CONNECTOR_UPDATE_ERROR') . '<br>' . $this->_getIolyErrorMsg($ex) . '<br>';
            }

            $this->addTplParam("iolymsg", $message_success);
            $this->addTplParam("iolyerror", $message_error);
        }
    }

    /**
     * Executes parent method parent::render(), passes shop configuration parameters
     * to Smarty and returns name of template file "shop_config.tpl".
     *
     * @return string
     */
    public function render()
    {
        parent::render();

        $this->iolyAutoUpdate();
        // add curr language abbrevation to the template
        $iLang = oxRegistry::getLang()->getTplLanguage();
        $sLang = oxRegistry::getLang()->getLanguageAbbr($iLang);
        $this->addTplParam("langabbrev", $sLang);
        // ajax call?
        $isAjax = oxRegistry::getConfig()->getRequestParameter('isajax');
        if ($isAjax) {
            die("ajax");
        }

        return $this->_sThisTemplate;
    }
}

?>
