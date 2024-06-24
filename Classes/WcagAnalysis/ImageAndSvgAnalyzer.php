<?php

namespace Exdeliver\ExdeliverWcag\WcagAnalysis;

use DOMElement;
use DOMXPath;

class ImageAndSvgAnalyzer extends WcagAnalyzer
{
    protected string $conformanceLevel;

    public function __construct(string $conformanceLevel)
    {
        $this->conformanceLevel = $conformanceLevel;
    }

    protected function performLevelAChecks(DOMXPath $xpath, array &$problems, array &$improvements): void
    {
        // Check for images without alt attribute
        $imagesWithoutAlt = $xpath->query('//img[not(@alt)]');
        foreach ($imagesWithoutAlt as $img) {
            $problems[] = [
                'message' => 'Image is missing the alt attribute (WCAG 2.0 Level A)',
                'element' => $this->getElementInfo($img),
            ];
            $improvements[] = [
                'message' => 'Add an alt attribute to the image, even if it\'s empty for decorative images',
                'element' => $this->getElementInfo($img),
            ];
        }

        // Check for SVG elements without titles or descriptions
        $svgsWithoutTitleOrDesc = $xpath->query('//svg[not(title) and not(desc)]');
        foreach ($svgsWithoutTitleOrDesc as $svg) {
            $problems[] = [
                'message' => 'SVG element without title or description (WCAG 2.0 Level A)',
                'element' => $this->getElementInfo($svg),
            ];
            $improvements[] = [
                'message' => 'Add <title> and/or <desc> elements to SVG for accessibility',
                'element' => $this->getElementInfo($svg),
            ];
        }

        // Check for SVG elements with role="img" but without aria-label or aria-labelledby
        $svgsWithRoleImg = $xpath->query('//svg[@role="img" and not(@aria-label) and not(@aria-labelledby)]');
        foreach ($svgsWithRoleImg as $svg) {
            $problems[] = [
                'message' => 'SVG with role="img" missing aria-label or aria-labelledby (WCAG 2.0 Level A)',
                'element' => $this->getElementInfo($svg),
            ];
            $improvements[] = [
                'message' => 'Add aria-label or aria-labelledby to SVG with role="img"',
                'element' => $this->getElementInfo($svg),
            ];
        }

        // Check for decorative images with non-empty alt text
        $decorativeImagesWithAlt = $xpath->query('//img[@role="presentation" and @alt and @alt!=""]');
        foreach ($decorativeImagesWithAlt as $img) {
            $problems[] = [
                'message' => 'Decorative image with non-empty alt text (WCAG 2.0 Level A)',
                'element' => $this->getElementInfo($img),
            ];
            $improvements[] = [
                'message' => 'Ensure decorative images have an empty alt attribute',
                'element' => $this->getElementInfo($img),
            ];
        }

        // Check for SVG elements with aria-hidden="true" containing focusable content
        $svgsWithHiddenFocusable = $xpath->query('//svg[@aria-hidden="true" and (.//a or .//button or .//input or .//select or .//textarea or .//object or .//iframe)]');
        foreach ($svgsWithHiddenFocusable as $svg) {
            $problems[] = [
                'message' => 'SVG with aria-hidden="true" contains focusable content (WCAG 2.0 Level A)',
                'element' => $this->getElementInfo($svg),
            ];
            $improvements[] = [
                'message' => 'Ensure SVG elements with aria-hidden="true" do not contain focusable content',
                'element' => $this->getElementInfo($svg),
            ];
        }
    }

    protected function performLevelAAChecks(DOMXPath $xpath, array &$problems, array &$improvements): void
    {
        // Check for images with non-descriptive alt text
        $imagesWithGenericAlt = $xpath->query('//img[starts-with(@alt, "image")]');
        foreach ($imagesWithGenericAlt as $img) {
            $problems[] = [
                'message' => 'Image has non-descriptive alt text (WCAG 2.0 Level AA)',
                'element' => $this->getElementInfo($img),
            ];
            $improvements[] = [
                'message' => 'Replace generic alt text like "image" with more descriptive content',
                'element' => $this->getElementInfo($img),
            ];
        }

        // Check for complex SVG elements without role="application"
        $complexSvgsWithoutRole = $xpath->query('//svg[.//g or .//path or .//rect or .//circle or .//line or .//polyline or .//polygon or .//text or .//tspan or .//textPath or .//image or .//use or .//symbol or .//defs or .//clipPath or .//mask or .//pattern or .//marker or .//linearGradient or .//radialGradient or .//stop or .//foreignObject][not(@role="application")]');
        foreach ($complexSvgsWithoutRole as $svg) {
            $problems[] = [
                'message' => 'Complex SVG without role="application" (WCAG 2.0 Level AA)',
                'element' => $this->getElementInfo($svg),
            ];
            $improvements[] = [
                'message' => 'Add role="application" to complex SVG elements',
                'element' => $this->getElementInfo($svg),
            ];
        }

        $svgsWithAriaHiddenFalse = $xpath->query('//svg[@aria-hidden="false" and not(title) and not(desc) and not(@aria-label) and not(@aria-labelledby)]');
        foreach ($svgsWithAriaHiddenFalse as $svg) {
            $problems[] = [
                'message' => 'SVG with aria-hidden="false" not properly labeled (WCAG 2.0 Level AA)',
                'element' => $this->getElementInfo($svg),
            ];
            $improvements[] = [
                'message' => 'Ensure SVG elements with aria-hidden="false" are properly labeled for accessibility',
                'element' => $this->getElementInfo($svg),
            ];
        }
    }

