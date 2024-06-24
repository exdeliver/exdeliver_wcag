<?php

namespace Exdeliver\ExdeliverWcag\WcagAnalysis;

use DOMElement;
use DOMXPath;

class ButtonsAndAnchorsAnalyzer extends WcagAnalyzer
{
    protected string $conformanceLevel;

    public function __construct(string $conformanceLevel)
    {
        $this->conformanceLevel = $conformanceLevel;
    }

    protected function performLevelAChecks(DOMXPath $xpath, array &$problems, array &$improvements): void
    {
        // Check for buttons without accessible names
        $buttonsWithoutAccessibleNames = $xpath->query('//button[not(text()) and not(@aria-label) and not(@aria-labelledby)]');
        foreach ($buttonsWithoutAccessibleNames as $button) {
            $problems[] = [
                'message' => 'Button without accessible name (WCAG 2.0 Level A)',
                'element' => $this->getElementInfo($button),
            ];
            $improvements[] = [
                'message' => 'Add text content, aria-label, or aria-labelledby to buttons for accessibility',
                'element' => $this->getElementInfo($button),
            ];
        }

        // Check for anchors without href or with empty href
        $anchorsWithoutHref = $xpath->query('//a[not(@href) or @href=""]');
        foreach ($anchorsWithoutHref as $anchor) {
            $problems[] = [
                'message' => 'Anchor without href or with empty href (WCAG 2.0 Level A)',
                'element' => $this->getElementInfo($anchor),
            ];
            $improvements[] = [
                'message' => 'Ensure all anchors have a valid href attribute',
                'element' => $this->getElementInfo($anchor),
            ];
        }
    }

    protected function performLevelAAChecks(DOMXPath $xpath, array &$problems, array &$improvements): void
    {
        // Check for buttons with generic or non-descriptive text
        $genericButtonTexts = ['submit', 'button', 'click here', 'more', 'read more'];
        $buttonsWithGenericText = $xpath->query('//button[' . implode(' or ', array_map(function ($text) {
                return "translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz') = '$text'";
            }, $genericButtonTexts)) . ']');
        foreach ($buttonsWithGenericText as $button) {
            $problems[] = [
                'message' => 'Button with generic or non-descriptive text (WCAG 2.0 Level AA)',
                'element' => $this->getElementInfo($button),
            ];
            $improvements[] = [
                'message' => 'Use more descriptive text for buttons',
                'element' => $this->getElementInfo($button),
            ];
        }

        // Check for anchors with generic or non-descriptive text
        $genericAnchorTexts = ['click here', 'more', 'read more'];
        $anchorsWithGenericText = $xpath->query('//a[' . implode(' or ', array_map(function ($text) {
                return "translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz') = '$text'";
            }, $genericAnchorTexts)) . ']');
        foreach ($anchorsWithGenericText as $anchor) {
            $problems[] = [
                'message' => 'Anchor with generic or non-descriptive text (WCAG 2.0 Level AA)',
                'element' => $this->getElementInfo($anchor),
            ];
            $improvements[] = [
                'message' => 'Use more descriptive text for anchors',
                'element' => $this->getElementInfo($anchor),
            ];
        }
    }

    protected function performLevelAAAChecks(DOMXPath $xpath, array &$problems, array &$improvements): void
    {
        // Check for buttons with insufficient contrast
        $buttonsWithoutContrast = $xpath->query('//button[not(contains(@style, "color:") and contains(@style, "background-color:"))]');
        foreach ($buttonsWithoutContrast as $button) {
            $problems[] = [
                'message' => 'Button with potentially insufficient contrast (WCAG 2.0 Level AAA)',
                'element' => $this->getElementInfo($button),
            ];
            $improvements[] = [
                'message' => 'Ensure buttons have sufficient contrast between text and background colors',
                'element' => $this->getElementInfo($button),
            ];
        }

        // Check for anchors with insufficient contrast
        $anchorsWithoutContrast = $xpath->query('//a[not(contains(@style, "color:"))]');
        foreach ($anchorsWithoutContrast as $anchor) {
            $problems[] = [
                'message' => 'Anchor with potentially insufficient contrast (WCAG 2.0 Level AAA)',
                'element' => $this->getElementInfo($anchor),
            ];
            $improvements[] = [
                'message' => 'Ensure anchors have sufficient contrast for text colors',
                'element' => $this->getElementInfo($anchor),
            ];
        }
    }

    protected function getElementInfo(DOMElement $element): array
    {
        return [
            'tagName' => $element->tagName,
            'id' => $element->getAttribute('id'),
            'class' => $element->getAttribute('class'),
            'xpath' => $this->getXPath($element),
            'text' => $element->textContent,
        ];
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