<?php
/**
 * ioly
 *
 * PHP version 5.3
 *
 * @category Ioly_Modulmanager
 * @package  Core
 * @author   Dave Holloway <dh@gn2-netwerk.de>
 * @author   Tobias Merkl <merkl@proudsourcing.de>
 * @author   Stefan Moises <stefan@rent-a-hero.de>
 * @license  MIT License http://opensource.org/licenses/MIT
 * @link     http://getioly.com/
 */
namespace ioly;

class ioly
{
    protected $_baseDir = null;
    protected $_recipeCacheFile = null;
    protected $_recipeCache = array();
    protected $_digestCacheFile = null;
    protected $_digestCache = array();
    protected $_curlCallback = null;
    protected $_systemBasePath = null;
    protected $_systemVersion = null;
    protected $_debugLogging = false;
    protected $_cookbooks = array(
        'ioly' => 'http://github.com/ioly/ioly/archive/master.zip'
    );

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
     * Overwrites the default cookbook with a custom one.
     * Useful when testing merge-contributions
     * @param $url
     */
    public function setCookbook($url)
    {
        $this->_cookbooks['ioly'] = $url;
        $this->update();
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
     * Sets up file databases. Updates if the cache is empty.
     */
    public function __construct()
    {
        $this->_baseDir = $this->_dirName(__FILE__);
        $this->_recipeCacheFile = $this->_baseDir.'/.recipes.db';
        $this->_digestCacheFile = $this->_baseDir.'/.digest.db';
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
    public function search($query = null)
    {
        $results = array();
        if ($query !== null) {

            if (strpos($query, '/') !== false) {
                $parts = explode('/', $query);
                $vendor = $parts[0];
                $packageName = $parts[1];
            }
            foreach ($this->_recipeCache as $package) {
                if (     (strpos($package['name'], $query) !== false)
                      || (strpos($package['vendor'], $query) !== false)
                      || (strpos($package['_filename'], $query) !== false)
                      || (in_array($query, $package['tags']))
                      || (isset($vendor) && isset($packageName)
                          && $package['vendor'] == $vendor
                          && $package['_filename'] == $packageName)
                    ) {
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
    public function listAll()
    {
        return $this->_recipeCache;
    }


    /**
     * Updates the internal list of recipes
     */
    public function update()
    {
        foreach ($this->_getCookbooks() as $repo=>$url) {
            $data = $this->_curlRequest($url, true);
            $fn = 'cookbook.'.$repo.'.zip';
            if ($data[0] != '') {
                file_put_contents($this->_baseDir.'/'.$fn, $data[0]);
                $this->_parseRecipes();
            }
        }
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
     * Debug logging function, mainly for AJAX calls.
     * @param string $sLogMessage
     * @return int
     */
    protected function _writeLog($sLogMessage) {
        if($this->_debugLogging) {
            $sLogDist = $this->getSystemBasePath()."/log/ioly_core.log";
            if ( ( $oHandle = fopen( $sLogDist, 'a' ) ) !== false ) {
                fwrite( $oHandle, $sLogMessage );
                $blOk = fclose( $oHandle );
            }  
        }
        return $blOk;
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
                        $delDir = $this->_dirName($delDir);
                        $msg .= "\ndelDir: $delDir syspath: " . $this->getSystemBasePath();
                        
                        if ($delDir == $this->getSystemBasePath()) {
                            $continue = false;
                        }
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
                    $this->_writeLog("\nFailed to delete: " . print_r($failedToDelete));
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
                    $this->_writeLog("\nModified, failed to delete: " . print_r($failedToDelete));
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
                file_put_contents($tmpName, $data[0]);
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
        foreach (glob($this->_baseDir.'/cookbook.*.zip') as $cookbookArchive) {
            $cookbook = new \PharData($cookbookArchive, 0);
            if ($cookbook) {
                $files = new \RecursiveIteratorIterator($cookbook);
                $db = array();
                foreach ($files as $file) {
                    if (substr($file, -5) == '.json') {
                        $packageData = file_get_contents($file);
                        $package = json_decode($packageData, true);
                        if ($package !== null) {
                            //TODO: sanity checks? is json valid?....
                            //TODO: minimum 1 valid version???
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
                            $db[] = $package;
                        }
                    }
                }
                file_put_contents($this->_recipeCacheFile, serialize($db));
            }
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
                $results = $ioly->search($query);
                foreach ($results as $package) {
                    echo $package['vendor'].'/'.$package['_filename']."\n";
                }
                break;

            case "list":
                $results = $ioly->listAll();
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


            case "setcookbookurl":
                $cookbookUrl= isset($argv[2]) ? $argv[2] : null;
                $ioly->setCookbook($cookbookUrl);
                break;

            case "show":
                $query = isset($argv[2]) ? $argv[2] : null;
                $results = $ioly->search($query);
                if (count($results) == 1) {
                    print_r($results[0]);
                }
                break;

            case "help":
            case "-v":
            case "--v":
            default:
                echo "ioly - Modulmanager\n";
                echo "===================\n\n";
                echo "Usage Examples:\n\n";

                echo "\tupdate recipes:\n";
                echo "\t\t php ioly.php update\n\n";

                echo "\tsearch recipes:\n";
                echo "\t php ioly.php search <term>\n";
                echo "\t php ioly.php search gn2\n";
                echo "\t php ioly.php search proudcommerce\n";
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
