<?php
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 * 2015
 *
 */

namespace angelrove\utils\WebCrawler;

class WebCrawlerContent
{
    private $url;
    private $dom;

    //-------------------------------------------------------
    public function __construct($url)
    {
        $this->url = $url;

        // Load HTML ----
        $this->dom = new \DOMDocument();
        @$this->dom->loadHTMLFile($this->url, LIBXML_NOERROR);
        // $this->dom->formatOutput = true;
    }
    //-------------------------------------------------------
    public function getElementContent($tag, $attribute, $attr_value, $getAttr='', $flagType=false)
    {
        $elements = $this->dom->getElementsByTagName($tag);

        foreach ($elements as $element)
        {
            $value = $element->getAttribute($attribute);
            if ($value && ($value == $attr_value)) {
            }
            else {
                continue;
            }

            // content attr ---
            if ($getAttr) {
                $content = $element->getAttribute($getAttr);
            }
            // content ---
            else {
                if ($flagType == 'xml') {
                    $content = $this->dom->saveXML($element);
                } else {
                    $content = $element->nodeValue;
                }
            }

            return $content;
        }
    }
    //-------------------------------------------------------
    // $flagType: false, xml
    public function getElements($tag, $attribute, $attr_value, array $getAttrs = [], $flagType = false)
    {
        $ret = array();

        $elements = $this->dom->getElementsByTagName($tag);

        foreach ($elements as $element) {
            $value = $element->getAttribute($attribute);
            if ($value && ($value == $attr_value)) {
            } else {
                continue;
            }

            // content attr ---
            if (count($getAttrs) > 0) {
                $content = array();
                foreach ($getAttrs as $attr) {
                    $content[$attr] = $element->getAttribute($attr);
                }
            }
            // content ---
            else {
                if ($flagType == 'xml') {
                    $content = $this->dom->saveXML($element);
                } else {
                    $content = $element->nodeValue;
                }
            }

            $ret[] = $content;
        }

        return $ret;
    }
    //-------------------------------------------------------
}
