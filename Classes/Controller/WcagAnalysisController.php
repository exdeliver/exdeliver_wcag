<?php

namespace Exdeliver\ExdeliverWcag\Controller;

use Exdeliver\ExdeliverWcag\Service\WcagAnalysisService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerInterface;

class WcagAnalysisController implements ControllerInterface
{
    protected ModuleTemplateFactory $moduleTemplateFactory;
    protected WcagAnalysisService $wcagAnalysisService;
    protected PageRenderer $pageRenderer;

    public function __construct(
        ModuleTemplateFactory $moduleTemplateFactory,
        WcagAnalysisService $wcagAnalysisService,
        PageRenderer $pageRenderer
    ) {
        $this->moduleTemplateFactory = $moduleTemplateFactory;
        $this->wcagAnalysisService = $wcagAnalysisService;
        $this->pageRenderer = $pageRenderer;
    }

    public function analyzeAction(ServerRequestInterface $request): ResponseInterface
    {
        $pageUid = (int)($request->getParsedBody()['pageUid'] ?? $request->getQueryParams()['pageUid'] ?? 0);
        $analysisResult = $this->wcagAnalysisService->analyzePageWcag($pageUid);

        // Prepare the response data
        $responseData = [
            'success' => true,
            'results' => $analysisResult,
        ];

        // Return a JSON response
        return new JsonResponse($responseData);
    }

    public function listAction(ServerRequestInterface $request): ResponseInterface
    {
        $this->pageRenderer->loadJavaScriptModule('@exdeliver/exdeliver-wcag/WcagAnalysis.js');

        $pageUid = (int)($request->getQueryParams()['id'] ?? 0);
        $page = $this->getPageInfo($pageUid);

        $moduleTemplate = $this->moduleTemplateFactory->create($request);
        $moduleTemplate->assign('page', $page);
        $moduleTemplate->assign('pageUid', $pageUid);
        $moduleTemplate->assign('conformanceLevels', ['A', 'AA', 'AAA']);
        $moduleTemplate->assign('currentConformanceLevel', $page['tx_exdeliverwcag_conformance_level'] ?? 'AA');
        $moduleTemplate->assign('readabilityScore', $page['tx_exdeliverwcag_readability_score'] ?? 0);
        $moduleTemplate->assign('problems', json_decode($page['tx_exdeliverwcag_problems'] ?? '[]', true));
        $moduleTemplate->assign('improvements', json_decode($page['tx_exdeliverwcag_improvements'] ?? '[]', true));

        return $moduleTemplate->renderResponse('WcagAnalysis/List');
    }

    private function getPageInfo(int $pageUid): array
    {
        if ($pageUid === 0) {
            return [];
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');

        return $queryBuilder
            ->select('*')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($pageUid, \PDO::PARAM_INT))
            )
            ->executeQuery()
            ->fetchAssociative() ?: [];
    }

    public function processRequest(ServerRequestInterface $request): ResponseInterface
    {
        $action = $request->getQueryParams()['action'] ?? 'list';

        return match ($action) {
            'analyze' => $this->analyzeAction($request),
            default => $this->listAction($request),
        };
    }
}
