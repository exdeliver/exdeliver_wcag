<?php

namespace Exdeliver\ExdeliverWcag\WcagAnalysis;

use DOMDocument;
use DOMElement;
use DOMXPath;

class AriaAttributesAnalyzer extends WcagAnalyzer
{
    protected string $conformanceLevel;

    public function __construct(string $conformanceLevel)
    {
        $this->conformanceLevel = $conformanceLevel;
    }

    protected function performLevelAChecks(DOMXPath $xpath, array &$problems, array &$improvements): void
    {
        // Check for proper use of aria-hidden
        $elementsWithAriaHidden = $xpath->query('//*[@aria-hidden="true"]');
        foreach ($elementsWithAriaHidden as $element) {
            if ($xpath->query('.//a|.//button|.//input|.//select|.//textarea', $element)->length > 0) {
                $problems[] = [
                    'message' => 'Elements with aria-hidden="true" contain focusable content (WCAG 2.0 Level A)',
                    'element' => $this->getElementInfo($element),
                ];
                $improvements[] = [
                    'message' => 'Ensure elements with aria-hidden="true" do not contain focusable content',
                    'element' => $this->getElementInfo($element),
                ];
            }
        }

        // Check for proper use of role attributes
        $elementsWithRole = $xpath->query('//*[@role]');
        foreach ($elementsWithRole as $element) {
            $role = $element->getAttribute('role');
            if (!in_array($role, $this->getValidRoles(), true)) {
                $problems[] = [
                    'message' => "Invalid role attribute value: '$role' (WCAG 2.0 Level A)",
                    'element' => $this->getElementInfo($element),
                ];
                $improvements[] = [
                    'message' => 'Use only valid ARIA role values',
                    'element' => $this->getElementInfo($element),
                ];
            }
        }

        // Check for proper use of aria-label on interactive elements
        $interactiveElementsWithAriaLabel = $xpath->query('//a[@aria-label]|//button[@aria-label]|//input[@aria-label]|//select[@aria-label]|//textarea[@aria-label]');
        foreach ($interactiveElementsWithAriaLabel as $element) {
            $ariaLabel = $element->getAttribute('aria-label');
            if (empty(trim($ariaLabel))) {
                $problems[] = [
                    'message' => 'Interactive element has empty aria-label attribute (WCAG 2.0 Level A)',
                    'element' => $this->getElementInfo($element),
                ];
                $improvements[] = [
                    'message' => 'Provide meaningful text for aria-label attributes on interactive elements',
                    'element' => $this->getElementInfo($element),
                ];
            }
        }

        // Check for proper use of aria-labelledby
        $elementsWithAriaLabelledby = $xpath->query('//*[@aria-labelledby]');
        foreach ($elementsWithAriaLabelledby as $element) {
            $labelledById = $element->getAttribute('aria-labelledby');
            if (!$xpath->query("//*[@id='$labelledById']")->length) {
                $problems[] = [
                    'message' => "Element referenced by aria-labelledby does not exist: '$labelledById' (WCAG 2.0 Level A)",
                    'element' => $this->getElementInfo($element),
                ];
                $improvements[] = [
                    'message' => 'Ensure elements referenced by aria-labelledby exist in the document',
                    'element' => $this->getElementInfo($element),
                ];
            }
        }

        $elementsWithAriaOwns = $xpath->query('//*[@aria-owns]');
        foreach ($elementsWithAriaOwns as $element) {
            $ownedId = $element->getAttribute('aria-owns');
            if (!$xpath->query("//*[@id='$ownedId']")->length) {
                $problems[] = [
                    'message' => "Element referenced by aria-owns does not exist: '$ownedId' (WCAG 2.0 Level A)",
                    'element' => $this->getElementInfo($element),
                ];
                $improvements[] = [
                    'message' => 'Ensure elements referenced by aria-owns exist in the document',
                    'element' => $this->getElementInfo($element),
                ];
            }
        }

        // Check for aria-activedescendant
        $elementsWithAriaActiveDescendant = $xpath->query('//*[@aria-activedescendant]');
        foreach ($elementsWithAriaActiveDescendant as $element) {
            $activeId = $element->getAttribute('aria-activedescendant');
            if (!$xpath->query("//*[@id='$activeId']")->length) {
                $problems[] = [
                    'message' => "Element referenced by aria-activedescendant does not exist: '$activeId' (WCAG 2.0 Level A)",
                    'element' => $this->getElementInfo($element),
                ];
                $improvements[] = [
                    'message' => 'Ensure elements referenced by aria-activedescendant exist in the document',
                    'element' => $this->getElementInfo($element),
                ];
            }
        }

        // Check for aria-live
        $elementsWithAriaLive = $xpath->query('//*[@aria-live]');
        foreach ($elementsWithAriaLive as $element) {
            $liveValue = $element->getAttribute('aria-live');
            if (!in_array($liveValue, ['off', 'polite', 'assertive'], true)) {
                $problems[] = [
                    'message' => "Invalid aria-live attribute value: '$liveValue' (WCAG 2.0 Level A)",
                    'element' => $this->getElementInfo($element),
                ];
                $improvements[] = [
                    'message' => 'Use only valid aria-live values (off, polite, assertive)',
                    'element' => $this->getElementInfo($element),
                ];
            }
        }

        // Check for aria-relevant
        $elementsWithAriaRelevant = $xpath->query('//*[@aria-relevant]');
        foreach ($elementsWithAriaRelevant as $element) {
            $relevantValue = $element->getAttribute('aria-relevant');
            $validRelevantValues = ['additions', 'removals', 'text', 'all'];
            $relevantValues = preg_split('/\s+/', $relevantValue);
            foreach ($relevantValues as $value) {
                if (!in_array($value, $validRelevantValues, true)) {
                    $problems[] = [
                        'message' => "Invalid aria-relevant attribute value: '$value' (WCAG 2.0 Level A)",
                        'element' => $this->getElementInfo($element),
                    ];
                    $improvements[] = [
                        'message' => 'Use only valid aria-relevant values (additions, removals, text, all)',
                        'element' => $this->getElementInfo($element),
                    ];
                }
            }
        }
    }

