<?php
namespace angelrove\utils\Caching;

class CachingDel
{
    private $cacheDir;

    //-------------------------------------------------------------------
    public function __construct($cacheDir, $filePref = '')
    {
        $this->cacheDir = $cacheDir;
        $this->filePref = $filePref;
    }
    //-------------------------------------------------------------------
    public function beginDeleteCaching()
    {
        // Get requested page
        $requestedPage = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        // Get cache file name (MD5)
        $this->cacheFileName = $this->cacheDir . '/' . $this->filePref . md5($requestedPage) . '.cache';

        // Leer de cache
        if (file_exists($this->cacheFileName)) {
            unlink($this->cacheFileName);
            //echo "DEBUG: unlink: ".$this->cacheFileName;
        }
    }
    //-------------------------------------------------------------------
    public function endCaching()
    {
    }
    //-------------------------------------------------------------------
}
