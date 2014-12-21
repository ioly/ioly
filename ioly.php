<?php
/**
 * ioly
 *
 * PHP version 5.3
 *
 * @category ioly_modulmanager
 * @package  Core
 * @author   Dave Holloway <dh@gn2-netwerk.de>
 * @author   Tobias Merkl <merkl@proudsourcing.de>
 * @author   Stefan Moises <stefan@rent-a-hero.de>
 * @license  MIT License http://opensource.org/licenses/MIT
 * @link     http://getioly.com/
 * @version	 1.7.0
 */
namespace ioly;

class ioly
{
    protected $_version = "1.7.0";
    
    protected $_baseDir = null;
    protected $_recipeCacheFile = null;
    protected $_recipeCache = array();
    protected $_digestCacheFile = null;
    protected $_digestCache = array();
    protected $_curlCallback = null;
    protected $_systemBasePath = null;
    protected $_systemVersion = null;
    protected $_debugLogging = true;
    protected $_cookbooks = array(
        array('ioly', 'http://github.com/ioly/ioly/archive/master.zip')
    );
    
    /**
     * Sets up file databases. Updates if the cache is empty.
     */
    public function __construct()
    {
        $tz = ini_get('date.timezone');
        if (!$tz) {
            $tz = 'Europe/Berlin';
        }
        date_default_timezone_set($tz);
        $this->_baseDir = $this->_dirName(__FILE__);
        $this->_recipeCacheFile = $this->_baseDir.'/.recipes.db';
        $this->_digestCacheFile = $this->_baseDir.'/.digest.db';
        $this->_cookbookCacheFile = $this->_baseDir.'/.cookbooks.db';
        $this->_authFile = $this->_baseDir.'/.auth';
        $this->_init();
        if (empty($this->_recipeCache)) {
            $this->update();
        }
    }

    /**
     * General init function. Used by the constructor to create caches.
     */
    public function _init()
    {
        if (file_exists($this->_cookbookCacheFile)) {
            $cache = unserialize(file_get_contents($this->_cookbookCacheFile));
            if (is_array($cache)) {
                $this->_cookbooks = $cache;
            }
        }
        if (file_exists($this->_recipeCacheFile)) {
            $cache = unserialize(file_get_contents($this->_recipeCacheFile));
            if (is_array($cache)) {
                $this->_recipeCache = $cache;
            }
        }
        if (file_exists($this->_digestCacheFile)) {
            $cache = unserialize(file_get_contents($this->_digestCacheFile));
            if (is_array($cache)) {
                $this->_digestCache = $cache;
            }
        }
    }

    /**
     * Sets base path to the PHP installation.
     * This is not where ioly is located, but the path to your
     * system installation
     * @param string $systemBasePath
     */
    public function setSystemBasePath($systemBasePath)
    {
        // path should not end with /  or \ so remove it
        if($this->_endsWith($systemBasePath, '/') || $this->_endsWith($systemBasePath, '\\')) {
            $systemBasePath = substr($systemBasePath, 0, strlen($systemBasePath)-1);
        }
        $this->_systemBasePath = $systemBasePath;
    }

    /**
     * Gets ioly core version
     * @return string version
     */
    public function getCoreVersion()
    {
        return $this->_version;
    }
  
    /**
     * Gets cookbook/s version
     * @return array
     */  
    public function getCookbookVersion()
    {
    	$aCookbooks = array();
    	foreach (glob($this->_baseDir.'/cookbook.*.zip') as $cookbookArchive)
    	{
            $aCookbook = explode("cookbook.", $cookbookArchive);
            $key = trim($aCookbook[1]);
            if(!empty($key) && strstr($key, ".zip"))
            {
            	$aCookbooks[$key] = sha1_file($cookbookArchive);
            }
        }
        return $aCookbooks;
    }
    
    /**
     * Gets base path to the PHP installation.
     * @return string Base Path
     */
    public function getSystemBasePath()
    {
        return $this->_systemBasePath;
    }

    /**
     * Defines an optional callback function used while
     * curl downloads from a remote server.
     * @param $function
     */
    public function setCurlCallback($function)
    {
        $this->_curlCallback = $function;
    }

    /**
     * (unused) Sets system version of PHP Installation.
     * @param string $systemVersion
     */
    public function setSystemVersion($systemVersion)
    {
        $this->_systemVersion = $systemVersion;
    }

