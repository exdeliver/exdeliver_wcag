<?php

namespace Exdeliver\ExdeliverWcag\EventListener;

use Exdeliver\ExdeliverWcag\Service\WcagAnalysisService;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\DataHandling\DataHandler;

class PerformWcagAnalysisListener
{
    use LoggerAwareTrait;

    protected WcagAnalysisService $wcagAnalysisService;

    public function __construct(WcagAnalysisService $wcagAnalysisService)
    {
        $this->wcagAnalysisService = $wcagAnalysisService;
    }

    public function __invoke(DataHandler $dataHandler): void
    {
        // Handle new and updated pages
        if (isset($dataHandler->datamap['pages'])) {
            foreach ($dataHandler->datamap['pages'] as $pageUid => $pageData) {
                if (!is_array($pageData)) {
                    continue;
                }
                try {
                    $this->wcagAnalysisService->analyzePageWcag((int)$pageUid);
                } catch (\Exception $e) {
                    $this->logger->error('Failed to analyze page WCAG compliance for page UID ' . $pageUid . ': ' . $e->getMessage());
                }
            }
        }

        // Handle deleted pages (if needed)
        if (isset($dataHandler->deleteRecords['pages'])) {
            foreach ($dataHandler->deleteRecords['pages'] as $pageUid => $value) {
                // Perform any necessary cleanup for deleted pages
                // For example, you might want to remove analysis data associated with the page
                try {
                    $this->wcagAnalysisService->cleanupPageWcag((int)$pageUid);
                } catch (\Exception $e) {
                    $this->logger->error('Failed to clean up WCAG analysis data for deleted page UID ' . $pageUid . ': ' . $e->getMessage());
                }
            }
        }
    }
}
