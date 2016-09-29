<?
$base_path = './_uploads/';

/** Evitar HotLink **/
 if(isRemoteIp($_SERVER['REMOTE_ADDR'])) {
    $setError = 'getFoto.php: intento de acceso remoto';
    require('404/404.php');
 }

/** xxxx **/
 readfile($base_path.'usuarios_foto_'.$_GET['id'].'.jpg');

//---------------------------------------------------
function isRemoteIp($ip) {
  $localIps = array('127.0.0.1',
                    '111.168',
                    );

  foreach($localIps as $localIp) {
     if(strstr($ip, $localIp)) return false;
  }

  return true;
}
//---------------------------------------------------
?>