    /**
     * Add a cookbook.
     * @param $key A unique identifier.
     * @param $url The url to the cookbook.
     */
    public function addCookbook($key, $url)
    {
    	if(!empty($key) && !empty($url)) {
            $this->_writeLog("Adding cookbook: $key => $url");
            $this->_cookbooks[] = array($key, $url);
            $this->update();
        }
    }
    /**
     * Add a cookbook.
     * @param $key A unique identifier.
     * @param $url The url to the cookbook.
     */
    public function removeCookbook($key)
    {
    	if(!empty($key)) {
            $zipFile = $this->_baseDir."/cookbook.{$key}.zip";
            $this->_writeLog("Removing cookbook: $key, file: " . $zipFile);
            foreach ($this->_cookbooks as $k=>$cookbook) {
                if ($cookbook[0] == $key) {
                    unset($this->_cookbooks[$k]);
                }
            }
            // remove file in any case
            if(file_exists($zipFile)) {
                @unlink($zipFile);
            }
            // and update
            $this->update();
        }
    }
    /**
     * Set the cookbooks to use.
     * @param array $aCookbooks
     */
    public function setCookbooks($cookbooks)
    {
        $newCookbooks = array();
        if (is_array($cookbooks)) {
            foreach ($cookbooks as $cookbook) {
                if (count($cookbook) == 2) {
                    $key = $cookbook[0];
                    $url = $cookbook[1];
                    $newCookbooks[] = array($key, $url);
                } 
            }
        }
        $this->_cookbooks = $newCookbooks;
        $this->update();

    }
    /**
     * Return a specific cookbook URL
     * @return string
     */
    public function getCookbook($key)
    {
        foreach ($this->_cookbooks as $cookbook) {
            if ($cookbook[0] == $key) {
                return $cookbook[1];
            }
        }
        return false;
    }

    /**
     * Gets the set version of the PHP Installation
     * @return string
     */
    public function getSystemVersion()
    {
        return $this->_systemVersion;
    }
    
    /**
     * Activate debug logging
     * @param boolean $writeLog
     */
    public function setDebugLogging($writeLog) {
        $this->_debugLogging = $writeLog;
    }

    /**
     * Gets a list of defined cookbooks.
     * @return array
     */
    protected function _getCookbooks()
    {
        return $this->_cookbooks;
    }

    /**
     * Searches through the loaded cookbooks
     * @param string $query Search string
     * @return array Associative array of results
     */
    public function search($query = null, $aFilter = array())
    {
        $results = array();
        if ($query !== null) {

            if (strpos($query, '/') !== false) {
                $parts = explode('/', $query);
                $vendor = $parts[0];
                $packageName = $parts[1];
            }
            foreach ($this->_recipeCache as $package) {
                // filter active?
                $filterRecipe = false;
                if($aFilter && is_array($aFilter) && count($aFilter) > 0) {
                    foreach($aFilter as $sKey => $sVal) {
                        if(isset($package[$sKey]) && $package[$sKey] != $sVal) {
                            $filterRecipe = true;
                            break;
                        }
                    }
                }
                // lowercase all tags and use them as keys for faster access
                $search_array = array_combine(array_map('strtolower', $package['tags']), $package['tags']);
                if ( !$filterRecipe  &&  ((stripos($package['name'], $query) !== false)
                      || (stripos($package['vendor'], $query) !== false)
                      || (stripos($package['_filename'], $query) !== false)
                      || !empty($search_array[strtolower($query)])
                      || (isset($vendor) && isset($packageName)
                          && $package['vendor'] == $vendor
                          && $package['_filename'] == $packageName)
                    )) {
                    $results[] = $package;
                }
            }
        }
        return $results;
    }

    /**
     * Lists all cached recipes
     * @return array
     */
    public function listAll($aFilter = array())
    {
        if($aFilter && is_array($aFilter) && count($aFilter) > 0) {
            $aFilteredPackages = array();
            foreach($this->_recipeCache as $package) {
                foreach($aFilter as $sKey => $sVal) {
                    if(isset($package[$sKey]) && $package[$sKey] != $sVal) {
                        continue;
                    }
                    $aFilteredPackages[] = $package;
                }
            }
            return $aFilteredPackages;
        }
        return $this->_recipeCache;
    }

