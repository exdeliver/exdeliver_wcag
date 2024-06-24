<?php

namespace Exdeliver\ExdeliverWcag\Service;

use Exdeliver\ExdeliverWcag\WcagAnalysis\AriaAttributesAnalyzer;
use Exdeliver\ExdeliverWcag\WcagAnalysis\ButtonsAndAnchorsAnalyzer;
use Exdeliver\ExdeliverWcag\WcagAnalysis\HeadingStructureAnalyzer;
use Exdeliver\ExdeliverWcag\WcagAnalysis\ImageAndSvgAnalyzer;
use Exdeliver\ExdeliverWcag\WcagAnalysis\ReadabilityAnalyzer;
use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Chrome\ChromeDriverService;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Routing\PageRouter;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class WcagAnalysisService
{
    use LoggerAwareTrait;

    protected ConnectionPool $connectionPool;
    protected SiteFinder $siteFinder;
    protected ExtensionConfiguration $extensionConfiguration;

    public function __construct(
        ConnectionPool $connectionPool,
        SiteFinder $siteFinder,
        ExtensionConfiguration $extensionConfiguration,
    ) {
        $this->connectionPool = $connectionPool;
        $this->siteFinder = $siteFinder;
        $this->extensionConfiguration = $extensionConfiguration;
    }

    public function analyzePageWcag(int $pageUid): array
    {
        $pageUrl = $this->getPageUrl($pageUid);
        $pageSource = $this->getPageSource($pageUrl);
        $conformanceLevel = $this->getPageConformanceLevel($pageUid);

        $analysisResult = $this->performWcagAnalysis($pageSource, $conformanceLevel);

        $this->saveAnalysisResult($pageUid, $analysisResult);

        return $analysisResult;
    }

    protected function getPageConformanceLevel(int $pageUid): string
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $result = $queryBuilder
            ->select('tx_exdeliverwcag_conformance_level')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($pageUid, \PDO::PARAM_INT))
            )
            ->executeQuery()
            ->fetchAssociative();

        return $result['tx_exdeliverwcag_conformance_level'] ?? 'AA';
    }

    protected function getPageUrl(int $pageUid): string
    {
        $site = $this->siteFinder->getSiteByPageId($pageUid);
        $pageRouter = GeneralUtility::makeInstance(PageRouter::class, $site);
        $uri = $pageRouter->generateUri($pageUid, ['_language' => 0]);

        return (string)$uri;
    }

    protected function getPageSource(string $url): string
    {
        // Get ChromeDriver options from extension configuration
        $chromeConfig = $this->extensionConfiguration->get('exdeliver_wcag', 'ChromeDriverOptions');

        // Set up the path to ChromeDriver
        $chromeDriverPath = Environment::getProjectPath() . '/drivers/chromedriver';

        // Check if ChromeDriver exists
        if (!file_exists($chromeDriverPath)) {
            throw new \RuntimeException('ChromeDriver not found at ' . $chromeDriverPath);
        }

        // Ensure ChromeDriver is executable (for Unix-like systems)
        if (PHP_OS_FAMILY !== 'Windows') {
            chmod($chromeDriverPath, 0755);
        }

        // Set up ChromeOptions
        $options = new ChromeOptions();
        $options->addArguments($chromeConfig['chromeOptions']);

        // Set up DesiredCapabilities
        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);

        // Set up ChromeDriverService
        $service = new ChromeDriverService(
            $chromeDriverPath,
            9515,
            [],
            null
        );

        try {
            // Start ChromeDriver
            $driver = ChromeDriver::start($capabilities, $service);

            // Navigate to the URL
            $driver->get($url);

            $pageSource = $driver->getPageSource();

            return $pageSource;
        } finally {
            // Ensure WebDriver is quit even if an exception occurs
            if (isset($driver)) {
                $driver->quit();
            }
            // Stop the ChromeDriver service
            if (isset($service)) {
                $service->stop();
            }
        }
    }

    private function ensureChromeDriverInstalled($driver): string
    {
        $installPath = Environment::getVarPath() . '/chrome-driver';
        $driverPath = $installPath . '/chromedriver';

        if (!$this->filesystem->exists($driverPath)) {
            $driverDownloader = $this->driverDownloaderFactory->createFromDriver($driver);
            $driverPath = $driverDownloader->download($driver, $installPath);
        }

        return $driverPath;
    }

    protected function performWcagAnalysis(string $pageSource, string $conformanceLevel): array
    {
        $analyzers = [
            new ImageAndSvgAnalyzer($conformanceLevel),
            new HeadingStructureAnalyzer($conformanceLevel),
            new ReadabilityAnalyzer($conformanceLevel),
            new AriaAttributesAnalyzer($conformanceLevel),
            new ButtonsAndAnchorsAnalyzer($conformanceLevel),
        ];

        $result = [
            'conformanceLevel' => $conformanceLevel,
            'readabilityScore' => 0,
            'problems' => [],
            'improvements' => [],
        ];

        foreach ($analyzers as $analyzer) {
            $analysisResult = $analyzer->analyze($pageSource);
            $result['problems'] = array_merge($result['problems'], $analysisResult['problems'] ?? []);
            $result['improvements'] = array_merge($result['improvements'], $analysisResult['improvements'] ?? []);
            if (isset($analysisResult['readabilityScore'])) {
                $result['readabilityScore'] = $analysisResult['readabilityScore'];
            }
        }

        return $result;
    }

    protected function saveAnalysisResult(int $pageUid, array $analysisResult): void
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder
            ->update('pages')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($pageUid, \PDO::PARAM_INT))
            )
            ->set('tx_exdeliverwcag_conformance_level', $analysisResult['conformanceLevel'])
            ->set('tx_exdeliverwcag_readability_score', $analysisResult['readabilityScore'])
            ->set('tx_exdeliverwcag_problems', json_encode($analysisResult['problems']))
            ->set('tx_exdeliverwcag_improvements', json_encode($analysisResult['improvements']));

        $queryBuilder->executeStatement();
    }

    public function cleanupPageWcag(int $pageUid): void
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder
            ->update('pages')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($pageUid, \PDO::PARAM_INT))
            )
            ->set('tx_exdeliverwcag_conformance_level', null)
            ->set('tx_exdeliverwcag_readability_score', null)
            ->set('tx_exdeliverwcag_problems', null)
            ->set('tx_exdeliverwcag_improvements', null)
            ->executeStatement();
    }
}
