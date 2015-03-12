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
 * Interface for code-cache I/O
 *
 * @package PHPTAL
 */
interface PHPTAL_CodeCacheStorage
{
    public function initialize($cacheLifetime, $source, $encoding, $cacheId, $outputMode, $basePath, $phpCodeExtension);
    public function getFunctionName();
    public function getPath();

    /**
     * Put code to storage
     */
    public function put($code);
    
    /**
     * Get code from storage
     */
    public function loadFunction();
    public function cacheExists();
    public function cleanUpGarbage();
    public function cleanUpCache();
}
