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

    //-------------------------------------------------------
    public function __construct($url)
    {
        $this->url = $url;
    }
    //-------------------------------------------------------
    /*
     * http://stackoverflow.com/questions/2313107/how-do-i-make-a-simple-crawler-in-php
     * $scan_domain: [all, main, others]
     */
    public function crawl_links($depth=5, $scan_domain='all', $scan_seo=false)
    {
        static $seen = array();
        if (isset($seen[$this->url]) || $depth === 0) {
            return;
        }

        // Load HTML ----
        $dom = new \DOMDocument('1.0');
        @$dom->loadHTMLFile($this->url);

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
        $seen[$this->url] = array(
            'url'   => $this->url,
            'title' => $title,
        );

        // Get links ----
        $anchors = $dom->getElementsByTagName('a');
        foreach ($anchors as $element)
        {
            $href = $this->parse_href($element->getAttribute('href'));

            // Filtrar dominios
            if ($this->parse_domain($href, $scan_domain) === false) {
                continue;
            }

            // Recursive
            $this->crawl_links($href, $depth - 1);
        }

        // echo "URL:",$this->url,PHP_EOL,"CONTENT:",PHP_EOL,$dom->saveHTML(),PHP_EOL;
        return $seen;
    }
    //-------------------------------------------------------
    static public function getElementContent($url, $tag, $attribute, $attr_value)
    {
        // Load HTML ----
        $dom = new \DOMDocument('1.0');
        @$dom->loadHTMLFile($url);
        // $dom->formatOutput = true;

        // Elements -----
        $elements = $dom->getElementsByTagName($tag);
        foreach ($elements as $element)
        {
            $value = $element->getAttribute($attribute);

            if ($value && ($value == $attr_value)) {
                // $content = $element->nodeValue;
                $content = $dom->saveXML($element);

                return $content;
            }
        }
    }
    //-------------------------------------------------------
    // PRIVATE
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
    private function parse_domain($url, $scan_domain)
    {
        $find_domain = (strpos($url, $this->url) === 0) ? true : false;

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
