<?php
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 * 2015
 *
 */

namespace angelrove\utils\WebCrawler;

class WebCrawler
{
    //-------------------------------------------------------
    /*
     * http://stackoverflow.com/questions/2313107/how-do-i-make-a-simple-crawler-in-php
     * $scan_domain: [all, main, others]
     */
    static public function crawl_links($url, $depth=5, $scan_domain='all', $scan_seo=false)
    {
        static $seen = array();
        if (isset($seen[$url]) || $depth === 0) {
            return;
        }

        // Load HTML ----
        $dom = new \DOMDocument('1.0');
        @$dom->loadHTMLFile($url);

        // SEO ----------
        $title = '';
        if ($dom->getElementsByTagName('title')->item(0)) {
            $title = $dom->getElementsByTagName('title')->item(0)->nodeValue;
        }

        if ($scan_seo) {
            if (!$title) {
                return;
            }
        }

        // Datos --------
        $seen[$url] = array(
            'url'   => $url,
            'title' => $title,
        );

        // Get links ----
        $anchors = $dom->getElementsByTagName('a');
        foreach ($anchors as $element)
        {
            $href = self::parse_href($element->getAttribute('href'));

            // Filtrar dominios
            if (self::parse_domain($url, $href, $scan_domain) === false) {
                continue;
            }

            // Recursive
            self::crawl_links($href, $depth - 1);
        }

        // echo "URL:",$url,PHP_EOL,"CONTENT:",PHP_EOL,$dom->saveHTML(),PHP_EOL;
        return $seen;
    }
    //-------------------------------------------------------
    // PRIVATE
    //-------------------------------------------------------
    static private function parse_href($href)
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
    static private function parse_domain($url, $href, $scan_domain)
    {
        $find_domain = (strpos($href, $url) === 0) ? true : false;

        switch ($scan_domain) {
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
