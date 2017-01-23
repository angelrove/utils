<?
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 * 2006
 *
 * Ejem...
 *  $imageText = new ImageText('Texto de prueba', 20, 45, 'Santana-BlackCondensed.ttf', 21, array(254,224,51));
 *  $imageText->setBgImg('fondo2.png');
 *  $txtPos = $imageText->getTextBox();
 *  $imageText->setImageMerge('prueba.png', $txtPos[2]+12,24);
 *  $imageText->showImage();
 *
 */

namespace angelrove\utils\images;


class ImageText {
  private $width  = 250;
  private $height = 100;
  private $background = array(180, 180, 250);
  private $baseImg;

  private $text = 'Text';
  private $font = 'arial.ttf';
  private $fontSize = 18;
  private $fontColor = array(50, 50, 50);

  private $setSombra = false;
  private $imageMerge;

  //-----------------------------------------------------------
  function __construct($text, $posX, $posY, $font, $fontSize, $fontColor) {
    $this->text      = $text;
    $this->posX      = $posX;
    $this->posY      = $posY;
    $this->fontSize  = $fontSize;
    $this->fontColor = $fontColor;

    // fontpath ---
    //putenv('GDFONTPATH='.realpath('.'));  // puede estar deshabilitado
    $fontpath = __DIR__; // directorio actual
    $this->fontpath = $fontpath.'/'.$font;

    //----
    $this->txtPos = imageftbbox($this->fontSize, 0, $this->fontpath, $this->text);
  }
  //-----------------------------------------------------------
  // Seters
  //-----------------------------------------------------------
  // Imágen sobre la que se trabaja
  public function setBgImg($baseImg) {
    $this->image_type = exif_imagetype($baseImg);

    switch($this->image_type) {
       case IMAGETYPE_JPEG:
         $this->baseImg = imagecreatefromjpeg($baseImg);
       break;
       case IMAGETYPE_PNG:
         $this->baseImg = imagecreatefrompng($baseImg);
       break;
       default:
         trigger_error("setBgImg(): formato no válido", E_USER_ERROR);
         return false;
       break;
    }
  }
  //-----------------------------------------------------------
  public function setBgProperties($width, $height, $background) {
    $this->baseImg = imagecreatetruecolor($width, $height);

    $colorFondo = imagecolorallocate($this->baseImg, $background[0], $background[1], $background[2]);
    imagefilledrectangle($this->baseImg, 0, 0, $width, $height, $colorFondo);
  }
  //-----------------------------------------------------------
  //-----------------------------------------------------------
  public function setTransparencyText($transparency=80) {
    $this->setTransparencyText = $transparency;
  }
  //-----------------------------------------------------------
  public function setTextSombra($color, $distorsion) {
    $this->setSombra      = $color;
    $this->setSombra_dist = $distorsion;
  }
  //-----------------------------------------------------------
  public function setImageMerge($image, $margin_x, $margin_y) {
    $this->imageMerge_x = $margin_x;
    $this->imageMerge_y = $margin_y;
    $this->imageMerge = imagecreatefrompng($image);
  }
  //-----------------------------------------------------------
  // Geters
  //-----------------------------------------------------------
  public function getTextBox() {
    return $this->txtPos;
  }
  //-----------------------------------------------------------
  // Out
  public function showImage() {
    $this->getObjImage();

    /** Out image **/
     header("Content-type: image/png");
     header('Cache-Control: public');
     header('Expires: Thu, 15 Apr 2015 20:00:00 GMT');

     imagepng($this->baseImg);
     imagedestroy($this->baseImg);
  }
  //-----------------------------------------------------------
  public function setImage_file($file) {
    $this->getObjImage();

    /** Write image **/
     imagepng($this->baseImg, $file);
     imagedestroy($this->baseImg);
  }
  //-----------------------------------------------------------
  // PRIVATE
  //-----------------------------------------------------------
  private function getObjImage() {
    /** Image Merge **/
     if($this->imageMerge) {
        $pct = 100; // transparencia (0 to 100)

        // recorte
        $src_x = 0; // x-coordinate of source point
        $src_y = 0; // y-coordinate of source point
        $src_w = imagesx($this->imageMerge); // source width
        $src_h = imagesy($this->imageMerge); // source height

        imagecopymerge($this->baseImg, $this->imageMerge, $this->imageMerge_x, $this->imageMerge_y, $src_x, $src_y, $src_w, $src_h, $pct);
     }

    /** Sombra **/
     if($this->setSombra) {
        $posX = $this->posX + $this->setSombra_dist;
        $posY = $this->posY + $this->setSombra_dist;
        $colorTexto = imagecolorallocate($this->baseImg, $this->setSombra[0],  $this->setSombra[1],  $this->setSombra[2]);
        imagettftext($this->baseImg, $this->fontSize, 0, $posX, $posY, $colorTexto, $this->fontpath, $this->text);
     }

    /** Texto **/
     $colorTexto = imagecolorallocatealpha($this->baseImg,
                                           $this->fontColor[0],  $this->fontColor[1],  $this->fontColor[2],
                                           $this->setTransparencyText);
     imagettftext($this->baseImg, $this->fontSize, 0, $this->posX, $this->posY, $colorTexto, $this->fontpath, $this->text);
  }
  //-----------------------------------------------------------
}