    /**
     * Clear all cookbooks, removes zip files
     */
    public function clearCookbooks() {
        // clear downloaded cookbooks
        foreach (glob($this->_baseDir.'/cookbook.*.zip') as $cookbookArchive) {
            @unlink($cookbookArchive);
        }
        $this->_cookbooks = array();
    }
    
    /**
     * Updates the internal list of recipes
     */
    public function update()
    {
        foreach ($this->_getCookbooks() as $cookbook) {
            $repo = $cookbook[0];
            $url = $cookbook[1];
            $this->_writeLog("Trying to get recipe $repo from $url");
            $data = $this->_curlRequest($url, true);
            $fn = 'cookbook.'.$repo.'.zip';
            if ($data[0] != '') {
                $this->_writeLog("Successfully downloaded recipe $repo from $url");
                file_put_contents($this->_baseDir.'/'.$fn, $data[0]);
            }
        }
        file_put_contents($this->_cookbookCacheFile, serialize($this->_cookbooks));
        $this->_parseRecipes();
        $this->_init();
    }

    /**
     * Shows internal package
     * @param $packageString
     * @return array
     */
    public function show($packageString)
    {
        return $this->search($packageString);
    }

    /**
     * Checks to see if required variables have been set before
     * doing anything dangerous
     * @throws Exception
     */
    protected function _checkEnvironment()
    {
        if (!$this->_systemBasePath) {
            throw new Exception(
                "Please call \$ioly->setSystemBasePath(\$path);
                or run export IOLY_SYSTEM_BASE=%path%",
                1000
            );
        }
        if (!$this->_systemVersion) {
            throw new Exception(
                "Please call \$ioly->setSystemVersion(\$version);
                or run export IOLY_SYSTEM_VERSION=%version%",
                1001
            );
        }
    }

    /**
     * Installs a specific version of a given package
     * @param $packageString
     * @param $packageVersion
     * @return bool
     * @throws Exception
     */
    public function install($packageString, $packageVersion)
    {
        $this->_checkEnvironment();
        if (strpos($packageString, '/') !== false) {
            $results = $this->search($packageString);
            if (count($results) == 1) {
                $package = $results[0];
                if (array_key_exists($packageVersion, $package['versions'])) {
                    $version = $package['versions'][$packageVersion];
                    $filesystem = $this->_downloadPackage($version['url']);
                    if ($filesystem !== null) {
                        $filelist = $this->_copyToSystem($version, $filesystem);
                        $digestEntry = array();
                        $digestEntry['version'] = $packageVersion;
                        $digestEntry['files'] = $filelist;
                        $this->_digestCache[$packageString] = $digestEntry;
                        $this->_saveDigestCache();
                        $this->update();
                    }
                } else {
                    throw new Exception(
                        "Could not find package version: "
                        .$packageString."#".$packageVersion,
                        1003
                    );
                }
            } else {
                throw new Exception(
                    "Could not find package ".$packageString,
                    1005
                );
            }
        } else {
            throw new Exception(
                "Please use vendor/package to install a package",
                1006
            );
        }
        return true;
    }

    /**
     * Read a specific JSON / array value from a package
     * @param string $packageString
     * @param string $packageVersion
     * @param string $jsonKey
     * @return array
     */
    public function getJsonValueFromPackage($packageString, $packageVersion, $jsonKey) 
    {
        if (strpos($packageString, '/') !== false) {
            $results = $this->search($packageString);
            if (count($results) == 1) {
                $package = $results[0];
                if (array_key_exists($packageVersion, $package['versions'])) {
                    return $this->recursiveFind($package, $jsonKey);
                    
                }
            }
        }
    }