    protected function performLevelAAChecks(DOMXPath $xpath, array &$problems, array &$improvements): void
    {
        // Check for proper use of aria-expanded on expandable elements
        $elementsWithAriaExpanded = $xpath->query('//*[@aria-expanded="true"]');
        foreach ($elementsWithAriaExpanded as $element) {
            if (!$element->hasChildNodes()) {
                $problems[] = [
                    'message' => 'Elements with aria-expanded="true" do not have visible expanded content (WCAG 2.0 Level AA)',
                    'element' => $this->getElementInfo($element),
                ];
                $improvements[] = [
                    'message' => 'Ensure elements with aria-expanded="true" have visible expanded content',
                    'element' => $this->getElementInfo($element),
                ];
            }
        }

        // Check for aria-atomic
        $elementsWithAriaAtomic = $xpath->query('//*[@aria-atomic]');
        foreach ($elementsWithAriaAtomic as $element) {
            if (!$element->hasAttribute('aria-live')) {
                $problems[] = [
                    'message' => 'aria-atomic is used without aria-live (WCAG 2.0 Level AA)',
                    'element' => $this->getElementInfo($element),
                ];
                $improvements[] = [
                    'message' => 'Use aria-atomic in conjunction with aria-live for dynamic content updates',
                    'element' => $this->getElementInfo($element),
                ];
            }
        }

        // Check for aria-autocomplete
        $inputsWithAriaAutocomplete = $xpath->query('//input[@aria-autocomplete]');
        foreach ($inputsWithAriaAutocomplete as $input) {
            $autocompleteValue = $input->getAttribute('aria-autocomplete');
            if ($autocompleteValue !== 'none' && !$input->hasAttribute('aria-expanded')) {
                $problems[] = [
                    'message' => "aria-autocomplete='$autocompleteValue' is used without aria-expanded (WCAG 2.0 Level AA)",
                    'element' => $this->getElementInfo($input),
                ];
                $improvements[] = [
                    'message' => 'Use aria-expanded with aria-autocomplete when autocomplete suggestions are available',
                    'element' => $this->getElementInfo($input),
                ];
            }
        }

        // Check for aria-modal
        $elementsWithAriaModal = $xpath->query('//*[@aria-modal="true"]');
        foreach ($elementsWithAriaModal as $element) {
            if (!$element->hasChildNodes()) {
                $problems[] = [
                    'message' => 'Elements with aria-modal="true" do not behave as modal dialogs (WCAG 2.0 Level AA)',
                    'element' => $this->getElementInfo($element),
                ];
                $improvements[] = [
                    'message' => 'Ensure elements with aria-modal="true" are properly implemented as modal dialogs',
                    'element' => $this->getElementInfo($element),
                ];
            }
        }

        // Check for aria-haspopup
        $elementsWithAriaHasPopup = $xpath->query('//*[@aria-haspopup]');
        foreach ($elementsWithAriaHasPopup as $element) {
            $hasPopupValue = $element->getAttribute('aria-haspopup');
            if (!in_array($hasPopupValue, ['true', 'menu', 'listbox', 'tree', 'grid', 'dialog'], true)) {
                $problems[] = [
                    'message' => "Invalid aria-haspopup attribute value: '$hasPopupValue' (WCAG 2.0 Level AA)",
                    'element' => $this->getElementInfo($element),
                ];
                $improvements[] = [
                    'message' => 'Use only valid aria-haspopup values (true, menu, listbox, tree, grid, dialog)',
                    'element' => $this->getElementInfo($element),
                ];
            }
        }
    }

