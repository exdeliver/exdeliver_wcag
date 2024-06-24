<?php

namespace Exdeliver\ExdeliverWcag\WcagAnalysis;

use DOMDocument;
use DOMXPath;

abstract class WcagAnalyzer implements WcagAnalyzerInterface
{
    public function analyze(string $pageSource): array
    {
        $problems = [];
        $improvements = [];

        $dom = new DOMDocument();
        @$dom->loadHTML($pageSource, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $xpath = new DOMXPath($dom);

        // Level A checks
        $this->performLevelAChecks($xpath, $problems, $improvements);

        // Level AA checks
        if ($this->conformanceLevel === 'AA' || $this->conformanceLevel === 'AAA') {
            $this->performLevelAAChecks($xpath, $problems, $improvements);
        }

        // Level AAA checks
        if ($this->conformanceLevel === 'AAA') {
            $this->performLevelAAAChecks($xpath, $problems, $improvements);
        }

        return [
            'problems' => $problems,
            'improvements' => $improvements,
        ];
    }

    abstract protected function performLevelAChecks(DOMXPath $xpath, array &$problems, array &$improvements): void;

    abstract protected function performLevelAAChecks(DOMXPath $xpath, array &$problems, array &$improvements): void;

    abstract protected function performLevelAAAChecks(DOMXPath $xpath, array &$problems, array &$improvements): void;

    protected function getElementInfo(\DOMElement $element): array
    {
        return [
            'tagName' => $element->tagName,
            'id' => $element->getAttribute('id'),
            'class' => $element->getAttribute('class'),
            'xpath' => $this->getXPath($element),
            'text' => $element->textContent,
        ];
    }

    protected function getXPath(\DOMElement $element): string
    {
        $xpath = '';
        for (; $element && $element instanceof \DOMElement; $element = $element->parentNode) {
            $idx = 0;
            $sibs = $element->previousSibling;
            while ($sibs) {
                if ($sibs->nodeName === $element->nodeName) {
                    $idx++;
                }
                $sibs = $sibs->previousSibling;
            }
            $xpath = '/' . $element->nodeName . '[' . ($idx + 1) . ']' . $xpath;
        }
        return $xpath;
    }
}
