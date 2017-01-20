<?
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 * 2006
 *
 */
namespace angelrove\utils;


class DebugTrace
{
  //-------------------------------------------------
  static function out($objectName, $object)
  {
    if(DEBUG_VARS == 0) {
       return;
    }

    if (!isset($_SESSION['id_traza'])) {
       $_SESSION['id_traza'] = 0;
    }

    $idTraza = $_SESSION['id_traza']++;

    CssJsLoad::set_script('
  $(document).ready(function() {
    $(".DebugTrace .display").click(function() {
      var id_traza = $(this).attr("id_traza");
      $("#traza_"+id_traza).toggle();
    });
  });
    ', 'DebugTrace');

    ?>
    <span class="DebugTrace">
      <button class="btn btn-xs btn-info display" id_traza="<?=$idTraza?>"><?=$objectName?></button>
      <pre class="traze" id="traza_<?=$idTraza?>" style="display:none;position:absolute;z-index:1001">
        <? print_r($object) ?>
      </pre>
    </span>
    <?
  }
  //-------------------------------------------------
}
?>