    protected function performLevelAAAChecks(DOMXPath $xpath, array &$problems, array &$improvements): void
    {
        // Check for proper use of aria-describedby
        $elementsWithAriaDescribedby = $xpath->query('//*[@aria-describedby]');
        foreach ($elementsWithAriaDescribedby as $element) {
            $describedById = $element->getAttribute('aria-describedby');
            $describedElement = $xpath->query("//*[@id='$describedById']")->item(0);
            if (!$describedElement) {
                $problems[] = [
                    'message' => "Element referenced by aria-describedby does not exist: '$describedById' (WCAG 2.0 Level AAA)",
                    'element' => $this->getElementInfo($element),
                ];
                $improvements[] = [
                    'message' => 'Ensure elements referenced by aria-describedby exist and contain descriptive text',
                    'element' => $this->getElementInfo($element),
                ];
            } elseif (strlen(trim($describedElement->textContent)) < 10) {
                $problems[] = [
                    'message' => 'Description text for aria-describedby is too short (WCAG 2.0 Level AAA)',
                    'element' => $this->getElementInfo($element),
                ];
                $improvements[] = [
                    'message' => 'Provide more detailed description text for elements referenced by aria-describedby',
                    'element' => $this->getElementInfo($element),
                ];
            }
        }

        $elementsWithAriaKeyShortcuts = $xpath->query('//*[@aria-keyshortcuts]');
        foreach ($elementsWithAriaKeyShortcuts as $element) {
            $shortcuts = preg_split('/\s+/', $element->getAttribute('aria-keyshortcuts'));
            foreach ($shortcuts as $shortcut) {
                if (strlen($shortcut) < 2) {
                    $problems[] = [
                        'message' => "aria-keyshortcuts value '$shortcut' is too short (WCAG 2.0 Level AAA)",
                        'element' => $this->getElementInfo($element),
                    ];
                    $improvements[] = [
                        'message' => 'Ensure aria-keyshortcuts values are descriptive and easy to understand',
                        'element' => $this->getElementInfo($element),
                    ];
                }
            }
        }

        // Check for aria-roledescription
        $elementsWithAriaRoleDescription = $xpath->query('//*[@aria-roledescription]');
        foreach ($elementsWithAriaRoleDescription as $element) {
            $roledescription = $element->getAttribute('aria-roledescription');
            if (strlen($roledescription) < 5) {
                $problems[] = [
                    'message' => "aria-roledescription value is too short: '$roledescription' (WCAG 2.0 Level AAA)",
                    'element' => $this->getElementInfo($element),
                ];
                $improvements[] = [
                    'message' => 'Provide more descriptive aria-roledescription values',
                    'element' => $this->getElementInfo($element),
                ];
            }
        }

        // Check for aria-valuetext with aria-valuenow
        $elementsWithAriaValueNow = $xpath->query('//*[@aria-valuenow]');
        foreach ($elementsWithAriaValueNow as $element) {
            if (!$element->hasAttribute('aria-valuetext')) {
                $problems[] = [
                    'message' => 'aria-valuenow is used without aria-valuetext (WCAG 2.0 Level AAA)',
                    'element' => $this->getElementInfo($element),
                ];
                $improvements[] = [
                    'message' => 'Provide aria-valuetext along with aria-valuenow for better accessibility of range widgets',
                    'element' => $this->getElementInfo($element),
                ];
            }
        }

        // Check for aria-required on form fields
        $requiredFormFields = $xpath->query('//input[@required]|//select[@required]|//textarea[@required]');
        foreach ($requiredFormFields as $field) {
            if (!$field->hasAttribute('aria-required')) {
                $problems[] = [
                    'message' => 'Required form field does not have aria-required="true" (WCAG 2.0 Level AAA)',
                    'element' => $this->getElementInfo($field),
                ];
                $improvements[] = [
                    'message' => 'Add aria-required="true" to all required form fields',
                    'element' => $this->getElementInfo($field),
                ];
            }
        }

        // Check for aria-invalid on form fields
        $formFields = $xpath->query('//input|//select|//textarea');
        foreach ($formFields as $field) {
            if (!$field->hasAttribute('aria-invalid')) {
                $problems[] = [
                    'message' => 'Form field does not use aria-invalid attribute (WCAG 2.0 Level AAA)',
                    'element' => $this->getElementInfo($field),
                ];
                $improvements[] = [
                    'message' => 'Add aria-invalid attribute to form fields to indicate validation state',
                    'element' => $this->getElementInfo($field),
                ];
            }
        }

        // Check for aria-details
        $elementsWithAriaDetails = $xpath->query('//*[@aria-details]');
        foreach ($elementsWithAriaDetails as $element) {
            $detailsId = $element->getAttribute('aria-details');
            if (!$xpath->query("//*[@id='$detailsId']")->length) {
                $problems[] = [
                    'message' => "Element referenced by aria-details does not exist: '$detailsId' (WCAG 2.0 Level AAA)",
                    'element' => $this->getElementInfo($element),
                ];
                $improvements[] = [
                    'message' => 'Ensure elements referenced by aria-details exist in the document',
                    'element' => $this->getElementInfo($element),
                ];
            }
        }

        // Check for aria-current
        $elementsWithAriaCurrent = $xpath->query('//*[@aria-current]');
        foreach ($elementsWithAriaCurrent as $element) {
            $currentValue = $element->getAttribute('aria-current');
            if (!in_array($currentValue, ['page', 'step', 'location', 'date', 'time', 'true', 'false'], true)) {
                $problems[] = [
                    'message' => "Invalid aria-current attribute value: '$currentValue' (WCAG 2.0 Level AAA)",
                    'element' => $this->getElementInfo($element),
                ];
                $improvements[] = [
                    'message' => 'Use only valid aria-current values (page, step, location, date, time, true, false)',
                    'element' => $this->getElementInfo($element),
                ];
            }
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

    protected function getValidRoles(): array
    {
        return [
            'alert',
            'alertdialog',
            'application',
            'article',
            'banner',
            'button',
            'cell',
            'checkbox',
            'columnheader',
            'combobox',
            'complementary',
            'contentinfo',
            'definition',
            'dialog',
            'directory',
            'document',
            'feed',
            'figure',
            'form',
            'grid',
            'gridcell',
            'group',
            'heading',
            'img',
            'link',
            'list',
            'listbox',
            'listitem',
            'log',
            'main',
            'marquee',
            'math',
            'menu',
            'menubar',
            'menuitem',
            'menuitemcheckbox',
            'menuitemradio',
            'navigation',
            'none',
            'note',
            'option',
            'presentation',
            'progressbar',
            'radio',
            'radiogroup',
            'region',
            'row',
            'rowgroup',
            'rowheader',
            'scrollbar',
            'search',
            'searchbox',
            'separator',
            'slider',
            'spinbutton',
            'status',
            'switch',
            'tab',
            'table',
            'tablist',
            'tabpanel',
            'term',
            'textbox',
            'timer',
            'toolbar',
            'tooltip',
            'tree',
            'treegrid',
            'treeitem',
        ];
    }
}