<?php
/**
 * @author José A. Romero Vegas <jangel.romero@gmail.com>
 * 2015
 *
 */

namespace angelrove\utils\webcrawler;

class WebCrawlerContent
{
    private $url;
    private $flagType;
    private $dom;

    //-------------------------------------------------------
    public function __construct($url, $flagType=false)
    {
        $this->url = $url;
        $this->flagType = $flagType;

        // Load HTML ----
        $this->dom = new \DOMDocument('1.0');
        @$this->dom->loadHTMLFile($this->url);
        // $this->dom->formatOutput = true;
    }
    //-------------------------------------------------------
    public function getElementContent($tag, $attribute, $attr_value, $getAttr='')
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
                if ($this->flagType == 'xml') {
                    $content = $this->domdom->saveXML($element);
                } else {
                    $content = $element->nodeValue;
                }
            }

            return $content;
        }
    }
    //-------------------------------------------------------
}
