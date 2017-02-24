<?php
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 * 2015
 *
 */

namespace angelrove\utils;

class WebCrawler
{
    private $url = '';
    private $depth;

    private $scan_domain  = 'all';
    private $set_scan_seo = false;

    //-------------------------------------------------------
    public function __construct($url, $depth = 5)
    {
        $this->url   = $url;
        $this->depth = $depth;
    }
    //-------------------------------------------------------
    // Seters
    //-------------------------------------------------------
    public function set_scan_seo($set_scan_seo)
    {
        $this->set_scan_seo = $set_scan_seo;
    }
    //-------------------------------------------------------
    // all, main, others
    public function set_scan_domain($scan_domain)
    {
        $this->scan_domain = $scan_domain;
    }
    //-------------------------------------------------------
    // Geters
    //-------------------------------------------------------
    public function crawl()
    {
        return $this->crawl_page($this->url, $this->depth);
    }
    //-------------------------------------------------------
    // PRIVATE
    //-------------------------------------------------------
    // http://stackoverflow.com/questions/2313107/how-do-i-make-a-simple-crawler-in-php
    private function crawl_page($url, $depth = 5)
    {
        static $seen = array();
        if (isset($seen[$url]) || $depth === 0) {
            return;
        }

        // Load HTML ----
        $dom = new \DOMDocument('1.0');
        @$dom->loadHTMLFile($url);

        $title = '';
        if ($dom->getElementsByTagName('title')->item(0)) {
            $title = $dom->getElementsByTagName('title')->item(0)->nodeValue;
        }

        // SEO ----------
        if ($this->set_scan_seo) {
            if (!$title) {
                return;
            }
        }

        // Datos --------
        $seen[$url] = array('url' => $url,
            'title'                   => $title,
        );

        // Get links ----
        $anchors = $dom->getElementsByTagName('a');
        foreach ($anchors as $element) {
            $href = $this->parse_href($element->getAttribute('href'));

            // Filtrar dominios
            if ($this->parse_domain($href) === false) {
                continue;
            }

            $this->crawl_page($href, $depth - 1);
        }

        // echo "URL:",$url,PHP_EOL,"CONTENT:",PHP_EOL,$dom->saveHTML(),PHP_EOL;
        return $seen;
    }
    //-------------------------------------------------------
    //-------------------------------------------------------
    private function parse_href($href)
    {
        if (0 !== strpos($href, 'http')) {
            $path = '/' . ltrim($href, '/');
            if (extension_loaded('http')) {
                $href = http_build_url($href, array('path' => $path));
            } else {
                $parts = parse_url($href);

                if (isset($parts['scheme'])) {
                    $href = $parts['scheme'] . '://';
                }

                if (isset($parts['user']) && isset($parts['pass'])) {
                    $href .= $parts['user'] . ':' . $parts['pass'] . '@';
                }

                if (isset($parts['host'])) {
                    $href .= $parts['host'];
                }

                if (isset($parts['port'])) {
                    $href .= ':' . $parts['port'];
                }
                $href .= $path;
            }
        }

        return $href;
    }
    //-------------------------------------------------------
    private function parse_domain($url)
    {
        $find_domain = (strpos($url, $this->url) === 0) ? true : false;

        switch ($this->scan_domain) {
            case 'main':
                if ($find_domain) {
                    return true;
                }
                break;

            case 'other':
                if (!$find_domain) {
                    return true;
                }
                break;

            default:
                return true;
                break;
        }

        return false;
    }
    //-------------------------------------------------------
}
