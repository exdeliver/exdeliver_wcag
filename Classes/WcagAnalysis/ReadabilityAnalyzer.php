<?php

namespace Exdeliver\ExdeliverWcag\WcagAnalysis;

use DOMDocument;
use DOMElement;
use DOMXPath;

class ReadabilityAnalyzer extends WcagAnalyzer
{
    protected string $conformanceLevel;

    public function __construct(string $conformanceLevel)
    {
        $this->conformanceLevel = $conformanceLevel;
    }

    public function analyze(string $pageSource): array
    {
        $problems = [];
        $improvements = [];

        $dom = new DOMDocument();
        @$dom->loadHTML($pageSource, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $xpath = new DOMXPath($dom);

        $cleanText = $this->cleanText($pageSource);
        $readabilityScore = $this->calculateFleschKincaidScore($cleanText);

        $parameters = [
            'readabilityScore' => $readabilityScore,
        ];

        $this->performLevelAChecks($xpath, $problems, $improvements, $parameters);

        if ($this->conformanceLevel === 'AA' || $this->conformanceLevel === 'AAA') {
            $this->performLevelAAChecks($xpath, $problems, $improvements, $parameters);
        }

        if ($this->conformanceLevel === 'AAA') {
            $this->performLevelAAAChecks($xpath, $problems, $improvements, $parameters);
        }

        return [
            'readabilityScore' => $readabilityScore,
            'problems' => $problems,
            'improvements' => $improvements,
        ];
    }

    protected function performLevelAChecks(DOMXPath $xpath, array &$problems, array &$improvements, array $parameters = []): void
    {
        if ($parameters['readabilityScore'] < 60) {
            $problems[] = [
                'message' => 'The content may be difficult to read (WCAG 2.0 Level A)',
                'element' => ['tagName' => 'body'],
            ];
            $improvements[] = [
                'message' => 'Simplify the language used in the content',
                'element' => ['tagName' => 'body'],
            ];
        }

        $uppercaseElements = $xpath->query('//*[text()[contains(translate(., "abcdefghijklmnopqrstuvwxyz", "ABCDEFGHIJKLMNOPQRSTUVWXYZ"), "ABCDE")]]');
        foreach ($uppercaseElements as $element) {
            $problems[] = [
                'message' => 'The content contains long strings of uppercase letters (WCAG 2.0 Level A)',
                'element' => $this->getElementInfo($element),
            ];
            $improvements[] = [
                'message' => 'Avoid using long strings of uppercase letters, as they are harder to read',
                'element' => $this->getElementInfo($element),
            ];
        }
    }

    protected function performLevelAAChecks(DOMXPath $xpath, array &$problems, array &$improvements, array $parameters = []): void
    {
        if ($parameters['readabilityScore'] < 70) {
            $problems[] = [
                'message' => 'The content readability could be improved (WCAG 2.0 Level AA)',
                'element' => ['tagName' => 'body'],
            ];
            $improvements[] = [
                'message' => 'Consider simplifying the language further for better readability',
                'element' => ['tagName' => 'body'],
            ];
        }

        $textNodes = $xpath->query('//text()[normalize-space()]');
        $totalWords = 0;
        $totalSentences = 0;

        foreach ($textNodes as $textNode) {
            $text = $textNode->nodeValue;
            $words = $this->countWords($text);
            $sentences = $this->countSentences($text);
            $totalWords += $words;
            $totalSentences += $sentences;

            if ($sentences > 0 && ($words / $sentences) > 20) {
                $problems[] = [
                    'message' => 'The average sentence length is too long (WCAG 2.0 Level AA)',
                    'element' => $this->getElementInfo($textNode->parentNode),
                ];
                $improvements[] = [
                    'message' => 'Try to keep the average sentence length to 20 words or fewer',
                    'element' => $this->getElementInfo($textNode->parentNode),
                ];
            }
        }
    }

    protected function performLevelAAAChecks(DOMXPath $xpath, array &$problems, array &$improvements, array $parameters = []): void
    {
        if ($parameters['readabilityScore'] < 80) {
            $problems[] = [
                'message' => 'The content readability should be further improved for highest accessibility (WCAG 2.0 Level AAA)',
                'element' => ['tagName' => 'body'],
            ];
            $improvements[] = [
                'message' => 'Aim for simpler language and shorter sentences to achieve a higher readability score',
                'element' => ['tagName' => 'body'],
            ];
        }

        $textContent = $xpath->query('//body')->item(0)->textContent;
        $wordCount = str_word_count($textContent);
        $uniqueWords = count(array_unique(str_word_count($textContent, 1)));
        $lexicalDensity = ($uniqueWords / $wordCount) * 100;

        if ($lexicalDensity > 50) {
            $problems[] = [
                'message' => 'The lexical density is high, which may make the content difficult to understand (WCAG 2.0 Level AAA)',
                'element' => ['tagName' => 'body'],
            ];
            $improvements[] = [
                'message' => 'Use more common words and reduce the variety of terms used to describe similar concepts',
                'element' => ['tagName' => 'body'],
            ];
        }
    }

    public function calculateFleschKincaidScore(string $text): float
    {
        $wordCount = $this->countWords($text);
        $sentenceCount = $this->countSentences($text);
        $syllableCount = $this->countSyllables($text);

        if ($sentenceCount === 0 || $wordCount === 0) {
            return 0;
        }

        $score = 206.835 - 1.015 * ($wordCount / $sentenceCount) - 84.6 * ($syllableCount / $wordCount);

        return max(0, min(100, $score));
    }

    private function cleanText(string $text): string
    {
        // Remove HTML tags
        $text = strip_tags($text);

        // Remove extra whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    private function countWords(string $text): int
    {
        return str_word_count($text);
    }

    private function countSentences(string $text): int
    {
        return preg_match_all('/[.!?]+/', $text, $matches);
    }

    private function countSyllables(string $text): int
    {
        $syllableCount = 0;
        $words = explode(' ', strtolower($text));

        foreach ($words as $word) {
            $syllableCount += $this->countWordSyllables($word);
        }

        return $syllableCount;
    }

    private function countWordSyllables(string $word): int
    {
        $syllables = 0;
        $word = preg_replace('/(?:[^laeiouy]es|ed|[^laeiouy]e)$/', '', $word);
        $word = preg_replace('/^y/', '', $word);
        $syllables = preg_match_all('/[aeiouy]{1,2}/', $word, $matches);

        return max(1, $syllables);
    }

    private function calculateAverageSentenceLength(string $text): float
    {
        $cleanText = $this->cleanText($text);
        $wordCount = $this->countWords($cleanText);
        $sentenceCount = $this->countSentences($cleanText);

        if ($sentenceCount === 0) {
            return 0;
        }

        return $wordCount / $sentenceCount;
    }

    protected function getElementInfo($element): array
    {
        if ($element instanceof DOMElement) {
            return [
                'tagName' => $element->tagName,
                'id' => $element->getAttribute('id'),
                'class' => $element->getAttribute('class'),
                'xpath' => $this->getXPath($element),
                'text' => $element->textContent,
            ];
        }

        return ['tagName' => 'unknown'];
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
