<?
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 * 2015
 *
 */

namespace angelrove\utils\Ajax;

use angelrove\utils\CssJsLoad;


class Ajax
{
   //-------------------------------------------------------------------
   function __construct()
   {
CssJsLoad::set(__DIR__.'/lib.js');
   }
   //-------------------------------------------------------------------
}