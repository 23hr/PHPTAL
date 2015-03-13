<?php
/**
 * PHPTAL templating engine
 *
 * PHP Version 5
 *
 * @category HTML
 * @package  PHPTAL
 * @author Fumiharu Doi <fumiharu.doi@gmail.com>
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link     http://phptal.org/
 */


/**
 * Stores code-cache to filesystem
 *
 * @package PHPTAL
 */
class PHPTAL_FileCodeCacheStorage implements PHPTAL_CodeCacheStorage
{
    private $_cacheLifetime = 30;
	private $_source = null;
	private $_encoding = 'UTF-8';
	private $_cacheId = null;
	private $_outputMode = PHPTAL::XHTML;
	private $_basePath = '/tmp';
	private $_phpCodeExtension = 'php';

	private $_functionName;
	private $_path = '';
    
    public function initialize($cacheLifetime, $source, $encoding, $cacheId, $outputMode, $basePath, $phpCodeExtension)
    {
    	$this->_cacheLifetime = $cacheLifetime;
		$this->_source = $source;
		$this->_encoding = $encoding;
		$this->_cacheId = $cacheId;
		$this->_outputMode = $outputMode;
		$this->_basePath = rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		$this->_phpCodeExtension = $phpCodeExtension;

		/*
		 * generates function name
		 */
		$basename = preg_replace('/\.[a-z]{3,5}$/', '', basename($this->_source->getRealPath()));
		$basename = substr(trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $basename), "_"), 0, 20);
        $hash = md5(PHPTAL_VERSION . PHP_VERSION
                . $this->_source->getRealPath()
                . $this->_encoding
                . $this->_cacheId
                . $this->_outputMode,
                true
                );
        $hash = strtr(rtrim(base64_encode($hash),"="),"+/=","_A_");
        $this->_functionName = $this->getFunctionNamePrefix($this->_source->getLastModifiedTime()) .
                               $basename . '__' . $hash;

		$this->_path = $this->_basePath . $this->_functionName . '.' . $this->_phpCodeExtension;
    }

    /**
     * Returns prefix used for function name.
     * Function name is also base name for the template.
     *
     * @param int $timestamp unix timestamp with template modification date
     *
     * @return string
     */
    private function getFunctionNamePrefix($timestamp)
    {
        // tpl_ prefix and last modified time must not be changed,
        // because cache cleanup relies on that
    	return 'tpl_' . sprintf("%08x", $timestamp) .'_';
    }
    
    public function getFunctionName()
    {
    	return $this->_functionName;
    }
    
    public function getPath()
    {
    	return $this->_path;
    }
    
    /**
     * Store code to storage
     */
    public function store($code)
    {
    	return file_put_contents($this->_path, $code);
    }
    
    /**
     * call php require()
     */
    public function loadFunction()
    {
    	require $this->_path;
    }
    
    public function cacheExists()
    {
    	return file_exists($this->_path);
    }
    
    public function cleanUpGarbage()
    {
        $cacheFilesExpire = time() - $this->_cacheLifetime * 3600 * 24;

        // relies on templates sorting order being related to their modification dates
        $upperLimit = $this->_basePath . $this->getFunctionNamePrefix($cacheFilesExpire) . '_';
        $lowerLimit = $this->_basePath . $this->getFunctionNamePrefix(0);

        // second * gets phptal:cache
        $cacheFiles = glob($this->_basePath . 'tpl_????????_*.' . $this->_phpCodeExtension . '*');

        if ($cacheFiles) {
            foreach ($cacheFiles as $index => $file) {

                // comparison here skips filenames that are certainly too new
                if (strcmp($file, $upperLimit) <= 0 || substr($file, 0, strlen($lowerLimit)) === $lowerLimit) {
                    $time = filemtime($file);
                    if ($time && $time < $cacheFilesExpire) {
                        @unlink($file);
                    }
                }
            }
        }
    }
    
    public function cleanUpCache()
    {
        $filename = $this->getPath();
        $cacheFiles = glob($filename . '?*');
        if ($cacheFiles) {
            foreach ($cacheFiles as $file) {
                if (substr($file, 0, strlen($filename)) !== $filename) continue; // safety net
                @unlink($file);
            }
        }
    }
}
