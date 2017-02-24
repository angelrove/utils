<?php
/**
 *
 */

namespace angelrove\utils\ColorsPalette;

use angelrove\clone_csscolor\CSS_Color;

class ColorsPalette extends CSS_Color
{
    //----------------------------------------------------------------
    public function __construct($baseColor)
    {
        if (!$baseColor) {
            $baseColor = 'f8f8f8';
        }
        parent::__construct($baseColor);
    }
    //----------------------------------------------------------------
    public function get_basic_palette()
    {
        $colors = array(
            'primary' => '#' . $this->bg['0'],
            'light'   => '#' . $this->bg['+2'],
            'light2'  => '#' . $this->bg['+3'],
            'dark'    => '#' . $this->bg['-5'],
        );

        return $colors;
    }
    //----------------------------------------------------------------
    public function get_palette()
    {
        // tx-muted -----
        $tx_muted = $this->darken($this->fg['0'], 0.7);
        if ($tx_muted == '000000') {
            $tx_muted = $this->lighten($this->fg['0'], 0.7);
        }

        //-----
        $colors = array(
            'bg'           => '#' . $this->bg['0'],
            'active'       => '#' . $this->bg['+2'],
            'active:hover' => '#' . $this->bg['+3'],
            'text'         => '#' . $this->fg['0'],
            'tx-muted'     => '#' . $tx_muted,
            'a'            => '#' . $this->fg['0'],
            'a-hover'      => '#' . $tx_muted,
        );

        return $colors;
    }
    //------------------------------------------------------------------
    public function debug()
    {
        $listKeys = array('-5', '-4', '-3', '-2', '-1', '0', '+1', '+2', '+3', '+4', '+5');
        ?>
    <style>
    .lala { display:inline-block; text-align:center; padding: 9px 13px; border:1px solid #ddd; }
    </style>
    <?

        echo "<hr>background ";
        foreach ($listKeys as $key) {
            ?><div class="lala" style="background:#<?=$this->bg[$key]?>; color:#<?=$this->fg[$key]?>"><?=$key?></div><?
        }
        ?><br><?

        echo "foreground &nbsp;";
        foreach ($listKeys as $key) {
            ?><div class="lala" style="background:#<?=$this->fg[$key]?>; color:#<?=$this->bg[$key]?>"><?=$key?></div><?
        }
        ?><br><?

        echo "basic &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;";
        $listKeys = $this->get_basic_palette();
        foreach ($listKeys as $key => $color) {
            ?><div class="lala" style="background:<?=$color?>; color:#fff"><?=$key?></div><?
        }
    }
    //----------------------------------------------------------------
}