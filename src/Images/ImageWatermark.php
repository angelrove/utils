<?
/**
 * ImageWatermark
 */

namespace angelrove\utils\images;


class ImageWatermark {
  //----------------------------------------------------------
  static function show($image, $text) {
    $imageText = self::getImg($image, $text);
    $imageText->showImage();
  }
  //----------------------------------------------------------
  static function updateImg($image, $text) {
    $imageText = self::getImg($image, $text);
    $imageText->setImage_file($image);
  }
  //----------------------------------------------------------
  static private function getImg($image, $text) {
    // Params----
    $tx_font = 'arial.ttf';
    $tx_fontColor = array(180,180,180);

    list($img_width, $img_height, $tipo, $atributos) = getimagesize($image);

    $tx_posX = ($img_width  * 5) / 100;
    $tx_posY = $img_height - (($img_height * 8) / 100);

    $tx_fontSize = ($img_width > 200)? 28 : 16;

    //----
    $imageText = new ImageText($text, $tx_posX, $tx_posY, $tx_font, $tx_fontSize, $tx_fontColor);
    $imageText->setBgImg($image);
    $imageText->setTransparencyText();
    return $imageText;
  }
  //----------------------------------------------------------
}
