<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\PageProvider;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use TYPO3\CMS\Backend\View\BackendLayout\BackendLayout;
use TYPO3\CMS\Backend\View\BackendLayout\BackendLayoutCollection;
use TYPO3\CMS\Backend\View\BackendLayout\DataProviderContext;
use TYPO3\CMS\Backend\View\BackendLayout\DataProviderInterface;
use TYPO3\CMS\Backend\View\BackendLayout\DefaultDataProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

class BackendLayoutDataProvider extends DefaultDataProvider implements DataProviderInterface
{
    protected ObjectManagerInterface $objectManager;
    protected FluxService $configurationService;
    protected WorkspacesAwareRecordService $recordService;

    public function injectObjectManager(ObjectManagerInterface $objectManager): void
    {
        $this->objectManager = $objectManager;
    }

    public function injectConfigurationService(FluxService $configurationService): void
    {
        $this->configurationService = $configurationService;
    }

    public function injectWorkspacesAwareRecordService(WorkspacesAwareRecordService $workspacesAwareRecordService): void
    {
        $this->recordService = $workspacesAwareRecordService;
    }

    public function __construct()
    {
        /** @var ObjectManagerInterface $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->injectObjectManager($objectManager);

        /** @var FluxService $fluxService */
        $fluxService = $this->objectManager->get(FluxService::class);
        $this->injectConfigurationService($fluxService);

        /** @var WorkspacesAwareRecordService $workspacesAwareRecordService */
        $workspacesAwareRecordService = $this->objectManager->get(WorkspacesAwareRecordService::class);
        $this->injectWorkspacesAwareRecordService($workspacesAwareRecordService);
    }

    public function addBackendLayouts(
        DataProviderContext $dataProviderContext,
        BackendLayoutCollection $backendLayoutCollection
    ): void {
        $backendLayout = $this->getBackendLayout('grid', $dataProviderContext->getPageId());
        if ($backendLayout) {
            $backendLayoutCollection->add($backendLayout);
        }
    }

    /**
     * Gets a backend layout by (regular) identifier.
     *
     * @param string $identifier
     * @param integer $pageId
     */
    public function getBackendLayout($identifier, $pageId): ?BackendLayout
    {
        $emptyLayout = $this->createBackendLayoutInstance($identifier, 'Empty', '');
        $record = $this->recordService->getSingle('pages', '*', $pageId);
        if (null === $record) {
            return $emptyLayout;
        }
        $provider = $this->resolveProvider($record);
        if (!$provider instanceof PageProvider) {
            return $emptyLayout;
        }
        $grid = $provider->getGrid($record);
        return $grid->buildBackendLayout(0);
    }

    protected function resolveProvider(array $record): ?ProviderInterface
    {
        $record = $this->recordService->getSingle('pages', '*', $record['uid']);

        // Stop processing if no template configured in rootline
        if (null === $record) {
            return null;
        }

        return $this->configurationService->resolvePageProvider($record);
    }

    /**
     * @param string|array $configuration
     * @codeCoverageIgnore
     */
    protected function createBackendLayoutInstance(string $identifier, string $title, $configuration): BackendLayout
    {
        return new BackendLayout($identifier, 'Empty', '');
    }
}
