<?php
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com> 2006
 *
 *  Ejem...
 *    $example = new Pagination($sqlQ, 10);
 *    $example->setUrlFormat('?query='.$_GET['query'].'&id_page=[id_page]');
 *
 *    $listRows  = $example->getListRows();
 *    $listPages = $example->get();
 *
 */

namespace angelrove\utils;

use angelrove\utils\Db_mysql;
use Illuminate\Database\Capsule\Manager as DB;

class Pagination
{
    private $labels;
    private $listLabels = array();

    private $numRowsPage;
    private $id_page;

    private $hrefSecc = '?id_page=[id_page]';

    private $numPagesRango = 15;
    private $numRows;
    private $numTotalPages;

    private $showAlways = true;
    private $maxPages;

    //----------------------------------------------------------------------
    // PUBLIC
    //----------------------------------------------------------------------
    public function __construct($sqlQ, $numRowsPage, $id_page = '', $lang = 'es')
    {
        if (!$sqlQ) {
            return '';
        }

        $listLabels['es'] = array(
            'Showing' => 'Mostrando',
        );
        $listLabels['en'] = array(
            'Showing' => 'Showing',
        );

        $this->labels      = $listLabels[$lang];
        $this->numRowsPage = $numRowsPage;

        /** id_page **/
        if ($id_page) {
            $this->id_page = $id_page;
        } else {
            $this->id_page = 1;
            if (isset($_REQUEST['id_page']) && $_REQUEST['id_page'] > 0) {
                $this->id_page = $_REQUEST['id_page'];
            }
        }
        //echo "Paging - id_page = ".$this->id_page;

        /** Query **/
        // limit
        $limitInicio = $this->getItemDesde() - 1;
        $limitHasta  = $this->numRowsPage;
        $sqlLimit    = " LIMIT $limitInicio, $limitHasta";

        // Rows
        $sqlQ = preg_replace('/SELECT/', 'SELECT SQL_CALC_FOUND_ROWS ', $sqlQ, 1);
        $sqlQ .= $sqlLimit;

        $this->listRows = DB::select($sqlQ);
        $this->numRows  = Db_mysql::getValue("SELECT FOUND_ROWS()");

        /** Nº páginas **/
        $this->numTotalPages = ceil($this->numRows / $this->numRowsPage);
        $this->maxPages      = $this->numTotalPages;

        /** Rango: pag. inicial, pag. final **/
        $this->rango = $this->getRango($this->id_page, $this->numPagesRango, $this->numTotalPages);
    }
    //----------------------------------------------------------------------
    // SET
    //----------------------------------------------------------------------
    /**
     * $urlFormat = 'index[id_page].html'
     * $urlFormat = 'FRIENDLY';
     */
    public function setUrlFormat($urlFormat)
    {
        $this->hrefSecc = stripslashes($urlFormat);
        //$this->urlFormat = stripslashes($urlFormat);
    }
    //----------------------------------------------------------------------
    public function setShowAlways($flag)
    {
        $this->showAlways = $flag;
    }
    //----------------------------------------------------------------------
    public function setNumPages($numPagesRango)
    {
        $this->numPagesRango = $numPagesRango;

        /** Rango: actualizar **/
        $this->rango = $this->getRango($this->id_page, $this->numPagesRango, $this->numTotalPages);
    }
    //----------------------------------------------------------------------
    public function setMaxPages($maxPages)
    {
        if ($this->maxPages > $maxPages) {
            $this->maxPages = $maxPages;
        }

        // Comprobaciones
        if ($this->maxPages && ($this->id_page > $this->maxPages)) {
            require 'errors/404.php';
        }
    }
    //----------------------------------------------------------------------
    // GET
    //----------------------------------------------------------------------
    public function getItemDesde()
    {
        $itemDesde = ($this->numRowsPage * $this->id_page) - $this->numRowsPage + 1;
        return $itemDesde;
    }
    //----------------------------------------------------------------------
    public function getItemHasta()
    {
        $itemHasta = $this->numRowsPage * $this->id_page;
        if ($itemHasta > $this->numRows) {
            $itemHasta = $this->numRows;
        }

        return $itemHasta;
    }
    //----------------------------------------------------------------------
    public function getNumRows()
    {
        return $this->numRows;
    }
    //----------------------------------------------------------------------
    public function &getListRows()
    {
        return $this->listRows;
    }
    //----------------------------------------------------------------------
    public function getLinkNext()
    {
        $urlNext = '#';

        if ($this->rango[1] > 1) {
            if ($this->id_page < $this->numTotalPages && $this->id_page < $this->maxPages) {
                $urlNext = $this->getUrlParams($this->id_page + 1);
            }
        }

        return $urlNext;
    }
    //----------------------------------------------------------------------
    // HTM
    //----------------------------------------------------------------------
    public function getHtmStatus()
    {
        $total = $this->getNumRows();
        $desde = $this->getItemDesde();
        $hasta = $this->getItemHasta();
        return '<div>Total: ' . $total . ' &nbsp; Mostrando: ' . $desde . ' - ' . $hasta . '</div>';
    }
    //----------------------------------------------------------------------
    public function get()
    {
        $urlPrev = '#';
        $prev_disabled = 'disabled';

        $urlNext = '#';
        $next_disabled = 'disabled';

        $htmPaginas = '';

        /** Rango: pag. inicial, pag. final **/
        if ($this->showAlways == false && $this->rango[1] <= 1) {
            return false;
        }


        /** números de páginas **/
        if ($this->numPagesRango > 0) {
            for ($c = $this->rango[0]; $c <= $this->rango[1]; $c++) {
                if ($this->id_page == $c) {
                    $urlPagina = $this->getUrlParams($c);
                    $htmPaginas .= '<li class="active"><a href="#">' . $c . '</a></li>';
                } else {
                    $urlPagina = $this->getUrlParams($c);
                    $htmPaginas .= '<li><a href="' . $urlPagina . '">' . $c . '</a></li>';
                }

                if ($c == $this->maxPages) {
                    break;
                }

            }
        }

        /** Anterior / Siguiente **/
        // Prev
        if ($this->id_page > 1) {
            $urlPrev = $this->getUrlParams($this->id_page - 1);
            $prev_disabled = '';
        }

        if ($this->numPagesRango == 0) {
            return '';
        }

        // Next ---
        if ($urlNext = $this->getLinkNext()) {
            $next_disabled = '';
        }

        /** Out **/
        return <<<EOD
        <li class="$prev_disabled"><a href="$urlPrev"><i class="fas fa-angle-left"></i></a></li>
            $htmPaginas
        <li class="$next_disabled"><a href="$urlNext"><i class="fas fa-angle-right"></i></a></li>
        EOD;
}
    //----------------------------------------------------------------------
    // PRIVATE
    //----------------------------------------------------------------------
    private function getUrlParams($id_page)
    {
        $ret = '';

        if ($this->hrefSecc == 'FRIENDLY') {
            if ($id_page > 1) {
                $ret = 'index_' . $id_page . '.html';
            } else {
                $ret = 'index.html';
            }

        } else {
            $ret = str_replace('[id_page]', $id_page, $this->hrefSecc);
        }

        return $ret;
    }
    //----------------------------------------------------------------------
    private function getRango($id_page, $numPagesRango, $numTotalPages)
    {
        $margenIzq = floor($numPagesRango / 2) - 1;

        // $pagInicio
        $pagInicio = $id_page - $margenIzq;

        if ($pagInicio < 1) {
            $pagInicio = 1;
        } elseif (($pagInicio + $numPagesRango) > $numTotalPages) {
            $pagInicio = $numTotalPages - $numPagesRango + 1;
            if ($pagInicio < 1) {
                $pagInicio = 1;
            }

        }

        // $pagFin
        $pagFin = $pagInicio + $numPagesRango - 1;
        if ($pagFin > $numTotalPages) {
            $pagFin = $numTotalPages;
        }

        // maxPages
        if ($this->maxPages && ($pagFin > $this->maxPages)) {
            $pagFin = $this->maxPages;
        }

        return array($pagInicio, $pagFin);
    }
    //----------------------------------------------------------------------
}
