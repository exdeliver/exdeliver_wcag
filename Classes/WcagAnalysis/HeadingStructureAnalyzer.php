<?php

namespace Exdeliver\ExdeliverWcag\WcagAnalysis;

use DOMElement;
use DOMXPath;

class HeadingStructureAnalyzer extends WcagAnalyzer
{
    protected string $conformanceLevel;

    public function __construct(string $conformanceLevel)
    {
        $this->conformanceLevel = $conformanceLevel;
    }

    protected function performLevelAChecks(DOMXPath $xpath, array &$problems, array &$improvements): void
    {
        $h1Elements = $xpath->query('//h1');
        if ($h1Elements->length === 0) {
            $problems[] = [
                'message' => 'Page is missing a main heading (h1) (WCAG 2.0 Level A)',
                'element' => null,
            ];
            $improvements[] = [
                'message' => 'Add a main heading (h1) to the page',
                'element' => null,
            ];
        }

        for ($i = 1; $i <= 6; $i++) {
            $sameHeadings = $xpath->query("//h" . $i . "[following-sibling::*[1][self::h" . $i . "]]");
            foreach ($sameHeadings as $heading) {
                $problems[] = [
                    'message' => "Multiple h$i headings are used without content in between (WCAG 2.0 Level A)",
                    'element' => $this->getElementInfo($heading),
                ];
                $improvements[] = [
                    'message' => "Ensure proper nesting of headings and include content between h$i headings",
                    'element' => $this->getElementInfo($heading),
                ];
            }
        }
    }

    protected function performLevelAAChecks(DOMXPath $xpath, array &$problems, array &$improvements): void
    {
        $headings = $xpath->query('//h1|//h2|//h3|//h4|//h5|//h6');
        $previousLevel = 0;

        foreach ($headings as $heading) {
            $level = (int)substr($heading->nodeName, 1);

            if ($level - $previousLevel > 1) {
                $problems[] = [
                    'message' => "Heading structure is not properly nested (skipped from h{$previousLevel} to h{$level}) (WCAG 2.0 Level AA)",
                    'element' => $this->getElementInfo($heading),
                ];
                $improvements[] = [
                    'message' => 'Ensure heading levels are properly nested without skipping levels',
                    'element' => $this->getElementInfo($heading),
                ];
            }

            $previousLevel = $level;
        }
    }

    protected function performLevelAAAChecks(DOMXPath $xpath, array &$problems, array &$improvements): void
    {
        $headings = $xpath->query('//h1|//h2|//h3|//h4|//h5|//h6');

        if ($headings->length > 0) {
            $firstHeading = $headings->item(0);
            if ($firstHeading->nodeName !== 'h1') {
                $problems[] = [
                    'message' => 'The first heading on the page is not an h1 (WCAG 2.0 Level AAA)',
                    'element' => $this->getElementInfo($firstHeading),
                ];
                $improvements[] = [
                    'message' => 'Ensure the first heading on the page is an h1',
                    'element' => $this->getElementInfo($firstHeading),
                ];
            }
        }

        foreach ($headings as $heading) {
            $headingText = trim($heading->textContent);
            if (strlen($headingText) > 100) {
                $problems[] = [
                    'message' => 'Heading text is too long (over 100 characters) (WCAG 2.0 Level AAA)',
                    'element' => $this->getElementInfo($heading),
                ];
                $improvements[] = [
                    'message' => 'Keep heading text concise (under 100 characters) for better readability',
                    'element' => $this->getElementInfo($heading),
                ];
            }
        }
    }

    protected function getXPath(DOMElement $element): string
    {
        $xpath = '';
        for (; $element && $element instanceof DOMElement; $element = $element->parentNode) {
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