    /**
     * Iterate through an array recursively
     * @param array $array
     * @param string $needle
     * @return array
     */
    private function recursiveFind(array $array, $needle)
    {
        $iterator  = new \RecursiveArrayIterator($array);
        $recursive = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST);
        $aHitList = array();
        foreach ($recursive as $key => $value) {
            if ($key === $needle) {
                array_push($aHitList, $value);
            }
        }
        return $aHitList;
    }

    /**
     * Debug logging function, mainly for AJAX calls.
     * @param string $sLogMessage
     * @return int
     */
    protected function _writeLog($sLogMessage) {
        if($this->_debugLogging) {
            $sLogDist = dirname(__FILE__)."/ioly.log";
            if ( ( $oHandle = fopen( $sLogDist, 'a' ) ) !== false ) {
                $sLogMessage = "\n" . date('Y-m-d H:i:s') . " " . $sLogMessage;
                fwrite( $oHandle, $sLogMessage );
                $blOk = fclose( $oHandle );
            }  
        }
        return $blOk;
    }
    
    /**
     * Get a list of files for the installed package
     * @param string $packageString
     * @param string $versionString
     * @return array
     */
    public function getFileList($packageString, $versionString)
    {
        if (array_key_exists($packageString, $this->_digestCache)) {
            $digestVersion = $this->_digestCache[$packageString];
            if ($digestVersion['version'] == $versionString) {
                return $digestVersion['files'];
            }
        }
        return null;
    }
    
    /**
     * Uninstalls a version of a specific package
     * @param $packageString
     * @param $versionString
     * @throws Exception
     */
    public function uninstall($packageString, $versionString)
    {
        $msg = "\nuninstalling $packageString $versionString";
        if (array_key_exists($packageString, $this->_digestCache)) {
            $digestVersion = $this->_digestCache[$packageString];
            $msg .= "\nexisting in digest!";
            if ($digestVersion['version'] == $versionString) {
                $msg .= "\nmatching version in digest!";
                $filesToDelete = $digestVersion['files'];
                $blockedFiles = array();
                $modifiedFiles = array();
                $failedToDelete = array();
                $checkDeleteFolders = array();
                $msg .= "\nfiles to delete: " . print_r($filesToDelete, true);

                /* Check other cached digests for the same file */
                foreach ($this->_digestCache as $dgstPackageStr=>$dgstPackage) {
                    if ($dgstPackageStr != $packageString) {
                        foreach ($filesToDelete as $k=>$v) {
                            if (array_key_exists($k, $dgstPackage['files'])) {
                                $blockedFiles[] = $k;
                                unset($filesToDelete[$k]);
                            }
                        }
                    }
                }
                /* Check SHA1s and delete */
                foreach ($filesToDelete as $k=>$v) {
                    $deletePath = $this->getSystemBasePath().'/'.$k;
                    $msg .= "\ndeletePath: $deletePath";
                    $continue = true;
                    $delDir = $this->_dirName($deletePath);
                    /* Collect a list of directories to check for deletion */
                    while ($continue) {
                        if (!in_array($delDir, $checkDeleteFolders)) {
                            $checkDeleteFolders[] = $delDir;
                        }
                        if ($delDir == $this->getSystemBasePath()) {
                            $continue = false;
                            break;
                        }
                        // go up ....
                        $delDir = $this->_dirName($delDir);
                        $msg .= "\ndelDir: $delDir syspath: " . $this->getSystemBasePath();
                    }

                    if (file_exists($deletePath)
                        && sha1_file($deletePath) != $v) {
                        $modifiedFiles[] = $k;
                        unset($filesToDelete[$k]);
                    } else {
                        
                        if (file_exists($deletePath)) {
                            $msg .= "\ntrying to deletePath: $deletePath";
                            @unlink($deletePath);

                        }
                        if (file_exists($deletePath)) {
                            $msg .= "\nfailed delete: $deletePath";
                            $failedToDelete[] = $k;
                        }
                    }
                }

                usort($checkDeleteFolders, array($this, '_sortByFolderDepth'));
                foreach ($checkDeleteFolders as $deleteFolder) {
                    $msg .= "\nfolder to delete: $deleteFolder";
                    $pattern = $deleteFolder.'/{,.}*';
                    $remainingFiles = glob($pattern, GLOB_BRACE);
                    foreach ($remainingFiles as $k=>$v) {
                        $fn = basename($v);
                        if ($fn == '.' || $fn == '..' || $fn == '.DS_Store') {
                            unset($remainingFiles[$k]);
                        }
                    }
                    if (count($remainingFiles) == 0) {
                        $dsStore = $deleteFolder.'/.DS_Store';
                        if (file_exists($dsStore)) {
                            @unlink($dsStore);
                        }
                        @rmdir($deleteFolder);
                        $msg .= "\nfolder deleted: $deleteFolder";
                    }
                }

                if (!empty($failedToDelete)) {
                    $this->_writeLog("Failed to delete: " . print_r($failedToDelete));
                    $exception = new Exception(
                        "Module was uninstalled but the following ".
                        "files could not be deleted:\n"
                        .implode("\n", $failedToDelete),
                        1022
                    );
                    $exception->setExtraData(array($failedToDelete));
                    throw $exception;
                }

                if (!empty($modifiedFiles)) {
                    $this->_writeLog("Modified, failed to delete: " . print_r($failedToDelete));
                    $exception = new Exception(
                        "Module was uninstalled but the following ".
                        "files were modified and not deleted:\n"
                        .implode("\n", $modifiedFiles),
                        1022
                    );
                    $exception->setExtraData(array($modifiedFiles));
                    throw $exception;
                }

                foreach ($this->_digestCache as $dgstPackageStr=>$dgstPackage) {
                    if ($dgstPackageStr == $packageString) {
                        if ($dgstPackage['version'] == $versionString) {
                            $msg .= "\nRemoving $packageString, version $versionString from digest...";
                            unset($this->_digestCache[$dgstPackageStr]);
                        }
                    }
                }
                $msg .= "\nSaving digest...";
                $this->_saveDigestCache();
                $this->update();
                $this->_writeLog($msg);
            } else {
                throw new Exception(
                    "Could not find specified digest version of "
                    .$packageString,
                    1021
                );
            }
        } else {
            throw new Exception(
                "Could not find any digest version of ".trim($packageString),
                1020
            );
        }
        $this->_writeLog($msg);
    }

    /**
     * Checks if a package is installed
     * @param $packageString
     * @return bool
     */
    public function isInstalled($packageString)
    {
        if (array_key_exists($packageString, $this->_digestCache)) {
            return true;
        }
        return false;
    }
    
    /**
     * Checks if a package is installed in a specific version
     * @param string $packageString
     * @param string $versionString
     * @return boolean
     */
    public function isInstalledInVersion($packageString, $versionString) {
        if($this->isInstalled($packageString)) {
            $aDigestInfo = $this->_digestCache[$packageString];
            return $aDigestInfo['version'] == $versionString;
        }
    }

    /**
     * Always use / for directories, even on Windows
     * @param string $path
     * @return string The path with \ changed to /
     */
    protected function _dirName($path) {
        return str_replace("\\", "/", dirname($path));        
    }
    /**
     * Always use '/' for directories, even on Windows
     * @param string $path
     * @return string The path with '\' changed to '/'
     */
    protected function _endsWith($haystack, $needle) {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;        
    }
    
    /**
     * usort callback. Sorts a list of folders by depth.
     * Could probably be improved
     * @param $a
     * @param $b
     * @return int
     */
    protected function _sortByFolderDepth($a, $b)
    {
        return strlen($b)-strlen($a);
    }

    /**
     * Saves the digest cache to a file
     * @throws Exception
     */
    protected function _saveDigestCache()
    {
        @file_put_contents(
            $this->_digestCacheFile,
            serialize($this->_digestCache)
        );
        if (!is_readable($this->_digestCacheFile)) {
            $exception = new Exception(
                "Please check permissions on the following files/folders ".
                "(use \$e->getExtraData());\n"
                .$this->_digestCacheFile,
                1008
            );
            $exception->setExtraData(array($this->_digestCacheFile));
            throw $exception;
        }
    }

    /**
     * Downloads a given package to a temporary file
     * @param $url URL to Zip
     * @return null|string Zip Filename
     * @throws Exception
     */
    protected function _downloadPackage($url)
    {
        $tmpName = tempnam(sys_get_temp_dir(), 'IOLY_').'.zip';

        $data = $this->_curlRequest($url);
        if ($data[1] == 200) {
            if ($data[0] != '') {
                if (substr($url, -4) != ".zip") {
                    $this->_createPseudoZip($data[0], basename($url), $tmpName);
                } else {
                    file_put_contents($tmpName, $data[0]);
                }
                return $tmpName;
            }
        } else {
            throw new Exception(
                "Download server ".$url
                ." responded with an error. HTTP-Response: ".$data[1],
                1007
            );
        }
        return null;
    }

    /**
     * Creates a pseudo zip file if the downloaded file isn't a zip.
     * This is useful for snippets.
     **/
    protected function _createPseudoZip($content, $baseName, $zipName)
    {
        $zip = new \ZipArchive;
        $res = $zip->open($zipName, \ZipArchive::CREATE);
        if ($res === TRUE) {
            $zip->addFromString($baseName, $content);
            $zip->close();
        }
    }

    /**
     * Low level curl request
     * @param $url
     * @param bool $authenticate
     * @return array Headers + Body
     */
    protected function _curlRequest($url, $authenticate=false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($this->_curlCallback) {
            curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, $this->_curlCallback);
            curl_setopt($ch, CURLOPT_NOPROGRESS, false);
        }
        if ($authenticate && file_exists($this->_authFile)) {
            $auth = trim(file_get_contents($this->_authFile));
            curl_setopt($ch, CURLOPT_USERPWD, $auth);
        }
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $response = array($data, intVal($responseCode));
        return $response;
    }

    /**
     * Copies a package to the base filesystem.
     * @param $version
     * @param null $filesystem
     * @return array
     * @throws Exception
     */
    protected function _copyToSystem($version, $filesystem=null)
    {
        $filelist = array();
        if ($filesystem) {
            $tmpDir = tempnam(sys_get_temp_dir(), 'IOLY_');
            unlink($tmpDir);
            mkdir($tmpDir);
            $zip = new \ZipArchive();
            if ($zip->open($filesystem)) {
                $zip->extractTo($tmpDir);
                $zip->close();
            }
            if (     (strpos($version['url'], 'github.com') !== false)
                  || (strpos($version['url'], 'bitbucket.org') !== false)
                 ) {
                $dirs = glob($tmpDir.'/*', GLOB_ONLYDIR);
                if (count($dirs) == 1) {
                    $tmpDir = $dirs[0];
                }
            }
            if ($tmpDir != "") {
                $filelist = array();
                foreach ($version['mapping'] as $mapping) {
                    $src = $mapping['src'];
                    if ($src === '') {
                        $src = '.';
                    }
                    $src = $tmpDir.'/'.trim($src, '/');
                    $dest = $mapping['dest'];
                    $dest = $this->getSystemBasePath().'/'.$dest;
                    $this->_recursiveCopy($src, $dest, $filelist);
                }
                if (isset($version['touch'])) {
                    foreach ($version['touch'] as $file) {
                        $baseFile = $file;
                        $file = $this->getSystemBasePath().'/'.$baseFile;
                        if (!file_exists($file)) {
                            file_put_contents($file, "");
                            if (!is_readable($file)) {
                                $exception = new Exception(
                                    "Please check permissions on the ".
                                    "following files/folder ".
                                    "(use \$e->getExtraData());\n"
                                    .$file,
                                    1010
                                );
                                $exception->setExtraData($file);
                                throw $exception;
                            }
                        }
                        $filelist['/'.trim($baseFile, '/')] = sha1_file($file);
                    }
                }
            }
        }
        return $filelist;
    }


    /**
     * Recursively copies a file to a given folder
     * @param $source
     * @param $dest
     * @param array $filelist Array of copied files
     * @throws Exception Applicable errors
     */
    function _recursiveCopy($source, $dest, &$filelist=array())
    {
        if (is_dir($source)) {
            $pattern = $source.'/{,.}*';
            $files = glob($pattern, GLOB_BRACE);
        } else {
            $files = array($source);
        }

        $failedFiles = array();
        foreach ($files as $file) {
            $basename = basename($file);
            if ($basename != "." && $basename != "..") {
                $destpath = $dest.'/'.$basename;
                if (is_dir($file)) {
                    $this->_recursiveCopy(
                        $file,
                        $dest.'/'.basename($file),
                        $filelist
                    );
                } else {
                    $destdir = $this->_dirName($destpath);
                    if (!file_exists($destdir)) {
                        mkdir($destdir, 0777, true);
                    }
                    if (@copy($file, $destpath)) {
                        if (is_file($destpath)) {
                            $path = str_replace(
                                '//',
                                '/',
                                str_replace(
                                    $this->getSystemBasePath(),
                                    '',
                                    $destpath
                                )
                            );
                            $filelist[$path] = sha1_file($destpath);
                        }
                    } else {
                        $failedFiles[] = str_replace('//', '/', $destpath);
                    }
                }
            }
        }

        if (count($failedFiles) > 0) {
            $exception = new Exception(
                "Please check permissions on the following "
                ."files/folders (use \$e->getExtraData());\n"
                .implode($failedFiles, "\n"),
                1011
            );
            $exception->setExtraData($failedFiles);
            throw $exception;
        }
    }

    /**
     * Unzips and parses a cookbook repo zip file
     */
    protected function _parseRecipes()
    {
        $db = array();
        $cachedCookbooks = unserialize(file_get_contents($this->_cookbookCacheFile));
        foreach ($cachedCookbooks as $cookbook) {
            $cookbookArchive = $this->_baseDir.'/cookbook.'.$cookbook[0].'.zip'; 

            $tmpDir = tempnam(sys_get_temp_dir(), 'IOLY_');
            unlink($tmpDir);
            mkdir($tmpDir);
            $zip = new \ZipArchive();
            if ($zip->open($cookbookArchive)) {
                $zip->extractTo($tmpDir);
                $zip->close();
            }
            if (is_writable($tmpDir)) {
                $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($tmpDir));
                foreach ($files as $file) {
                    if (substr($file, -5) == '.json') {
                        $packageData = file_get_contents($file);
                        $package = json_decode($packageData, true);
                        if ($package !== null) {
                            $package['_cookbook'] = $cookbookArchive;
                            $package['_filename'] = substr(
                                basename($file),
                                0,
                                -5
                            );
                            $package['packageString'] =
                                basename($this->_dirName($file))
                                .'/'.$package['_filename'];
                            if ($this->isInstalled($package['packageString'])) {
                                $package['installed'] = true;
                            }
                            foreach($package['versions'] as $version => $versionData) {
                                if($this->isInstalledInVersion($package['packageString'], $version)) {
                                    $package['versions'][$version]['installed'] = true;
                                }
                                $matchingVersions = is_array($versionData['supported']) ? $versionData['supported'] : explode(",", trim($versionData['supported']));
                                if(count($matchingVersions)) {
                                    foreach($matchingVersions as $matchingVersion) {
                                        if($matchingVersion == $this->getSystemVersion()) {
                                            $package['versions'][$version]['matches'] = true;
                                            break;
                                        }
                                    }
                                }
                            }
                            $replacePackage = null;
                            foreach ($db as $k=>$cachedPackage) {
                                if ($cachedPackage['packageString'] == $package['packageString']) {
                                    $replacePackage = $k;
                                }
                            }
                            if ($replacePackage) {
                                $db[$replacePackage] = $package;
                            } else {
                                $db[] = $package;
                            }
                        }
                    }
                }
            }
            file_put_contents($this->_recipeCacheFile, serialize($db));
        }
    }
}

