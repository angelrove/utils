<?
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 * 2015
 *
 * Example:
 *
 *   $caching = new Caching($cacheDir, $cacheTime);
 *   $caching->beginCaching();
 *
 *   // Para eliminar archivos de caché
 *   //$caching = new CachingDel('../_uploads/cache/directorio_prov/');
 *   //$caching->beginDeleteCaching();
 *   ...
 *   $caching->endCaching();
 */

namespace angelrove\utils\Caching;


class Caching
{
  // Params
  private $enabled = true;

  private $cacheDir;
  private $cacheTime;  // minutos
  private $filePref = '';
  private $setCompress = true;

  private $listInore = array(); // array('pruebano', 'prueba.com/search/');
  private $debug     = false;

  private $counterMode = ''; // 'R', 'W', 'RW'
  private $counterDir  = '';

  // Datos
  private $cacheFileName = '';

  //-------------------------------------------------------------------
  // Setters
  //-------------------------------------------------------------------
  // $cacheTime: nº de minutos
  function __construct($cacheDir, $cacheTime=1)
  {
    $this->cacheDir  = $cacheDir;
    $this->cacheTime = 60 * $cacheTime;
  }
  //-------------------------------------------------------------------
  public function set_compress($flag) {
    $this->setCompress = $flag;
  }
  //-------------------------------------------------------------------
  public function set_filePref($filePref) {
    $this->filePref = $filePref;
  }
  //-------------------------------------------------------------------
  public function setTime_days($cacheTime) {
    $one_day = 24 * (60 * 60);
    $this->cacheTime = $one_day * $cacheTime;
  }
  //-------------------------------------------------------------------
  public function startCounter($counterDir, $counterMode) {
    $this->counterDir  = $counterDir;
    $this->counterMode = $counterMode;
  }
  //-------------------------------------------------------------------
  public function showDebug() {
    $this->debug = true;
  }
  //-------------------------------------------------------------------
  //-------------------------------------------------------------------
  public function beginCaching($enabled=true)
  {
    $this->enabled = $enabled;
    if(!$this->enabled) {
       return;
    }

    // Get requested page
    $requestedPage = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    // if($this->debug) echo "<b>DEBUG</b> >> beginCaching(): requestedPage = '$requestedPage' <br>";

    // Get cache file name (MD5)
    $this->cacheFileName = $this->cacheDir.'/'. $this->filePref.md5($requestedPage) .'.cache';
    // print_r2("<b>DEBUG: Caching</b> >> cacheFileName: [$this->cacheFileName]"); exit();

    // Leer de cache
    if($this->isValidCacheFile($this->cacheFileName)) {
       $this->readCache();
       exit();
    }
    // Escribir cache
    else {
       ob_start();
       echo "<!-- Caching: requestedPage = '$requestedPage' -->";
    }
  }
  //-------------------------------------------------------------------
  // End: escribir en cache
  public function endCaching()
  {
    if(!$this->enabled) {
       return;
    }

    $this->writeCache();
  }
  //-------------------------------------------------------------------
  // PRIVATE
  //-------------------------------------------------------------------
  // Read / Write
  private function writeCache()
  {
    // Write file (comprimido)
    if($this->setCompress) {
       file_put_contents($this->cacheFileName, gzencode(ob_get_contents()));
    } else {
       file_put_contents($this->cacheFileName, ob_get_contents());
    }

    // OUT buffer
    ob_end_flush();

    // Counter
    if($this->counterMode == 'W' || $this->counterMode == 'RW') {
       $this->incrementCounter('W');
    }

    //---
    if($this->debug) {
       print_r2("<b>DEBUG: Caching.inc</b> >> writeCache(): file_put_contents('$this->cacheFileName')");
    }
    //---
  }
  //-------------------------------------------------------------------
  private function readCache()
  {
    // Read cache file
    if($this->setCompress) {
       header('Content-Encoding: gzip');
    }
    readfile($this->cacheFileName);

    // Counter
    if($this->counterMode == 'R' || $this->counterMode == 'RW') {
       $this->incrementCounter('R');
    }

    //---
    /*if($this->debug) {
       echo gzencode("<b>DEBUG: Caching.inc</b> >> readCache(): readfile('$this->cacheFileName')");
    }*/
    //---
  }
  //-------------------------------------------------------------------
  private function incrementCounter($R_W)
  {
    $fileCounter = $this->counterDir.'Catching_counter_'.$R_W.'.txt';

    $fp = fopen($fileCounter, 'r+');
    $num = fgets($fp);
    rewind($fp);
    fwrite($fp, $num + 1);
    fclose($fp);
  }
  //-------------------------------------------------------------------
  // Comprueba el archivo de caché: existe y no ha caducado
  private function isValidCacheFile($fileName)
  {
    // ¿Existe?
    if(!file_exists($fileName)) {
       return false;
    }

    // ¿Caducado?
    if($this->cacheTime == 0) {
       return true;
    }
    else {
       $timeCreated = filemtime($fileName);
       //@clearstatcache();

       if((time() - $this->cacheTime) < $timeCreated) {
          return true;
       } else {
          return false;
       }
    }
  }
  //-------------------------------------------------------------------

}
