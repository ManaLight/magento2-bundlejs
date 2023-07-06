<?php

namespace PureMashiro\BundleJs\Model;

use KubAT\PhpSimple\HtmlDomParser;
use PureMashiro\BundleJs\Helper\DeferJsReplacer as HelperDeferJsReplacer;
use PureMashiro\BundleJs\Source\Js as SourceJs;

defined('MAX_FILE_SIZE') || define('MAX_FILE_SIZE', 1000000);

class DeferJsReplacer extends \DOMUtilForWebP\ImageUrlReplacer
{
    /**
     * Replace Html.
     *
     * @param string $html
     * @param mixed|null $storage
     * @return mixed|string
     */
    public function replaceHtml($html, $storage = null)
    {
        if ($html == '') {
            return '';
        }

        // https://stackoverflow.com/questions/4812691/preserve-line-breaks-simple-html-dom-parser

        // function str_get_html($str, $lowercase=true, $forceTagsClosed=true, $target_charset = DEFAULT_TARGET_CHARSET,
        //    $stripRN=true, $defaultBRText=DEFAULT_BR_TEXT, $defaultSpanText=DEFAULT_SPAN_TEXT)

        $dom = $storage !== null && $storage->getDomContent() ?
            $storage->getDomContent() : HtmlDomParser::str_get_html($html, false, false, 'UTF-8', false);
        //$dom = str_get_html($html, false, false, 'UTF-8', false);

        // MAX_FILE_SIZE is defined in simple_html_dom.
        // For safety sake, we make sure it is defined before using
        defined('MAX_FILE_SIZE') || define('MAX_FILE_SIZE', 1000000);

        if ($dom === false) {
            if (strlen($html) > MAX_FILE_SIZE) {
                return '<!-- Alter HTML was skipped because the HTML is too big to process! ' .
                    '(limit is set to ' . MAX_FILE_SIZE . ' bytes) -->' . "\n" . $html;
            }
            return '<!-- Alter HTML was skipped because the helper library refused to process the html -->' .
                "\n" . $html;
        }

        /** @var HelperDeferJsReplacer $helperDeferJsReplacer */
        $helperDeferJsReplacer = $storage->getHelperDeferJsReplacer();
        $innerExclusion = $this->getDeferInnerExclusion($helperDeferJsReplacer);
        $outerExclusion = $this->getDeferOuterExclusion($helperDeferJsReplacer);

        foreach (['script'] as $tagName) {
            $elems = $dom->find($tagName);
            foreach ($elems as $elem) {
                $type = $elem->getAttribute('type');
                $excludedDefer = $elem->getAttribute('excluded-defer');
                if ($type && trim($type) !== 'text/javascript' || $excludedDefer) {
                    continue;
                }

                $innerText = $elem->innertext();
                if ($innerText) {
                    if (!$this->canNotDeferInner($innerText, $innerExclusion)) {
                        $elem->setAttribute('type', 'deferInner/javascript');
                    }
                } else {
                    if (!$this->canNotDeferOuter($elem, $outerExclusion)) {
                        $elem->setAttribute('type', 'deferOuter/javascript');
                    }
                }
            }
        }

        $this->addDeferOuterJS($dom);

        $helperDeferJsReplacer->getAddRequireJsContextsConfigAction()->execute($dom);

        $storage->setDomContent($dom);

        return $dom;
    }

    /**
     * Add Defer Outer JS.
     *
     * @param string $dom
     */
    private function addDeferOuterJS($dom): void
    {
        $node = $dom->createElement("script");
        $node->innertext = SourceJs::DEFER_OUTER_JS;
        $head = $dom->find('head');
        if (isset($head[0])) {
            $head[0]->appendChild($node);
        }
    }

    /**
     * Can Not Defer Inner.
     *
     * @param string $innerText
     * @param array $exclusion
     * @return bool
     */
    private function canNotDeferInner($innerText, $exclusion): bool
    {
        foreach ($exclusion as $item) {
            if (strpos($innerText, $item) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get Defer Inner Exclusion.
     *
     * @param HelperDeferJsReplacer $helperDeferJsReplacer
     * @return array|string[]
     */
    private function getDeferInnerExclusion($helperDeferJsReplacer)
    {
        return $helperDeferJsReplacer->getExcludedInternalScripts();
    }

    /**
     * Can not defer Outer.
     *
     * @param string    $elem
     * @param array     $exclusion
     * @return bool
     */
    private function canNotDeferOuter($elem, $exclusion): bool
    {
        $src = $elem->getAttribute('src');
        if (!$src) {
            return false;
        }

        foreach ($exclusion as $item) {
            if (strpos($src, $item) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get defer outer exclusion.
     *
     * @param HelperDeferJsReplacer $helperDeferJsReplacer
     * @return array|string[]
     */
    private function getDeferOuterExclusion(HelperDeferJsReplacer $helperDeferJsReplacer)
    {
        return $helperDeferJsReplacer->getExcludedExternalScripts();
    }
}