if (php_sapi_name() == 'cli') {
    try {
        $ioly = new ioly();
        if (isset($_SERVER['IOLY_SYSTEM_BASE']))
            $ioly->setSystemBasePath($_SERVER['IOLY_SYSTEM_BASE']);
        if (isset($_SERVER['IOLY_SYSTEM_VERSION']))
            $ioly->setSystemVersion($_SERVER['IOLY_SYSTEM_VERSION']);

        if (!isset($argv[1])) $argv[1] = 'help';

        switch (strtolower($argv[1])) {
            case "search":
                $query = isset($argv[2]) ? $argv[2] : null;
                $filter = isset($argv[3]) ? $argv[3] : null;
                $aFilter = array();
                if($filter !== null && strpos($filter, "=") !== FALSE) {
                    $aTmp = explode("=", $filter);
                    $aFilter = array($aTmp[0] => $aTmp[1]);
                }
                $results = $ioly->search($query, $aFilter);
                foreach ($results as $package) {
                    echo $package['vendor'].'/'.$package['_filename']."\n";
                }
                break;

            case "list":
                $filter = isset($argv[2]) ? $argv[2] : null;
                $aFilter = array();
                if($filter !== null && strpos($filter, "=") !== FALSE) {
                    $aTmp = explode("=", $filter);
                    $aFilter = array($aTmp[0] => $aTmp[1]);
                }
                $results = $ioly->listAll( $aFilter );
                foreach ($results as $package) {
                    echo $package['vendor'].'/'.$package['_filename']."\n";
                }
                break;

            case "update":
                $ioly->update();
                break;

            case "install":
                $package = isset($argv[2]) ? $argv[2] : null;
                $version = isset($argv[3]) ? $argv[3] : null;
                $ioly->install($package, $version);
                break;

            case "uninstall":
                $package = isset($argv[2]) ? $argv[2] : null;
                $version = isset($argv[3]) ? $argv[3] : null;
                $ioly->uninstall($package, $version);
                break;

            case "isinstalled":
                $package = isset($argv[2]) ? $argv[2] : null;
                return var_dump($ioly->isInstalled($package));
                break;


            case "addcookbook":
            	$cookbookKey= isset($argv[2]) ? $argv[2] : null;
                $cookbookUrl= isset($argv[3]) ? $argv[3] : null;
                $ioly->addCookbook($cookbookKey, $cookbookUrl);
                break;
            case "removecookbook":
            	$cookbookKey= isset($argv[2]) ? $argv[2] : null;
                $ioly->removeCookbook($cookbookKey);
                break;
            case "clearcookbooks":
                $ioly->clearCookbooks();
                break;

            case "show":
                $query = isset($argv[2]) ? $argv[2] : null;
                $results = $ioly->search($query);
                if (count($results) == 1) {
                    print_r($results[0]);
                }
                break;
            case "getjsonvalue":
                $package = isset($argv[2]) ? $argv[2] : null;
                $version = isset($argv[3]) ? $argv[3] : null;
                $key = isset($argv[4]) ? $argv[4] : null;
                $res = $ioly->getJsonValueFromPackage($package, $version, $key);
                if($res) {
                    print_r($res);
                }
                break;

            case "help":
            case "-v":
            case "--v":
            default:
                echo "ioly - Modulmanager\n";
                echo "===================\n\n";
                echo "Usage Examples:\n\n";

                echo "\tlist recipes with optional filter:\n";
                echo "\t php ioly.php list <filterkey=filtervalue>\n";
                echo "\t php ioly.php list vendor=gn2netwerk\n";
                echo "\t php ioly.php list\n\n";
                
                echo "\tupdate recipes:\n";
                echo "\t php ioly.php update\n\n";

                echo "\tsearch recipes with optional filter:\n";
                echo "\t php ioly.php search <term> <filterkey=filtervalue>\n";
                echo "\t php ioly.php search gn2\n";
                echo "\t php ioly.php search proudcommerce type=oxid\n";
                echo "\t php ioly.php search paypal\n\n";

                echo "\tshow recipe:\n";
                echo "\t php ioly.php show <vendor>/<package>\n";
                echo "\t php ioly.php show gn2netwerk/processor\n";
                echo "\t php ioly.php show proudcommerce/psarticlerequest\n\n";

                echo "\tinstall recipe:\n";
                echo "\t php ioly.php install <vendor>/<package> <version>\n";
                echo "\t php ioly.php install gn2netwerk/processor 1.0.0\n\n";

                echo "\tuninstall recipe:\n";
                echo "\t php ioly.php uninstall <vendor>/<package> <version>\n";
                echo "\t php ioly.php uninstall gn2netwerk/processor 1.0.0\n\n";
                
                echo "\tget JSON value from recipe:\n";
                echo "\t php ioly.php getjsonvalue <vendor>/<package> <version> <key>\n";
                echo "\t php ioly.php getjsonvalue gn2netwerk/processor 1.0.0 versions\n\n";
                
                echo "\tadd cookbook:\n";
                echo "\t php ioly.php addcookbook <key> <url>\n";
                echo "\t php ioly.php addcookbook ioly http://github.com/ioly/ioly/archive/master.zip\n\n";
                
                echo "\tremove cookbook:\n";
                echo "\t php ioly.php removecookbook <key>\n";
                echo "\t php ioly.php removecookbook ioly\n\n";
                
                echo "\tclear cookbooks:\n";
                echo "\t php ioly.php clearcookbooks\n";
                break;
        }
    } catch (Exception $e) {
        echo $e."\n";
    }
}

/**
 * Class Exception
 * @package ioly
 */
class Exception extends \Exception
{
    /**
     * @var mixed Any extra data the exception requires
     */
    protected $_extraData;

    /**
     * Gives extra information to the exception
     * @param mixed $extraData
     */
    public function setExtraData($extraData)
    {
        $this->_extraData = $extraData;
    }

    /**
     * Returns the extra data
     * @return mixed Extra data
     */
    public function getExtraData()
    {
        return $this->_extraData;
    }

    /**
     * Allows you to write echo $e to receive a debug message
     * @return string
     */
    public function __toString()
    {
        return "[".$this->getCode()."]: ".$this->getMessage();
    }
}
