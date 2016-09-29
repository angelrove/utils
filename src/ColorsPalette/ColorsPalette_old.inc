<?
/**
*
*/

class ColorsPalette
{
   //----------------------------------------------------------------
   function __construct()
   {

   }
   //----------------------------------------------------------------
   /* >> 1 */
   //----------------------------------------------------------------
   function get_colorsPalette($hexColor)
   {
     $colors = array(
        'primary' => '0',
        'light'   => '0',
        'light2'  => '0',
        'dark'    => '0',
     );

     return $colors;
   }
   //----------------------------------------------------------------
   /* >> 2 */
   //----------------------------------------------------------------
   /**
   * Example of use of percent2Color function
   * @author Dan M
   * @date 06/24/2010
   *
   * This function takes a value and returns an RGB color between Red to Yellow to Green in a given spectrum.
   * 50% = FFFF00
   *
   * @param mixed $value required
   * @param mixed $brightness value between 1 and 255
   * @param mixed $max default 100
   * @param mixed $min default 0
   * @param mixed $thirdColorHex '00'
   */
   function percent2Color($value, $brightness = 255, $max = 100, $min = 0, $thirdColorHex = '00')
   {
       // Calculate first and second color (Inverse relationship)
       $first = (1-($value/$max))*$brightness;
       $second = ($value/$max)*$brightness;

       // Find the influence of the middle color (yellow if 1st and 2nd are red and green)
       $diff = abs($first-$second);
       $influence = ($brightness-$diff)/2;
       $first = intval($first + $influence);
       $second = intval($second + $influence);

       // Convert to HEX, format and return
       $firstHex = str_pad(dechex($first),2,0,STR_PAD_LEFT);
       $secondHex = str_pad(dechex($second),2,0,STR_PAD_LEFT);

       //-----------------
       $listColors = array(
         $firstHex . $secondHex . $thirdColorHex,
         $thirdColorHex . $firstHex . $secondHex,
         $firstHex . $thirdColorHex . $secondHex
       );

       return $listColors;
   }
   //------------------
   function debug_percent2Color()
   {
      ?>
      <table width="300px" border="1">
      <tr><td>Percent</td><td>Color1</td><td>Color2</td><td>Color3</td></tr>
      <?
      $example = array(0,15,30,50,70,85,100);
      foreach($example as $x)
      {
         $color = $this->percent2Color($x, 240);
         echo "<tr>".
                 "<td>$x</td>".
                 "<td style='background-color:#".$color[0]."'>$color[0]</td>".
                 "<td style='background-color:#".$color[1]."'>$color[1]</td>".
                 "<td style='background-color:#".$color[2]."'>$color[2]</td>".
              "<tr>";
      }
      ?></table><?

   }
   //----------------------------------------------------------------
   /* >> 3 */
   //----------------------------------------------------------------
   function adjustBrightness($hex, $steps)
   {
       // Steps should be between -255 and 255. Negative = darker, positive = lighter
       $steps = max(-255, min(255, $steps));

       // Normalize into a six character long hex string
       $hex = str_replace('#', '', $hex);
       if (strlen($hex) == 3) {
           $hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
       }

       // Split into three parts: R, G and B
       $color_parts = str_split($hex, 2);
       $return = '#';

       foreach ($color_parts as $color) {
           $color   = hexdec($color); // Convert to decimal
           $color   = max(0,min(255,$color + $steps)); // Adjust color
           $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
       }

       return $return;
   }
   //----------------------------------------------------------------
   /* >> 4 */
   //----------------------------------------------------------------
   function colourBrightness($hex, $percent)
   {
      // Work out if hash given
      $hash = '';
      if (stristr($hex,'#')) {
         $hex = str_replace('#','',$hex);
         $hash = '#';
      }

      /// HEX TO RGB
      $rgb = array(hexdec(substr($hex,0,2)), hexdec(substr($hex,2,2)), hexdec(substr($hex,4,2)));

      //// CALCULATE
      for ($i=0; $i<3; $i++) {
         // See if brighter or darker
         if ($percent > 0) {
            // Lighter
            $rgb[$i] = round($rgb[$i] * $percent) + round(255 * (1-$percent));
         } else {
            // Darker
            $positivePercent = $percent - ($percent*2);
            $rgb[$i] = round($rgb[$i] * $positivePercent) + round(0 * (1-$positivePercent));
         }
         // In case rounding up causes us to go to 256
         if ($rgb[$i] > 255) {
            $rgb[$i] = 255;
         }
      }

      //// RBG to Hex
      $hex = '';
      for($i=0; $i < 3; $i++) {
         // Convert the decimal digit to hex
         $hexDigit = dechex($rgb[$i]);
         // Add a leading zero if necessary
         if(strlen($hexDigit) == 1) {
         $hexDigit = "0" . $hexDigit;
         }
         // Append to the hex string
         $hex .= $hexDigit;
      }
      return $hash.$hex;
   }
   //----------------------------------------------------------------
}