    protected function performLevelAAAChecks(DOMXPath $xpath, array &$problems, array &$improvements): void
    {
        // Check for images with alt text that's too long
        $imagesWithLongAlt = $xpath->query('//img[string-length(@alt) > 100]');
        foreach ($imagesWithLongAlt as $img) {
            $problems[] = [
                'message' => 'Image alt text is too long (over 100 characters) (WCAG 2.0 Level AAA)',
                'element' => $this->getElementInfo($img),
            ];
            $improvements[] = [
                'message' => 'Ensure image alt text is concise (under 100 characters) and descriptive',
                'element' => $this->getElementInfo($img),
            ];
        }

        // Check for images with alt text that's too short
        $imagesWithShortAlt = $xpath->query('//img[string-length(@alt) > 0 and string-length(@alt) < 10]');
        foreach ($imagesWithShortAlt as $img) {
            $problems[] = [
                'message' => 'Image alt text might be too short (under 10 characters) (WCAG 2.0 Level AAA)',
                'element' => $this->getElementInfo($img),
            ];
            $improvements[] = [
                'message' => 'Review short alt texts to ensure they are sufficiently descriptive',
                'element' => $this->getElementInfo($img),
            ];
        }

        $svgsWithTextContent = $xpath->query('//svg[.//text or .//tspan or .//textPath][not(title) or not(desc)]');
        foreach ($svgsWithTextContent as $svg) {
            $problems[] = [
                'message' => 'SVG with text content missing proper text alternatives (WCAG 2.0 Level AAA)',
                'element' => $this->getElementInfo($svg),
            ];
            $improvements[] = [
                'message' => 'Ensure SVG text content has appropriate <title> or <desc> elements',
                'element' => $this->getElementInfo($svg),
            ];
        }

        // Check for SVG elements with role="presentation" containing interactive content
        $svgsWithPresentationRole = $xpath->query('//svg[@role="presentation" and (.//a or .//button or .//input or .//select or .//textarea or .//object or .//iframe)]');
        foreach ($svgsWithPresentationRole as $svg) {
            $problems[] = [
                'message' => 'SVG with role="presentation" contains interactive content (WCAG 2.0 Level AAA)',
                'element' => $this->getElementInfo($svg),
            ];
            $improvements[] = [
                'message' => 'Ensure SVG elements with role="presentation" do not contain interactive content',
                'element' => $this->getElementInfo($svg),
            ];
        }

        // Check for SVG elements with aria-labelledby or aria-describedby
        $svgsWithAriaReferences = $xpath->query('//svg[@aria-labelledby or @aria-describedby]');
        foreach ($svgsWithAriaReferences as $svg) {
            $ariaLabelledby = $svg->getAttribute('aria-labelledby');
            $ariaDescribedby = $svg->getAttribute('aria-describedby');

            if ($ariaLabelledby && !$xpath->query("//*[@id='$ariaLabelledby']")->length) {
                $problems[] = [
                    'message' => "SVG referenced by aria-labelledby does not exist: '$ariaLabelledby' (WCAG 2.0 Level AAA)",
                    'element' => $this->getElementInfo($svg),
                ];
                $improvements[] = [
                    'message' => 'Ensure elements referenced by aria-labelledby exist in the document',
                    'element' => $this->getElementInfo($svg),
                ];
            }

            if ($ariaDescribedby && !$xpath->query("//*[@id='$ariaDescribedby']")->length) {
                $problems[] = [
                    'message' => "SVG referenced by aria-describedby does not exist: '$ariaDescribedby' (WCAG 2.0 Level AAA)",
                    'element' => $this->getElementInfo($svg),
                ];
                $improvements[] = [
                    'message' => 'Ensure elements referenced by aria-describedby exist in the document',
                    'element' => $this->getElementInfo($svg),
                ];
            }
        }

        // Check for SVG elements with tabindex
        $svgsWithTabindex = $xpath->query('//svg[@tabindex and not(title) and not(desc) and not(@aria-label) and not(@aria-labelledby)]');
        foreach ($svgsWithTabindex as $svg) {
            $problems[] = [
                'message' => 'SVG with tabindex not properly labeled (WCAG 2.0 Level AAA)',
                'element' => $this->getElementInfo($svg),
            ];
            $improvements[] = [
                'message' => 'Ensure SVG elements with tabindex are properly labeled for accessibility',
                'element' => $this->getElementInfo($svg),
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